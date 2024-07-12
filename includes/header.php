<?php
session_start();
require_once 'includes/db_connect.php'; // Adjust the path as per your file structure

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch admin's name from the database based on session
$adminId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->bind_param("i", $adminId); // "i" for integer type
$stmt->execute();
$result = $stmt->get_result();
$adminName = $result->fetch_assoc()['username'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<header class="site-header">
    <div class="container">
      
        <div class="user">
            
            <a href="index.php">Home</a>
            
            <span>Welcome, <?php echo htmlspecialchars($adminName); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<!-- Rest of the body content goes here -->

</body>
</html>
