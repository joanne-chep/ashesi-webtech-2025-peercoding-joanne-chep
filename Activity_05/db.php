<?php
//Connecting to the database and setting up the connection
$servername = "localhost";
$username = "joanne.chepkoech";
$password = "Mjo68@nne83";
$dbname = "webtech_2025A_joanne_chepkoech";
//This is the creation of a new mysqli object to connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
//Checking if the connection was successful or not
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
//This function enables connection to the database from other files
function connectDB() {
    global $conn;
    return $conn;
}

?>
