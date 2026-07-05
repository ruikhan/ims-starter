<?php
// shop/index.php — Customer storefront homepage
require_once '../config/db.php';
require_once '../includes/helpers.php';

// Stats for hero
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status != 'out_of_stock'")->fetchColumn();
$totalCats     = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status != 'out_of_stock'
    GROUP BY c.id HAVING product_count > 0 ORDER BY product_count DESC
")->fetchAll();

// Featured / in-stock products
$featured = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status != 'out_of_stock'
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

$shopName = 'CGShop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $shopName ?> — Premium Inventory Store</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/shop.css"/>
</head>
<body>

<!-- ── NAV ── -->
<nav class="shop-nav">
  <a href="<?= BASE_URL ?>/shop/index.php" class="nav-brand">
    <div class="nav-brand-icon">
      <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
    </div>
    <?= $shopName ?>
  </a>
  <div class="nav-links">
    <a href="<?= BASE_URL ?>/shop/index.php" class="active">Home</a>
    <a href="<?= BASE_URL ?>/shop/catalog.php">Shop</a>
    <a href="#features">About</a>
  </div>
  <!-- <div class="nav-actions">
    <button class="cart-btn" onclick="toggleCart()">
      <svg class="ico" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Cart
      <span class="cart-count" id="cart-count">0</span>
    </button>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Admin</a>
  </div> -->
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <div class="hero-tag">Now open for orders</div>
    <h1>Quality products,<br/><span>delivered fast</span></h1>
    <p>Browse our curated inventory of premium products. Everything in stock, ready to ship directly to your door.</p>
    <div class="hero-actions">
      <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-primary btn-lg">
        <svg class="ico" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        Shop now
      </a>
      <button class="btn btn-outline btn-lg" onclick="toggleCart()">View cart</button>
    </div>
    <div class="hero-stats">
      <div>
        <div class="hero-stat-val"><?= $totalProducts ?>+</div>
        <div class="hero-stat-label">Products available</div>
      </div>
      <div>
        <div class="hero-stat-val"><?= $totalCats ?></div>
        <div class="hero-stat-label">Categories</div>
      </div>
      <div>
        <div class="hero-stat-val">24h</div>
        <div class="hero-stat-label">Fast delivery</div>
      </div>
    </div>
  </div>
</section>

<!-- ── CATEGORIES ── -->
<?php if ($categories): ?>
<section class="section">
  <div class="section-header">
    <div>
      <div class="section-label">Browse by</div>
      <div class="section-title">Categories</div>
    </div>
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-ghost btn-sm">View all →</a>
  </div>
  <div class="cat-grid">
    <?php
    $catIcons = [
      'Electronics'    => '<path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>',
      'Office Supplies'=> '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>',
      'Furniture'      => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
      'default'        => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
    ];
    foreach ($categories as $cat):
      $icon = $catIcons[$cat['name']] ?? $catIcons['default'];
    ?>
    <a href="<?= BASE_URL ?>/shop/catalog.php?cat=<?= $cat['id'] ?>" class="cat-card">
      <div class="cat-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg>
      </div>
      <div class="cat-name"><?= e($cat['name']) ?></div>
      <div class="cat-count"><?= $cat['product_count'] ?> product<?= $cat['product_count'] != 1 ? 's' : '' ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ── FEATURED PRODUCTS ── -->
<section class="section section-alt">
  <div class="section-header">
    <div>
      <div class="section-label">Hand picked</div>
      <div class="section-title">Featured products</div>
      <div class="section-sub">All products are in stock and ready to ship</div>
    </div>
    <a href="<?= BASE_URL ?>/shop/catalog.php" class="btn btn-outline btn-sm">Browse all →</a>
  </div>
  <div class="products-grid">
    <?php foreach ($featured as $p): ?>
    <?php
      $isNew = strtotime($p['created_at']) > strtotime('-30 days');
      $imgSrc = $p['image'] ? BASE_URL . '/uploads/products/' . e($p['image']) : null;
    ?>
    <div class="product-card">
      <div class="product-img">
        <?php if ($imgSrc): ?>
          <img src="<?= $imgSrc ?>" alt="<?= e($p['name']) ?>" loading="lazy"/>
        <?php else: ?>
          <div class="product-img-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
        <?php endif; ?>
        <?php if ($isNew): ?><span class="product-badge badge-new">New</span><?php endif; ?>
        <?php if ($p['status'] === 'low_stock'): ?><span class="product-badge badge-low">Low stock</span><?php endif; ?>
      </div>
      <div class="product-body">
        <div class="product-cat"><?= e($p['category_name'] ?? 'General') ?></div>
        <div class="product-name"><?= e($p['name']) ?></div>
        <div class="product-desc"><?= e($p['description'] ?? 'Quality product from our inventory.') ?></div>
        <div class="product-footer">
          <div>
            <div class="product-price"><?= formatMoney($p['price']) ?></div>
            <div class="product-stock"><?= $p['quantity'] ?> in stock</div>
          </div>
          <!-- <button class="add-to-cart"
            onclick="addToCart(<?= $p['id'] ?>,'<?= e(addslashes($p['name'])) ?>',<?= $p['price'] ?>,'<?= $imgSrc ?>',<?= $p['quantity'] ?>)"
            <?= $p['status'] === 'out_of_stock' ? 'disabled' : '' ?> title="Add to cart">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button> -->
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── FEATURES ── -->
<section class="section" id="features">
  <div class="section-header">
    <div>
      <div class="section-label">Why us</div>
      <div class="section-title">Shopping made simple</div>
    </div>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="feature-title">Fast delivery</div>
      <div class="feature-text">Orders are processed immediately and dispatched within 24 hours of confirmation.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="feature-title">Quality guaranteed</div>
      <div class="feature-text">Every product is quality-checked before dispatch. We stand behind every item we sell.</div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      </div>
      <div class="feature-title">Real-time support</div>
      <div class="feature-text">Track your order status live and get instant updates as your order is processed.</div>
    </div>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer class="shop-footer">
  <div>
    <div class="footer-brand"><?= $shopName ?></div>
    <div class="footer-text">Powered by IMS · All rights reserved <?= date('Y') ?></div>
  </div>
  <div style="display:flex;gap:16px">
    <a href="<?= BASE_URL ?>/shop/catalog.php" style="font-size:13px;color:var(--text3)">Shop</a>
    <a href="<?= BASE_URL ?>/shop/checkout.php" style="font-size:13px;color:var(--text3)">Checkout</a>
    <a href="<?= BASE_URL ?>/login.php" style="font-size:13px;color:var(--text3)">Admin</a>
  </div>
</footer>

<!-- ── CART PANEL ── -->
<?php include 'cart-panel.php'; ?>

<!-- ── TOAST ── -->
<div id="shop-toast"></div>

<script src="<?= BASE_URL ?>/assets/js/shop.js"></script>
</body>
</html>