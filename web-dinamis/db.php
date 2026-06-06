<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'uas_2388010028';
$password = getenv('DB_PASSWORD') ?: '123456';
$dbname = getenv('DB_NAME') ?: 'uas_2388010028';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, we can log it but let's keep it simple for now
    die("Database connection failed. Please check your database settings or verify if MySQL is running. Error: " . $conn->connect_error);
}

// Auto-migration: Check if packages table exists, create and seed it if missing
$table_check = $conn->query("SHOW TABLES LIKE 'packages'");
if ($table_check && $table_check->num_rows == 0) {
    $conn->query("CREATE TABLE packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        points INT NOT NULL,
        price INT NOT NULL
    )");
    
    // Seed default packages
    $conn->query("INSERT INTO packages (points, price) VALUES
        (120, 15000),
        (625, 70000),
        (1375, 150000),
        (2400, 250000),
        (4000, 400000),
        (8150, 800000)
    ");
}

// Auto-migration: Check if transactions table exists, create it if missing
$trans_check = $conn->query("SHOW TABLES LIKE 'transactions'");
if ($trans_check && $trans_check->num_rows == 0) {
    $conn->query("CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        riot_id VARCHAR(50) NOT NULL,
        vp_amount INT NOT NULL,
        price INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}
?>
