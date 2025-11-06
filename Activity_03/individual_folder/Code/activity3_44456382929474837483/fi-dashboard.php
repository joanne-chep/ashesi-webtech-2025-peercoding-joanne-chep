<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'fi') {
    header("Location: login.php");
    exit();
}

$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') : 'Faculty Intern';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ashesi Attendance System | Faculty Intern Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="Ashesi.jpeg" alt="Ashesi University Logo" class="ashesi-image">
            <h2 class="system-name">Ashesi Attendance System</h2>
        </div>
        <div class="profile-section">
            <img src="Joanne--Chepkoech.jpg" alt="Profile Photo" class="profile-photo">
            <p class="profile-name"><?php echo $name; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" onclick="showSection('courses')">Courses</a></li>
            <li><a href="#" onclick="showSection('sessions')">Sessions</a></li>
            <li><a href="#" onclick="showSection('reports')">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2 id="welcomeMessage">Welcome, <?php echo $name; ?> 👋</h2>
        <section id="courses" class="content-section active">
            <h3>Courses</h3>
            <ul id="courseList"></ul>
        </section>
        <section id="sessions" class="content-section">
            <h3>Class Sessions</h3>
            <ul id="sessionList"></ul>
        </section>
        <section id="reports" class="content-section">
            <h3>Reports</h3>
            <ul id="reportList"></ul>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>
