<?php
require_once '../db-config/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get predictive analytics for tomorrow's possible absentees (7-day pattern)
    $predictions = getPredictiveAnalytics($conn);
    
    $response = [
        'success' => true,
        'predictions' => $predictions,
        'last_updated' => date('Y-m-d H:i:s')
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
                           LEFT JOIN parents p ON s.parent_id = p.id");
    
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