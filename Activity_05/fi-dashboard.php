<?php
//fi-dashboard.php, responsible for displaying the Faculty Intern dashboard
//Includes course management, session management, and enrollment requests handling

//This is for error identification in cases of issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
//This includes the database connection file
include 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


//This ensures that the user logged in is a Faculty Intern and redirects them to the login page if not
//This is very important as it ensures that not anyone is allowed to anyother users dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fi') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
//Get the FI's id to fetch their data
$fi_id = $_SESSION['user_id'];
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') : 'Faculty Intern';
$message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
//These arrays will hold the data fetched from the database associated with the current FI
$courses = [];
$sessions = [];
$requests = []; 

try {
    //Get the courses that the FI creates
    $stmt_courses = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE fi_id = ? ORDER BY course_code");
    $stmt_courses->bind_param("i", $fi_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row; //Store the various courses in the array
    }
    $stmt_courses->close();
    //Once the FI creates sessions, this will get the sessions stored in the database
    //We also fetch the attendance code here to display it on the dashboard
    $sql_sessions = "
        SELECT 
            s.id, 
            s.session_title, 
            s.attendance_code,
            s.session_date, 
            s.start_time, 
            c.course_code, 
            c.course_name
        FROM sessions s JOIN courses c ON s.course_id = c.id
        WHERE c.fi_id = ? ORDER BY s.session_date DESC, s.start_time DESC
    ";
    $stmt_sessions = $conn->prepare($sql_sessions);
    $stmt_sessions->bind_param("i", $fi_id);
    $stmt_sessions->execute();
    $result_sessions = $stmt_sessions->get_result();
    while ($row = $result_sessions->fetch_assoc()) {
        $row['display_text'] = htmlspecialchars("{$row['course_code']} - {$row['session_title']} ({$row['session_date']})", ENT_QUOTES, 'UTF-8');
        $row['session_id'] = $row['id'];
        $sessions[] = $row;
    }
    $stmt_sessions->close();
    //Get the pending requests by students to join the FI courses
    //Get them from the request, users and courses table
    //This only gets the pending courses
    $sql_requests = "
        SELECT 
            er.id AS request_id, 
            u.name AS student_name, 
            c.course_code, 
            c.course_name,
            er.requested_at
        FROM requests er
        JOIN users u ON er.student_id = u.id
        JOIN courses c ON er.course_id = c.id
        WHERE c.fi_id = ? AND er.request_status = 'Pending'
        ORDER BY er.requested_at ASC
    ";
    $stmt_requests = $conn->prepare($sql_requests);
    $stmt_requests->bind_param("i", $fi_id);
    $stmt_requests->execute();
    $result_requests = $stmt_requests->get_result();
    while ($row = $result_requests->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt_requests->close();

} catch (Exception $e) {
    error_log("Database error fetching dashboard data: " . $e->getMessage());
    $error = "Database error fetching dashboard data.";
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ashesi Attendance System | Faculty Intern Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .list-with-actions {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            transition: background 0.2s;
        }
        .btn-add { background-color: #7A0019; }
        .btn-add:hover { background-color: #9e0022; }
        .btn-approve { background-color: #008000; }
        .btn-approve:hover { background-color: #006400; }
        .btn-reject { background-color: #cc0000; }
        .btn-reject:hover { background-color: #a30000; }
        .btn-take-attendance { background-color: #007bff; }
        .btn-take-attendance:hover { background-color: #0056b3; }
        .code-display { font-weight: bold; color: #7A0019; background: #ffe6e6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="Ashesi.jpeg" alt="Ashesi University Logo" class="ashesi-image">
            <h2 class="system-name">Ashesi Attendance System</h2>
        </div>
        <div class="profile-section">
            <img src="Joanne--Chepkoech.jpg" alt="Profile Photo" class="profile-photo" onerror="this.src='default-profile.png'">
            <p class="profile-name"><?php echo $name; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" onclick="showSection('courses')">Courses</a></li>
            <li><a href="#" onclick="showSection('sessions')">Sessions</a></li>
            <li><a href="#" onclick="showSection('requests')">Requests (<?php echo count($requests); ?>)</a></li>
            <li><a href="#" onclick="showSection('reports')">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2 id="welcomeMessage">WELCOME! <?php echo $name; ?> 👋</h2>

        <?php if ($message): ?>
            <p style="color:green; background-color:#e0ffe0; padding:10px; border-radius:6px;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red; background-color:#ffe0e0; padding:10px; border-radius:6px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <section id="courses" class="content-section active">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>My Courses</h3>
                <a href="add_course.php" class="action-button btn-add">+ Add New Course</a>
            </div>
            <ul id="courseList">
                <?php if (empty($courses)): ?>
                    <li>There are no courses assigned to you yet. You can add courses!</li>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <li><?php echo htmlspecialchars("{$course['course_code']} - {$course['course_name']}"); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
        
        <section id="sessions" class="content-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>Upcoming/Recent Class Sessions</h3>
                <a href="add_session.php" class="action-button btn-add">+ Add New Session</a>
            </div>
            <ul id="sessionList">
                <?php if (empty($sessions)): ?>
                    <li>No recent or upcoming sessions found for your courses. You can add sessions!</li>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <li class="list-with-actions">
                            <span>
                                <?php echo $session['display_text']; ?>
                                <br>
                                Code: <span class="code-display"><?php echo htmlspecialchars($session['attendance_code']); ?></span>
                            </span>
                            <a href="manage_attendance.php?session_id=<?php echo $session['session_id']; ?>" class="action-button btn-take-attendance">
                                Take Attendance
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <section id="requests" class="content-section">
            <h3>Pending Enrollment Requests</h3>
            <ul id="requestList">
                <?php if (empty($requests)): ?>
                    <li style="color: green; background-color: #f0fff0;">No new enrollment requests pending.</li>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <li style="display: flex; justify-content: space-between; align-items: center; background-color: #fff0f0; border-left: 4px solid #cc0000;">
                            <span>
                                <strong><?php echo htmlspecialchars($request['student_name']); ?></strong> wants to join: 
                                <?php echo htmlspecialchars("{$request['course_code']} - {$request['course_name']}"); ?> 
                                <br>
                                <span style="font-size: 0.9em; color: #666;">(Requested: <?php echo date('M d, h:i A', strtotime($request['requested_at'])); ?>)</span>
                            </span>
                            <div style="display: flex; gap: 10px;">
                                <form action="accept.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="action-button btn-approve">Approve</button>
                                </form>
                                <form action="accept.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="action-button btn-reject">Reject</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
        
        <section id="reports" class="content-section">
            <h3>Reports (Attendance Data)</h3>
            <ul id="reportList">
                <li><a href="reports.php?type=attendance" style="color: #7A0019; text-decoration: none;">View Overall Attendance Report</a></li>
                <li><a href="reports.php?type=course_summary" style="color: #7A0019; text-decoration: none;">View Course Summary Reports</a></li>
            </ul>
        </section>
    </main>

    <script src="script.js"></script> 
</body>
</html>