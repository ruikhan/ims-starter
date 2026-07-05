<?php
// includes/csrf.php — Lightweight CSRF protection
// Auto-loaded by includes/auth.php, so every page that already does
// requireLogin() / requireAdmin() has these functions for free.

/** Get (or create) the CSRF token for the current session. */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Render a hidden input carrying the CSRF token — drop inside any <form>. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify the CSRF token on a POST request. Call this as the very first
 * line of any POST handler that changes state (delete, update, insert).
 * Halts the request with 403 if the token is missing or wrong.
 */
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Security check failed (invalid or expired form token). Please go back, refresh the page, and try again.');
    }
}