<?php
// includes/helpers.php — Shared utility functions

/**
 * Auto-calculate and update a product's stock status.
 * Call this after every stock change.
 */
function updateStockStatus(PDO $pdo, int $productId): void {
    $stmt = $pdo->prepare("SELECT quantity, low_stock_threshold FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $p = $stmt->fetch();
    if (!$p) return;

    if ($p['quantity'] <= 0) {
        $status = 'out_of_stock';
    } elseif ($p['quantity'] <= $p['low_stock_threshold']) {
        $status = 'low_stock';
    } else {
        $status = 'in_stock';
    }

    $pdo->prepare("UPDATE products SET status = ? WHERE id = ?")->execute([$status, $productId]);
}

/** Format number as Philippine Peso */
function formatMoney(float $n): string {
    return '₱' . number_format($n, 2);
}

/** Human-readable status label */
function statusLabel(string $status): string {
    return match($status) {
        'in_stock'     => 'In Stock',
        'low_stock'    => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        default        => ucfirst($status),
    };
}

/** Badge CSS class for status */
function statusClass(string $status): string {
    return match($status) {
        'in_stock'     => 'badge-success',
        'low_stock'    => 'badge-warning',
        'out_of_stock' => 'badge-danger',
        default        => 'badge-secondary',
    };
}

/** Sanitize output to prevent XSS */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** Redirect with flash message */
function redirectWith(string $url, string $type, string $msg): void {
    setFlash($type, $msg);
    header("Location: " . BASE_URL . $url);
    exit;
}

/** Count low/out-of-stock products (for sidebar badge) */
function getLowStockCount(PDO $pdo): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status IN ('low_stock','out_of_stock')")->fetchColumn();
}

/**
 * Get the interactive "feature hotspot" callouts for a product, in
 * display order. Each row has label / description / pos_x / pos_y
 * (percentages), used to place numbered markers over the product image
 * on the storefront. Returns [] if the product has none yet (the
 * storefront simply skips the hotspot layer in that case).
 */
function getProductFeatures(PDO $pdo, int $productId): array {
    $stmt = $pdo->prepare("
        SELECT id, label, description, pos_x, pos_y
        FROM product_features
        WHERE product_id = ?
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}
