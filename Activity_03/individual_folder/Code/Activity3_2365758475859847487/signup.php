<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            header("Location: signup.php?error=" . urlencode("All fields are required"));
            exit();
        }

        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            header("Location: signup.php?error=" . urlencode("Email already exists"));
            exit();
        }

        $check->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
        $stmt->execute();
        $stmt->close();

        header("Location: login.php?success=" . urlencode("Account created, please log in"));
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    error_log("MySQL error: " . $ex->getMessage());
    header("Location: signup.php?error=" . urlencode("Database error occurred"));
    exit();
} catch (Exception $ex) {
    error_log("General error: " . $ex->getMessage());
    header("Location: signup.php?error=" . urlencode("An unexpected error occurred"));
    exit();
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ashesi Attendance System | Sign Up</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h1>Create Account</h1>
    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php elseif (isset($_GET['success'])): ?>
        <p style="color:green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>
    <form action="signup.php" method="POST" class="auth-form">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="fi">Faculty Intern</option>
            <option value="other">Other Role</option>
        </select>
        <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
