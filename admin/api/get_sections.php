<?php
require_once "../db-config/security.php";

header('Content-Type: application/json');

$query = "
    SELECT DISTINCT section_id, section_name
    FROM students
    WHERE section_id IS NOT NULL
    ORDER BY section_name
";

$result = $conn->query($query);

$sections = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = [
            'section_id'   => $row['section_id'],
            'section_name' => $row['section_name']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $sections
]);

$conn->close();
exit;
