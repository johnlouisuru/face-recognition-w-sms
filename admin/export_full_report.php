<?php
// export_full_report.php
require_once 'db-config/security.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Set filename
$filename = 'absence_report_' . $date . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Headers
fputcsv($output, [
    'Date',
    'Student ID',
    'Last Name',
    'First Name',
    'Grade',
    'Section',
    'Reason for Absence',
    'Remarks',
    'Recorded Date'
]);

// Get data
$query = "
    SELECT 
        sa.attendance_date,
        sa.student_id,
        s.student_last,
        s.student_name,
        s.grade,
        s.section_name,
        sa.reason,
        sa.remarks,
        sa.created_at
    FROM student_absences sa
    JOIN students s ON sa.student_id = s.student_id
    WHERE sa.attendance_date = ?
    ORDER BY s.grade, s.section_name, s.student_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['attendance_date'],
        $row['student_id'],
        $row['student_last'],
        $row['student_name'],
        $row['grade'],
        $row['section_name'] ?? 'N/A',
        $row['reason'],
        $row['remarks'] ?? '',
        $row['created_at']
    ]);
}

fclose($output);
exit();
?>