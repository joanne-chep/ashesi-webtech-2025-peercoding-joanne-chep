<?php
//Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("database.php");

//Security check to ensure only therapists access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

//Handle form submission to update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $specialization = trim($_POST['specialization']);
    $bio = trim($_POST['bio']);
    $city = trim($_POST['city']);
    $rate = trim($_POST['hourly_rate']);
    $image = trim($_POST['profile_image']);

    //Update the basic user table for name and image
    $stmt1 = $conn->prepare("UPDATE serenity_users SET full_name=?, profile_image=? WHERE id=?");
    $stmt1->bind_param("ssi", $full_name, $image, $user_id);
    $stmt1->execute();

    //Update the therapist details table
    $stmt2 = $conn->prepare("UPDATE therapist_profiles SET specialization=?, bio=?, city=?, hourly_rate=? WHERE user_id=?");
    $stmt2->bind_param("sssdi", $specialization, $bio, $city, $rate, $user_id);
    
    if ($stmt2->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

//Fetch current data to pre-fill the form
$query = "SELECT u.full_name, u.email, u.profile_image, tp.specialization, tp.bio, tp.city, tp.hourly_rate 
        FROM serenity_users u
        JOIN therapist_profiles tp ON u.id = tp.user_id
        WHERE u.id = $user_id";
$data = $conn->query($query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Therapist</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/therapist_settings.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="settings-container">
        <div class="header-section">
            <h1>Edit Public Profile</h1>
            <p>This is what clients will see when booking.</p>
            <a href="landing_therapistpage.php" class="back-link">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($data['full_name']); ?>" required>

                    <label>Profile Image URL</label>
                    <input type="text" name="profile_image" value="<?php echo htmlspecialchars($data['profile_image']); ?>" placeholder="https://...">

                    <label>Specialization (e.g. Anxiety, Child Therapy)</label>
                    <input type="text" name="specialization" value="<?php echo htmlspecialchars($data['specialization']); ?>" required>

                    <label>City / Location</label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($data['city']); ?>" required>
                    
                    <label>Hourly Rate ($)</label>
                    <input type="number" name="hourly_rate" value="<?php echo htmlspecialchars($data['hourly_rate']); ?>" required>
                </div>

                <div>
                    <label>Biography</label>
                    <p class="hint">Write a short introduction about your experience and approach.</p>
                    <textarea name="bio" rows="12" required><?php echo htmlspecialchars($data['bio']); ?></textarea>
                </div>
            </div>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>
    </div>

</body>
</html>