<?php
require_once "db-config/security.php";

$student_id = $_GET['student_id'];
$stmt = $conn->prepare("SELECT 1 FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$stmt->store_result();

echo json_encode(['found' => $stmt->num_rows > 0]);
