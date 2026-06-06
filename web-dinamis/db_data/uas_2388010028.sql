-- Create database if not exists and use it
CREATE DATABASE IF NOT EXISTS uas_2388010028;
USE uas_2388010028;

-- Drop table transactions if it exists
DROP TABLE IF EXISTS transactions;

-- Create transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    riot_id VARCHAR(50) NOT NULL,
    vp_amount INT NOT NULL,
    price INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed some sample transactions
INSERT INTO transactions (riot_id, vp_amount, price, payment_method, status) VALUES
('TenZ#NA1', 2400, 250000, 'Dana', 'success'),
('Shroud#NA1', 8150, 800000, 'GoPay', 'success'),
('f0resT#EU1', 625, 75000, 'OVO', 'pending');

-- Drop table packages if it exists
DROP TABLE IF EXISTS packages;

-- Create packages table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    points INT NOT NULL,
    price INT NOT NULL
);

-- Seed default VP packages
INSERT INTO packages (points, price) VALUES
(120, 15000),
(625, 70000),
(1375, 150000),
(2400, 250000),
(4000, 400000),
(8150, 800000);
