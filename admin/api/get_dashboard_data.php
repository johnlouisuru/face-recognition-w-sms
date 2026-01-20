<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get summary statistics
    $today = date('Y-m-d');
    
    // Total students
    $result = $conn->query("SELECT COUNT(*) as total FROM students");
    $totalStudents = $result->fetch_assoc()['total'];
    
    // Present today (students with time_in today)
    $result = $conn->query("SELECT COUNT(DISTINCT student_id) as present FROM attendance 
                           WHERE DATE(time_in) = '$today'");
    $presentToday = $result->fetch_assoc()['present'];
    
    // Attendance rate
    $attendanceRate = $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100, 2) : 0;
    
    // 7-day attendance trend
    $trendLabels = [];
    $trendData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $result = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM attendance 
                               WHERE DATE(time_in) = '$date'");
        $count = $result->fetch_assoc()['count'];
        $trendLabels[] = date('M d', strtotime($date));
        $trendData[] = $count;
    }
    
    // Grade distribution
    $result = $conn->query("SELECT grade, COUNT(*) as count FROM students GROUP BY grade");
    $gradeLabels = [];
    $gradeData = [];
    while ($row = $result->fetch_assoc()) {
        $gradeLabels[] = $row['grade'];
        $gradeData[] = $row['count'];
    }
    
    // Recent attendance (last 20 records) - REMOVED STATUS
    $result = $conn->query("SELECT a.*, s.grade, s.section_name 
                           FROM attendance a 
                           JOIN students s ON a.student_id = s.student_id 
                           WHERE a.time_in IS NOT NULL
                           ORDER BY a.timestamp DESC 
                           LIMIT 20");
    $recentAttendance = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate duration if time_out exists
        $duration = 'N/A';
        if ($row['time_in'] && $row['time_out']) {
            $timeIn = strtotime($row['time_in']);
            $timeOut = strtotime($row['time_out']);
            $diff = $timeOut - $timeIn;
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $duration = sprintf('%02d:%02d', $hours, $minutes);
        }
        
        $recentAttendance[] = [
            'timestamp' => date('M d, H:i', strtotime($row['timestamp'])),
            'student_id' => $row['student_id'],
            'student_name' => $row['student_name'],
            'grade' => $row['grade'],
            'section_name' => $row['section_name'],
            'time_in' => $row['time_in'] ? date('H:i', strtotime($row['time_in'])) : 'N/A',
            'time_out' => $row['time_out'] ? date('H:i', strtotime($row['time_out'])) : 'Not yet',
            'duration' => $duration
        ];
    }
    
    // Predictive analytics for tomorrow's possible absentees (7-day pattern)
    $predictions = getPredictiveAnalytics($conn);
    
    $response = [
        'success' => true,
        'summary' => [
            'total_students' => $totalStudents,
            'present_today' => $presentToday,
            'attendance_rate' => $attendanceRate
        ],
        'charts' => [
            'trend_labels' => $trendLabels,
            'trend_data' => $trendData,
            'grade_labels' => $gradeLabels,
            'grade_data' => $gradeData
        ],
        'recent_attendance' => $recentAttendance,
        'predictions' => $predictions
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);

function getPredictiveAnalytics($conn) {
    $predictions = [];
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
    $today = date('Y-m-d');
    
    // Get all active students
    $result = $conn->query("SELECT s.*, p.contact as parent_contact 
                           FROM students s 
                           LEFT JOIN parents p ON s.id = p.student_id");
    
    while ($student = $result->fetch_assoc()) {
        $studentId = $student['student_id'];
        
        // Count school days in last 7 days (Mon-Fri)
        $schoolDays = getSchoolDaysCount($sevenDaysAgo, $today);
        
        // Count days present in last 7 days
        $presentResult = $conn->query("SELECT COUNT(DISTINCT DATE(time_in)) as present_days 
                                      FROM attendance 
                                      WHERE student_id = '$studentId' 
                                      AND DATE(time_in) BETWEEN '$sevenDaysAgo' AND '$today'");
        $presentDays = $presentResult->fetch_assoc()['present_days'];
        
        // Calculate absence rate for last 7 days
        $absenceDays = $schoolDays - $presentDays;
        $absenceRate = $schoolDays > 0 ? round(($absenceDays / $schoolDays) * 100, 2) : 0;
        
        // Get last absent date
        if ($absenceDays > 0) {
            // Find the most recent date in last 7 days where student didn't check in
            $absentDates = [];
            $current = strtotime($sevenDaysAgo);
            $end = strtotime($today);
            
            while ($current <= $end) {
                $date = date('Y-m-d', $current);
                $dayOfWeek = date('N', $current);
                
                // Only count school days
                if ($dayOfWeek <= 5) {
                    // Check if student was present on this date
                    $checkResult = $conn->query("SELECT 1 FROM attendance 
                                                WHERE student_id = '$studentId' 
                                                AND DATE(time_in) = '$date' 
                                                LIMIT 1");
                    
                    if ($checkResult->num_rows == 0) {
                        $absentDates[] = $date;
                    }
                }
                $current = strtotime('+1 day', $current);
            }
            
            $lastAbsent = !empty($absentDates) ? end($absentDates) : null;
        } else {
            $lastAbsent = null;
        }
        
        // Determine risk level based on 7-day pattern
        $riskLevel = 'Low';
        if ($absenceRate >= 50) { // 50% or more absent in last week
            $riskLevel = 'High';
        } elseif ($absenceRate >= 30) { // 30-49% absent in last week
            $riskLevel = 'Medium';
        }
        
        // Only include students with medium or high risk
        if ($absenceRate >= 30) {
            $predictions[] = [
                'student_id' => $student['student_id'],
                'student_name' => $student['student_name'],
                'grade' => $student['grade'],
                'absence_rate' => $absenceRate,
                'last_absent' => $lastAbsent ? date('M d', strtotime($lastAbsent)) : 'None',
                'parent_contact' => $student['parent_contact'],
                'risk_level' => $riskLevel
            ];
        }
    }
    
    // Sort by risk level (High to Low) and then by absence rate
    usort($predictions, function($a, $b) {
        $riskOrder = ['High' => 3, 'Medium' => 2, 'Low' => 1];
        if ($riskOrder[$a['risk_level']] == $riskOrder[$b['risk_level']]) {
            return $b['absence_rate'] <=> $a['absence_rate'];
        }
        return $riskOrder[$b['risk_level']] <=> $riskOrder[$a['risk_level']];
    });
    
    return array_slice($predictions, 0, 15); // Return top 15 potential absentees
}

function getSchoolDaysCount($startDate, $endDate) {
    $count = 0;
    $current = strtotime($startDate);
    $end = strtotime($endDate);
    
    while ($current <= $end) {
        $dayOfWeek = date('N', $current);
        if ($dayOfWeek <= 5) { // Monday to Friday
            $count++;
        }
        $current = strtotime('+1 day', $current);
    }
    
    return $count;
}
?>