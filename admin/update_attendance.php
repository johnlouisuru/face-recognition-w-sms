<?php
require_once "db-config/security.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $time_in = $_POST['timestamp']; // e.g., "2025-12-21T10:30"
    $time_out = $_POST['time_out']; // e.g., "2025-12-21T10:30"

    // Parse time_in
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $time_in);
    if (!$dateTime) {
        echo json_encode(['success' => false, 'message' => 'Invalid time_in format']);
        exit;
    }
    $time_in = $dateTime->format('Y-m-d H:i:s');

    // Parse time_out only if provided
    $time_out_formatted = null;
    if (!empty($time_out)) {
        $dateTimeOut = DateTime::createFromFormat('Y-m-d\TH:i', $time_out);
        if (!$dateTimeOut) {
            echo json_encode(['success' => false, 'message' => 'Invalid time_out format']);
            exit;
        }
        $time_out_formatted = $dateTimeOut->format('Y-m-d H:i:s');
    }

    // Prepare SQL
    $stmt = $conn->prepare("UPDATE attendance SET time_in = ?, time_out = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssi", $time_in, $time_out_formatted, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
