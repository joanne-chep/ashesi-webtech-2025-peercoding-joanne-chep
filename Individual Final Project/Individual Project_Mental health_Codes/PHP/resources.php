<?php
session_start();
include("database.php");

//Security check to ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Handle the save resource action when a user clicks the heart icon
if (isset($_POST['save_resource'])) {
    $res_id = $_POST['resource_id'];
    
    //Check if the resource is already saved to toggle it
    $check = $conn->prepare("SELECT id FROM saved_resources WHERE user_id = ? AND resource_id = ?");
    $check->bind_param("ii", $user_id, $res_id);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        //If not saved, insert into the saved_resources table
        $save = $conn->prepare("INSERT INTO saved_resources (user_id, resource_id) VALUES (?, ?)");
        $save->bind_param("ii", $user_id, $res_id);
        $save->execute();
    } else {
        //If already saved, delete it to toggle off
        $delete = $conn->prepare("DELETE FROM saved_resources WHERE user_id = ? AND resource_id = ?");
        $delete->bind_param("ii", $user_id, $res_id);
        $delete->execute();
    }
}

//Fetch all available resources from the database ordered by date
$sql = "SELECT * FROM resources ORDER BY created_at DESC";
$result = $conn->query($sql);

//Fetch the IDs of saved resources to display the red heart
$saved_ids = [];
$save_sql = $conn->query("SELECT resource_id FROM saved_resources WHERE user_id = $user_id");
while ($row = $save_sql->fetch_assoc()) {
    $saved_ids[] = $row['resource_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wellness Library | Serenity</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <style>
        body { background-color: #F9F7F2; font-family: 'Poppins', sans-serif; }
        
        .library-hero {
            background-color: #4A7C59;
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            margin-bottom: 50px;
        }

        .quote-container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
        }

        .quote-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #e78cf3ff;
            object-fit: cover;
            background-image: url('https://i.pinimg.com/736x/58/f8/5c/58f85cf747896040a0502522bff84523.jpg'); 
            background-size: cover;
            background-position: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .quote-text { text-align: left; }
        
        .quote-text em {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            display: block;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .quote-author {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .resource-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .resource-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            border: 1px solid #eee;
        }

        .resource-card:hover { transform: translateY(-5px); border-color: #4A7C59; }

        .card-body { padding: 30px; flex-grow: 1; }

        .tag {
            background-color: #f1f8cfff; color: #00695c;
            padding: 6px 14px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            display: inline-block; margin-bottom: 15px;
        }

        .card-title {
            color: #2C3E50; font-size: 20px;
            margin: 0 0 10px 0; font-weight: 600;
            line-height: 1.3;
        }

        .card-desc {
            color: #7f8c8d; font-size: 15px;
            line-height: 1.6; margin-bottom: 20px;
        }

        .card-footer {
            padding: 20px 30px;
            background-color: #cce7f8ff;
            border-top: 1px solid #cce7f8ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .read-btn {
            text-decoration: none; color: #4A7C59;
            font-weight: 600; font-size: 14px;
            transition: color 0.2s;
        }
        .read-btn:hover { color: #2C3E50; }

        .save-btn {
            background: none; border: none;
            cursor: pointer; font-size: 24px;
            color: #ccc; transition: transform 0.2s;
        }
        .save-btn:hover { transform: scale(1.2); }
        .save-btn.saved { color: #3f0903ff; }

    </style>
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="library-hero">
        <div class="quote-container">
            <div class="quote-avatar"></div>
            <div class="quote-text">
                <em>"It always seems impossible until it is done."</em>
                <span class="quote-author">— Nelson Mandela</span>
            </div>
        </div>
    </div>

    <div class="resource-container">
        
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="resource-card">
                    <div class="card-body">
                        <span class="tag"><?php echo htmlspecialchars($row['type']); ?></span>
                        <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank" class="read-btn">Access Resource &rarr;</a>
                        
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="resource_id" value="<?php echo $row['id']; ?>">
                            <?php
                                $is_saved = in_array($row['id'], $saved_ids);
                                $heart_class = $is_saved ? 'saved' : '';
                                $heart_icon = $is_saved ? '❤️' : '🤍';
                            ?>
                            <button type="submit" name="save_resource" class="save-btn <?php echo $heart_class; ?>" title="Save to Profile">
                                <?php echo $heart_icon; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <h3 style="color: #ccc;">The library is currently being curated.</h3>
            </div>
        <?php endif; ?>

    </div>

    <?php include("../HTML/footer.html"); ?>

</body>
</html>