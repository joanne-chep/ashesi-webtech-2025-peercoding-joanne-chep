<?php
//Serenity Database Connection
$servername = "localhost";
$username = "joanne.chepkoech";
$password = "Mjo68@nne83";
$dbname = "webtech_2025A_joanne_chepkoech";

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Function to get the connection
function connectDB() {
    global $conn;
    return $conn;
}
?>