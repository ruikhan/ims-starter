<?php
// manifest.php — Web App Manifest, served dynamically so icon/start_url paths
// respect BASE_URL whether running under /ims-starter (XAMPP) or "" (Render).
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/manifest+json');

echo json_encode([
    'name'             => 'CGShop',
    'short_name'       => 'CGShop',
    'description'      => 'Shop premium products, delivered fast.',
    'start_url'        => BASE_URL . '/shop/index.php',
    'scope'            => BASE_URL . '/',
    'display'          => 'standalone',
    'orientation'      => 'portrait-primary',
    'background_color' => '#0b0806',
    'theme_color'      => '#f2b65b',
    'icons' => [
        [
            'src'     => BASE_URL . '/assets/icons/icon-192.png',
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src'     => BASE_URL . '/assets/icons/icon-512.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src'     => BASE_URL . '/assets/icons/icon-512-maskable.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
], JSON_UNESCAPED_SLASHES);
