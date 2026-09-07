<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function take_admin_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['admin_csrf'];
}

function verify_admin_csrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['admin_csrf'])
        && hash_equals($_SESSION['admin_csrf'], $token);
}

/**
 * -------------------------------------------------------------
 * Student / User Authentication Helpers
 * -------------------------------------------------------------
 */

function is_user_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_user_logged_in()) {
        return null;
    }
    return [
        'id' => (int)$_SESSION['user_id'],
        'name' => (string)($_SESSION['user_name'] ?? ''),
        'email' => (string)($_SESSION['user_email'] ?? ''),
        'whatsapp' => (string)($_SESSION['user_whatsapp'] ?? ''),
    ];
}

function require_user(string $redirectUrl = ''): void
{
    if (!is_user_logged_in()) {
        $dest = $redirectUrl !== '' ? $redirectUrl : ($_SERVER['REQUEST_URI'] ?? 'my-workshops.php');
        header('Location: login.php?redirect=' . urlencode($dest));
        exit;
    }
}

function user_flash(string $type, string $message): void
{
    $_SESSION['user_flash'] = ['type' => $type, 'message' => $message];
}

function take_user_flash(): ?array
{
    $flash = $_SESSION['user_flash'] ?? null;
    unset($_SESSION['user_flash']);
    return $flash;
}

function user_csrf_token(): string
{
    if (empty($_SESSION['user_csrf'])) {
        $_SESSION['user_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['user_csrf'];
}

function verify_user_csrf(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['user_csrf'])
        && hash_equals($_SESSION['user_csrf'], $token);
}