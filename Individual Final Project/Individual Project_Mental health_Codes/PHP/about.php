<?php
session_start();
//Include the external database connection file
require_once "database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Serenity</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <style>
        body {
            background-color: #F9F7F2;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        
        .about-hero {
            background-color: #4A7C59;
            color: white;
            padding: 100px 20px;
            text-align: center;
        }
        .about-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            margin: 0;
            letter-spacing: 1px;
        }
        .about-hero p {
            font-size: 18px;
            opacity: 0.9;
            margin-top: 10px;
            font-weight: 300;
        }
        
        .content-section {
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 60px;
        }
        
        .content-text { flex: 1; }
        .content-text h2 {
            color: #2C3E50;
            font-size: 36px;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        .content-text p {
            color: #666;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .content-img { flex: 1; }
        .content-img img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(74, 124, 89, 0.2);
            height: 400px;
            object-fit: cover;
        }

        .contact-section {
            background-color: white;
            padding: 80px 20px;
            text-align: center;
            margin-top: 80px;
        }

        .contact-grid {
            max-width: 900px;
            margin: 40px auto 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .contact-card {
            padding: 30px;
            border: 1px solid #eee;
            border-radius: 12px;
            transition: 0.3s;
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-color: #4A7C59;
        }

        .contact-card h3 {
            color: #4A7C59;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        .contact-card p, .contact-card a {
            color: #555;
            font-size: 15px;
            text-decoration: none;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .content-section { flex-direction: column; }
            .content-img img { height: auto; }
        }
    </style>
</head>
<body>
    <?php include("Header.php"); ?>

    <div class="about-hero">
        <h1>Our Mission</h1>
        <p>Connecting you to peace, one meaningful conversation at a time.</p>
    </div>

    <div class="content-section">
        <div class="content-text">
            <h2>Why Serenity?</h2>
            <p>At Serenity, we believe finding mental health support should be as comforting as the support itself. We created a friendly space where individuals can easily connect with verified professionals without the confusion.</p>
            <p>We are more than just a booking site; we are a community built on trust, privacy, and accessibility. Whether you are looking for guidance, resources, or just someone to listen, we are here to make that journey easier.</p>
        </div>
        <div class="content-img">
            <img src="https://images.unsplash.com/photo-1573497620053-ea5300f94f21?q=80&w=1000&auto=format&fit=crop" alt="Supportive Conversation">
        </div>
    </div>

    <div class="contact-section">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 36px; color: #2C3E50; margin: 0;">Get in Touch</h2>
        <p style="color: #666; margin-top: 10px;">We would love to hear from you.</p>

        <div class="contact-grid">
            <div class="contact-card">
                <h3>Email Us</h3>
                <p>For support and inquiries:</p>
                <a href="mailto:serenity@gmail.com"><strong>serenity@gmail.com</strong></a>
            </div>
            
            <div class="contact-card">
                <h3>Visit Us</h3>
                <p>Serenity Wellness Hub<br>Accra, Ghana</p>
            </div>

            <div class="contact-card">
                <h3>For Therapists</h3>
                <p>Interested in joining?</p>
                <a href="Signup.php"><strong>Join our network</strong></a>
            </div>
        </div>
    </div>

    <?php include("../HTML/footer.html"); ?>
</body>
</html>