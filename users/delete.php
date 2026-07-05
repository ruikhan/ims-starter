<?php
// users/delete.php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWith('/users/index.php', 'danger', 'Invalid request method.');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);

// Cannot delete root admin or yourself
if ($id === 1)                  redirectWith('/users/index.php', 'danger', 'The root admin account cannot be deleted.');
if ($id === currentUserId())    redirectWith('/users/index.php', 'danger', 'You cannot delete your own account.');

$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirectWith('/users/index.php', 'danger', 'User not found.');

$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
redirectWith('/users/index.php', 'success', "User \"{$user['name']}\" removed.");