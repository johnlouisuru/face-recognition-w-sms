<?php
require_once "db-config/security.php";

$sql = "
  SELECT id, student_id, student_name, 
         time_in, time_out, timestamp
  FROM attendance
  WHERE DATE(time_in) = CURDATE() OR DATE(timestamp) = CURDATE()
  ORDER BY COALESCE(time_in, timestamp) DESC
  LIMIT 100
";

$result = $conn->query($sql);
if (!$result) {
    http_response_code(500);
    echo json_encode([]);
    $conn->close();
    exit;
}

$records = [];
while ($row = $result->fetch_assoc()) {
    // Optionally convert DATETIME to ISO 8601 for consistent JS parsing
    $row['time_in']  = $row['time_in']  ? date('c', strtotime($row['time_in']))  : null;
    $row['time_out'] = $row['time_out'] ? date('c', strtotime($row['time_out'])) : null;
    $row['timestamp']= $row['timestamp'] ? date('c', strtotime($row['timestamp'])) : null;
    $records[] = $row;
}

echo json_encode($records);
$conn->close();
