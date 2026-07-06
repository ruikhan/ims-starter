<?php
// sw.php — Service worker, served as JS via PHP so BASE_URL can be baked
// into the cached asset list. Bump CACHE_VERSION whenever shop.css/shop.js
// change so returning visitors pick up the new files instead of a stale cache.
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/javascript');

$base = BASE_URL;
$cacheVersion = 'cgshop-v1';
?>
const CACHE = '<?= $cacheVersion ?>';
const BASE  = '<?= $base ?>';

const SHELL_ASSETS = [
  BASE + '/shop/index.php',
  BASE + '/shop/catalog.php',
  BASE + '/assets/css/shop.css',
  BASE + '/assets/js/shop.js',
  BASE + '/assets/icons/icon-192.png',
  BASE + '/assets/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(SHELL_ASSETS)).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Network-first for pages (so stock/prices stay fresh), cache-first fallback
// for everything else (css/js/images) — with an offline fallback to cache.
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const isPage = event.request.mode === 'navigate';

  if (isPage) {
    event.respondWith(
      fetch(event.request)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put(event.request, copy));
          return res;
        })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      if (cached) return cached;
      return fetch(event.request)
        .then((res) => {
          const copy = res.clone();
          caches.open(CACHE).then((c) => c.put(event.request, copy));
          return res;
        })
        .catch(() => cached);
    })
  );
});
