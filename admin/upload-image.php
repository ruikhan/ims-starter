<?php
// admin/upload-image.php — Admin product image upload (AJAX)
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']); exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
if (!$productId) { echo json_encode(['success' => false, 'error' => 'No product specified.']); exit; }

// Check product exists
$stmt = $pdo->prepare("SELECT id, name, image FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) { echo json_encode(['success' => false, 'error' => 'Product not found.']); exit; }

if (empty($_FILES['image'])) { echo json_encode(['success' => false, 'error' => 'No file uploaded.']); exit; }

$file    = $_FILES['image'];
$allowed = ['image/jpeg','image/png','image/webp','image/gif'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error.']); exit;
}
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, WebP, GIF allowed.']); exit;
}
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Max 5MB.']); exit;
}

// Delete old image
$uploadDir = dirname(__DIR__) . '/uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($product['image'] && file_exists($uploadDir . $product['image'])) {
    unlink($uploadDir . $product['image']);
}

// Save new image
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'product-' . $productId . '-' . time() . '.' . strtolower($ext);
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file.']); exit;
}

$pdo->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$filename, $productId]);
echo json_encode([
    'success'   => true,
    'filename'  => $filename,
    'image_url' => BASE_URL . '/uploads/products/' . $filename,
    'msg'       => 'Image uploaded successfully.'
]);