<?php
//Enable error reporting to help in debugging issues during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Start the session to allow storage of user data across different pages
session_start();

//Include the external database connection file
require_once "database.php";

//Security Check: Ensure user is logged in AND is actually an ADMIN
//If they are not an admin, kick them back to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

//Handle verification actions (Approve/Reject Therapists)
if (isset($_POST['action']) && isset($_POST['t_id'])) {
    $action = $_POST['action'];//approve or reject
    $target_id = $_POST['t_id'];
    
    //Update the verification status in the therapist_profiles table
    $stmt = $conn->prepare("UPDATE therapist_profiles SET verification_status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $action, $target_id);
    
    if($stmt->execute()) {
        $message = "Therapist status updated to " . htmlspecialchars($action) . "!";
    }
    $stmt->close();
}

//Fetch the Admin's basic information
$admin = $conn->query("SELECT * FROM serenity_users WHERE id=$user_id")->fetch_assoc();

//Fetch all Pending Therapists who need verification
$pending_therapists = $conn->query("SELECT u.id, u.full_name, u.email, tp.verification_status
                                    FROM serenity_users u
                                    JOIN therapist_profiles tp ON u.id = tp.user_id
                                    WHERE tp.verification_status = 'pending'");

//Fetch all users just for the count/stats
$all_users = $conn->query("SELECT role, COUNT(*) as count FROM serenity_users GROUP BY role");
$stats = [];
while($row = $all_users->fetch_assoc()) {
    $stats[$row['role']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Serenity</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/therapist_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .sidebar { background-color: #2c3e50; }
        .sidebar h2 { color: #ecf0f1; }
        

        .btn-approve { background-color: #27ae60; color: white; border:none; padding: 5px 10px; border-radius:4px; cursor:pointer; font-weight:bold;}
        .btn-reject { background-color: #c0392b; color: white; border:none; padding: 5px 10px; border-radius:4px; cursor:pointer; font-weight:bold;}
        
        .stat-box {
            background: white; padding: 20px; border-radius: 8px; text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: 1px solid #ddd;
        }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { font-size: 14px; color: #7f8c8d; text-transform: uppercase; }
    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="dashboard-wrapper">
        
        <div class="sidebar">
            <div class="sidebar-title">Admin Menu</div>
            
            <a href="admin_dashboard.php" class="menu-link active">
                <i class="fas fa-chart-pie" style="margin-right:10px;"></i> Overview
            </a>
            
            <a href="admin_manage_users.php" class="menu-link">
                <i class="fas fa-users" style="margin-right:10px;"></i> Manage Users
            </a>

            <a href="admin_resources.php" class="menu-link">
                <i class="fas fa-book" style="margin-right:10px;"></i> Resources
            </a>
            
            <a href="logout.php" class="menu-link logout">
                <i class="fas fa-sign-out-alt" style="margin-right:10px;"></i> Sign Out
            </a>
        </div>

        <div class="content">
            
            <div class="page-header">
                <div>
                    <h1>Welcome, Admin <?php echo htmlspecialchars($admin['full_name']); ?></h1>
                    <p style="margin:5px 0 0; color:#777;">System Overview and Verification Tasks</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="stat-box">
                    <div class="stat-number"><?php echo isset($stats['client']) ? $stats['client'] : 0; ?></div>
                    <div class="stat-label">Clients</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo isset($stats['therapist']) ? $stats['therapist'] : 0; ?></div>
                    <div class="stat-label">Therapists</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo isset($stats['admin']) ? $stats['admin'] : 0; ?></div>
                    <div class="stat-label">Admins</div>
                </div>
            </div>

            <div class="grid-container">
                
                <div class="card" style="grid-column: 1 / -1;">
                    <h3>
                        <span>Pending Therapist Verifications</span>
                        <?php if ($pending_therapists->num_rows > 0): ?>
                            <span style="font-size:12px; background:#e74c3c; color:white; padding:2px 8px; border-radius:10px;">Needs Action</span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if ($message): ?>
                        <div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($pending_therapists->num_rows > 0): ?>
                        <table style="width:100%; border-collapse: collapse;">
                            <tr style="text-align:left; color:#777; border-bottom:1px solid #eee;">
                                <th style="padding:10px;">Name</th>
                                <th style="padding:10px;">Email</th>
                                <th style="padding:10px;">Status</th>
                                <th style="padding:10px;">Action</th>
                            </tr>
                            <?php while($t = $pending_therapists->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #f9f9f9;">
                                    <td style="padding:15px 10px;"><strong><?php echo htmlspecialchars($t['full_name']); ?></strong></td>
                                    <td style="padding:15px 10px;"><?php echo htmlspecialchars($t['email']); ?></td>
                                    <td style="padding:15px 10px;"><span style="background:#fff3cd; padding:3px 8px; border-radius:4px; font-size:12px;">Pending</span></td>
                                    <td style="padding:15px 10px;">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="t_id" value="<?php echo $t['id']; ?>">
                                            <button name="action" value="approved" class="btn-approve">Approve</button>
                                            <button name="action" value="rejected" class="btn-reject">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px 0; color:#aaa;">
                            <i class="fas fa-check-circle" style="font-size:30px; margin-bottom:10px; color:#27ae60;"></i>
                            <p>All therapists are verified.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include("../HTML/footer.html"); ?>

</body>
</html>