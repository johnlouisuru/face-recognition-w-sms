<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get filter parameters
    $grade = $_GET['grade'] ?? '';
    $section = $_GET['section'] ?? '';
    $status = $_GET['status'] ?? '';
    $timeFilter = $_GET['time'] ?? 'all';
    
    $today = date('Y-m-d');
    
    // Build base query for students
    $studentQuery = "SELECT s.*, 
                    a.time_in, 
                    a.time_out,
                    CASE 
                        WHEN a.time_in IS NULL THEN 'absent'
                        WHEN TIME(a.time_in) > '08:00:00' THEN 'late'
                        ELSE 'present'
                    END as attendance_status
                    FROM students s 
                    LEFT JOIN attendance a ON s.student_id = a.student_id 
                        AND DATE(a.time_in) = '$today'";
    
    $whereConditions = [];
    $params = [];
    $types = "";
    
    // Apply filters
    if (!empty($grade)) {
        $whereConditions[] = "s.grade = ?";
        $params[] = $grade;
        $types .= "s";
    }
    
    if (!empty($section)) {
        $whereConditions[] = "s.section_name = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    // Add WHERE clause if there are conditions
    if (!empty($whereConditions)) {
        $studentQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $studentQuery .= " ORDER BY s.grade, s.section_name, s.student_name";
    
    // Prepare and execute student query
    $stmt = $conn->prepare($studentQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    $stats = [
        'total_students' => 0,
        'present_count' => 0,
        'late_count' => 0,
        'absent_count' => 0
    ];
    
    // Process students and apply additional filters
    while ($row = $result->fetch_assoc()) {
        // Parse student name
        $nameParts = explode(' ', $row['student_name']);
        $row['student_name'] = $nameParts[0] . ' ' . $nameParts[count($nameParts) - 1];
        
        // Apply status filter
        if (!empty($status)) {
            if ($status === 'present' && $row['attendance_status'] !== 'present') continue;
            if ($status === 'late' && $row['attendance_status'] !== 'late') continue;
            if ($status === 'absent' && $row['attendance_status'] !== 'absent') continue;
        }
        
        // Apply time filter
        if ($timeFilter === 'time_in' && !$row['time_in']) continue;
        if ($timeFilter === 'time_out' && !$row['time_out']) continue;
        
        // Update statistics
        $stats['total_students']++;
        switch ($row['attendance_status']) {
            case 'present':
                $stats['present_count']++;
                break;
            case 'late':
                $stats['late_count']++;
                break;
            case 'absent':
                $stats['absent_count']++;
                break;
        }
        
        $students[] = $row;
    }
    
    // Get recent activity (last 10 attendance records)
    $activityQuery = "SELECT a.*, s.student_name, s.grade, s.section_name,
                     CASE 
                         WHEN TIME(a.time_in) > '08:00:00' THEN 'Late'
                         ELSE 'On Time'
                     END as status
                     FROM attendance a 
                     JOIN students s ON a.student_id = s.student_id 
                     WHERE DATE(a.timestamp) = '$today'
                     ORDER BY a.timestamp DESC 
                     LIMIT 10";
    
    $activityResult = $conn->query($activityQuery);
    $recent_activity = [];
    while ($row = $activityResult->fetch_assoc()) {
        $recent_activity[] = $row;
    }
    
    // Get section summary
    $summaryQuery = "SELECT 
                    s.section_name,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(DISTINCT CASE WHEN a.time_in IS NOT NULL THEN s.student_id END) as present_count
                    FROM students s 
                    LEFT JOIN attendance a ON s.student_id = a.student_id 
                        AND DATE(a.time_in) = '$today'";
    
    // Apply filters to summary
    $summaryWhere = [];
    $summaryParams = [];
    $summaryTypes = "";
    
    if (!empty($grade)) {
        $summaryWhere[] = "s.grade = ?";
        $summaryParams[] = $grade;
        $summaryTypes .= "s";
    }
    
    if (!empty($section)) {
        $summaryWhere[] = "s.section_name = ?";
        $summaryParams[] = $section;
        $summaryTypes .= "s";
    }
    
    if (!empty($summaryWhere)) {
        $summaryQuery .= " WHERE " . implode(" AND ", $summaryWhere);
    }
    
    $summaryQuery .= " GROUP BY s.section_name ORDER BY s.section_name";
    
    $summaryStmt = $conn->prepare($summaryQuery);
    if (!empty($summaryParams)) {
        $summaryStmt->bind_param($summaryTypes, ...$summaryParams);
    }
    $summaryStmt->execute();
    $summaryResult = $summaryStmt->get_result();
    
    $section_summary = [];
    while ($row = $summaryResult->fetch_assoc()) {
        $section_summary[] = $row;
    }
    
    $response = [
        'success' => true,
        'stats' => $stats,
        'students' => $students,
        'recent_activity' => $recent_activity,
        'section_summary' => $section_summary
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>