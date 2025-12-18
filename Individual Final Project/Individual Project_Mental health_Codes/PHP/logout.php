<?php
session_start();

//Clear Data in the dashboard session
//Unset all session variables
$_SESSION = array();

//Remove all the session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

//Destroy Session
session_destroy();

//Prevent Caching of the Logged-in Page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//Redirect to login page
header("Location: login.php");
exit();
?>