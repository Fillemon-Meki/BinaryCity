<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Update with your MySQL password
$dbname = "bcity_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
?>
