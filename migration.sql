USE north_end_cafe;

-- Run once after importing the original database.sql.
ALTER TABLE menu_items
  ADD COLUMN stock_quantity INT NOT NULL DEFAULT 10 AFTER available;

ALTER TABLE orders
  ADD COLUMN payment_method ENUM('Cash','Card','bKash') NOT NULL DEFAULT 'Cash',
  ADD COLUMN payment_status ENUM('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending';

-- Set sample inventory levels for the presentation.
UPDATE menu_items SET stock_quantity = 12 WHERE category = 'Coffee';
UPDATE menu_items SET stock_quantity = 15 WHERE category = 'Tea';
UPDATE menu_items SET stock_quantity = 8 WHERE category = 'Pastry';
UPDATE menu_items SET stock_quantity = 6 WHERE category = 'Dessert';
