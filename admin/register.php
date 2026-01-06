<?php
require_once "db-config/security.php";

// Add JSON header at the top
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $student_name = strtoupper($_POST['student_name']);
    $student_last = strtoupper($_POST['student_last']);
    $student_middle = isset($_POST['student_middle']) ? strtoupper($_POST['student_middle']) : 'N/A';
    $grade = $_POST['grade'];
    $section_id = $_POST['section_id'];
    $section_name = strtoupper($_POST['section_name']);
    $face_descriptor = $_POST['face_descriptor'];
    
    // 🔑 Get parent information
    $parent_fullname = strtoupper($_POST['parent_fullname']);
    $parent_contact = $_POST['parent_contact'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if student already exists
        $check = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $check->bind_param("s", $student_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Student ID already exists']);
            $conn->rollback();
            exit;
        }
        $check->close();

        // Insert new student
        $stmt = $conn->prepare("INSERT INTO students (student_id, student_name, student_last, student_mi, grade, section_id, section_name, face_descriptor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $student_id, $student_name, $student_last, $student_middle, $grade, $section_id, $section_name, $face_descriptor);

        if (!$stmt->execute()) {
            throw new Exception('Failed to register student: ' . $stmt->error);
        }

        // 🔑 Get the newly inserted student's ID
        $new_student_id = $conn->insert_id;
        $stmt->close();

        // 🔑 Insert parent information linked to the student
        $parent_stmt = $conn->prepare("INSERT INTO parents (student_id, fullname, contact) VALUES (?, ?, ?)");
        $parent_stmt->bind_param("iss", $new_student_id, $parent_fullname, $parent_contact);

        if (!$parent_stmt->execute()) {
            throw new Exception('Failed to register parent information: ' . $parent_stmt->error);
        }
        $parent_stmt->close();

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student and parent information registered successfully',
            'student_id' => $new_student_id
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>