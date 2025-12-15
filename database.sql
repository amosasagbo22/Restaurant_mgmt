-- Restaurant Database
-- Simple and clean SQL code

-- Create database
CREATE DATABASE restaurant_db;
USE restaurant_db;

-- Drop tables if they exist (for clean start)


-- Create tables
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(30) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    party_size INT NOT NULL,
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO categories (name) VALUES 
('Appetizers'),
('Main Course'),
('Desserts'),
('Beverages');

INSERT INTO menu_items (category_id, name, description, price) VALUES 
(1, 'Bruschetta', 'Grilled bread with tomatoes and basil', 5.99),
(1, 'Garlic Bread', 'Fresh bread with garlic butter', 3.99),
(2, 'Margherita Pizza', 'Classic pizza with tomato and mozzarella', 12.99),
(2, 'Spaghetti Bolognese', 'Pasta with meat sauce', 11.99),
(2, 'Chicken Alfredo', 'Pasta with creamy chicken sauce', 13.99),
(3, 'Tiramisu', 'Italian coffee dessert', 6.99),
(3, 'Chocolate Cake', 'Rich chocolate layer cake', 5.99),
(4, 'Lemonade', 'Fresh squeezed lemonade', 2.99),
(4, 'Coffee', 'Hot brewed coffee', 2.49);

-- Insert sample orders
INSERT INTO orders (customer_name, phone, address, total_amount) VALUES 
('John Smith', '555-0101', '123 Main Street', 25.97),
('Jane Doe', '555-0102', '456 Oak Avenue', 18.98),
('Bob Wilson', '555-0103', '789 Pine Road', 32.96);

-- Insert sample order items
INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES 
(1, 1, 1, 5.99),
(1, 3, 1, 12.99),
(1, 6, 1, 6.99),
(2, 2, 1, 3.99),
(2, 4, 1, 11.99),
(2, 8, 1, 2.99),
(3, 3, 2, 12.99),
(3, 7, 1, 5.99),
(3, 9, 1, 2.49);

-- Insert sample reservations
INSERT INTO reservations (name, email, phone, reservation_date, reservation_time, party_size, special_requests) VALUES 
('Alice Johnson', 'alice@email.com', '555-0201', '2024-02-15', '19:00:00', 4, 'Window seat please'),
('Mike Brown', 'mike@email.com', '555-0202', '2024-02-16', '20:00:00', 2, ''),
('Sarah Davis', 'sarah@email.com', '555-0203', '2024-02-17', '18:30:00', 6, 'Birthday celebration');

INSERT INTO users(username,password,created_at) values ("admin","admin123","2024-02-15");


-- ========================================
-- PART 3: DATA SELECTION AND ORDERING
-- ========================================

-- 1. Select Statements (3 different queries)

-- Query 1: Get all menu items with their categories
SELECT m.id, m.name, c.name as category, m.price 
FROM menu_items m 
JOIN categories c ON m.category_id = c.id;

-- Query 2: Get all orders with customer details
SELECT id, customer_name, phone, total_amount, created_at 
FROM orders;

-- Query 3: Get all reservations for today and future
SELECT id, name, phone, reservation_date, reservation_time, party_size 
FROM reservations 
WHERE reservation_date >= CURDATE();

-- 2. Ordering Data (ascending and descending)

-- Order menu items by price ascending
SELECT name, price FROM menu_items ORDER BY price ASC;

-- Order menu items by price descending  
SELECT name, price FROM menu_items ORDER BY price DESC;

-- Order orders by date (newest first)
SELECT customer_name, total_amount, created_at 
FROM orders 
ORDER BY created_at DESC;

-- Order reservations by date and time
SELECT name, reservation_date, reservation_time 
FROM reservations 
ORDER BY reservation_date ASC, reservation_time ASC;

-- ========================================
-- PART 4: CONSTRAINTS AND FUNCTIONS
-- ========================================

-- 1. Constraints used in our tables:
-- - PRIMARY KEY: id columns (auto increment)
-- - FOREIGN KEY: category_id, order_id, menu_item_id
-- - UNIQUE: username, category name
-- - NOT NULL: most important fields like names, prices, dates

-- 2. Aggregate Functions (3 different functions)

-- Function 1: COUNT and AVG - Count total items and average price
SELECT COUNT(*) as total_items, AVG(price) as average_price 
FROM menu_items;

-- Function 2: MIN and MAX - Find cheapest and most expensive items
SELECT MIN(price) as cheapest_item, MAX(price) as most_expensive_item 
FROM menu_items;

-- Function 3: SUM - Calculate total revenue from orders
SELECT SUM(total_amount) as total_revenue 
FROM orders;

-- Function 4: COUNT DISTINCT - Count unique customers
SELECT COUNT(DISTINCT customer_name) as unique_customers 
FROM orders;

-- ========================================
-- PART 5: JOIN OPERATIONS
-- ========================================

-- 1. INNER JOIN - Get menu items with their categories
SELECT m.name as item_name, c.name as category_name, m.price
FROM menu_items m
INNER JOIN categories c ON m.category_id = c.id;

-- 2. LEFT JOIN - Get all categories and their menu items (even empty categories)
SELECT c.name as category_name, m.name as item_name
FROM categories c
LEFT JOIN menu_items m ON c.id = m.category_id;

-- 3. RIGHT JOIN - Get all menu items and their order details (if any)
SELECT m.name as item_name, oi.quantity, oi.unit_price
FROM order_items oi
RIGHT JOIN menu_items m ON oi.menu_item_id = m.id;

-- 4. Multiple table JOIN - Get complete order details
SELECT o.customer_name, m.name as item_name, oi.quantity, oi.unit_price
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
JOIN menu_items m ON oi.menu_item_id = m.id;

-- ========================================
-- PART 6: WILDCARDS AND LIKE STATEMENTS
-- ========================================

-- 1. Names starting with 'J'
SELECT customer_name, phone 
FROM orders 
WHERE customer_name LIKE 'J%';

-- 2. Menu items containing 'pizza' (case insensitive)
SELECT name, price 
FROM menu_items 
WHERE name LIKE '%pizza%';

-- 3. Email addresses containing '@gmail.com'
SELECT name, email 
FROM reservations 
WHERE email LIKE '%@gmail.com%';

-- 4. Addresses containing 'Street'
SELECT customer_name, address 
FROM orders 
WHERE address LIKE '%Street%';

-- 5. Names ending with 'n'
SELECT name, phone 
FROM reservations 
WHERE name LIKE '%n';

-- ========================================
-- EXTRA USEFUL QUERIES
-- ========================================

-- Get total items sold by category
SELECT c.name as category, COUNT(oi.id) as items_sold
FROM categories c
LEFT JOIN menu_items m ON c.id = m.category_id
LEFT JOIN order_items oi ON m.id = oi.menu_item_id
GROUP BY c.id, c.name;

-- Get customer order history
SELECT o.customer_name, COUNT(o.id) as total_orders, SUM(o.total_amount) as total_spent
FROM orders o
GROUP BY o.customer_name
ORDER BY total_spent DESC;

-- Get popular menu items
SELECT m.name, SUM(oi.quantity) as total_ordered
FROM menu_items m
LEFT JOIN order_items oi ON m.id = oi.menu_item_id
GROUP BY m.id, m.name
ORDER BY total_ordered DESC;


