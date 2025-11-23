<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fi') {
    header("Location: login.php");
    exit();
}

$conn = $conn; 
$fi_id = $_SESSION['user_id'];
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') : 'Faculty Intern';


$courses = [];
try {
    $stmt_courses = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE fi_id = ? ORDER BY course_code");
    $stmt_courses->bind_param("i", $fi_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt_courses->close();
} catch (Exception $e) {
    error_log("Error fetching courses: " . $e->getMessage());
}

$sessions = [];
try {
    $sql_sessions = "
        SELECT 
            s.id, 
            s.session_title, 
            s.session_date, 
            s.start_time, 
            c.course_code, 
            c.course_name
        FROM sessions s
        JOIN courses c ON s.course_id = c.id
        WHERE c.fi_id = ?
        ORDER BY s.session_date DESC, s.start_time DESC
    ";
    
    $stmt_sessions = $conn->prepare($sql_sessions);
    $stmt_sessions->bind_param("i", $fi_id);
    $stmt_sessions->execute();
    $result_sessions = $stmt_sessions->get_result();
    
    while ($row = $result_sessions->fetch_assoc()) {
        $row['display_text'] = htmlspecialchars("{$row['course_code']} - {$row['session_title']} ({$row['session_date']})", ENT_QUOTES, 'UTF-8');
        $sessions[] = $row;
    }
    $stmt_sessions->close();
} catch (Exception $e) {
    error_log("Error fetching sessions: " . $e->getMessage());
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
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
            <h3>My Courses</h3>
            <ul id="courseList">
                <?php if (empty($courses)): ?>
                    <li>No courses assigned to you yet.</li>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <li><?php echo htmlspecialchars("{$course['course_code']} - {$course['course_name']}"); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
        
        <section id="sessions" class="content-section">
            <h3>Upcoming/Recent Class Sessions</h3>
            <ul id="sessionList">
                <?php if (empty($sessions)): ?>
                    <li>No recent or upcoming sessions found for your courses.</li>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <li><?php echo $session['display_text']; ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
        
        <section id="reports" class="content-section">
            <h3>Reports (Attendance Data)</h3>
            <ul id="reportList">
                <li><a href="reports.php?type=attendance">View Overall Attendance Report</a></li>
                <li><a href="reports.php?type=course_summary">View Course Summary Reports</a></li>
            </ul>
        </section>
    </main>

    <script src="script.js"></script> 
</body>
</html>