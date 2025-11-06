<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['loginEmail']) || empty($_POST['loginPassword'])) {
            header("Location: login.php?error=" . urlencode("Please enter email and password"));
            exit();
        }

        $email = trim($_POST['loginEmail']);
        $password = trim($_POST['loginPassword']);

        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            header("Location: login.php?error=" . urlencode("Invalid email or password"));
            exit();
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($password, $user['password'])) {
            header("Location: login.php?error=" . urlencode("Invalid email or password"));
            exit();
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($_SESSION['role'] === 'fi') {
            header("Location: fi-dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    error_log("MySQL error: " . $ex->getMessage());
    header("Location: login.php?error=" . urlencode("A database error occurred."));
    exit();
} catch (Exception $ex) {
    error_log("General error: " . $ex->getMessage());
    header("Location: login.php?error=" . urlencode("An unexpected error occurred."));
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ashesi Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-logo">
            <img src="Ashesi.jpeg" alt="Ashesi University" class="auth-bg-logo">
            <h1>Ashesi Attendance System</h1>
        </div>
        <div class="form-container">
            <form id="login-form" class="auth-form" method="POST" action="">
                <h2>Login</h2>
                <?php if (isset($_GET['error'])): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php elseif (isset($_GET['success'])): ?>
                    <p style="color:green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
                <?php endif; ?>
                <input type="email" name="loginEmail" placeholder="Email" required>
                <input type="password" name="loginPassword" placeholder="Password" required>
                <button type="submit">Login</button>
                <p>Don’t have an account? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </div>
</body>
</html>
