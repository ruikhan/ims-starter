<?php
// shop/place-order.php — JSON API: creates order + deducts stock
require_once '../config/db.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$name    = trim($input['name']    ?? '');
$email   = trim($input['email']   ?? '');
$phone   = trim($input['phone']   ?? '');
$address = trim($input['address'] ?? '');
$notes   = trim($input['notes']   ?? '');
$cart    = $input['cart']         ?? [];

// Validate
if (!$name || !$email || !$address || empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit;
}

$pdo->beginTransaction();
try {
    $total = 0;
    $items = [];

    // Validate stock for each cart item
    foreach ($cart as $cartItem) {
        $pid = (int)($cartItem['id']  ?? 0);
        $qty = (int)($cartItem['qty'] ?? 0);
        if (!$pid || $qty < 1) continue;

        $stmt = $pdo->prepare("SELECT id, name, price, quantity, status FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();

        if (!$product) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => "Product not found."]);
            exit;
        }
        if ($product['quantity'] < $qty) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => "Insufficient stock for \"{$product['name']}\". Only {$product['quantity']} available."]);
            exit;
        }

        $subtotal = $product['price'] * $qty;
        $total   += $subtotal;
        $items[]  = [
            'product_id'   => $pid,
            'product_name' => $product['name'],
            'quantity'     => $qty,
            'unit_price'   => $product['price'],
            'subtotal'     => $subtotal,
        ];
    }

    if (empty($items)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Cart is empty.']);
        exit;
    }

    // Generate unique order code
    $orderCode = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Insert order
    $pdo->prepare("INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, customer_address, total_amount, notes)
                   VALUES (?,?,?,?,?,?,?)")
        ->execute([$orderCode, $name, $email, $phone, $address, $total, $notes ?: null]);
    $orderId = $pdo->lastInsertId();

    // Insert order items + deduct stock + log transactions
    foreach ($items as $item) {
        // Order item
        $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal)
                       VALUES (?,?,?,?,?,?)")
            ->execute([$orderId, $item['product_id'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['subtotal']]);

        // Deduct stock
        $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
            ->execute([$item['quantity'], $item['product_id']]);

        // Log as stock-out transaction
        $pdo->prepare("INSERT INTO transactions (product_id, type, quantity, notes, user_id)
                       VALUES (?, 'out', ?, ?, NULL)")
            ->execute([$item['product_id'], $item['quantity'], "Customer order $orderCode"]);

        // Update status
        updateStockStatus($pdo, $item['product_id']);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'order_code' => $orderCode, 'total' => $total]);

} catch (Exception $ex) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Order failed: ' . $ex->getMessage()]);
}
