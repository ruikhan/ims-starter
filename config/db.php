<?php
// config/db.php — Database connection (env-var aware: local XAMPP + cloud deploy)
//
// Locally, none of the getenv() calls below will find anything, so every
// setting quietly falls back to your original XAMPP defaults — nothing
// changes for local development.
//
// On Render (or any host that lets you set environment variables), set
// DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS to your Aiven MySQL
// credentials and BASE_URL="" (the app is served from the domain root
// there, not from a /ims-starter subfolder).

define('BASE_URL', getenv('BASE_URL') !== false ? rtrim(getenv('BASE_URL'), '/') : '/ims-starter');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3307');
define('DB_NAME', getenv('DB_NAME') ?: 'ims_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHAR', 'utf8mb4');

// Path to a CA bundle for TLS. Aiven (and most managed MySQL hosts) require
// TLS in production. Leave unset for local XAMPP.
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: null);

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (DB_SSL_CA) {
        $options[PDO::MYSQL_ATTR_SSL_CA]                = DB_SSL_CA;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    // Full error only when APP_DEBUG is set (local dev) — never leak DB details in production.
    $detail = getenv('APP_DEBUG') ? $e->getMessage() : 'Please check your database configuration.';
    die(json_encode(['error' => 'Database connection failed: ' . $detail]));
}