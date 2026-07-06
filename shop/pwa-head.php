<!-- shop/pwa-head.php — PWA install support. Include inside <head> on every
     storefront page (shop/index.php, shop/catalog.php, shop/checkout.php,
     shop/success.php). Requires BASE_URL to already be defined (config/db.php
     is already required by every page that includes this). -->
<link rel="manifest" href="<?= BASE_URL ?>/manifest.php"/>
<link rel="icon" href="<?= BASE_URL ?>/assets/icons/favicon.ico" sizes="any"/>
<link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/icons/icon-192.png"/>
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icons/apple-touch-icon.png"/>
<meta name="theme-color" content="#f2b65b"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="CGShop"/>

<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= BASE_URL ?>/sw.php', { scope: '<?= BASE_URL ?>/' })
      .catch(err => console.warn('SW registration failed:', err));
  });
}
</script>
