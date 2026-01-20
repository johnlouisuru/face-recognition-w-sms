<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Trim whitespace
    $fullname = trim($fullname);
    $email = trim($email);
    $password = trim($password);
    
    // Basic validation
    if (empty($fullname)) {
        $response['message'] = 'Full name is required';
        echo json_encode($response);
        exit;
    }
    
    if (empty($email)) {
        $response['message'] = 'Email is required';
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
        echo json_encode($response);
        exit;
    }
    
    if ($id == 0) {
        // INSERT new teacher
        if (empty($password)) {
            $response['message'] = 'Password is required for new teacher';
            echo json_encode($response);
            exit;
        }
        
        if (strlen($password) < 6) {
            $response['message'] = 'Password must be at least 6 characters long';
            echo json_encode($response);
            exit;
        }
        
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            exit;
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert the teacher
        $stmt = $conn->prepare("INSERT INTO teachers (fullname, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Teacher added successfully';
            $response['insert_id'] = $conn->insert_id;
        } else {
            $response['message'] = 'Failed to save teacher. Please try again.';
        }
        
    } else {
        // UPDATE existing teacher
        // Check if email already exists (excluding current teacher)
        $checkStmt = $conn->prepare("SELECT id FROM teachers WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $response['message'] = 'Email already exists';
            echo json_encode($response);
            exit;
        }
        
        if (!empty($password)) {
            // Update with password
            if (strlen($password) < 6) {
                $response['message'] = 'Password must be at least 6 characters long';
                echo json_encode($response);
                exit;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE teachers SET fullname = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullname, $email, $hashedPassword, $id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE teachers SET fullname = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fullname, $email, $id);
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Teacher updated successfully';
        } else {
            $response['message'] = 'Failed to update teacher. Please try again.';
        }
    }
    
} catch (Exception $e) {
    $response['message'] = 'An error occurred. Please try again.';
}

echo json_encode($response);
?>