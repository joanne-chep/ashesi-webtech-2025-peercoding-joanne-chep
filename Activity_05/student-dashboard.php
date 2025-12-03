<?php
//student-dashboard.php, responsible for displaying the student dashboard

//This is for error identification in cases of issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//Ensure the user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$studentId = $_SESSION['user_id'];
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') : 'Student User';
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

//Get the data from the database associated with the current student
$enrolledCourses = [];
$pendingRequests = [];
$availableCourses = [];

try {
    //Fetch enrolled courses
    //This will only show the courses that have been approved by the FI
    $sqlEnrolled = "
        SELECT 
            c.id, 
            c.course_code, 
            c.course_name,
            u.name AS fi_name
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.id
        JOIN users u ON c.fi_id = u.id
        WHERE ce.student_id = ?
        ORDER BY c.course_code
    ";
    $stmtEnrolled = $conn->prepare($sqlEnrolled);
    $stmtEnrolled->bind_param("i", $studentId);
    $stmtEnrolled->execute();
    $resultEnrolled = $stmtEnrolled->get_result();
    while ($row = $resultEnrolled->fetch_assoc()) {
        $enrolledCourses[] = $row;
    }
    $stmtEnrolled->close();
    //Fetch the available courses including the pending ones
    $sqlAvailable = "
        SELECT 
            c.id, 
            c.course_code, 
            c.course_name,
            u.name AS fi_name,
            r.request_status,
            ce.id AS is_enrolled
        FROM courses c
        JOIN users u ON c.fi_id = u.id
        LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.student_id = ?
        LEFT JOIN requests r ON c.id = r.course_id AND r.student_id = ?
        WHERE ce.id IS NULL
        ORDER BY c.course_code
    ";
    
    $stmtAvailable = $conn->prepare($sqlAvailable);
    $stmtAvailable->bind_param("ii", $studentId, $studentId); 
    $stmtAvailable->execute();
    $resultAvailable = $stmtAvailable->get_result();

    while ($row = $resultAvailable->fetch_assoc()) {
        //Separate the courses into enrolled and pending courses
        if ($row['request_status'] === 'Pending') {
            $pendingRequests[] = $row;
        } 
        elseif ($row['request_status'] !== 'Pending') {
            //This also includes the rejected ones
            $availableCourses[] = $row;
        }
    }
    $stmtAvailable->close();

} catch (Exception $e) {
    error_log("Database error in student dashboard: " . $e->getMessage());
    $error = "Database error: Could not fetch course data.";
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
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .attendance-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 5px solid #7A0019;
        }
        .attendance-card h3 { margin-top: 0; color: #7A0019; }
        .code-form { display: flex; gap: 10px; margin-top: 15px; }
        .code-input {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1.2rem;
            width: 200px;
            letter-spacing: 2px;
            text-align: center;
        }
        .submit-btn {
            padding: 12px 24px;
            background-color: #7A0019;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        .submit-btn:hover { background-color: #9e0022; }
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
            <li><a href="#" onclick="showSection('dashboard_home')">Home</a></li>
            <li><a href="#" onclick="showSection('available')">Request Courses</a></li>
            <li><a href="student_reports.php">My Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2 id="welcomeMessage">WELCOME! <?php echo $name; ?> (Student) 👋</h2>

        <?php if ($message): ?>
            <p style="color:green; background-color:#e0ffe0; padding:15px; border-radius:6px; margin-bottom: 20px; border: 1px solid green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red; background-color:#ffe0e0; padding:15px; border-radius:6px; margin-bottom: 20px; border: 1px solid red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <section id="dashboard_home" class="content-section active">
            <div class="attendance-card">
                <h3>Mark Attendance</h3>
                <p>Enter the 4-character code provided by your instructor for the current session.</p>
                <form action="submit_attendance.php" method="POST" class="code-form">
                    <input type="text" name="attendance_code" class="code-input" placeholder="CODE" required maxlength="4">
                    <button type="submit" class="submit-btn">Submit Attendance</button>
                </form>
            </div>

            <h3>My Enrolled Courses</h3>
            <ul id="enrolledList">
                <?php if (empty($enrolledCourses)): ?>
                    <li>You are not currently enrolled in any courses. Please go to <strong>Request Courses</strong> to join a class.</li>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <li style="border-left: 4px solid #008000; background-color: #f0fff0;">
                            <strong><?php echo htmlspecialchars($course['course_code']); ?></strong> - 
                            <?php echo htmlspecialchars($course['course_name']); ?> 
                            <br>
                            <span style="font-size: 0.9em; color: #666;">Instructor: <?php echo htmlspecialchars($course['fi_name']); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <section id="available" class="content-section">
            <h3>Course Requests</h3>
            <ul id="availableList">
                
                <?php if (!empty($pendingRequests)): ?>
                    <li style="background-color: #fff3cd; color: #856404; font-weight: 600; border: 1px solid #ffeeba;">
                        Waitlisted / Pending Approval:
                    </li>
                    <?php foreach ($pendingRequests as $course): ?>
                        <li style="background-color: #fffac2; border-left: 4px solid #ffc107;">
                            <strong><?php echo htmlspecialchars($course['course_code']); ?></strong> - 
                            <?php echo htmlspecialchars($course['course_name']); ?>
                            (Instructor: <?php echo htmlspecialchars($course['fi_name']); ?>)
                            <span style="float:right; font-style:italic; color: #997404;">Pending...</span>
                        </li>
                    <?php endforeach; ?>
                    <li style="background: none; border: none; height: 10px;"></li>
                <?php endif; ?>

                <li style="font-weight: 600; margin-bottom: 10px;">Available Courses:</li>
                <?php if (empty($availableCourses)): ?>
                    <li>No new courses available to request at this time.</li>
                <?php else: ?>
                    <?php foreach ($availableCourses as $course): ?>
                        <li style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <strong><?php echo htmlspecialchars($course['course_code']); ?></strong> - 
                                <?php echo htmlspecialchars($course['course_name']); ?> 
                                <span style="font-size: 0.9em; color: #666;">(Instructor: <?php echo htmlspecialchars($course['fi_name']); ?>)</span>
                            </span>
                            
                            <?php if(isset($course['request_status']) && $course['request_status'] == 'Rejected'): ?>
                                <span style="color: #dc3545; font-size: 0.9em;">Previously Rejected</span>
                                <?php endif; ?>

                            <form action="request.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <button type="submit" style="padding: 6px 12px; background-color: #7A0019; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background 0.2s;">
                                    Request to Join
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>