<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

// Safely clear student user session variables without affecting admin sessions
unset(
    $_SESSION['user_id'],
    $_SESSION['user_name'],
    $_SESSION['user_email'],
    $_SESSION['user_whatsapp'],
    $_SESSION['user_csrf']
);

user_flash('success', 'You have been signed out successfully. See you at the studio!');

header('Location: login.php');
exit;
