<?php
require_once "db-config/security.php";

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([]);
  exit;
}

// Select attendance rows joined with student info
$sql = "
  SELECT
    a.id AS attendance_id,
    a.student_id,
    s.student_last, 
    s.student_mi,
    s.section_id,
    s.section_name,
    COALESCE(s.student_name, a.student_name) AS student_name,
    s.grade AS student_grade,
    a.is_sent,
    a.is_logout_sent,
    a.time_in,
    a.time_out,
    a.timestamp
  FROM attendance a
  LEFT JOIN students s
    ON s.student_id = a.student_id
  ORDER BY a.timestamp DESC
  LIMIT 100
";

$result = $conn->query($sql);

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode($records);

$conn->close();