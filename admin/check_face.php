<?php
require_once "db-config/security.php";

$input = json_decode($_POST['descriptor'], true);

// Fetch all stored descriptors
$result = $conn->query("SELECT student_id, descriptor FROM student_faces");

function euclideanDistance($a, $b) {
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}

while ($row = $result->fetch_assoc()) {
    $stored = json_decode($row['descriptor'], true);
    $distance = euclideanDistance($input, $stored);

    if ($distance < 0.45) {
        echo json_encode([
            'found' => true,
            'student_id' => $row['student_id'],
            'distance' => $distance
        ]);
        exit;
    }
}

echo json_encode(['found' => false]);
