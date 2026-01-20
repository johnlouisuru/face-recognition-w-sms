<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id = $_POST['student_db_id'] ?? 0;
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $student_last = trim($_POST['student_last']);
    $student_mi = trim($_POST['student_mi'] ?? '');
    $grade = trim($_POST['grade']);
    $section_name = trim($_POST['section_name']);
    $parent_fullname = trim($_POST['parent_fullname'] ?? '');
    $parent_contact = trim($_POST['parent_contact'] ?? '');
    $parent_password = $_POST['parent_password'] ?? '';
    $face_descriptor = $_POST['face_descriptor'] ?? '';
    
    // Validate required fields
    if (empty($student_id) || empty($student_name) || empty($student_last) || empty($grade)) {
        $response['message'] = 'All required fields must be filled';
        echo json_encode($response);
        exit;
    }
    
    // Check if student ID already exists (for new student)
    if ($id == 0) {
        $checkStmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $checkStmt->bind_param("s", $student_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $response['message'] = 'Student ID already exists';
            echo json_encode($response);
            exit;
        }
    }
    
    // Handle file uploads
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                $profile_picture = 'uploads/profiles/' . $file_name;
            }
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Prepare student name
        // $full_student_name = $student_name . ($student_mi ? ' ' . $student_mi . '.' : '') . ' ' . $student_last;
        $full_student_name = $student_name;
        
        if ($id == 0) {
            // Insert new student
            if (empty($face_descriptor)) {
                $response['message'] = 'Face descriptor data is required for new student';
                throw new Exception($response['message']);
            }
            
            $stmt = $conn->prepare("
                INSERT INTO students (
                    student_id, student_name, student_last, student_mi, 
                    grade, section_name, profile_picture, face_descriptor
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssssssss", 
                $student_id, $full_student_name, $student_last, $student_mi,
                $grade, $section_name, $profile_picture, $face_descriptor
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to insert student');
            }
            
            $student_db_id = $conn->insert_id;
            
        } else {
            // Update existing student
            $student_db_id = $id;
            
            // Get current face descriptor if not updating
            $current_face_descriptor = $face_descriptor;
            if (empty($face_descriptor)) {
                $faceStmt = $conn->prepare("SELECT face_descriptor FROM students WHERE id = ?");
                $faceStmt->bind_param("i", $id);
                $faceStmt->execute();
                $faceResult = $faceStmt->get_result();
                if ($faceResult->num_rows > 0) {
                    $current_face_descriptor = $faceResult->fetch_assoc()['face_descriptor'];
                }
            }
            
            if ($profile_picture) {
                $stmt = $conn->prepare("
                    UPDATE students SET 
                        student_name = ?, student_last = ?, student_mi = ?,
                        grade = ?, section_name = ?, 
                        profile_picture = ?, face_descriptor = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "sssssssi", 
                    $full_student_name, $student_last, $student_mi,
                    $grade, $section_name, $profile_picture, 
                    $current_face_descriptor, $id
                );
            } else {
                $stmt = $conn->prepare("
                    UPDATE students SET 
                        student_name = ?, student_last = ?, student_mi = ?,
                        grade = ?, section_name = ?, face_descriptor = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "ssssssi", 
                    $full_student_name, $student_last, $student_mi,
                    $grade, $section_name, $current_face_descriptor, $id
                );
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update student');
            }
        }
        
        // Handle parent information - FIXED RELATIONSHIP
        if (!empty($parent_fullname)) {
            if (!empty($parent_contact) && !empty($parent_password)) {
                $hashedParentPassword = password_hash($parent_password, PASSWORD_DEFAULT);
                
                // Check if parent already exists for this student
                $parentCheck = $conn->prepare("SELECT id FROM parents WHERE student_id = ?");
                $parentCheck->bind_param("i", $student_db_id);
                $parentCheck->execute();
                $parentResult = $parentCheck->get_result();
                
                if ($parentResult->num_rows > 0) {
                    // Update existing parent
                    $parentRow = $parentResult->fetch_assoc();
                    $parent_id = $parentRow['id'];
                    
                    $parentStmt = $conn->prepare("
                        UPDATE parents SET 
                            fullname = ?, contact = ?, password = ? 
                        WHERE id = ?
                    ");
                    $parentStmt->bind_param("sssi", $parent_fullname, $parent_contact, $hashedParentPassword, $parent_id);
                    
                    if (!$parentStmt->execute()) {
                        throw new Exception('Failed to update parent');
                    }
                } else {
                    // Insert new parent
                    $parentStmt = $conn->prepare("
                        INSERT INTO parents (fullname, contact, password, student_id) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $parentStmt->bind_param("sssi", $parent_fullname, $parent_contact, $hashedParentPassword, $student_db_id);
                    
                    if (!$parentStmt->execute()) {
                        throw new Exception('Failed to insert parent');
                    }
                    
                    $parent_id = $conn->insert_id;
                    
                    // Update student with parent_id (if your schema has this column)
                    // Note: Your schema shows students.parent_id, so we need to update it
                    $updateStudentStmt = $conn->prepare("UPDATE students SET parent_id = ? WHERE id = ?");
                    $updateStudentStmt->bind_param("ii", $parent_id, $student_db_id);
                    $updateStudentStmt->execute();
                }
            } elseif (!empty($parent_fullname) || !empty($parent_contact)) {
                // Parent info provided but password missing - create parent without password
                $parentCheck = $conn->prepare("SELECT id FROM parents WHERE student_id = ?");
                $parentCheck->bind_param("i", $student_db_id);
                $parentCheck->execute();
                $parentResult = $parentCheck->get_result();
                
                if ($parentResult->num_rows > 0) {
                    // Update existing parent without changing password
                    $parentRow = $parentResult->fetch_assoc();
                    $parent_id = $parentRow['id'];
                    
                    $parentStmt = $conn->prepare("
                        UPDATE parents SET 
                            fullname = ?, contact = ? 
                        WHERE id = ?
                    ");
                    $parentStmt->bind_param("ssi", $parent_fullname, $parent_contact, $parent_id);
                    $parentStmt->execute();
                } else {
                    // Insert new parent with empty password
                    $parentStmt = $conn->prepare("
                        INSERT INTO parents (fullname, contact, student_id) 
                        VALUES (?, ?, ?)
                    ");
                    $parentStmt->bind_param("ssi", $parent_fullname, $parent_contact, $student_db_id);
                    $parentStmt->execute();
                    
                    $parent_id = $conn->insert_id;
                    
                    // Update student with parent_id
                    $updateStudentStmt = $conn->prepare("UPDATE students SET parent_id = ? WHERE id = ?");
                    $updateStudentStmt->bind_param("ii", $parent_id, $student_db_id);
                    $updateStudentStmt->execute();
                }
            }
        } else {
            // No parent info - remove existing parent if exists
            $parentCheck = $conn->prepare("SELECT id FROM parents WHERE student_id = ?");
            $parentCheck->bind_param("i", $student_db_id);
            $parentCheck->execute();
            $parentResult = $parentCheck->get_result();
            
            if ($parentResult->num_rows > 0) {
                $parentRow = $parentResult->fetch_assoc();
                $parent_id = $parentRow['id'];
                
                // Delete parent
                $deleteParentStmt = $conn->prepare("DELETE FROM parents WHERE id = ?");
                $deleteParentStmt->bind_param("i", $parent_id);
                $deleteParentStmt->execute();
                
                // Remove parent_id from student
                $updateStudentStmt = $conn->prepare("UPDATE students SET parent_id = NULL WHERE id = ?");
                $updateStudentStmt->bind_param("i", $student_db_id);
                $updateStudentStmt->execute();
            }
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Student saved successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>