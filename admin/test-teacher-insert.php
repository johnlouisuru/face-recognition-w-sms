<?php
require_once 'db-config/security.php';

// Simple direct insert test
$fullname = "Test Teacher";
$email = "test@example.com";
$password = "password123";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO teachers (fullname, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $fullname, $email, $hashedPassword);

if ($stmt->execute()) {
    echo "Success! Insert ID: " . $conn->insert_id;
} else {
    echo "Error: " . $stmt->error;
}
?>