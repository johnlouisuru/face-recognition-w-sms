<?php
session_start();
// Local Server
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "attendance_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Set timezone
date_default_timezone_set('Asia/Manila');


// Live Server
// $servername = "localhost";
// $username   = "bwzavjig_attendance";
// $password   = "iQHo@R@rncq&W(HE";
// $dbname     = "bwzavjig_attendance";

?>