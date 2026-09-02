<?php
declare(strict_types=1);
/**
 * BOLSO configuration.
 * Copy this file to config/config.local.php for local overrides, or provide
 * the values as environment variables on your hosting provider.
 */
const BOLSO_NAME = 'BOLSO';
const BOLSO_TAGLINE = 'Art, Emotion, Fashion.';
const BOLSO_WHATSAPP_NUMBER = 'YOUR_WHATSAPP_NUMBER';
const BOLSO_PAYMENT_KEY = 'YOUR_PAYMENT_KEY';
const BOLSO_PAYMENT_SECRET = 'YOUR_PAYMENT_SECRET';

$bolsoLocalConfig = __DIR__ . '/config.local.php';
if (is_file($bolsoLocalConfig)) {
    require $bolsoLocalConfig;
}

function bolso_env(string $key, string $fallback = ''): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $fallback : $value;
}

function bolso_config(string $key, string $fallback = ''): string
{
    $defaults = [
        'db_host' => '127.0.0.1',
        'db_port' => '3306',
        'db_name' => 'bolso',
        'db_user' => 'root',
        'db_password' => '',
        'whatsapp' => BOLSO_WHATSAPP_NUMBER,
        'payment_key' => BOLSO_PAYMENT_KEY,
        'payment_secret' => BOLSO_PAYMENT_SECRET,
    ];
    return bolso_env(
        'BOLSO_' . strtoupper($key),
        $defaults[$key] ?? $fallback
    );
}