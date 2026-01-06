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
    COALESCE(s.student_name, a.student_name) AS student_name,
    s.grade AS student_grade,
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
if (!$result) {
  error_log('Query failed: ' . $conn->error);
  echo json_encode([]);
  $conn->close();
  exit;
}

$records = [];
while ($row = $result->fetch_assoc()) {
  // Normalize datetimes to ISO 8601 (or null)
  $row['time_in']  = $row['time_in']  ? date('c', strtotime($row['time_in']))  : null;
  $row['time_out'] = $row['time_out'] ? date('c', strtotime($row['time_out'])) : null;
  $row['timestamp'] = $row['timestamp'] ? date('c', strtotime($row['timestamp'])) : null;
  $records[] = $row;
}

echo json_encode($records);
$conn->close();
