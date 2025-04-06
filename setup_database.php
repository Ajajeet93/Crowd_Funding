<?php
// Database configuration
$host = 'localhost';
$port = 3307;
$user = 'root';
$pass = 'Ajeet';
$dbname = 'crowdfunding';

try {
    // First, try to connect without database to create it
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database '$dbname' created successfully<br>";
    
    // Connect to the created database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Users table created successfully<br>";
    
    // Create projects table
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        funding_goal DECIMAL(10,2) NOT NULL,
        current_amount DECIMAL(10,2) DEFAULT 0.00,
        image_url VARCHAR(255),
        category VARCHAR(50) NOT NULL,
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "Projects table created successfully<br>";
    
    // Create donations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS donations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "Donations table created successfully<br>";
    
    // Create comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "Comments table created successfully<br>";
    
    // Insert sample data
    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO users (username, password, full_name, email) VALUES 
            ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@example.com')");
        echo "Admin user created successfully<br>";
    }
    
    // Check if sample project exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE title = 'Sample Project'");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO projects (user_id, title, description, funding_goal, category) VALUES 
            (1, 'Sample Project', 'This is a sample project description.', 1000.00, 'Technology')");
        echo "Sample project created successfully<br>";
    }
    
    echo "<br>Database setup completed successfully!<br>";
    echo "You can now access the website at: <a href='http://localhost/Crowd_Funding'>http://localhost/Crowd_Funding</a><br>";
    echo "Admin login credentials:<br>";
    echo "Username: admin<br>";
    echo "Password: password<br>";
    
} catch(PDOException $e) {
    die("<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>" .
        "<h3>Database Connection Error</h3>" .
        "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>" .
        "<p>Please check:</p>" .
        "<ol>" .
        "<li>Is MySQL running in XAMPP?</li>" .
        "<li>Is the port number correct? (Current: " . $port . ")</li>" .
        "<li>Are the database credentials correct?</li>" .
        "<li>Does the database '" . $dbname . "' exist?</li>" .
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