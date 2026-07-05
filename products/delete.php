<?php
// products/delete.php — POST-only delete handler
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWith('/products/index.php', 'danger', 'Invalid request method.');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if (!$id) { redirectWith('/products/index.php', 'danger', 'Invalid product.'); }

$stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { redirectWith('/products/index.php', 'danger', 'Product not found.'); }

$pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
redirectWith('/products/index.php', 'success', "Product \"{$product['name']}\" deleted.");