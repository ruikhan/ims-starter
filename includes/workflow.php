<?php
// includes/workflow.php — Order status state machine
//
// This is the single source of truth for which order-status transitions
// are allowed (e.g. you can't jump straight from "pending" to "delivered").
// The Python automation service exposes the same rules read-only at
// GET /workflow/next-statuses/{status} so both sides of the app agree on
// what "valid" means — see python-service/app/routers/workflow.py.

const ORDER_STATUS_TRANSITIONS = [
    'pending'    => ['confirmed', 'cancelled'],
    'confirmed'  => ['processing', 'cancelled'],
    'processing' => ['shipped', 'cancelled'],
    'shipped'    => ['delivered'],
    'delivered'  => [],
    'cancelled'  => [],
];

function isValidOrderTransition(string $from, string $to): bool {
    if ($from === $to) return true; // idempotent no-op, always allowed
    return in_array($to, ORDER_STATUS_TRANSITIONS[$from] ?? [], true);
}

function nextAllowedStatuses(string $from): array {
    return ORDER_STATUS_TRANSITIONS[$from] ?? [];
}

/**
 * Safely move an order to a new status:
 *   1. validates the transition against the state machine
 *   2. updates orders.status
 *   3. logs the change to order_status_log (audit trail)
 *   4. drops a row in notifications_queue so the Python service can act
 *      on it (email the customer, alert staff, etc.) without the PHP
 *      request having to wait on that work
 *
 * Run `database/workflow.sql` once before using this function.
 *
 * @return array{success: bool, error: ?string}
 */
function transitionOrder(PDO $pdo, int $orderId, string $newStatus, ?int $actorUserId = null): array {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$orderId]);
        $current = $stmt->fetchColumn();

        if ($current === false) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Order not found.'];
        }

        if (!isValidOrderTransition($current, $newStatus)) {
            $pdo->rollBack();
            return ['success' => false, 'error' => "Cannot move an order from \"$current\" to \"$newStatus\"."];
        }

        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);

        $pdo->prepare("
            INSERT INTO order_status_log (order_id, from_status, to_status, changed_by, changed_at)
            VALUES (?, ?, ?, ?, NOW())
        ")->execute([$orderId, $current, $newStatus, $actorUserId]);

        $pdo->prepare("
            INSERT INTO notifications_queue (type, payload, status, created_at)
            VALUES ('order_status_changed', ?, 'pending', NOW())
        ")->execute([json_encode([
            'order_id'   => $orderId,
            'from_status'=> $current,
            'to_status'  => $newStatus,
        ])]);

        $pdo->commit();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => 'Transition failed: ' . $e->getMessage()];
    }
}