<?php
require_once "db-config/security.php";
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed",
        "message" => $conn->connect_error
    ]);
    exit;
}

// ========== HANDLE UPDATE REQUESTS ==========
if (isset($_GET['update_timein']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE attendance SET is_sent = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Time-in flag updated",
            "id" => $id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $conn->error
        ]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if (isset($_GET['update_timeout']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE attendance SET is_logout_sent = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Time-out flag updated",
            "id" => $id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $conn->error
        ]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// ========== FETCH ATTENDANCE RECORDS ==========
// Scenario 1: Time In (is_sent=0, is_logout_sent=0, time_out IS NULL)
// Scenario 2: Time Out (is_sent=1, is_logout_sent=0, time_out IS NOT NULL)

$sql = "
    SELECT 
        a.id,
        a.student_id,
        s.student_name,
        p.contact,
        DATE_FORMAT(a.time_in, '%H:%i:%s') AS time_in,
        CASE 
            WHEN a.time_out IS NULL THEN NULL
            ELSE DATE_FORMAT(a.time_out, '%H:%i:%s')
        END AS time_out,
        a.is_sent,
        a.is_logout_sent
    FROM attendance a
    JOIN students s ON a.student_id = s.student_id
    JOIN parents p ON p.student_id = s.id
    WHERE 
        (
            (a.is_sent = 0 AND a.is_logout_sent = 0 AND a.time_out IS NULL)
            OR
            (a.is_sent = 1 AND a.is_logout_sent = 0 AND a.time_out IS NOT NULL)
        )
        AND p.contact IS NOT NULL
        AND p.contact != ''
    ORDER BY a.id ASC
    LIMIT 50
    ";

$result = $conn->query($sql);

$records = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = [
            "id"             => intval($row["id"]),
            "student_id"     => $row["student_id"],
            "student_name"   => $row["student_name"],
            "parent_contact" => $row["contact"],
            "time_in"        => $row["time_in"],
            "time_out"       => $row["time_out"] ?? "NULL",
            "is_sent"        => intval($row["is_sent"]),
            "is_logout_sent" => intval($row["is_logout_sent"])
        ];
    }
}

echo json_encode([
    "success" => true,
    "count" => count($records),
    "records" => $records
], JSON_PRETTY_PRINT);

$conn->close();
?>