<?php
// shop/catalog.php — Full product catalog
require_once '../config/db.php';
require_once '../includes/helpers.php';

$catId  = (int)($_GET['cat']    ?? 0);
$search = trim($_GET['q']       ?? '');

$where  = ["p.status != 'out_of_stock'"];
$params = [];
if ($catId)  { $where[] = 'p.category_id = ?'; $params[] = $catId; }
if ($search) { $where[] = '(p.name LIKE ? OR p.description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$sql = "SELECT p.*, c.name AS category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where) . " ORDER BY p.name ASC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS cnt FROM categories c
    LEFT JOIN products p ON p.category_id=c.id AND p.status!='out_of_stock'
    GROUP BY c.id HAVING cnt>0 ORDER BY c.name
")->fetchAll();

$activeCat = $catId ? $pdo->prepare("SELECT name FROM categories WHERE id=?") : null;
if ($activeCat) { $activeCat->execute([$catId]); $activeCat = $activeCat->fetchColumn(); }
$shopName = 'CGShop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Shop — <?= $shopName ?></title>
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
  <div class="nav-links">
    <a href="<?= BASE_URL ?>/shop/index.php">Home</a>
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="active">Shop</a>
  </div>
  <!-- <div class="nav-actions">
    <button class="cart-btn" onclick="toggleCart()">
      <svg class="ico" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Cart <span class="cart-count" id="cart-count">0</span>
    </button>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Admin</a>
  </div> -->
</nav>

<section class="section">
  <div class="section-header">
    <div>
      <div class="section-label"><?= $activeCat ? e($activeCat) : 'All products' ?></div>
      <div class="section-title"><?= $search ? 'Results for "'.e($search).'"' : ($activeCat ? e($activeCat) : 'Our catalogue') ?></div>
      <div class="section-sub"><?= count($products) ?> product<?= count($products)!=1?'s':'' ?> available</div>
    </div>
  </div>

  <!-- Filters -->
  <div class="shop-filters">
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="filter-chip <?= !$catId ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= BASE_URL ?>/shop/catalog.php?cat=<?= $cat['id'] ?>" class="filter-chip <?= $catId==$cat['id'] ? 'active' : '' ?>">
      <?= e($cat['name']) ?> <span style="opacity:.6">(<?= $cat['cnt'] ?>)</span>
    </a>
    <?php endforeach; ?>
    <form method="GET" action="<?= BASE_URL ?>/shop/catalog.php" class="search-shop">
      <?php if($catId): ?><input type="hidden" name="cat" value="<?= $catId ?>"/><?php endif; ?>
      <svg class="ico" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" name="q" placeholder="Search products…" value="<?= e($search) ?>"/>
    </form>
  </div>

  <?php if (empty($products)): ?>
  <div style="text-align:center;padding:80px 20px;color:var(--text3)">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 16px;display:block;opacity:.3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <p style="font-size:16px">No products found</p>
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-outline btn-sm" style="margin-top:16px">Clear filters</a>
  </div>
  <?php else: ?>
  <div class="products-grid">
    <?php foreach ($products as $p):
      $imgSrc   = $p['image'] ? BASE_URL . '/uploads/products/' . e($p['image']) : null;
      $isNew    = strtotime($p['created_at']) > strtotime('-30 days');
      $features = getProductFeatures($pdo, $p['id']);

      // Data payload read by shop.js to power the hover hotspots and
      // the Quick View modal — no extra request needed on click.
      $cardData = json_encode([
          'id'       => $p['id'],
          'name'     => $p['name'],
          'category' => $p['category_name'] ?? 'General',
          'price'    => (float)$p['price'],
          'desc'     => $p['description'] ?? '',
          'img'      => $imgSrc,
          'stock'    => (int)$p['quantity'],
          'features' => array_map(fn($f) => [
              'label'       => $f['label'],
              'description' => $f['description'],
              'pos_x'       => (float)$f['pos_x'],
              'pos_y'       => (float)$f['pos_y'],
          ], $features),
      ], JSON_UNESCAPED_UNICODE);
    ?>
    <div class="product-card" data-product='<?= htmlspecialchars($cardData, ENT_QUOTES, 'UTF-8') ?>'>
      <div class="product-img">
        <?php if ($imgSrc): ?>
          <img src="<?= $imgSrc ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
        <?php else: ?>
          <div class="product-img-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
        <?php endif; ?>
        <?php if ($isNew): ?><span class="product-badge badge-new">New</span><?php endif; ?>
        <?php if ($p['status']==='low_stock'): ?><span class="product-badge badge-low">Low stock</span><?php endif; ?>

        <?php if ($features): ?>
        <!-- Hover preview: numbered hotspots pop onto the thumbnail -->
        <div class="hotspot-layer">
          <?php foreach ($features as $i => $f): ?>
          <div class="hotspot-dot" data-idx="<?= $i ?>" style="left:<?= $f['pos_x'] ?>%;top:<?= $f['pos_y'] ?>%">
            <?= $i + 1 ?>
            <div class="hotspot-tip">
              <strong><?= e($f['label']) ?></strong><?= e($f['description'] ?? '') ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <span class="hotspot-hint">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <?= count($features) ?> feature<?= count($features)!=1?'s':'' ?>
        </span>
        <?php endif; ?>

        <span class="quickview-expand" title="Quick view">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
        </span>
      </div>
      <div class="product-body">
        <div class="product-cat"><?= e($p['category_name'] ?? 'General') ?></div>
        <div class="product-name"><?= e($p['name']) ?></div>
        <div class="product-desc"><?= e($p['description'] ?? 'Quality product from our catalogue.') ?></div>
        <div class="product-footer">
          <div>
            <div class="product-price"><?= formatMoney($p['price']) ?></div>
            <div class="product-stock"><?= number_format($p['quantity']) ?> in stock</div>
          </div>
          <!-- <button class="add-to-cart"
            onclick="addToCart(<?= $p['id'] ?>,'<?= e(addslashes($p['name'])) ?>',<?= $p['price'] ?>,'<?= $imgSrc ?>',<?= $p['quantity'] ?>)"
            title="Add to cart">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button> -->
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<footer class="shop-footer">
  <div class="footer-brand"><?= $shopName ?></div>
  <div class="footer-text">Powered by IMS · <?= date('Y') ?></div>
</footer>

<?php include 'cart-panel.php'; ?>
<?php include 'quickview-modal.php'; ?>
<div id="shop-toast"></div>
<script src="<?= BASE_URL ?>/assets/js/shop.js"></script>
</body>
</html>
