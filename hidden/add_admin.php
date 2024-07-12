<?php
include 'includes/db_connect.php';

// Admin details
$adminUsername = 'Fillemon Meki';
$adminPassword = 'Password@123'; // Use a secure password in a real application

// Hash the password
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Insert admin into the database
$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $adminUsername, $hashedPassword);

if ($stmt->execute()) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
