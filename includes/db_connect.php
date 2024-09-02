<?php
$host = 'localhost'; // Your database host
$port = '5432'; // Your PostgreSQL port (default is 5432)
$dbname = 'festmanagement'; // Your database name
$username = 'postgres'; // Your database username
$password = 'postgres'; // Your database password

// Set up the DSN with port
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
