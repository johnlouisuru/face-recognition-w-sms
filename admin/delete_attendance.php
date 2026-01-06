<?php
require_once "db-config/security.php";

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attendance record deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>