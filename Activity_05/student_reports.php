<?php
//student_reports.php, responsible for displaying attendance reports to students

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
$name = $_SESSION['name'];
$records = [];

try {
    //Fetch all attendance records for this student
    $sql = "
        SELECT 
            c.course_code, 
            c.course_name,
            s.session_title, 
            s.session_date, 
            a.status, 
            a.attended_at
        FROM attendance a
        JOIN sessions s ON a.session_id = s.id
        JOIN courses c ON s.course_id = c.id
        WHERE a.user_id = ?
        ORDER BY s.session_date DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Error fetching student report: " . $e->getMessage());
    $error = "Could not load records.";
} finally {
    if (isset($conn)) $conn->close();
}

//This is a helper function to get the status label
function get_status_label($status) {
    switch ($status) {
        case 'P': return 'Present';
        case 'L': return 'Late';
        case 'A': return 'Absent';
        default: return 'Unknown';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance Reports</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .report-table th, .report-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .report-table th { background-color: #7A0019; color: white; }
        .status-present { color: green; font-weight: bold; }
        .status-late { color: orange; font-weight: bold; }
        .status-absent { color: red; font-weight: bold; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
    </style>
</head>
<body style="background-color: #f5f5f5;">
    <div class="container">
        <h2 style="color: #7A0019;">My Attendance Report</h2>
        <p>Student: <?php echo htmlspecialchars($name); ?></p>
        
        <?php if (empty($records)): ?>
            <p>No attendance records found yet.</p>
        <?php else: ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Session</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Time Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($r['session_title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($r['session_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower(get_status_label($r['status'])); ?>">
                                    <?php echo get_status_label($r['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('h:i A', strtotime($r['attended_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p style="margin-top: 20px;"><a href="student-dashboard.php" style="color: #7A0019; text-decoration: none;">← Back to Dashboard</a></p>
    </div>
</body>
</html>