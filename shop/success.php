<?php
// shop/success.php — Order confirmation
require_once '../config/db.php';
require_once '../includes/helpers.php';

$code  = trim($_GET['code'] ?? '');
$order = null;
$items = [];

if ($code) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
    $stmt->execute([$code]);
    $order = $stmt->fetch();
    if ($order) {
        $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$order['id']]);
        $items = $items->fetchAll();
    }
}
$shopName = 'CGShop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Order Confirmed — <?= $shopName ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/shop.css"/>
</head>
<body>
<nav class="shop-nav">
  <a href="<?= BASE_URL ?>/shop/index.php" class="nav-brand">
    <div class="nav-brand-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    </div>
    <?= $shopName ?>
  </a>
</nav>

<div class="success-wrap">
  <?php if ($order): ?>
  <div class="success-card">
    <div class="success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h1 style="font-family:var(--font-head);font-size:26px;font-weight:700;margin-bottom:8px">Order confirmed!</h1>
    <p style="color:var(--text2);font-size:14px;margin-bottom:4px">Thank you, <strong style="color:var(--text)"><?= e($order['customer_name']) ?></strong></p>
    <p style="color:var(--text2);font-size:14px">We'll contact you at <strong style="color:var(--text)"><?= e($order['customer_email']) ?></strong></p>

    <div class="success-code"><?= e($order['order_code']) ?></div>

    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--r-sm);padding:16px;margin-bottom:24px;text-align:left">
      <?php foreach ($items as $item): ?>
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
        <span style="color:var(--text2)"><?= e($item['product_name']) ?> ×<?= $item['quantity'] ?></span>
        <span style="font-family:var(--font-mono);color:var(--accent)"><?= formatMoney($item['subtotal']) ?></span>
      </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;padding:10px 0 0;font-family:var(--font-head);font-weight:700;font-size:16px">
        <span>Total</span>
        <span style="color:var(--accent)"><?= formatMoney($order['total_amount']) ?></span>
      </div>
    </div>

    <div style="background:rgba(34,211,160,.06);border:1px solid rgba(34,211,160,.15);border-radius:var(--r-sm);padding:14px;margin-bottom:24px;font-size:13px;color:var(--text2);text-align:left">
      <strong style="color:var(--accent)">Delivery address</strong><br/>
      <?= e($order['customer_address']) ?>
      <?php if ($order['notes']): ?>
      <br/><br/><strong style="color:var(--text2)">Notes:</strong> <?= e($order['notes']) ?>
      <?php endif; ?>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-primary">Continue shopping</a>
      <a href="<?= BASE_URL ?>/shop/index.php" class="btn btn-outline">Back to home</a>
    </div>
  </div>
  <?php else: ?>
  <div class="success-card">
    <h2 style="font-family:var(--font-head)">Order not found</h2>
    <p style="color:var(--text2);margin:12px 0 24px">The order code is invalid or expired.</p>
    <a href="<?= BASE_URL ?>/shop/index.php" class="btn btn-primary">Go to shop</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>