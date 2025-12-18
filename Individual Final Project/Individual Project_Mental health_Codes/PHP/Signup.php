<?php
//Enable error reporting to help in debugging issues during development
error_reporting(E_ALL);
ini_set('display_errors', 1);//If it goes live change to 0

//Start the session to allow storage of user data across different pages
session_start();

//Include the external database connection file using require_once to ensure critical dependency
require_once "database.php";

//Initialize an error variable to store any issues that arise during the signup process
$error = "";

//Check if the form was submitted using the POST method to prevent sharing data in the URL
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Collect data from the form. The trim method will remove accidentally typed spaces before or after the users input
    $name = trim($_POST['name']);//The name field
    $email = trim($_POST['email']);//The email field
    $password = $_POST['password'];//The password field
    $confirm_password = $_POST['confirm_password'];//The confirm password field
    $role = $_POST['role'];//The role field

    //Only allow specific values for security
    $allowed_roles = ['client', 'therapist'];

    //Check if any required fields are empty, these are critical for account creation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please enter all required fields.";
    }
    //Validate email format on the backend
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    //Check if the role is valid
    elseif (!in_array($role, $allowed_roles)) {
        $error = "Invalid role selected.";
    }
    //Enforce password strength on the backend (At least 6 chars, 1 letter, 1 number)
    elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password)) {
        $error = "Password must be at least 6 characters and include letters and numbers.";
    }
    //Check if the password matches the confirmation password, to avoid typos such that one may not even have their correct password saved
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please try again.";
    }
    else {
        //Prepare a SQL statement to check if the email is already in the database
        //Using prepare prevents SQL Injection attacks
        $stmt = $conn->prepare("SELECT id FROM serenity_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        //If we found more than 0 rows, the email is already taken
        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
            $stmt->close();//Close the statement
        } else {
            //Since the email is unique, we can proceed to create the account
            $stmt->close();

            //Secure the passwords in the database by hashing them before storage
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            //Prepare the SQL statement to insert the new user into the database
            $insertStmt = $conn->prepare("INSERT INTO serenity_users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            //Now insert the new user into the database
            if ($insertStmt->execute()) {
                
                //Get the ID of the newly created user from the database to use in session variables
                $user_id = $insertStmt->insert_id;
                
                //If the user is a therapist, create a profile entry for them in the therapist_profiles table
                if ($role === 'therapist') {
                    $profileStmt = $conn->prepare("INSERT INTO therapist_profiles (user_id, verification_status) VALUES (?, 'pending')");//Default verification status is 'pending'
                    $profileStmt->bind_param("i", $user_id);
                    $profileStmt->execute();
                    $profileStmt->close();//Close the therapist statement
                }

                //Log the user in by saving their details to the session
                //Regenerate ID to prevent session fixation this is for security purposes especially after login or signup
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                $_SESSION['name'] = $name;
                $_SESSION['is_logged_in'] = true;

                //Send the user to the correct page based on their role
                if ($role === 'therapist') {
                    header("Location: landing_therapistpage.php");
                } else {
                    header("Location: Landing.php");
                }
                
                //Stop the script to ensure the redirect happens
                exit();

            } else {
                //If the user could not be inserted, show an error message
                $error = "Something went wrong. Please try again!";
            }
            
            //End the insert statement
            $insertStmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Serenity</title>
    <link rel="stylesheet" href="../CSS/auth.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        .password-container {
            position: relative;
            width: 100%;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 0.9rem;
            user-select: none;
        }
        .toggle-password:hover {
            color: #333;
        }
        
        #js-error {
            color: #D8000C;
            background-color: #FFBABA;
            border: 1px solid #D8000C;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: none;
            font-size: 14px;
            text-align: center;
        }

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
        <div class="left-pane" style="background-image: url('https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=1000&auto=format&fit=crop');">
            <div class="pane-text">
                <h2>Experience healing with Serenity!</h2>
                <p>A community dedicated to mental wellness, growth, and finding your inner peace.</p>
            </div>
        </div>

        <div class="right-pane">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>It takes less than a minute to join.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div id="js-error"></div>

            <form action="Signup.php" method="POST" id="signupForm" novalidate>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="name" required placeholder="e.g. Joanne Chepkoech" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="joanne44@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>I am joining as a...</label>
                    <select name="role" required>
                        <option value="client">Client (I need support)</option>
                        <option value="therapist">Therapist (I provide support)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required placeholder="********">
                        <span class="toggle-password" onclick="togglePassword('password')">Show</span>
                    </div>
                    <small style="color: #666; font-size: 11px;">Must include at least 1 letter and 1 number.</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="********">
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">Show</span>
                    </div>
                </div>

                <button type="submit" class="auth-button">Sign Up</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleBtn = passwordField.nextElementSibling;
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                passwordField.type = "password";
                toggleBtn.textContent = "Show";
            }
        }

        //JavaScript form validation to enhance user experience by catching errors before submission
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            const errorDiv = document.getElementById('js-error');
            let errorMessage = "";

            //Check for empty fields
            if (!name || !email || !password || !confirmPassword) {
                errorMessage = "Please fill in all fields.";
            }
            
            //Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!errorMessage && !emailRegex.test(email)) {
                errorMessage = "Please enter a valid email address.";
            }

            //Validate password strength (1 letter, 1 number, 6+ chars)
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{6,}$/;
            if (!errorMessage && !passwordRegex.test(password)) {
                errorMessage = "Password must be 6+ chars and contain at least 1 letter and 1 number.";
            }

            //Check if passwords match
            if (!errorMessage && password !== confirmPassword) {
                errorMessage = "Passwords do not match.";
            }

            //Display error message if validation fails
            if (errorMessage) {
                event.preventDefault();
                errorDiv.style.display = 'block';
                errorDiv.innerText = errorMessage;
                window.scrollTo(0, 0);
            }
        });
    </script>

</body>
</html>