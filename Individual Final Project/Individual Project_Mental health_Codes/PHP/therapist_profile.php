<?php
//Enable error reporting to see if something is crashing
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("database.php");

//If not logged in, go to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//Ensure that only therapists allowed here
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'therapist') {
    header("Location: client_profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Fetch therapist details
//We use a left join just in case the therapist_profiles entry is missing
$query = "SELECT u.full_name, u.email, u.profile_image, tp.* FROM serenity_users u
        LEFT JOIN therapist_profiles tp ON u.id = tp.user_id
        WHERE u.id = $user_id";

$result = $conn->query($query);

//Check if query failed
if (!$result) {
    die("Database Error: " . $conn->error);
}

$user = $result->fetch_assoc();

//Set defaults if data is missing
$name = htmlspecialchars($user['full_name'] ?? 'Therapist');
$spec = htmlspecialchars($user['specialization'] ?? 'General Therapy');
$rate = htmlspecialchars($user['hourly_rate'] ?? '0');
$city = htmlspecialchars($user['city'] ?? 'Online');
$bio = nl2br(htmlspecialchars($user['bio'] ?? 'No biography added yet.'));
$status = ucfirst($user['verification_status'] ?? 'Pending');

//Set default image
$current_image = !empty($user['profile_image']) ? $user['profile_image'] : "https://cdn-icons-png.flaticon.com/512/847/847969.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Professional Profile | Serenity</title>
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/client_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge-verified {
            background-color: #d1fae5;
            color: #065f46;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 5px;
            display: inline-block;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .detail-label {
            font-size: 12px;
            color: #777;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include("Header.php"); ?>

    <div class="dash-header" style="background: #2C3E50;">
        <h1>Dr. <?php echo $name; ?></h1>
        <p style="opacity: 0.8; color: white;">This is how you appear to clients.</p>
    </div>

    <div class="container">
        
        <div class="profile-card card">
            <img src="<?php echo htmlspecialchars($current_image); ?>" class="profile-pic">
            <h2 class="user-name">Dr. <?php echo $name; ?></h2>
            <div class="badge-verified">
                <i class="fas fa-check-circle"></i> <?php echo $status; ?>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            
            <a href="therapist_settings.php" class="btn-save" style="text-decoration:none; display:block; text-align:center; margin-bottom:10px;">Edit Profile</a>
            <a href="landing_therapistpage.php" class="contact-btn" style="text-decoration:none; display:block; text-align:center; background:#eee; color:#333;">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn" style="margin-top:10px;">Log Out</a>
        </div>

        <div>
            <div class="card">
                <h3 class="section-title">Professional Details</h3>
                
                <div class="detail-grid">
                    <div class="detail-box">
                        <span class="detail-label">Specialization</span>
                        <span class="detail-value"><?php echo $spec; ?></span>
                    </div>
                    <div class="detail-box">
                        <span class="detail-label">Hourly Rate</span>
                        <span class="detail-value">$<?php echo $rate; ?> / hr</span>
                    </div>
                    <div class="detail-box">
                        <span class="detail-label">Location</span>
                        <span class="detail-value"><?php echo $city; ?></span>
                    </div>
                </div>

                <h4 style="margin-top: 30px; color: #4A7C59;">Biography</h4>
                <div style="background: #fff; padding: 20px; border-left: 4px solid #4A7C59; line-height: 1.6; color: #555;">
                    <?php echo $bio; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("../HTML/footer.html"); ?>
</body>
</html>