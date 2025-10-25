<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'fi') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ashesi Attendance System | Faculty Intern Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="images/Ashesi.jpeg" alt="Ashesi University Logo" class="ashesi-logo">
            <h2 class="system-name">Ashesi Attendance System</h2>
        </div>

        <div class="profile-section">
            <img src="images/Joanne--Chepkoech.jpg" alt="Profile Photo" class="profile-photo">
            <p class="profile-name"><?php echo $_SESSION['name']; ?></p>
        </div>

        <ul class="sidebar-menu">
            <li><a href="#" onclick="showSection('courses')">Courses</a></li>
            <li><a href="#" onclick="showSection('sessions')">Sessions</a></li>
            <li><a href="#" onclick="showSection('reports')">Reports</a></li>
            <li><a href="php/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2 id="welcomeMessage">Welcome, <?php echo $_SESSION['name']; ?> 👋</h2>

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

    <script src="js/script.js"></script>
</body>
</html>
