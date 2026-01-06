
<?php
require_once "db-config/security.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $student_name = $_POST['student_name'];
    $student_last = $_POST['student_last'];
    $student_middle = $_POST['student_middle'];
    $grade = $_POST['grade'];

    $stmt = $conn->prepare("UPDATE students SET student_last =? , student_mi =?, student_name = ?, grade = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $student_last, $student_middle, $student_name, $grade, $id);

    if ($stmt->execute()) {
        $get_student_id = $conn->prepare("SELECT student_id FROM students WHERE id = ?");
        $get_student_id->bind_param("i", $id);
        $get_student_id->execute();
        $result = $get_student_id->get_result();
        $student = $result->fetch_assoc();
        
        if ($student) {
            $update_attendance = $conn->prepare("UPDATE attendance SET student_name = ? WHERE student_id = ?");
            $update_attendance->bind_param("ss", $student_name, $student['student_id']);
            $update_attendance->execute();
            $update_attendance->close();
        }
        
        $get_student_id->close();
        
        echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
