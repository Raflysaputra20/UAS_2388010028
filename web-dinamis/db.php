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
?>
