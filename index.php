<?php
// index.php — Dashboard
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
$pageTitle = 'Dashboard';
require_once 'partials/header.php';

// ── Stats ─────────────────────────────────────────────────
$totalProducts  = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$inStock        = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='in_stock'")->fetchColumn();
$lowStock       = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='low_stock'")->fetchColumn();
$outOfStock     = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='out_of_stock'")->fetchColumn();
$totalValue     = (float)$pdo->query("SELECT SUM(price * quantity) FROM products")->fetchColumn();
$totalTx        = (int)$pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

// ── Recent transactions ───────────────────────────────────
$recentTx = $pdo->query("
    SELECT t.*, p.name AS product_name, u.name AS user_name
    FROM transactions t
    JOIN products p ON t.product_id = p.id
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC LIMIT 8
")->fetchAll();

// ── Low stock products ────────────────────────────────────
$lowProducts = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status IN ('low_stock','out_of_stock')
    ORDER BY p.quantity ASC LIMIT 6
")->fetchAll();

// ── All products (overview table) ─────────────────────────
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.status ASC, p.name ASC
")->fetchAll();
?>

<div class="page-header">
  <div>
    <h2>Overview</h2>
    <p>Welcome back, <strong><?= e(currentUserName()) ?></strong> — <?= date('l, F j, Y') ?></p>
  </div>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card green">
    <div class="stat-header">
      <div class="stat-icon green">
        <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
      </div>
    </div>
    <div class="stat-value"><?= $totalProducts ?></div>
    <div class="stat-label">Total products</div>
  </div>
  <div class="stat-card amber">
    <div class="stat-header">
      <div class="stat-icon amber">
        <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
    </div>
    <div class="stat-value"><?= $lowStock + $outOfStock ?></div>
    <div class="stat-label">Need attention</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-header">
      <div class="stat-icon blue">
        <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
      </div>
    </div>
    <div class="stat-value"><?= $totalTx ?></div>
    <div class="stat-label">Total transactions</div>
  </div>
  <div class="stat-card green">
    <div class="stat-header">
      <div class="stat-icon green">
        <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      </div>
    </div>
    <div class="stat-value" style="font-size:20px"><?= formatMoney($totalValue) ?></div>
    <div class="stat-label">Inventory value</div>
  </div>
</div>

<!-- Activity + Low Stock -->
<div class="grid-3-1">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Recent activity</span>
      <a href="<?= BASE_URL ?>/stock/history.php" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <div class="card-body">
      <?php foreach ($recentTx as $tx): ?>
      <div class="activity-item">
        <div class="activity-dot <?= $tx['type'] ?>"></div>
        <div class="activity-text">
          <span class="activity-time"><?= e($tx['created_at']) ?></span><br/>
          <strong><?= $tx['type'] === 'in' ? '+' : '−' ?><?= $tx['quantity'] ?></strong>
          <strong><?= e($tx['product_name']) ?></strong>
          <span><?= $tx['type'] === 'in' ? 'added to stock' : 'removed from stock' ?></span>
          <?php if ($tx['notes']): ?><span style="color:var(--text3)"> · <?= e($tx['notes']) ?></span><?php endif; ?>
          <div style="font-size:11px;color:var(--text3);margin-top:2px">by <?= e($tx['user_name'] ?? 'System') ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($recentTx)): ?>
      <div class="empty-state"><p>No transactions yet</p></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Low stock alerts</span>
      <?php if ($lowStock + $outOfStock > 0): ?>
      <span class="badge low-stock"><?= $lowStock + $outOfStock ?></span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php foreach ($lowProducts as $p): ?>
      <div class="lowstock-item">
        <div class="lowstock-icon">
          <svg class="ico-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <div class="lowstock-info">
          <div class="lowstock-name"><?= e($p['name']) ?></div>
          <div class="lowstock-cat"><?= e($p['category_name'] ?? 'Uncategorised') ?></div>
        </div>
        <div>
          <div class="lowstock-qty <?= $p['quantity'] == 0 ? 'zero' : 'warn' ?>">
            <?= $p['quantity'] == 0 ? 'Out' : $p['quantity'] ?>
          </div>
          <div class="lowstock-cat">/ <?= $p['low_stock_threshold'] ?> min</div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($lowProducts)): ?>
      <div class="empty-state"><p>All products well stocked</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Products overview table -->
<div class="card">
  <div class="card-header">
    <span class="card-title">Product status overview</span>
    <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost btn-sm">Manage products</a>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th><th>Stock level</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p):
          $pct = $p['quantity'] === 0 ? 0 : min(100, round($p['quantity'] / max($p['quantity'], $p['low_stock_threshold'] * 3) * 100));
          $barClass = match($p['status']) { 'in_stock' => 'green', 'low_stock' => 'amber', default => 'red' };
        ?>
        <tr>
          <td><span class="tbl-name"><?= e($p['name']) ?></span></td>
          <td><span class="tbl-mono"><?= e($p['sku']) ?></span></td>
          <td><?= e($p['category_name'] ?? '—') ?></td>
          <td><span class="tbl-mono"><?= formatMoney($p['price']) ?></span></td>
          <td><strong style="font-family:var(--font-mono)"><?= number_format($p['quantity']) ?></strong></td>
          <td><span class="badge <?= str_replace('_','-',$p['status']) ?>"><?= statusLabel($p['status']) ?></span></td>
          <td style="min-width:100px">
            <div class="progress-bar"><div class="progress-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div></div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once 'partials/footer.php'; ?>
