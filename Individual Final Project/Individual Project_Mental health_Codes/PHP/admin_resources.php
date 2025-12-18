<?php
//Enable error reporting for development
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

//Handle Adding a New Resource
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_resource'])) {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $link = trim($_POST['link_url']);
    $desc = trim($_POST['description']);
    
    if (empty($title) || empty($link) || empty($desc)) {
        $message = "Error: All fields are required.";
    } else {
        //Insert into database
        $stmt = $conn->prepare("INSERT INTO resources (type, title, description, link_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $type, $title, $desc, $link);
        
        if ($stmt->execute()) {
            $message = "Resource added successfully!";
        } else {
            $message = "Error adding resource.";
        }
        $stmt->close();
    }
}

//Handle Deleting a Resource
if (isset($_POST['delete_id'])) {
    $del_id = $_POST['delete_id'];
    $conn->query("DELETE FROM saved_resources WHERE resource_id = $del_id"); //Remove from users' saved lists first
    $conn->query("DELETE FROM resources WHERE id = $del_id"); //Delete actual resource
    $message = "Resource deleted.";
}

//Fetch all resources to show the list
$resources = $conn->query("SELECT * FROM resources ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources | Serenity Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/therapist_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .sidebar { background-color: #2c3e50; }
        .sidebar h2 { color: #ecf0f1; }
        
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #ddd;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: inherit; box-sizing: border-box;
        }
        
        .btn-submit {
            background-color: #4A7C59; color: white; border: none; padding: 10px 20px;
            border-radius: 5px; cursor: pointer; font-weight: 600;
        }
        .btn-submit:hover { background-color: #3a6346; }

        .res-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .res-table th, .res-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .res-table th { background-color: #f8f9fa; }
        
        .btn-delete {
            background: #ffebee; color: #c62828; border: 1px solid #ffcdd2;
            padding: 5px 10px; border-radius: 4px; cursor: pointer;
        }
        .btn-delete:hover { background: #c62828; color: white; }
    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="dashboard-wrapper">
        
        <div class="sidebar">
            <div class="sidebar-title">Admin Menu</div>
            <a href="admin_dashboard.php" class="menu-link">
                <i class="fas fa-chart-pie" style="margin-right:10px;"></i> Overview
            </a>
            <a href="admin_manage_users.php" class="menu-link">
                <i class="fas fa-users" style="margin-right:10px;"></i> Manage Users
            </a>
            <a href="admin_resources.php" class="menu-link active">
                <i class="fas fa-book" style="margin-right:10px;"></i> Resources
            </a>
            <a href="logout.php" class="menu-link logout">
                <i class="fas fa-sign-out-alt" style="margin-right:10px;"></i> Sign Out
            </a>
        </div>

        <div class="content">
            <div class="page-header">
                <h1>Manage Library</h1>
                <p style="margin:5px 0 0; color:#777;">Add or remove content from the Wellness Library.</p>
            </div>

            <?php if ($message): ?>
                <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #c3e6cb;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="grid-container">
                
                <div class="form-card" style="grid-column: 1 / -1;">
                    <h3 style="margin-top:0;">Add New Content</h3>
                    <form method="POST">
                        <div style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 2;">
                                <label>Title</label>
                                <input type="text" name="title" required placeholder="e.g. 5 Minute Meditation">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Type</label>
                                <select name="type">
                                    <option value="Article">Article</option>
                                    <option value="Video">Video</option>
                                    <option value="Book">Book</option>
                                    <option value="Podcast">Podcast</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Link URL</label>
                            <input type="url" name="link_url" required placeholder="https://...">
                        </div>

                        <div class="form-group">
                            <label>Description (Short)</label>
                            <textarea name="description" rows="3" required placeholder="Briefly describe this resource..."></textarea>
                        </div>

                        <button type="submit" name="add_resource" class="btn-submit">Add to Library</button>
                    </form>
                </div>

                <div class="card" style="grid-column: 1 / -1;">
                    <h3>Current Library Items</h3>
                    <table class="res-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Link</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resources->num_rows > 0): ?>
                                <?php while($r = $resources->fetch_assoc()): ?>
                                    <tr>
                                        <td><span style="background:#e0f2f1; color:#00695c; padding:3px 8px; border-radius:10px; font-size:12px; font-weight:bold;"><?php echo $r['type']; ?></span></td>
                                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                                        <td><a href="<?php echo $r['link_url']; ?>" target="_blank" style="color:#2980b9;">View</a></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Delete this resource?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $r['id']; ?>">
                                                <button class="btn-delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No resources found. Add one above!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    
    <?php include("../HTML/footer.html"); ?>

</body>
</html>