<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$is_therapist = (isset($_SESSION['role']) && $_SESSION['role'] === 'therapist');
?>

<link rel="stylesheet" href="../CSS/header.css?v=<?php echo time(); ?>">

<header class="serenity-header">
    <div class="logo">
        <a href="Landing.php">SERENITY</a>
    </div>
    
    <nav class="nav-links">
        <?php if (!$is_therapist): ?>
            <a href="BookTherapy.php" class="nav-item">Find a Therapist</a>
        <?php endif; ?>
        
        <a href="resources.php" class="nav-item">Library</a>
        <a href="about.php" class="nav-item">About Us</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $profile_target = $is_therapist ? "therapist_profile.php" : "client_profile.php";
            ?>
            <a href="<?php echo $profile_target; ?>" class="nav-btn">My Profile</a>
        <?php else: ?>
            <a href="login.php" class="nav-btn">Log In</a>
        <?php endif; ?>
    </nav>
</header>