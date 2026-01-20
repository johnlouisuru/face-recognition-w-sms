<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'student' => null];

try {
    $id = $_GET['id'] ?? 0;
    
    // Get student with parent info using the correct join
    $stmt = $conn->prepare("
        SELECT 
            s.*, 
            p.fullname as parent_fullname, 
            p.contact as parent_contact 
        FROM students s 
        LEFT JOIN parents p ON s.id = p.student_id 
        WHERE s.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Parse the student name from the combined field
        // Since student_name might contain full name, let's use the separate fields
        $full_name = $student['student_name'];
        
        $response = [
            'success' => true,
            'student' => $student
        ];
    } else {
        $response['message'] = 'Student not found';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>