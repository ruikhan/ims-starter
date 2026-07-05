-- ============================================================
--  IMS Database — ims_db
--  Import via phpMyAdmin or: mysql -u root -p < ims.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS ims_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ims_db;

-- ── USERS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('admin','staff') DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── CATEGORIES ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  description TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── PRODUCTS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  name                VARCHAR(150) NOT NULL,
  sku                 VARCHAR(80)  NOT NULL UNIQUE,
  category_id         INT DEFAULT NULL,
  price               DECIMAL(10,2) DEFAULT 0.00,
  quantity            INT DEFAULT 0,
  low_stock_threshold INT DEFAULT 10,
  status              ENUM('in_stock','low_stock','out_of_stock') DEFAULT 'in_stock',
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── TRANSACTIONS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  type       ENUM('in','out') NOT NULL,
  quantity   INT NOT NULL,
  notes      TEXT,
  user_id    INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── SEED DATA ───────────────────────────────────────────────
-- Admin password: Admin@123
INSERT INTO users (name, email, password, role) VALUES
('Admin User',   'admin@ims.com', '$2y$10$EEcfcvMKwcS9Spkx0DOkruvMq0aklQhI0q/PSe6qneRCKqqjUEzAC', 'admin'),
('Maria Santos', 'maria@ims.com', '$2y$10$D4IzqoH3OWRAMbL9g8ZF1OjH8GhJzRWdECGSEb.Gr2Cs5I/xF1Ge.', 'staff');

INSERT INTO categories (name, description) VALUES
('Electronics',    'Electronic devices and accessories'),
('Office Supplies','Stationery, paper, and office materials'),
('Furniture',      'Office and workspace furniture');

INSERT INTO products (name, sku, category_id, price, quantity, low_stock_threshold, status) VALUES
('Wireless Mouse',      'WM-001', 1, 650.00,  45, 10, 'in_stock'),
('USB-C Hub 7-in-1',   'UH-007', 1, 1200.00,  8, 10, 'low_stock'),
('Mechanical Keyboard', 'MK-TKL', 1, 2800.00,  0,  5, 'out_of_stock'),
('A4 Bond Paper (Ream)','PP-A4R', 2,  285.00,120, 20, 'in_stock'),
('Ballpen Box Blue',    'BP-BLU', 2,   95.00,  6, 10, 'low_stock'),
('Ergonomic Chair',     'EC-PRO', 3, 9500.00,  3,  2, 'in_stock');

INSERT INTO transactions (product_id, type, quantity, notes, user_id) VALUES
(1, 'in',  50, 'Initial stock',       1),
(4, 'in', 200, 'Office restock',      1),
(1, 'out',  5, 'Sales order #101',    2),
(2, 'in',  15, 'Purchase order #55',  1),
(2, 'out',  7, 'Sales order #102',    2),
(5, 'out', 14, 'Department request',  2),
(6, 'in',   3, 'New purchase',        1),
(3, 'out',  2, 'Sales order #103',    2);
