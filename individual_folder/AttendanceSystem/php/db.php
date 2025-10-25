<?php
function connectDB() {
    $servername = "localhost";
    $username = "root";
    $password = "Mjo68@nne83";
    $dbname = "webtech_2025A_joanne_chepkoech";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
