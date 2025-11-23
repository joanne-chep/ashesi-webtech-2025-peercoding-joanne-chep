<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fi') {
    header("Location: login.php");
    exit();
}

$conn = $conn;
$fi_id = $_SESSION['user_id'];
$report_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'attendance';
$name = $_SESSION['name'];

//This query retrieves all attendance records for courses taught by the logged-in FI
$sql = "
    SELECT
        a.attended_at,
        a.status,
        u.name AS student_name,
        u.email AS student_email,
        s.session_title,
        s.session_date,
        s.start_time,
        c.course_code,
        c.course_name
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    JOIN sessions s ON a.session_id = s.id
    JOIN courses c ON s.course_id = c.id
    WHERE c.fi_id = ?
    ORDER BY s.session_date DESC, c.course_code ASC, u.name ASC
";

$records = [];
try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching report data: " . $e->getMessage());
    $error_message = "Could not load report data.";
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

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
    <title>Attendance Reports | <?php echo ucfirst(str_replace('_', ' ', $report_type)); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 0.9rem;
        }
        .report-table th {
            background-color: #7A0019;
            color: white;
            position: sticky;
            top: 0;
        }
        .status-present { color: green; font-weight: 600; }
        .status-late { color: orange; font-weight: 600; }
        .status-absent { color: red; font-weight: 600; }
        .main-content-report {
            margin: 0; 
            padding: 2rem; 
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="main-content-report">
        <h2>📊 Attendance Report: All Records</h2>
        <p>Report generated for <?php echo $name; ?> (FI) on all associated courses.</p>
        
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?php echo $error_message; ?></p>
        <?php elseif (empty($records)): ?>
            <p>No attendance records found for your assigned courses.</p>
        <?php else: ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Session</th>
                        <th>Session Date</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                        <?php 
                            $status_class = 'status-' . strtolower(get_status_label($row['status']));
                        ?>
                        <tr>
                            <td><?php echo "{$row['course_code']} ({$row['course_name']})"; ?></td>
                            <td><?php echo htmlspecialchars($row['session_title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['session_date'])); ?> @ <?php echo date('h:i A', strtotime($row['start_time'])); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><span class="<?php echo $status_class; ?>"><?php echo get_status_label($row['status']); ?></span></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($row['attended_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p style="margin-top: 20px;"><a href="fi-dashboard.php">← Back to Dashboard</a></p>
    </div>
</body>
</html>