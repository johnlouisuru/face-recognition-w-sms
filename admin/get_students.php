<?php
require_once "db-config/security.php";

$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : null;

if ($section_id) {
    // Filter by section
    $sql = "SELECT * FROM students WHERE section_id = ? AND face_descriptor IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $section_id);
} else {
    // Get all students (fallback)
    $sql = "SELECT * FROM students WHERE face_descriptor IS NOT NULL";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($students);
?>