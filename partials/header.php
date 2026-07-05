<?php
// partials/header.php — Responsive layout with mobile hamburger
requireLogin();
$flash       = getFlash();
$lowCount    = getLowStockCount($pdo);
$scriptPath  = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$currentPage = preg_replace('#\.php$#', '', preg_replace('#.*/ims-starter/#', '', $scriptPath));

function navItem(string $page, string $icon, string $label, string $current, int $badge = 0): string {
    $active    = (strpos($current, $page) !== false) ? 'active' : '';
    $badgeHtml = $badge > 0 ? "<span class=\"nav-badge\">$badge</span>" : '';
    return "<a href=\"" . BASE_URL . "/$page.php\" class=\"nav-item $active\" onclick=\"closeSidebar()\">$icon<span>$label</span>$badgeHtml</a>";
}

function ico(string $name): string {
    $icons = [
      'grid'   => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
      'box'    => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>',
      'list'   => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h7"/></svg>',
      'arrows' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>',
      'clock'  => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
      'users'  => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
      'shop'   => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
      'orders' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    ];
    return $icons[$name] ?? '';
}

$initials = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_filter(explode(' ', currentUserName())))));
$initials = substr($initials, 0, 2) ?: 'US';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
  <meta name="apple-mobile-web-app-capable" content="yes"/>
  <meta name="theme-color" content="#161b27"/>
  <title><?= e($pageTitle ?? 'IMS') ?> — IMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css"/>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-spotlight-gold.css"/>
</head>
<body data-gx="admin">

<!-- Mobile backdrop -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ── SIDEBAR ── -->
<nav id="sidebar">
  <div class="brand">
    <div class="brand-logo">
      <div class="brand-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="3" width="20" height="6" rx="1"/><rect x="2" y="12" width="10" height="9" rx="1"/>
          <rect x="15" y="12" width="7" height="4" rx="1"/><rect x="15" y="19" width="7" height="2" rx="1"/>
        </svg>
      </div>
      <div>
        <div class="brand-name">IMS</div>
        <div class="brand-sub">v1.0 · ims-starter</div>
      </div>
    </div>
  </div>

  <div class="nav-section">
    <div class="nav-label">Main</div>
    <?= navItem('index',            ico('grid'),   'Dashboard',       $currentPage) ?>
    <?= navItem('products/index',   ico('box'),    'Products',        $currentPage) ?>
    <?= navItem('categories/index', ico('list'),   'Categories',      $currentPage) ?>
  </div>

  <div class="nav-section">
    <div class="nav-label">Inventory</div>
    <?= navItem('stock/index',   ico('arrows'), 'Stock Movement',  $currentPage) ?>
    <?= navItem('stock/history', ico('clock'),  'Transactions',    $currentPage, $lowCount) ?>
  </div>

  <div class="nav-section">
    <div class="nav-label">Shop</div>
    <?= navItem('admin/orders', ico('orders'), 'Customer Orders', $currentPage) ?>
    <a href="<?= BASE_URL ?>/shop/index.php" class="nav-item" target="_blank" rel="noopener">
      <?= ico('shop') ?><span>Storefront</span>
      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto;opacity:.35"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    </a>
  </div>

  <?php if (isAdmin()): ?>
  <div class="nav-section">
    <div class="nav-label">Admin</div>
    <?= navItem('users/index', ico('users'), 'User Management', $currentPage) ?>
  </div>
  <?php endif; ?>

  <div class="sidebar-bottom">
    <div class="user-card">
      <div class="user-avatar"><?= e($initials) ?></div>
      <div class="user-info">
        <div class="user-name"><?= e(currentUserName()) ?></div>
        <div class="user-role"><?= e($_SESSION['role'] ?? '') ?></div>
      </div>
      <a href="<?= BASE_URL ?>/logout.php" class="logout-btn" title="Logout">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </a>
    </div>
  </div>
</nav>

<!-- ── MAIN ── -->
<main id="main">
  <div class="topbar">
    <button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
    <span class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
    <div class="topbar-right">
      <?php if ($lowCount > 0): ?>
      <a href="/ims-starter/stock/history.php" class="icon-btn" title="<?= $lowCount ?> stock alerts">
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
        </svg>
        <span class="notif-dot"></span>
      </a>
      <?php endif; ?>
      <div style="font-size:11px;color:var(--text3);font-family:var(--font-mono);white-space:nowrap"><?= date('d M Y') ?></div>
    </div>
  </div>

  <div class="content">
    <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" style="margin-bottom:16px"><?= e($flash['msg']) ?></div>
    <?php endif; ?>
