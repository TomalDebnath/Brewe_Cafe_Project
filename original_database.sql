CREATE DATABASE IF NOT EXISTS north_end_cafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE north_end_cafe;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer'
);

CREATE TABLE sessions (
  token CHAR(64) PRIMARY KEY,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  category ENUM('Coffee','Tea','Pastry','Dessert') NOT NULL,
  image VARCHAR(500) NOT NULL DEFAULT '',
  sugar_supported TINYINT(1) NOT NULL DEFAULT 0,
  available TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_token CHAR(64) NOT NULL,
  menu_item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  extra_sugar TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_cart (session_token, menu_item_id, extra_sugar),
  FOREIGN KEY (session_token) REFERENCES sessions(token) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(20) NOT NULL,
  user_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL DEFAULT '',
  table_no VARCHAR(30) NOT NULL DEFAULT '',
  notes TEXT,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT NULL,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  quantity INT NOT NULL,
  extra_sugar TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Demo accounts: customer / customer123  and  admin / admin123
INSERT INTO users (username, password_hash, name, role) VALUES
('customer', '$2y$10$JPPeWPxz8k7LaE/HT5LtauB.mabk2k.9lxu3QZUjPelL655wUmYKW', 'Demo Customer', 'customer'),
('admin',    '$2y$10$GwF1f.trAlhmexmpm92OXe2IkILpKLuch0V84Hs2vasrYUKont1TW', 'Cafe Admin',    'admin');

-- Sample menu (replace image URLs with your own photos)
INSERT INTO menu_items (name, price, description, category, image, sugar_supported) VALUES
('Espresso', 120, 'Rich, bold single shot', 'Coffee', 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&w=400', 1),
('Americano', 150, 'Espresso with hot water', 'Coffee', 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&w=400', 1),
('Cappuccino', 190, 'Espresso, steamed milk and foam', 'Coffee', 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&w=400', 1),
('Caffe Latte', 200, 'Smooth espresso with milk', 'Coffee', 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&w=400', 1),
('Caramel Macchiato', 250, 'Vanilla, milk, espresso, caramel drizzle', 'Coffee', 'https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?auto=compress&w=400', 1),
('Masala Tea', 100, 'Spiced milk tea', 'Tea', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 1),
('Green Tea', 110, 'Light and refreshing', 'Tea', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 1),
('Lemon Tea', 110, 'Black tea with fresh lemon', 'Tea', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 1),
('Iced Peach Tea', 160, 'Chilled tea with peach', 'Tea', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 1),
('Butter Croissant', 140, 'Flaky and freshly baked', 'Pastry', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Chocolate Danish', 160, 'Pastry filled with dark chocolate', 'Pastry', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Blueberry Muffin', 130, 'Soft muffin with blueberries', 'Pastry', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Chocolate Cake', 220, 'Rich layered chocolate cake', 'Dessert', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Cheesecake', 240, 'Creamy New York style', 'Dessert', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Tiramisu', 260, 'Coffee-soaked layers with mascarpone', 'Dessert', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0),
('Brownie', 150, 'Fudgy chocolate brownie', 'Dessert', 'https://images.pexels.com/photos/6305501/pexels-photo-6305501.jpeg?auto=compress&w=400', 0);
