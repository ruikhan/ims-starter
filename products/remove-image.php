<?php
// products/remove-image.php — Remove a product's image
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$id = (int)($_POST['id'] ?? 0);
if (!$id) { redirectWith('/products/index.php', 'danger', 'Invalid product.'); }

$stmt = $pdo->prepare("SELECT id, name, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { redirectWith('/products/index.php', 'danger', 'Product not found.'); }

if ($product['image']) {
    $uploadDir = dirname(__DIR__) . '/uploads/products/';
    $filePath  = $uploadDir . $product['image'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    $pdo->prepare("UPDATE products SET image = NULL WHERE id = ?")->execute([$id]);
}

redirectWith('/products/edit.php?id=' . $id, 'success', 'Product image removed.');