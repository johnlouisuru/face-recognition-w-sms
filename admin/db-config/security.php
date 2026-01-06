<?php
session_start();

// $_SESSION['section_id'] = 10;
// $_SESSION['section_name'] = "Dedication";
// Always send JSON and avoid any accidental HTML output
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Manila');



// Turn off error display to client; log errors instead
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Local Server
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "attendance_system";

// Live Server
// $servername = "localhost";
// $username   = "bwzavjig_attendance";
// $password   = "iQHo@R@rncq&W(HE";
// $dbname     = "bwzavjig_attendance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}