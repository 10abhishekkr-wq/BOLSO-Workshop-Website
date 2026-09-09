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

/**
 * -------------------------------------------------------------
 * Password Reset Helpers (Secure Token Hashing + 1hr Expiry)
 * -------------------------------------------------------------
 */

function bolso_create_password_reset(string $userType, int $userId, string $email): ?string
{
    $pdo = bolso_db();
    if (!$pdo) return null;

    if (!in_array($userType, ['user', 'admin'], true)) {
        return null;
    }

    try {
        // Invalidate prior unused tokens for this user/admin
        $cancelStmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_type = :type AND user_id = :uid AND used_at IS NULL');
        $cancelStmt->execute([':type' => $userType, ':uid' => $userId]);

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $insStmt = $pdo->prepare(
            'INSERT INTO password_resets (user_type, user_id, email, token_hash, expires_at)
             VALUES (:type, :uid, :email, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
        );
        $insStmt->execute([
            ':type' => $userType,
            ':uid' => $userId,
            ':email' => $email,
            ':token_hash' => $tokenHash,
        ]);

        return $rawToken;
    } catch (PDOException $e) {
        error_log('Error creating password reset: ' . $e->getMessage());
        return null;
    }
}

function bolso_verify_reset_token(string $userType, string $rawToken): ?array
{
    $pdo = bolso_db();
    if (!$pdo) return null;

    $rawToken = trim($rawToken);
    if ($rawToken === '') return null;

    $tokenHash = hash('sha256', $rawToken);

    try {
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets 
             WHERE user_type = :type 
               AND token_hash = :hash 
               AND used_at IS NULL 
               AND expires_at > NOW() 
             LIMIT 1'
        );
        $stmt->execute([
            ':type' => $userType,
            ':hash' => $tokenHash,
        ]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    } catch (PDOException $e) {
        error_log('Error verifying reset token: ' . $e->getMessage());
        return null;
    }
}

function bolso_complete_password_reset(int $resetId, string $userType, int $userId, string $newPassword): bool
{
    $pdo = bolso_db();
    if (!$pdo) return false;

    if (strlen($newPassword) < 6) {
        return false;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        if ($userType === 'user') {
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        } elseif ($userType === 'admin') {
            $stmt = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
            $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        } else {
            $pdo->rollBack();
            return false;
        }

        $markStmt = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :rid');
        $markStmt->execute([':rid' => $resetId]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error completing password reset: ' . $e->getMessage());
        return false;
    }
}