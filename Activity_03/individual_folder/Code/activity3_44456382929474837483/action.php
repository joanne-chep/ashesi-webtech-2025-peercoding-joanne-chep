<?php
session_start();
include "db.php";

$conn = connectDB();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $_SESSION['temp_signup_data'] = $_POST;

    $data_to_display = array(
        "Full Name" => $_POST['name'],
        "Email"     => $_POST['email'],
        "Role"      => $_POST['role']
    );
} 
elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'confirm_insert' && isset($_SESSION['temp_signup_data'])) {

    $data = $_SESSION['temp_signup_data'];

    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check->bind_param("s", $data['email']);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        unset($_SESSION['temp_signup_data']);
        header("Location: ../signup.php?error=Email already exists");
        exit();
    }
    
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $data['name'], $data['email'], $password, $data['role']);
    $stmt->execute();

    unset($_SESSION['temp_signup_data']);
    header("Location: ../index.php?success=Account created, please log in");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Registration Data</title>
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
    <h1>Review and Confirm Registration</h1>

    <?php if (isset($data_to_display)): ?>
        <p>Please review the details you entered before final submission (Fulfilling Requirement b(b)).</p>
        
        <table border="1" style="width:50%; border-collapse: collapse;">
            <tr><th>Field Name</th><th>Value</th></tr>
            <?php 
            foreach ($data_to_display as $field => $value): ?>
                <tr><td><?php echo $field; ?></td><td><?php echo htmlspecialchars($value); ?></td></tr>
            <?php endforeach; ?>
        </table>
        
        <p>The password will be securely hashed upon confirmation.</p>

        <form action="action.php" method="POST">
            <input type="hidden" name="action" value="confirm_insert">
            <button type="submit">Confirm and Complete Registration</button>
        </form>
    <?php else: ?>
        <p>No data submitted for review. Please go back to the <a href="../signup.php">Sign Up</a> page.</p>
    <?php endif; ?>

</body>
</html>