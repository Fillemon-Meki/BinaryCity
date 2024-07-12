<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $adminUsername = $_POST['username'];
  $adminPassword = $_POST['password'];

  // Prepare statement
  $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
  $stmt->bind_param("s", $adminUsername);
  $stmt->execute();
  $result = $stmt->get_result();
  $admin = $result->fetch_assoc();

  if ($admin && password_verify($adminPassword, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    header("Location: index.php");
    exit();
  } else {
    $error = "Invalid username or password";
  }

  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>
    <form action="login.php" method="POST">
      <div class="input-group">
        <span class="icon"><i class="fas fa-user"></i></span>
        <input type="text" name="username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <span class="icon"><i class="fas fa-lock"></i></span>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit">Login</button>
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
  </div>
</body>
</html>