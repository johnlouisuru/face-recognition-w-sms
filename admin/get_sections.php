<?php
require_once "db-config/security.php";

if ($conn->connect_error) {
    die(json_encode([]));
}

// Get unique sections from students table
$result = $conn->query("SELECT DISTINCT section_id, section_name FROM students WHERE section_id IS NOT NULL ORDER BY section_name");

$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode($sections);

$conn->close();
?>