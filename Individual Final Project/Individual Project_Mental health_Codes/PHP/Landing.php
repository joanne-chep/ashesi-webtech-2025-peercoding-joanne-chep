<?php
session_start();
include("database.php");

//Check if user is logged in
$btn_link = isset($_SESSION['user_id']) ? "BookTherapy.php" : "login.php";
$btn_text = isset($_SESSION['user_id']) ? "Book a Session" : "Get Started";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serenity | Find Your Calm</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/landing.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include("Header.php"); ?>

    <div class="hero">
        <div class="hero-content">
            <h1>Develop practical strategies<br>Manage difficult thoughts and feelings</h1>
            <p>Connect with verified therapists and access curated resources tailored to your personal mental health journey.</p>
            
            <div class="hero-buttons">
                <a href="<?php echo $btn_link; ?>" class="btn btn-primary"><?php echo $btn_text; ?></a>
                <a href="resources.php" class="btn btn-outline">Explore Library</a>
            </div>
        </div>
    </div>

    <div class="features-section">
        
        <div class="section-header">
            <h2>Why Choose Serenity?</h2>
        </div>

        <div class="grid-container">
            <div class="feature-card">
                <img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?q=80&w=800&auto=format&fit=crop" alt="Therapist" class="card-image">
                <div class="card-text">
                    <h3>Verified Experts</h3>
                    <p>Every therapist on Serenity is manually vetted by our team to ensure safe, high-quality care for you.</p>
                </div>
            </div>

            <div class="feature-card">
                <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=800&auto=format&fit=crop" alt="Resources" class="card-image">
                <div class="card-text">
                    <h3>Profound Wisdom</h3>
                    <p>Access our library of articles, guides, and podcasts selected by experts to help you cope with anxiety.</p>
                </div>
            </div>

            <div class="feature-card">
                <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?q=80&w=800&auto=format&fit=crop" alt="Community" class="card-image">
                <div class="card-text">
                    <h3>Tailored to You</h3>
                    <p>Whether you need couples counseling, individual support, or child therapy, we help you find the perfect fit.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="quote-section">
        <p class="quote-text">"The journey of a thousand miles begins with a single step. Take that step today."</p>
    </div>

    <?php include("../HTML/footer.html"); ?>

</body>
</html>