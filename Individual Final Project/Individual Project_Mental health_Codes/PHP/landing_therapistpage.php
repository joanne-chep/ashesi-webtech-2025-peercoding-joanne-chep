<?php
//Enable error reporting for development purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Start the session to access logged-in user data
session_start();
include("database.php");

//Ensure user is logged in and is actually a therapist
//If they are not a therapist, kick them back to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

//Handle booking actions: When a therapist clicks Accept or Decline
if (isset($_POST['action'])) {
    $action = $_POST['action']; //Get the action (Confirmed or Cancelled)
    $bid = $_POST['booking_id']; //Get the specific booking ID
    
    //Prepare a secure statement to update the booking status
    //We also check 'therapist_id' to ensure therapists can only update their own appointments
    $updateStmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=? AND therapist_id=?");
    $updateStmt->bind_param("sii", $action, $bid, $user_id);
    
    //Execute the update
    if($updateStmt->execute()) {
        //Action successful, the page will reload and show the new status
    }
}

//Fetch the therapist's basic information
$therapist = $conn->query("SELECT * FROM serenity_users WHERE id=$user_id")->fetch_assoc();

//Fetch verification status from the profile table
$status_row = $conn->query("SELECT verification_status FROM therapist_profiles WHERE user_id=$user_id")->fetch_assoc();
$status = $status_row ? $status_row['verification_status'] : 'pending';

//Fetch 'Pending' requests to show in the "Session Requests" card
//We join with the user table to get the client's name, email and profile image
$requests = $conn->query("SELECT b.*, u.full_name, u.email as client_email, u.profile_image
                        FROM bookings b
                        JOIN serenity_users u ON b.user_id=u.id
                        WHERE b.therapist_id=$user_id AND b.status='Pending'
                        ORDER BY date ASC");

//Fetch 'Confirmed' appointments to show in the "Upcoming Schedule" card
//We join with the user table to get the client's name, email AND profile image
$schedule = $conn->query("SELECT b.*, u.full_name, u.email as client_email, u.profile_image
                        FROM bookings b
                        JOIN serenity_users u ON b.user_id=u.id
                        WHERE b.therapist_id=$user_id AND b.status='Confirmed'
                        ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Portal | Serenity</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/therapist_dashboard.css?v=<?php echo time(); ?>">

    <style>
        .client-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .client-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
            flex-shrink: 0;
        }
        .req-item, .cal-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        @media (max-width: 600px) {
            .req-item form, .cal-item a {
                margin-left: auto;
            }
        }
    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="dashboard-wrapper">
        
        <div class="sidebar">
            <div class="sidebar-title">Main Menu</div>
            
            <a href="landing_therapistpage.php" class="menu-link active">
                <i class="fas fa-home" style="margin-right:10px;"></i> Dashboard
            </a>
            
            <a href="#" class="menu-link" onclick="alert('Patient Records module coming soon!'); return false;">
                <i class="fas fa-folder-open" style="margin-right:10px;"></i> Patient Records
            </a>
            
            <a href="therapist_profile.php" class="menu-link">
                <i class="fas fa-user-md" style="margin-right:10px;"></i> My Profile
            </a>

            <a href="therapist_settings.php" class="menu-link">
                <i class="fas fa-cog" style="margin-right:10px;"></i> Settings
            </a>
            
            <a href="logout.php" class="menu-link logout">
                <i class="fas fa-sign-out-alt" style="margin-right:10px;"></i> Sign Out
            </a>
        </div>

        <div class="content">
            
            <div class="page-header">
                <div>
                    <h1>Dr. <?php echo htmlspecialchars($therapist['full_name']); ?></h1>
                    <p style="margin:5px 0 0; color:#777;">Overview of your upcoming appointments.</p>
                </div>
                <span class="status-badge">
                    <i class="fas fa-check-circle" style="margin-right:5px;"></i> <?php echo strtoupper($status); ?>
                </span>
            </div>

            <div class="grid-container">
                
                <div class="card">
                    <h3>
                        <span>Session Requests</span>
                        <span style="font-size:12px; background:#e74c3c; color:white; padding:2px 8px; border-radius:10px;">Needs Action</span>
                    </h3>
                    
                    <?php if ($requests->num_rows > 0): ?>
                        <?php while($r = $requests->fetch_assoc()): ?>
                            <?php $img = !empty($r['profile_image']) ? $r['profile_image'] : "https://cdn-icons-png.flaticon.com/512/847/847969.png"; ?>
                            <div class="req-item">
                                <div class="client-row">
                                    <img src="<?php echo htmlspecialchars($img); ?>" class="client-avatar">
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($r['full_name']); ?></strong>
                                        <small>
                                            <i class="far fa-clock"></i> <?php echo date('M d @ g:i A', strtotime($r['date'].' '.$r['time'])); ?>
                                            &nbsp;|&nbsp; <?php echo $r['session_type']; ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="booking_id" value="<?php echo $r['id']; ?>">
                                    <button name="action" value="Confirmed" class="btn btn-yes" title="Accept"><i class="fas fa-check"></i></button>
                                    <button name="action" value="Cancelled" class="btn btn-no" title="Decline"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px 0; color:#aaa;">
                            <i class="fas fa-inbox" style="font-size:30px; margin-bottom:10px;"></i>
                            <p>No pending requests right now.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Upcoming Schedule</h3>
                    
                    <?php if ($schedule->num_rows > 0): ?>
                        <?php while($s = $schedule->fetch_assoc()): ?>
                            <?php $img = !empty($s['profile_image']) ? $s['profile_image'] : "https://cdn-icons-png.flaticon.com/512/847/847969.png"; ?>
                            <div class="cal-item">
                                <div class="client-row">
                                    <img src="<?php echo htmlspecialchars($img); ?>" class="client-avatar">
                                    <div>
                                        <span class="cal-date"><?php echo date('M d, Y', strtotime($s['date'])); ?></span>
                                        <span class="cal-time">
                                            <?php echo date('g:i A', strtotime($s['time'])); ?> — <?php echo htmlspecialchars($s['full_name']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="mailto:<?php echo $s['client_email']; ?>?subject=Regarding our Session on <?php echo $s['date']; ?>" 
                                class="contact-btn" title="Email Client">
                                <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#999; text-align:center; padding: 20px;">No confirmed sessions yet.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include("../HTML/footer.html"); ?>

</body>
</html>