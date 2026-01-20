<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear any cookies
if (isset($_COOKIE['teacher_remember'])) {
    setcookie('teacher_remember', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php');
exit;
?>