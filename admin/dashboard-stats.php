<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$today = date('Y-m-d');

// Get teacher's section
$teacher_section = null;
$section_stmt = $conn->prepare("SELECT section_id FROM teachers WHERE id = ?");
$section_stmt->bind_param("i", $teacher_id);
$section_stmt->execute();
$section_result = $section_stmt->get_result();
if ($section_result->num_rows > 0) {
    $teacher_section = $section_result->fetch_assoc();
}

$response = [
    'success' => true,
    'today_present' => 0,
    'today_absent' => 0,
    'today_late' => 0,
    'current_time' => date('h:i A')
];

if ($teacher_section && $teacher_section['section_id']) {
    // Get total students
    $student_stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE section_id = ?");
    $student_stmt->bind_param("i", $teacher_section['section_id']);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $total_students = $student_result->fetch_assoc()['total'];
    
    // Get today's present count
    $attendance_stmt = $conn->prepare("
        SELECT COUNT(DISTINCT a.student_id) as present_count 
        FROM attendance a 
        INNER JOIN students s ON a.student_id = s.student_id 
        WHERE DATE(a.time_in) = ? AND s.section_id = ?
    ");
    $attendance_stmt->bind_param("si", $today, $teacher_section['section_id']);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    $present = $attendance_result->fetch_assoc()['present_count'] ?? 0;
    
    // Get late arrivals
    $late_stmt = $conn->prepare("
        SELECT COUNT(DISTINCT a.student_id) as late_count 
        FROM attendance a 
        INNER JOIN students s ON a.student_id = s.student_id 
        WHERE DATE(a.time_in) = ? 
        AND TIME(a.time_in) > '08:00:00' 
        AND s.section_id = ?
    ");
    $late_stmt->bind_param("si", $today, $teacher_section['section_id']);
    $late_stmt->execute();
    $late_result = $late_stmt->get_result();
    $late = $late_result->fetch_assoc()['late_count'] ?? 0;
    
    $response['today_present'] = $present;
    $response['today_absent'] = $total_students - $present;
    $response['today_late'] = $late;
}

echo json_encode($response);
$conn->close();
?>