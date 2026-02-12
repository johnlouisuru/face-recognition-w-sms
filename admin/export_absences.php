<?php
require_once 'db-config/security.php';

$date = $_GET['date'] ?? date('Y-m-d');

// Get absent students with reasons
$query = "
    SELECT 
        s.student_id,
        s.student_name,
        s.student_last,
        s.grade,
        s.section_name,
        sa.reason,
        sa.remarks,
        sa.attendance_date
    FROM students s
    LEFT JOIN student_absences sa ON s.student_id = sa.student_id AND sa.attendance_date = ?
    WHERE NOT EXISTS (
        SELECT 1 
        FROM attendance a 
        WHERE a.student_id = s.student_id 
        AND DATE(a.timestamp) = ?
    )
    AND EXISTS (
        SELECT 1 
        FROM attendance a 
        WHERE a.student_id = s.student_id
    )
    ORDER BY s.grade, s.section_name, s.student_name
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $date, $date);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="absent_students_' . $date . '.xls"');

// Create Excel table
echo "<table border='1'>";
echo "<tr>";
echo "<th>Student ID</th>";
echo "<th>Last Name</th>";
echo "<th>First Name</th>";
echo "<th>Grade</th>";
echo "<th>Section</th>";
echo "<th>Date</th>";
echo "<th>Reason for Absence</th>";
echo "<th>Remarks</th>";
echo "</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['student_id'] . "</td>";
    echo "<td>" . ($row['student_last'] ?? '') . "</td>";
    echo "<td>" . $row['student_name'] . "</td>";
    echo "<td>" . $row['grade'] . "</td>";
    echo "<td>" . ($row['section_name'] ?? 'N/A') . "</td>";
    echo "<td>" . $row['attendance_date'] . "</td>";
    echo "<td>" . ($row['reason'] ?? 'Not Specified') . "</td>";
    echo "<td>" . ($row['remarks'] ?? '') . "</td>";
    echo "</tr>";
}

echo "</table>";
?>