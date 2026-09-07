<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = bolso_db();
if (!$pdo) {
    http_response_code(500);
    echo "ERROR: Could not connect to database.\n";
    exit;
}

echo "=== BOLSO Users Migration ===\n";

try {
    // 1. Create users table
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(160) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        whatsapp VARCHAR(40) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sqlUsers);
    echo "✓ Checked/Created 'users' table successfully.\n";

    // 2. Check if user_id column exists in registrations
    $stmt = $pdo->query("SHOW COLUMNS FROM registrations LIKE 'user_id'");
    $hasUserId = $stmt->fetch();

    if (!$hasUserId) {
        $pdo->exec("ALTER TABLE registrations ADD COLUMN user_id INT UNSIGNED NULL AFTER id");
        echo "✓ Added 'user_id' column to registrations table.\n";
        
        try {
            $pdo->exec("ALTER TABLE registrations ADD INDEX idx_registration_user (user_id)");
            echo "✓ Added index 'idx_registration_user' on registrations(user_id).\n";
        } catch (PDOException $e) {
            echo "Index notice: " . $e->getMessage() . "\n";
        }

        try {
            $pdo->exec("ALTER TABLE registrations ADD CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL");
            echo "✓ Added foreign key 'fk_registration_user'.\n";
        } catch (PDOException $e) {
            echo "FK notice: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Column 'user_id' already exists in registrations table.\n";
    }

    // 3. Link any existing registrations to users with matching email
    $updateStmt = $pdo->exec("UPDATE registrations r INNER JOIN users u ON LOWER(r.email) = LOWER(u.email) SET r.user_id = u.id WHERE r.user_id IS NULL");
    echo "✓ Linked " . ($updateStmt !== false ? $updateStmt : 0) . " existing registration(s) to users.\n";

    echo "\nMigration completed successfully!\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR: Migration failed: " . $e->getMessage() . "\n";
}
