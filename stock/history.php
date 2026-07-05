<?php
// stock/history.php — Transaction history
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
$pageTitle = 'Transaction History';

$type      = $_GET['type']    ?? '';
$productId = (int)($_GET['product'] ?? 0);

$where  = ['1=1'];
$params = [];
if ($type)      { $where[] = 't.type = ?';       $params[] = $type; }
if ($productId) { $where[] = 't.product_id = ?'; $params[] = $productId; }

$transactions = $pdo->prepare("
    SELECT t.*, p.name AS product_name, p.sku, u.name AS user_name
    FROM transactions t
    JOIN products p ON t.product_id = p.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY t.created_at DESC
    LIMIT 200
");
$transactions->execute($params);
$transactions = $transactions->fetchAll();

$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll();
require_once '../partials/header.php';
?>
<div class="page-header">
  <div><h2>Transaction History</h2><p>Complete log of all stock movements</p></div>
</div>

<form method="GET" class="filter-bar">
  <select name="type" class="form-control">
    <option value="">All types</option>
    <option value="in"  <?= $type==='in'  ? 'selected':'' ?>>Stock In</option>
    <option value="out" <?= $type==='out' ? 'selected':'' ?>>Stock Out</option>
  </select>
  <select name="product" class="form-control">
    <option value="">All products</option>
    <?php foreach ($products as $p): ?>
    <option value="<?= $p['id'] ?>" <?= $productId==$p['id'] ? 'selected':'' ?>><?= e($p['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-ghost">Filter</button>
  <a href="<?= BASE_URL ?>/stock/history.php" class="btn btn-ghost">Reset</a>
</form>

<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Date &amp; Time</th><th>Product</th><th>SKU</th><th>Type</th><th>Qty</th><th>Notes</th><th>By</th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
        <tr><td colspan="8"><div class="empty-state"><p>No transactions found</p></div></td></tr>
        <?php else: foreach ($transactions as $t): ?>
        <tr>
          <td><span class="tbl-mono">#<?= $t['id'] ?></span></td>
          <td><span class="tbl-mono"><?= e($t['created_at']) ?></span></td>
          <td><span class="tbl-name"><?= e($t['product_name']) ?></span></td>
          <td><span class="tbl-mono"><?= e($t['sku']) ?></span></td>
          <td><span class="badge <?= $t['type'] ?>"><?= $t['type'] === 'in' ? 'Stock In' : 'Stock Out' ?></span></td>
          <td><strong style="font-family:var(--font-mono);color:<?= $t['type']==='in' ? 'var(--accent)':'var(--red)' ?>">
            <?= $t['type']==='in' ? '+':'-' ?><?= number_format($t['quantity']) ?>
          </strong></td>
          <td style="color:var(--text2)"><?= e($t['notes'] ?? '—') ?></td>
          <td style="color:var(--text2)"><?= e($t['user_name'] ?? 'System') ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once '../partials/footer.php'; ?>
