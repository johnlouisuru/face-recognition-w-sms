
<?php
require_once "db-config/security.php";

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];

    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("DELETE FROM attendance WHERE student_id = ?");
        $stmt1->bind_param("s", $student_id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt2->bind_param("s", $student_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
    }
}

$conn->close();
?>