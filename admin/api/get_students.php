<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'students' => []];

try {
    $result = $conn->query("
        SELECT 
            s.*, 
            p.fullname as parent_name, 
            p.contact as parent_contact 
        FROM students s 
        LEFT JOIN parents p ON s.id = p.student_id 
        ORDER BY s.id DESC
    ");
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        // Combine name parts for display
        $row['display_name'] = $row['student_name'] . 
                              ($row['student_mi'] ? ' ' . $row['student_mi'] . '.' : '') . 
                              ' ' . $row['student_last'];
        $students[] = $row;
    }
    
    $response = [
        'success' => true,
        'students' => $students
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>