-- ============================================================
--  IMS Shop Extension — Product Feature Hotspots
--  Run this AFTER ims.sql and shop.sql
--  Import via phpMyAdmin SQL tab, or:
--    mysql -u root -p ims_db < database/product_features.sql
-- ============================================================

USE ims_db;

-- Stores the numbered "hotspot" callouts shown on a product image —
-- e.g. "Cushioned insole", "Breathable mesh upper" — positioned as a
-- percentage of the image's width/height so they scale with any image
-- size (thumbnail in the grid, full size in Quick View).
CREATE TABLE IF NOT EXISTS product_features (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  label       VARCHAR(100) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  pos_x       DECIMAL(5,2) NOT NULL COMMENT 'Horizontal position, % from left (0–100)',
  pos_y       DECIMAL(5,2) NOT NULL COMMENT 'Vertical position, % from top (0–100)',
  sort_order  INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── OPTIONAL DEMO SEED ──────────────────────────────────────
-- Illustrates the feature on the seeded "Mechanical Keyboard" (id 3)
-- and "Ergonomic Chair" (id 6) from ims.sql. Safe to delete — every
-- product's hotspots are meant to be added from Products → Edit in
-- the admin UI once it has a real photo.
INSERT INTO product_features (product_id, label, description, pos_x, pos_y, sort_order) VALUES
(3, 'Hot-swappable switches', 'Swap switches without soldering.', 50.00, 20.00, 0),
(3, 'PBT keycaps',            'Double-shot legends that resist shine.', 30.00, 55.00, 1),
(3, 'USB-C connection',       'Detachable braided cable.', 75.00, 80.00, 2),
(6, 'Adjustable lumbar support', 'Slides to match your lower back.', 50.00, 35.00, 0),
(6, 'Breathable mesh back',      'Keeps you cool during long sessions.', 50.00, 55.00, 1),
(6, '4D armrests',               'Adjusts up, down, in, out.', 20.00, 45.00, 2);
