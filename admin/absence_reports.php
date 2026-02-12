<?php
require_once 'db-config/security.php';

// Get statistics
$stats_query = "
    SELECT 
        sa.reason,
        COUNT(*) as count,
        COUNT(DISTINCT sa.student_id) as unique_students
    FROM student_absences sa
    GROUP BY sa.reason
    ORDER BY count DESC
";
$stats_result = $conn->query($stats_query);

// Get monthly trends
$monthly_query = "
    SELECT 
        DATE_FORMAT(sa.attendance_date, '%Y-%m') as month,
        COUNT(*) as total_absences,
        COUNT(DISTINCT sa.student_id) as unique_students
    FROM student_absences sa
    GROUP BY DATE_FORMAT(sa.attendance_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
";
$monthly_result = $conn->query($monthly_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absence Reports</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <!-- Report content -->
</body>
</html>