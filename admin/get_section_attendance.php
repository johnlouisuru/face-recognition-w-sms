
<?php
// session_start();
header('Content-Type: application/json');

require_once "db-config/security.php";


// Get section_id from query parameter or session
$section_id = isset($_GET['section_id']) ? $_GET['section_id'] : (isset($_SESSION['section_id']) ? $_SESSION['section_id'] : null);

if (!$section_id) {
    echo json_encode(['error' => 'No section specified']);
    exit;
}

try {
    // Get today's date
    $today = date('Y-m-d');

    // Get all students in this section
    $studentsStmt = $conn->prepare("
        SELECT student_id, student_name, student_last, student_mi, grade, section_id, section_name 
        FROM students 
        WHERE section_id = ?
        ORDER BY student_name
    ");
    $studentsStmt->bind_param("i", $section_id);
    $studentsStmt->execute();
    $studentsResult = $studentsStmt->get_result();

    $students = [];
    while ($student = $studentsResult->fetch_assoc()) {
        $students[] = $student;
    }
    $studentsStmt->close();

    // Get today's attendance for this section
    $attendanceStmt = $conn->prepare("
        SELECT a.id, a.student_id, a.time_in, a.time_out, a.timestamp
        FROM attendance a
        INNER JOIN students s ON a.student_id = s.student_id
        WHERE s.section_id = ? AND DATE(a.timestamp) = ?
    ");
    $attendanceStmt->bind_param("is", $section_id, $today);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();

    // Create a map of attendance records by student_id
    $attendanceMap = [];
    while ($attendance = $attendanceResult->fetch_assoc()) {
        $attendanceMap[$attendance['student_id']] = $attendance;
    }
    $attendanceStmt->close();

    // Combine students with their attendance data
    $studentsWithAttendance = [];
    $presentCount = 0;

    foreach ($students as $student) {
        $studentData = [
            'student_id' => $student['student_id'],
            'student_name' => trim($student['student_name'] . ' ' . $student['student_mi'] . ' ' . $student['student_last']),
            'grade' => $student['grade'],
            'section_id' => $student['section_id'],
            'section_name' => $student['section_name'],
            'time_in' => null,
            'time_out' => null,
            'attendance_id' => null
        ];

        // Check if student has attendance for today
        if (isset($attendanceMap[$student['student_id']])) {
            $attendance = $attendanceMap[$student['student_id']];
            $studentData['time_in'] = $attendance['time_in'];
            $studentData['time_out'] = $attendance['time_out'];
            $studentData['attendance_id'] = $attendance['id'];
            $presentCount++;
        }

        $studentsWithAttendance[] = $studentData;
    }

    // Prepare response
    $response = [
        'total_students' => count($students),
        'present_today' => $presentCount,
        'absent_today' => count($students) - $presentCount,
        'section_id' => $section_id,
        'students' => $studentsWithAttendance,
        'last_update' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
