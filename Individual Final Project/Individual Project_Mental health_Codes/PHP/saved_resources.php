<?php
session_start();
include("database.php");

//Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Handle the unsave action when the user clicks remove
if (isset($_POST['unsave_resource'])) {
    $res_id = $_POST['resource_id'];
    //Delete the record from the saved_resources table
    $delete = $conn->prepare("DELETE FROM saved_resources WHERE user_id = ? AND resource_id = ?");
    $delete->bind_param("ii", $user_id, $res_id);
    $delete->execute();
}

//Fetch all resources that the specific user has saved
//We join the main resources table with the saved_resources table
$sql = "SELECT r.* FROM resources r
        JOIN saved_resources sr ON r.id = sr.resource_id
        WHERE sr.user_id = ?
        ORDER BY sr.saved_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Sanctuary | Serenity</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-image: url('https://i.pinimg.com/736x/5a/69/4b/5a694b9cb5be9d7854727bbcad35be9c.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }

        .page-overlay {
            background-color: rgba(44, 62, 80, 0.75);
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .sanctuary-header {
            text-align: center;
            padding: 60px 20px 40px 20px;
            color: white;
            animation: fadeIn 1s ease-in;
        }

        .sanctuary-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin: 0 0 10px 0;
        }

        .sanctuary-header p {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 25px;
        }

        .btn-pill {
            display: inline-block;
            padding: 8px 20px;
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 30px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            backdrop-filter: blur(5px);
            transition: background 0.3s;
            margin: 0 5px;
        }
        .btn-pill:hover { background: rgba(255,255,255,0.2); }

        .resource-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding-bottom: 100px;
            box-sizing: border-box;
        }

        .resource-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
        }

        .resource-card:hover { transform: translateY(-5px); }

        .card-top { height: 6px; background: linear-gradient(to right, #4A7C59, #9D84B7); }
        .card-body { padding: 25px; flex-grow: 1; }
        .card-title { font-size: 18px; color: #2C3E50; margin: 0 0 10px 0; font-weight: 600; }
        .card-desc { font-size: 14px; color: #555; line-height: 1.6; }

        .actions {
            padding: 15px 25px;
            background: rgba(250, 250, 250, 0.8);
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .read-link { text-decoration: none; color: #4A7C59; font-weight: 600; font-size: 13px; text-transform: uppercase; }
        
        .unsave-btn {
            background: transparent; border: 1px solid #e74c3c; 
            cursor: pointer; color: #e74c3c; font-size: 12px;
            padding: 5px 12px; border-radius: 20px;
            transition: all 0.2s;
        }
        .unsave-btn:hover { background-color: #e74c3c; color: white; }

        .empty-state {
            grid-column: 1/-1;
            text-align: center;
            padding: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="page-overlay">
        
        <div class="sanctuary-header">
            <h1>Your Sanctuary</h1>
            <p>A quiet space for the wisdom you have gathered.</p>
            
            <a href="client_profile.php" class="btn-pill">&larr; Back to Dashboard</a>
            <a href="resources.php" class="btn-pill">Browse Library &rarr;</a>
        </div>

        <div class="resource-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="resource-card">
                        <div class="card-top"></div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                        </div>
                        <div class="actions">
                            <a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank" class="read-link">Read Now</a>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="resource_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="unsave_resource" class="unsave-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3 style="color: #555;">Your collection is empty.</h3>
                    <p>Go to the Library to find peace and knowledge.</p>
                    <a href="resources.php" style="color: #4A7C59; font-weight: bold;">Browse Library</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php include("../HTML/footer.html"); ?>

</body>
</html>