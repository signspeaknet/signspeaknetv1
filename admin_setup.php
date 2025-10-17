<?php
/**
 * Admin Setup Script
 * Creates default admin user and sets up the admin system
 */

include 'config.php';

// Check if admin_users table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'admin_users'")->num_rows > 0;

if (!$table_exists) {
    echo "Error: admin_users table does not exist. Please import the sslocal.sql file first.";
    exit();
}

// Check if admin already exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin_exists = $result->fetch_assoc()['count'] > 0;

if ($admin_exists) {
    echo "Admin user already exists. Setup complete.";
    exit();
}

// Create default admin user
$username = 'admin';
$password = 'admin123'; // Change this in production
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$email = 'admin@signspeak.com';
$role = 'super_admin';
$status = 'active';

$stmt = $conn->prepare("
    INSERT INTO admin_users (username, password, email, role, status, created_at, last_login) 
    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
");

$stmt->bind_param("sssss", $username, $hashed_password, $email, $role, $status);

if ($stmt->execute()) {
    echo "Default admin user created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br>Please change these credentials after first login for security.<br>";
    echo "<a href='admin_login.php'>Go to Admin Login</a>";
} else {
    echo "Error creating admin user: " . $conn->error;
}

$stmt->close();
$conn->close();
?> 