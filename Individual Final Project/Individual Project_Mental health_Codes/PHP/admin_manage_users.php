<?php
//Enable error reporting to catch any hidden crashes
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "database.php";

//Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$search = "";

//Handle Delete Action
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    if ($delete_id == $_SESSION['user_id']) {
        $message = "Error: You cannot delete your own account.";
    } else {
        //Delete dependencies first
        $conn->query("DELETE FROM therapist_profiles WHERE user_id = $delete_id");
        $conn->query("DELETE FROM bookings WHERE user_id = $delete_id OR therapist_id = $delete_id");
        $conn->query("DELETE FROM saved_resources WHERE user_id = $delete_id");
        
        //Delete user
        $stmt = $conn->prepare("DELETE FROM serenity_users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
        $stmt->close();
    }
}

//Handle Search
$where_clause = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $where_clause = "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%'";
}

//Fetch Users
$query = "SELECT * FROM serenity_users $where_clause ORDER BY id DESC";
$users = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Serenity Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { margin: 0; background-color: #f0f2f5; font-family: 'Poppins', sans-serif; }
        
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 260px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        
        .sidebar-title { font-size: 18px; font-weight: bold; margin-bottom: 30px; color: #ecf0f1; border-bottom: 1px solid #34495e; padding-bottom: 15px; }
        
        .menu-link {
            display: block;
            padding: 12px 15px;
            color: #bdc3c7 !important;
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 6px;
            transition: 0.2s;
        }
        .menu-link:hover, .menu-link.active { background-color: #34495e; color: white !important; }
        .menu-link.logout { margin-top: auto; color: #e74c3c !important; }
        
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        
        .page-header h1 { margin: 0; color: #2c3e50; font-size: 28px; }
        
        .card { background: white; border-radius: 8px; padding: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px; }
        
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { padding: 10px; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px; }
        .btn-search { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #eee; }
        .user-table td { padding: 15px; border-bottom: 1px solid #eee; color: #333; }
        
        .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .role-client { background: #e3f2fd; color: #1565c0; }
        .role-therapist { background: #e8f5e9; color: #2e7d32; }
        .role-admin { background: #f3e5f5; color: #7b1fa2; }
        
        .btn-delete { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-delete:hover { background: #c62828; color: white; }
    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="dashboard-wrapper">
        
        <div class="sidebar">
            <div class="sidebar-title">Admin Panel</div>
            
            <a href="admin_dashboard.php" class="menu-link">
                <i class="fas fa-chart-pie" style="margin-right:10px;"></i> Overview
            </a>
            
            <a href="admin_manage_users.php" class="menu-link active">
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
                <h1>Manage Users</h1>
                <p style="color:#777; margin-top:5px;">Total Users: <strong><?php echo $users->num_rows; ?></strong></p>
            </div>

            <?php if ($message): ?>
                <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin:20px 0; border:1px solid #c3e6cb;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="GET" class="search-box">
                <input type="text" name="search" class="search-input" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>

            <div class="card">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="role-badge role-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                                    <td>
                                        <?php if($u['role'] !== 'admin'): ?>
                                            <form method="POST" onsubmit="return confirm('Permanently delete this user?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                                                <button class="btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:#ccc; font-size:12px;">Protected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="padding:30px; text-align:center; color:#999;">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include("../HTML/footer.html"); ?>
</body>
</html>