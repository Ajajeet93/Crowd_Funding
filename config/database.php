<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'crowdfunding');
define('DB_USER', 'root');
define('DB_PASS', 'Ajeet'); // Your original password
define('DB_PORT', '3307'); // Your original port

try {
    // First, try to connect without database to create it
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($stmt->rowCount() == 0) {
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        echo "Database created successfully<br>";
    }
    
    // Connect to the database
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
} catch(PDOException $e) {
    die("<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>" .
        "<h3>Database Connection Error</h3>" .
        "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>" .
        "<p>Please check:</p>" .
        "<ol>" .
        "<li>Is MySQL running in XAMPP?</li>" .
        "<li>Is the port number correct? (Current: " . DB_PORT . ")</li>" .
        "<li>Are the database credentials correct?</li>" .
        "<li>Does the database '" . DB_NAME . "' exist?</li>" .
        "</ol>" .
        "<p>Steps to fix:</p>" .
        "<ol>" .
        "<li>Open XAMPP Control Panel</li>" .
        "<li>Check if MySQL is running (should show green)</li>" .
        "<li>If not running, click 'Start' next to MySQL</li>" .
        "<li>Check MySQL port in XAMPP's my.ini file</li>" .
        "<li>Verify database credentials in config/database.php</li>" .
        "</ol>" .
        "</div>");
}
?> 