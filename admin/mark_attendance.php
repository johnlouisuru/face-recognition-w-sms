<?php
require_once "db-config/security.php";
// mark_attendance.php

// Initialize flags
$time_in_flag = null;
$time_out_flag = null;
$is_sent = 0;

// Read flags from POST: time_in = 1 => time_in; time_in = 2 => time_out
if (isset($_POST['time_in'])) {
    $val = intval($_POST['time_in']);
    if ($val == 1) {
        $time_in_flag = 1;
        $time_out_flag = 0;
    } elseif ($val == 2) {
        $time_in_flag = 0;
        $time_out_flag = 1;
    }
}

$student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';

if ($student_id === '' || ($time_in_flag === null && $time_out_flag === null)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}


// Fetch student name for messages
$student_name = $student_id;
if ($stmt = $conn->prepare("SELECT student_name FROM students WHERE student_id = ? LIMIT 1")) {
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $stmt->bind_result($fetched_name);
    if ($stmt->fetch()) {
        $student_name = $fetched_name;
    }
    $stmt->close();
}

// Helper functions
function time_in_exists_today($conn, $student_id) {
    $sql = "SELECT 1 FROM attendance WHERE student_id = ? AND DATE(time_in) = CURDATE() AND time_in IS NOT NULL LIMIT 1";
    if ($st = $conn->prepare($sql)) {
        $st->bind_param('s', $student_id);
        $st->execute();
        $st->store_result();
        $exists = $st->num_rows > 0;
        $st->close();
        return $exists;
    }
    return false;
}

function time_out_exists_today($conn, $student_id) {
    $sql = "SELECT 1 FROM attendance WHERE student_id = ? AND DATE(time_out) = CURDATE() AND time_out IS NOT NULL LIMIT 1";
    if ($st = $conn->prepare($sql)) {
        $st->bind_param('s', $student_id);
        $st->execute();
        $st->store_result();
        $exists = $st->num_rows > 0;
        $st->close();
        return $exists;
    }
    return false;
}

// CASE 1: Mark time_in
if ($time_in_flag === 1 && $time_out_flag === 0) {
    if (time_in_exists_today($conn, $student_id)) {
        echo json_encode([
            'success' => false,
            'already' => true,
            'message' => "Student {$student_name} already marked present"
        ]);
        $conn->close();
        exit;
    }

    $ins = $conn->prepare("INSERT INTO attendance (student_id, student_name, time_in, time_out, is_sent, is_logout_sent) 
                        VALUES (?, ?, NOW(), NULL, ?, ?)");
    if (!$ins) {
        error_log('Prepare insert failed: ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error (prepare insert)']);
        $conn->close();
        exit;
    }
    $ins->bind_param('ssii', $student_id, $student_name, $is_sent, $is_sent);
    if ($ins->execute()) {
        echo json_encode(['success' => true, 'message' => "Time in recorded for {$student_name}"]);
    } else {
        error_log('Insert execute failed: ' . $ins->error);
        echo json_encode(['success' => false, 'message' => 'Failed to insert attendance']);
    }
    $ins->close();
    $conn->close();
    exit;
}

// CASE 2: Mark time_out
if ($time_in_flag === 0 && $time_out_flag === 1) {
    if (time_out_exists_today($conn, $student_id)) {
        echo json_encode([
            'success' => false,
            'already' => true,
            'message' => "Student {$student_name} already marked time out today"
        ]);
        $conn->close();
        exit;
    }

    $sel = $conn->prepare(
        "SELECT id FROM attendance
         WHERE student_id = ?
           AND DATE(time_in) = CURDATE()
           AND time_in IS NOT NULL
           AND (time_out IS NULL OR DATE(time_out) <> CURDATE())
         LIMIT 1"
    );
    if (!$sel) {
        error_log('Prepare select failed: ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error (prepare select)']);
        $conn->close();
        exit;
    }
    $sel->bind_param('s', $student_id);
    $sel->execute();
    $sel->bind_result($attendance_id);
    if ($sel->fetch()) {
        $sel->close();
        $upd = $conn->prepare("UPDATE attendance SET time_out = NOW() WHERE id = ? LIMIT 1");
        if (!$upd) {
            error_log('Prepare update failed: ' . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error (prepare update)']);
            $conn->close();
            exit;
        }
        $upd->bind_param('i', $attendance_id);
        if ($upd->execute()) {
            echo json_encode(['success' => true, 'message' => "Time out recorded for {$student_name}"]);
        } else {
            error_log('Update execute failed: ' . $upd->error);
            echo json_encode(['success' => false, 'message' => 'Failed to update time out']);
        }
        $upd->close();
        $conn->close();
        exit;
    } else {
        $sel->close();
        echo json_encode([
            'success' => false,
            'message' => "No time_in found for {$student_name} today to mark time_out"
        ]);
        $conn->close();
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid time_in/time_out flags']);
$conn->close();
exit;
