<?php
//Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);//If it goes live change to 0

//Start the session
session_start();

//Include the database connection file using require_once to ensure critical dependency
require_once "database.php";

$error = "";

//Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //Check for empty fields
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        //Prepare SQL statement to fetch user details
        $stmt = $conn->prepare("SELECT id, full_name, password_hash, role FROM serenity_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        //Check if user exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            //Verify the password hash
            if (password_verify($password, $hashed_password)) {
                //Security: Prevent session fixation
                session_regenerate_id(true);
                
                //Set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;
                $_SESSION['is_logged_in'] = true;

                //Redirect based on user role
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($role === 'therapist') {
                    header("Location: landing_therapistpage.php");
                } else {
                    header("Location: Landing.php");
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Serenity</title>
    <link rel="stylesheet" href="../CSS/auth.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /*Mobile responsiveness adjustments*/
        @media screen and (max-width: 768px) {
            .split-screen {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }
            .left-pane {
                width: 100%;
                height: 200px;
                flex: none;
            }
            .right-pane {
                width: 100%;
                flex: 1;
                padding: 30px 20px;
            }
            .pane-text {
                display: none;
            }
        }
    </style>
</head>
<body class="auth-body">
    
    <div class="split-screen">
        <div class="left-pane">
            <div class="pane-text">
                <h2>Welcome Back.</h2>
                <p>Your journey to peace continues here. Reconnect with your resources and support system.</p>
            </div>
        </div>

        <div class="right-pane">
            <div class="form-header">
                <h2>Log In</h2>
                <p>Enter your details to access your account</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>

                <button type="submit" class="auth-button">Log In</button>
            </form>

            <div class="auth-footer">
                <p>New here? <a href="Signup.php">Create an account</a></p>
            </div>
        </div>
    </div>

</body>
</html>