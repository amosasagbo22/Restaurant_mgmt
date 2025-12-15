<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$pdo = get_pdo();

echo "<h2>Database Check</h2>";

// Check if users table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "✅ Users table exists<br>";
        
        // Check users table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<h3>Users table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>{$col['Field']} - {$col['Type']}</li>";
        }
        echo "</ul>";
        
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✅ Admin user exists<br>";
            echo "ID: {$admin['id']}<br>";
            echo "Username: {$admin['username']}<br>";
            echo "Password hash: " . substr($admin['password'], 0, 20) . "...<br>";
            
            // Test password verification
            if ($admin['password'] === 'admin123') {
                echo "✅ Password 'admin123' is correct<br>";
            } else {
                echo "❌ Password 'admin123' is incorrect<br>";
            }
        } else {
            echo "❌ Admin user does not exist<br>";
            echo "<a href='create_admin.php'>Create Admin User</a><br>";
        }
        
    } else {
        echo "❌ Users table does not exist<br>";
        echo "Please import the database.sql file first<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='admin/login.php'>Go to Admin Login</a>";
?> 