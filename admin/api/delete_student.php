<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id = $_POST['id'] ?? 0;
    
    // Get student info first
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $response['message'] = 'Student not found';
        echo json_encode($response);
        exit;
    }
    
    $student = $result->fetch_assoc();
    $student_id = $student['student_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete attendance records first
        $attendanceStmt = $conn->prepare("DELETE FROM attendance WHERE student_id = ?");
        $attendanceStmt->bind_param("s", $student_id);
        $attendanceStmt->execute();
        
        // Get parent_id before deleting student
        $parentStmt = $conn->prepare("SELECT id FROM parents WHERE student_id = ?");
        $parentStmt->bind_param("i", $id);
        $parentStmt->execute();
        $parentResult = $parentStmt->get_result();
        $parent_id = $parentResult->num_rows > 0 ? $parentResult->fetch_assoc()['id'] : null;
        
        // Delete the student
        $studentStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $studentStmt->bind_param("i", $id);
        $studentStmt->execute();
        
        // Delete parent if exists
        if ($parent_id) {
            $parentDeleteStmt = $conn->prepare("DELETE FROM parents WHERE id = ?");
            $parentDeleteStmt->bind_param("i", $parent_id);
            $parentDeleteStmt->execute();
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Student deleted successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Failed to delete student: ' . $e->getMessage();
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>