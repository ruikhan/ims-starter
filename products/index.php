<?php
// products/index.php — Product listing
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
$pageTitle = 'Products';
require_once '../partials/header.php';

$search   = trim($_GET['search']   ?? '');
$catId    = (int)($_GET['cat']     ?? 0);
$status   = $_GET['status']        ?? '';

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = '(p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catId)  { $where[] = 'p.category_id = ?'; $params[] = $catId; }
if ($status) { $where[] = 'p.status = ?';       $params[] = $status; }

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="page-header">
  <div><h2>Products</h2><p>Manage your inventory items</p></div>
  <?php if (isAdmin()): ?>
  <a href="<?= BASE_URL ?>/products/add.php" class="btn btn-primary">
    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Product
  </a>
  <?php endif; ?>
</div>

<form method="GET" class="filter-bar">
  <div class="search-inline">
    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="text" name="search" placeholder="Search name or SKU…" value="<?= e($search) ?>"/>
  </div>
  <select name="cat" class="form-control">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
    <option value="<?= $c['id'] ?>" <?= $catId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-control">
    <option value="">All status</option>
    <option value="in_stock"     <?= $status==='in_stock'     ? 'selected':'' ?>>In stock</option>
    <option value="low_stock"    <?= $status==='low_stock'    ? 'selected':'' ?>>Low stock</option>
    <option value="out_of_stock" <?= $status==='out_of_stock' ? 'selected':'' ?>>Out of stock</option>
  </select>
  <button type="submit" class="btn btn-ghost">Filter</button>
  <a href="<?= BASE_URL ?>/products/index.php" class="btn btn-ghost">Reset</a>
</form>

<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Qty</th><th>Threshold</th><th>Status</th><?php if(isAdmin()):?><th>Actions</th><?php endif;?></tr></thead>
        <tbody>
        <?php if (empty($products)): ?>
        <tr><td colspan="8"><div class="empty-state"><p>No products found</p></div></td></tr>
        <?php else: foreach ($products as $p): ?>
        <tr>
          <td><span class="tbl-name"><?= e($p['name']) ?></span></td>
          <td><span class="tbl-mono"><?= e($p['sku']) ?></span></td>
          <td><?= e($p['category_name'] ?? '—') ?></td>
          <td><span class="tbl-mono"><?= formatMoney($p['price']) ?></span></td>
          <td><strong style="font-family:var(--font-mono)"><?= number_format($p['quantity']) ?></strong></td>
          <td><span class="tbl-mono"><?= $p['low_stock_threshold'] ?></span></td>
          <td><span class="badge <?= str_replace('_','-',$p['status']) ?>"><?= statusLabel($p['status']) ?></span></td>
          <?php if (isAdmin()): ?>
          <td><div class="tbl-actions">
            <a href="<?= BASE_URL ?>/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
              <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
            <form method="POST" action="<?= BASE_URL ?>/products/delete.php" style="display:inline"
                  onsubmit="return confirm('Delete <?= e(addslashes($p['name'])) ?>? This cannot be undone.')">
              <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete">
                <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
              </button>
            </form>
          </div></td>
          <?php endif; ?>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../partials/footer.php'; ?>