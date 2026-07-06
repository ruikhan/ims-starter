<?php
// admin/save-feature.php — Add a feature hotspot to a product (AJAX)
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']); exit;
}
csrf_verify();

$productId   = (int)($_POST['product_id']   ?? 0);
$label       = trim($_POST['label']         ?? '');
$description = trim($_POST['description']   ?? '');
$posX        = isset($_POST['pos_x']) ? (float)$_POST['pos_x'] : -1;
$posY        = isset($_POST['pos_y']) ? (float)$_POST['pos_y'] : -1;

if (!$productId)                    { echo json_encode(['success' => false, 'error' => 'No product specified.']); exit; }
if ($label === '')                  { echo json_encode(['success' => false, 'error' => 'Feature name is required.']); exit; }
if ($posX < 0 || $posX > 100 || $posY < 0 || $posY > 100) {
    echo json_encode(['success' => false, 'error' => 'Invalid hotspot position.']); exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$productId]);
if (!$stmt->fetch()) { echo json_encode(['success' => false, 'error' => 'Product not found.']); exit; }

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM product_features WHERE product_id = ?");
$countStmt->execute([$productId]);
$sort = (int)$countStmt->fetchColumn();

$stmt = $pdo->prepare("
    INSERT INTO product_features (product_id, label, description, pos_x, pos_y, sort_order)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$productId, $label, $description ?: null, $posX, $posY, $sort]);

echo json_encode([
    'success' => true,
    'id'      => (int)$pdo->lastInsertId(),
]);
