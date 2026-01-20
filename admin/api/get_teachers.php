<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'teachers' => []];

try {
    $result = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
    $teachers = [];
    
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    
    $response = [
        'success' => true,
        'teachers' => $teachers
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>