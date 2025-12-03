<?php
//submit_attendance.php, responsible for processing student attendance via code

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

//This block checks if the form was actually submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['attendance_code']);
    $studentId = $_SESSION['user_id'];

    if (empty($code)) {
        header("Location: student-dashboard.php?error=" . urlencode("Please enter a code."));
        exit();
    }

    $conn = connectDB();

    try {
        //First check if the code exists in the database
        //This query ensures the code matches exactly what the FI generated for a session
        $stmt = $conn->prepare("SELECT id, course_id FROM sessions WHERE attendance_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();

        //If no session is found with this code, reject the request
        if (!$session) {
            header("Location: student-dashboard.php?error=" . urlencode("Invalid attendance code. Please check and try again."));
            exit();
        }

        $sessionId = $session['id'];
        $courseId = $session['course_id'];

        //Verify the student is actually enrolled in this course
        //If they are not enrolled in the course matching the code, they cannot mark attendance
        $checkEnroll = $conn->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ?");
        $checkEnroll->bind_param("ii", $courseId, $studentId);
        $checkEnroll->execute();
        $enrollResult = $checkEnroll->get_result();
        
        if ($enrollResult->num_rows === 0) {
            $checkEnroll->close();
            header("Location: student-dashboard.php?error=" . urlencode("You are not enrolled in the course associated with this code."));
            exit();
        }
        $checkEnroll->close();

        //Mark the student as Present (P)
        //We check if they already marked attendance to avoid duplicates
        $checkAtt = $conn->prepare("SELECT status FROM attendance WHERE session_id = ? AND user_id = ?");
        $checkAtt->bind_param("ii", $sessionId, $studentId);
        $checkAtt->execute();
        $attResult = $checkAtt->get_result();
        $alreadyMarked = $attResult->fetch_assoc();
        $checkAtt->close();

        if ($alreadyMarked) {
             //If already marked, just update the timestamp
            $updateStmt = $conn->prepare("UPDATE attendance SET status = 'P', attended_at = NOW() WHERE session_id = ? AND user_id = ?");
            $updateStmt->bind_param("ii", $sessionId, $studentId);
            $updateStmt->execute();
            $updateStmt->close();
            $msg = "Attendance updated! You are marked Present.";
        } else {
            //Insert new record
            $markStmt = $conn->prepare("INSERT INTO attendance (session_id, user_id, status, attended_at) VALUES (?, ?, 'P', NOW())");
            $markStmt->bind_param("ii", $sessionId, $studentId);
            $markStmt->execute();
            $markStmt->close();
            $msg = "Success! You have been marked Present.";
        }

        header("Location: student-dashboard.php?message=" . urlencode($msg));
        exit();

    } catch (Exception $e) {
        error_log("Attendance error: " . $e->getMessage());
        header("Location: student-dashboard.php?error=" . urlencode("Database error occurred."));
    } finally {
        if (isset($conn)) $conn->close();
    }
} else {
    //If someone tries to access this file directly without submitting the form
    header("Location: student-dashboard.php");
    exit();
}
?>