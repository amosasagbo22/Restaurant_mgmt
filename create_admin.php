<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$pdo = get_pdo();

// Create admin user
$username = 'admin';
$password = 'admin123'; // Change this to your desired password

try {
    // First check if admin already exists
    $check_stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $check_stmt->execute([$username]);
    $existing = $check_stmt->fetch();
    
    if ($existing) {
        // Update existing admin password
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
        $stmt->execute([$password, $username]);
        echo "Admin user password updated successfully!<br>";
    } else {
        // Create new admin user
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $password]);
        echo "Admin user created successfully!<br>";
    }
    
    echo "Username: $username<br>";
    echo "Password: $password<br>";
    echo "<a href='admin/login.php'>Go to Admin Login</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Make sure the database is imported and the users table exists.<br>";
}
?> 