-- ============================================================
--  IMS Shop Extension — run this AFTER ims.sql
--  Import via phpMyAdmin SQL tab
-- ============================================================

USE ims_db;

-- Add image column to products
ALTER TABLE products
  ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER status,
  ADD COLUMN description TEXT DEFAULT NULL AFTER image;

-- Customer orders
CREATE TABLE IF NOT EXISTS orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_code   VARCHAR(20)  NOT NULL UNIQUE,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(30)  DEFAULT NULL,
  customer_address TEXT       NOT NULL,
  total_amount  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status        ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  notes         TEXT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Order line items
CREATE TABLE IF NOT EXISTS order_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_id   INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  quantity   INT          NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  subtotal   DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
