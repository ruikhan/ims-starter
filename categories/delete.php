<?php
// categories/delete.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWith('/categories/index.php', 'danger', 'Invalid request method.');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
if (!$cat) redirectWith('/categories/index.php', 'danger', 'Category not found.');
$pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
redirectWith('/categories/index.php', 'success', "Category \"{$cat['name']}\" deleted. Products uncategorised.");