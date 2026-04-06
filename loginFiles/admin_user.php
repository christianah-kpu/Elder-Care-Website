<?php
require 'db_connection.php';

$username       = 'admin_user';
$email          = 'admin@example.com';
$password       = 'AdminPass123';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$role           = 'admin';
$createdAt      = date('Y-m-d H:i:s');

try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, is_verified, created_at) 
                            VALUES (?, ?, ?, ?, 1, ?)");
    $stmt->execute([$username, $email, $hashedPassword, $role, $createdAt]);
    echo "Admin user created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $stmt = null;  // PDO way to "close" a statement
    $conn = null;  // PDO way to "close" a connection
}
?>