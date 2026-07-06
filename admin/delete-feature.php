<?php
// admin/delete-feature.php — Remove a feature hotspot (AJAX)
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']); exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'error' => 'Invalid hotspot id.']); exit; }

$pdo->prepare("DELETE FROM product_features WHERE id = ?")->execute([$id]);
echo json_encode(['success' => true]);
