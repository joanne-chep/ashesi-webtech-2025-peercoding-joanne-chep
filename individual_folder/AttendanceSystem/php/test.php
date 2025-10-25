<?php
require_once "db.php";

$conn = connectDB();

if ($conn) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
