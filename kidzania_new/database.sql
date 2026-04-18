-- ============================================================
-- KidZania e-Ticketing System - Database Schema
-- Mini Project 2
-- ============================================================

CREATE DATABASE IF NOT EXISTS kidzania_New;
USE kidzania_New;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone    VARCHAR(20)  NOT NULL,
    role     ENUM('customer','admin') DEFAULT 'customer',
    regdate  DATE NOT NULL
);

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    booking_no       VARCHAR(10) NOT NULL,
    booking_date     DATE NOT NULL,
    infants          INT DEFAULT 0,
    toddlers         INT DEFAULT 0,
    kids             INT DEFAULT 0,
    adults           INT DEFAULT 0,
    senior_citizens  INT DEFAULT 0,
    disabled         INT DEFAULT 0,
    total_price      DECIMAL(10,2) DEFAULT 0.00,
    status           ENUM('Pending','Paid','Confirmed') DEFAULT 'Pending',
    receipt_path     VARCHAR(255) DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- ALTER TABLE (jika database dah wujud - untuk upgrade)
-- Jalankan hanya jika column belum ada
-- ============================================================
-- ALTER TABLE bookings ADD COLUMN status ENUM('Pending','Paid','Confirmed') DEFAULT 'Pending';
-- ALTER TABLE bookings ADD COLUMN receipt_path VARCHAR(255) DEFAULT NULL;
-- ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- Default Admin Account
-- Password: admin123
-- ============================================================
INSERT INTO users (fullname, username, password, phone, role, regdate)
VALUES (
    'Administrator',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0123456789',
    'admin',
    CURDATE()
) ON DUPLICATE KEY UPDATE role = 'admin';

-- ============================================================
-- Folder untuk upload resit (buat secara manual)
-- /kidzania_New/uploads/receipts/
-- ============================================================
