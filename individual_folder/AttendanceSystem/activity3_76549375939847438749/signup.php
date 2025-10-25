<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ashesi Attendance System | Sign Up</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-body">
<div class="auth-container">
    <h1>Create Account</h1>

    <form action="action.php" method="POST">
        <input type="hidden" name="action" value="signup">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="fi">Faculty Intern</option>
            <option value="other">Other Role</option>
        </select>
        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="index.php">Login</a></p>
</div>
</body>
</html>
