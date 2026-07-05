<?php
// includes/auth.php — Session and role-based access control

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // uncomment once the site is served over HTTPS only
    ]);
    session_start();
}

// Every page that loads auth.php automatically gets csrf_token() / csrf_field()
// / csrf_verify() for free — no extra require_once needed elsewhere.
require_once __DIR__ . '/csrf.php';

/** Redirect to login if not authenticated */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Access denied. Admins only.'];
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/** Check if the current session user is an admin */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** Return the logged-in user's ID */
function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/** Return the logged-in user's display name */
function currentUserName(): string {
    return $_SESSION['user_name'] ?? 'User';
}

/** Store a one-time flash message */
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = compact('type', 'msg');
}

/** Retrieve and clear the flash message */
function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}