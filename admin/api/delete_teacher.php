<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id = $_POST['id'] ?? 0;
    
    if ($id == 1) {
        $response['message'] = 'Cannot delete the primary admin account';
        echo json_encode($response);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Teacher deleted successfully';
    } else {
        $response['message'] = 'Failed to delete teacher';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>