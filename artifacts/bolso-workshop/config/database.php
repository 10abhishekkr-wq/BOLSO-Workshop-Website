<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function bolso_db(): ?PDO
{
    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }

    $attempted = true;
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        bolso_config('db_host'),
        bolso_config('db_port'),
        bolso_config('db_name')
    );

    try {
        $pdo = new PDO($dsn, bolso_config('db_user'), bolso_config('db_password'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('BOLSO database connection failed: ' . $exception->getMessage());
    }

    return $pdo;
}

function bolso_database_ready(): bool
{
    return bolso_db() instanceof PDO;
}