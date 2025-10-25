<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'fi') {
        header("Location: fi-dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ashesi Attendance System | Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h1>Ashesi Attendance System</h1>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo $_GET['error']; ?></p>
    <?php endif; ?>

    <form action="php/action.php" method="POST">
    <input type="hidden" name="action" value="login">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<p>Don’t have an account? <a href="php/signup.php">Sign up</a></p>
</div>
</body>
</html>
