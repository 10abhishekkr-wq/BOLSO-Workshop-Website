-- BOLSO Workshop Database Schema
-- Compatible with any MySQL/MariaDB database (Local XAMPP, Hostinger, cPanel, VPS)

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(190) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workshops (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(40) NOT NULL UNIQUE,
    title VARCHAR(120) NOT NULL,
    duration_days TINYINT UNSIGNED NOT NULL,
    online_price DECIMAL(10,2) NOT NULL,
    offline_price DECIMAL(10,2) NOT NULL,
    max_students TINYINT UNSIGNED NOT NULL DEFAULT 6,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    whatsapp VARCHAR(40) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(160) NOT NULL,
    whatsapp VARCHAR(40) NOT NULL,
    email VARCHAR(190) NOT NULL,
    workshop VARCHAR(40) NOT NULL,
    mode ENUM('online', 'offline') NOT NULL,
    preferred_date VARCHAR(160) NOT NULL,
    interest VARCHAR(80) NOT NULL,
    experience VARCHAR(80) NULL,
    message TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'online',
    timing_slot VARCHAR(255) NULL,
    timing_sent_at DATETIME NULL,
    CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_registration_workshop FOREIGN KEY (workshop) REFERENCES workshops(slug)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_registration_user (user_id),
    INDEX idx_registration_workshop (workshop),
    INDEX idx_registration_mode (mode),
    INDEX idx_registration_payment (payment_status),
    INDEX idx_registration_pay_method (payment_method)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id INT UNSIGNED NOT NULL,
    provider VARCHAR(60) NOT NULL,
    provider_payment_id VARCHAR(190) NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_registration FOREIGN KEY (registration_id) REFERENCES registrations(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reset_token (token_hash),
    INDEX idx_reset_user (user_type, user_id),
    INDEX idx_reset_expires (expires_at)
) ENGINE=InnoDB;

INSERT INTO workshops (slug, title, duration_days, online_price, offline_price, max_students)
VALUES
    ('2-day', '2-Day Workshop', 2, 399.00, 399.00, 6),
    ('5-day', '5-Day Workshop', 5, 899.00, 1299.00, 6)
ON DUPLICATE KEY UPDATE title = VALUES(title), online_price = VALUES(online_price), offline_price = VALUES(offline_price);

-- Change this password after importing the database.
-- Username: admin
-- Email: 10abhishekkr@gmail.com
-- Password: bolso2026
INSERT INTO admins (username, email, password_hash)
VALUES ('admin', '10abhishekkr@gmail.com', '$2y$12$34ugcnZpYPzpseZkemkfw.3vQrX.48DZb1k1YHTKFjF1kt5b8aOs6')
ON DUPLICATE KEY UPDATE email = VALUES(email);