<?php
//Enable error reporting to help in debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Start the session
session_start();
include("database.php");

//Check if the user is logged in
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

//Ensure that only clients can access this page. Therapists and admins cannot see this page.
//If a therapist tries to access this, send them to their professional profile instead
if (isset($_SESSION['role']) && $_SESSION['role'] === 'therapist') {
    header("Location: therapist_profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

//Handle form submission for updating profile details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = trim($_POST['full_name']); //Get new name
    $new_email = trim($_POST['email']); //Get new email
    $new_image = trim($_POST['profile_image']); //Get new image URL
    $new_pass = trim($_POST['new_password']); //Get new password if set
    
    //Use default image if none is provided
    if(empty($new_image)) {
        $new_image = "https://cdn-icons-png.flaticon.com/512/847/847969.png";
    }

    //If password field is not empty, update password too
    if (!empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT); //Hash the new password
        $stmt = $conn->prepare("UPDATE serenity_users SET full_name=?, email=?, profile_image=?, password_hash=? WHERE id=?");
        $stmt->bind_param("ssssi", $new_name, $new_email, $new_image, $hashed, $user_id);
    } else {
        //Otherwise, just update the personal details
        $stmt = $conn->prepare("UPDATE serenity_users SET full_name=?, email=?, profile_image=? WHERE id=?");
        $stmt->bind_param("sssi", $new_name, $new_email, $new_image, $user_id);
    }

    //Execute the update and show a message
    if ($stmt->execute()) {
        $message = "Your profile has been successfully updated!";
        $_SESSION['name'] = $new_name; //Update session name
    } else {
        $message = "We hit a snag updating your profile. Please try again.";
    }
}

//Fetch current user details to display on the page
$user = $conn->query("SELECT * FROM serenity_users WHERE id=$user_id")->fetch_assoc();
$current_image = !empty($user['profile_image']) ? $user['profile_image'] : "https://cdn-icons-png.flaticon.com/512/847/847969.png";

//Count saved resources and confirmed sessions for the dashboard stats
$res_count = $conn->query("SELECT id FROM saved_resources WHERE user_id=$user_id")->num_rows;
$book_count = $conn->query("SELECT id FROM bookings WHERE user_id=$user_id AND status='Confirmed'")->num_rows;

//Fetch all bookings for this client, joining with user table to get doctor names
$query = "SELECT b.*, u.full_name as doc_name, u.email as doc_email
        FROM bookings b
        JOIN serenity_users u ON b.therapist_id = u.id
        WHERE b.user_id = $user_id
        ORDER BY b.date ASC";

$sessions = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Serenity</title>
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/client_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include("Header.php"); ?>

    <div class="dash-header">
        <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?></h1>
        <p style="opacity: 0.9;">We are glad to see you today.</p>
    </div>

    <div class="container">
        
        <div class="profile-card card">
            <img src="<?php echo htmlspecialchars($current_image); ?>" class="profile-pic">
            <h2 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <div class="stats-container">
                <div class="stat-box">
                    <span class="stat-num"><?php echo $book_count; ?></span>
                    <span class="stat-label">Confirmed<br>Sessions</span>
                </div>
                
                <a href="saved_resources.php" class="stat-box" title="View your saved items">
                    <span class="stat-num"><?php echo $res_count; ?></span>
                    <span class="stat-label">Saved<br>Resources</span>
                </a>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>

        <div>
            <div class="card">
                <h3 class="section-title">My Sessions</h3>
                
                <?php if ($sessions->num_rows > 0): ?>
                    <?php while($s = $sessions->fetch_assoc()): ?>
                        <div class="session-item">
                            <div>
                                <strong class="doc-name">Dr. <?php echo htmlspecialchars($s['doc_name']); ?></strong>
                                <span class="session-time">
                                    <?php echo date('M d, Y', strtotime($s['date'])); ?> at <?php echo date('g:i A', strtotime($s['time'])); ?>
                                </span>
                            </div>
                            
                            <div style="display:flex; align-items:center;">
                                <span class="badge badge-<?php echo $s['status']; ?>"><?php echo $s['status']; ?></span>
                                
                                <?php if($s['status'] === 'Confirmed'): ?>
                                    <a href="mailto:<?php echo $s['doc_email']; ?>?subject=Regarding our Session on <?php echo $s['date']; ?>" 
                                    class="contact-btn"
                                    title="Email Dr. <?php echo htmlspecialchars($s['doc_name']); ?>">
                                    <i class="fas fa-envelope"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 20px;">
                        <p style="color:#888;">You haven't booked any sessions yet.</p>
                        <a href="BookTherapy.php" style="color: #4A7C59; font-weight: 600; text-decoration: none;">Find a Therapist &rarr;</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3 class="section-title">Profile Settings</h3>
                
                <?php if ($message): ?>
                    <div class="alert-box"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    
                    <label>Profile Image URL (Pinterest / Web Link)</label>
                    <input type="text" name="profile_image" value="<?php echo htmlspecialchars($current_image); ?>" placeholder="Paste https://...">
                    
                    <label>Change Password (Optional)</label>
                    <input type="password" name="new_password" placeholder="Type a new password only if you want to change it">
                    
                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include("../HTML/footer.html"); ?>
</body>
</html>