<?php
require_once 'db-config/security.php';

// Check if student_absences table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'student_absences'");
if ($table_check->num_rows == 0) {
    // Create the table
    $create_table = "
    CREATE TABLE IF NOT EXISTS `student_absences` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `student_id` varchar(50) NOT NULL,
      `attendance_date` date NOT NULL,
      `reason` varchar(100) NOT NULL,
      `remarks` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_student_attendance` (`student_id`, `attendance_date`),
      KEY `idx_student_id` (`student_id`),
      KEY `idx_attendance_date` (`attendance_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
}

// Get the oldest date from attendance records
$oldest_date_query = "SELECT MIN(DATE(timestamp)) as oldest_date FROM attendance";
$oldest_date_result = $conn->query($oldest_date_query);
$oldest_date_row = $oldest_date_result->fetch_assoc();
$oldest_date = $oldest_date_row['oldest_date'] ?? date('Y-m-d');

// Get all unique dates for filtering
$dates_query = "SELECT DISTINCT DATE(timestamp) as attendance_date 
                FROM attendance 
                ORDER BY attendance_date DESC";
$dates_result = $conn->query($dates_query);

// Get selected date from filter, default to today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Handle form submission for updating absence reasons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_absences'])) {
    foreach ($_POST['reason'] as $student_id => $reason) {
        $attendance_date = $_POST['attendance_date'];
        
        // Check if record already exists
        $check_sql = "SELECT id FROM student_absences 
                      WHERE student_id = ? AND attendance_date = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $student_id, $attendance_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $sql = "UPDATE student_absences 
                    SET reason = ?, remarks = ? 
                    WHERE student_id = ? AND attendance_date = ?";
            $stmt = $conn->prepare($sql);
            $remarks = $_POST['remarks'][$student_id] ?? '';
            $stmt->bind_param("ssss", $reason, $remarks, $student_id, $attendance_date);
        } else {
            // Insert new record
            $sql = "INSERT INTO student_absences (student_id, attendance_date, reason, remarks) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $remarks = $_POST['remarks'][$student_id] ?? '';
            $stmt->bind_param("ssss", $student_id, $attendance_date, $reason, $remarks);
        }
        $stmt->execute();
    }
    
    // Redirect to refresh the page and prevent form resubmission
    header("Location: absence_tracking.php?date=" . $attendance_date . "&success=1");
    exit();
}

// Get students who are absent on the selected date
// Using LEFT JOIN instead of trying to get sa.reason directly
$absent_students_query = "
    SELECT DISTINCT 
        s.student_id,
        s.student_name,
        s.student_last,
        s.grade,
        s.section_name,
        sa.reason as current_reason,
        sa.remarks as current_remarks
    FROM students s
    LEFT JOIN student_absences sa ON s.student_id = sa.student_id AND sa.attendance_date = ?
    WHERE NOT EXISTS (
        SELECT 1 
        FROM attendance a 
        WHERE a.student_id = s.student_id 
        AND DATE(a.timestamp) = ?
    )
    AND EXISTS (
        SELECT 1 
        FROM attendance a 
        WHERE a.student_id = s.student_id
    )
    ORDER BY s.grade, s.section_name, s.student_name
";

$stmt = $conn->prepare($absent_students_query);
$stmt->bind_param("ss", $selected_date, $selected_date);
$stmt->execute();
$absent_students = $stmt->get_result();

// Get success message
$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Absence Tracking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .date-filter {
            display: flex;
            gap: 15px;
            align-items: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .date-filter label {
            font-weight: 600;
            color: #555;
        }

        .date-filter input[type="date"] {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .date-filter button {
            padding: 8px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .date-filter button:hover {
            transform: translateY(-2px);
        }

        .success-alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .warning-alert {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeeba;
        }

        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .reason-select {
            width: 280px;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
        }

        .remarks-input {
            width: 200px;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .save-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .save-btn:hover {
            transform: translateY(-2px);
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            min-width: 200px;
        }

        .stat-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .export-btn {
            padding: 8px 20px;
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .export-btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
            
            .date-filter {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .reason-select {
                width: 200px;
            }
            
            .remarks-input {
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Student Absence Tracking</h1>
            <div class="action-buttons">
                <a href="index.php" class="export-btn">↩ Back to Dashboard</a>
                <button class="export-btn" onclick="exportToExcel()">📥 Export to Excel</button>
                <a href="view_absence_reports.php" style="text-decoration: none;">
                    <button class="export-btn" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        📈 View Reports
                    </button>
                </a>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="success-alert">
            <span style="font-size: 20px;">✅</span>
            <span>Absence reasons have been successfully updated!</span>
        </div>
        <?php endif; ?>

        <!-- Date Filter -->
        <div class="date-filter">
            <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <label for="date">Select Date:</label>
                <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" 
                       min="<?php echo $oldest_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                <button type="submit">View Absences</button>
            </form>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Selected Date</h3>
                <div class="number"><?php echo date('F d, Y', strtotime($selected_date)); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Absent Students</h3>
                <div class="number"><?php echo $absent_students->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3>Oldest Record</h3>
                <div class="number"><?php echo date('F d, Y', strtotime($oldest_date)); ?></div>
            </div>
        </div>

        <!-- Absence Form -->
        <form method="POST" id="absenceForm">
            <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Reason for Absence</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($absent_students->num_rows > 0): ?>
                            <?php while ($student = $absent_students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_last'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['grade']); ?></td>
                                <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <select name="reason[<?php echo $student['student_id']; ?>]" class="reason-select" required>
                                        <option value="">-- Select Reason --</option>
                                        <option value="Financial Difficulty / Poverty" <?php echo ($student['current_reason'] == 'Financial Difficulty / Poverty') ? 'selected' : ''; ?>>💰 Financial Difficulty / Poverty</option>
                                        <option value="Employment" <?php echo ($student['current_reason'] == 'Employment') ? 'selected' : ''; ?>>💼 Employment</option>
                                        <option value="Illness and Physical Health Issues" <?php echo ($student['current_reason'] == 'Illness and Physical Health Issues') ? 'selected' : ''; ?>>🤒 Illness and Physical Health Issues</option>
                                        <option value="Mental and Emotional Well-Being" <?php echo ($student['current_reason'] == 'Mental and Emotional Well-Being') ? 'selected' : ''; ?>>🧠 Mental and Emotional Well-Being</option>
                                        <option value="Early Pregnancy" <?php echo ($student['current_reason'] == 'Early Pregnancy') ? 'selected' : ''; ?>>🤰 Early Pregnancy</option>
                                        <option value="Weather Conditions" <?php echo ($student['current_reason'] == 'Weather Conditions') ? 'selected' : ''; ?>>☔ Weather Conditions</option>
                                        <option value="Lack of Interest" <?php echo ($student['current_reason'] == 'Lack of Interest') ? 'selected' : ''; ?>>😐 Lack of Interest</option>
                                        <option value="Influence of Peers" <?php echo ($student['current_reason'] == 'Influence of Peers') ? 'selected' : ''; ?>>👥 Influence of Peers</option>
                                        <option value="Family Problems" <?php echo ($student['current_reason'] == 'Family Problems') ? 'selected' : ''; ?>>🏠 Family Problems</option>
                                        <option value="Lack of Parental Support / Monitoring" <?php echo ($student['current_reason'] == 'Lack of Parental Support / Monitoring') ? 'selected' : ''; ?>>👪 Lack of Parental Support / Monitoring</option>
                                        <option value="Teacher-Related Issues" <?php echo ($student['current_reason'] == 'Teacher-Related Issues') ? 'selected' : ''; ?>>👨‍🏫 Teacher-Related Issues</option>
                                        <option value="Unfavorable Classroom" <?php echo ($student['current_reason'] == 'Unfavorable Classroom') ? 'selected' : ''; ?>>🏫 Unfavorable Classroom</option>
                                        <option value="Bullying and School Safety" <?php echo ($student['current_reason'] == 'Bullying and School Safety') ? 'selected' : ''; ?>>⚠️ Bullying and School Safety</option>
                                        <option value="Academic Pressure and Workload" <?php echo ($student['current_reason'] == 'Academic Pressure and Workload') ? 'selected' : ''; ?>>📚 Academic Pressure and Workload</option>
                                        <option value="School distance / Transportation" <?php echo ($student['current_reason'] == 'School distance / Transportation') ? 'selected' : ''; ?>>🚌 School distance / Transportation</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="remarks[<?php echo $student['student_id']; ?>]" 
                                           class="remarks-input"
                                           placeholder="Add remarks..."
                                           value="<?php echo htmlspecialchars($student['current_remarks'] ?? ''); ?>">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <span style="font-size: 48px; display: block; margin-bottom: 20px;">🎉</span>
                                    No absent students found for this date!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($absent_students->num_rows > 0): ?>
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="submit" name="update_absences" class="save-btn">
                    💾 Save All Absence Reasons
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Export to Excel function
        function exportToExcel() {
            const date = document.getElementById('date').value;
            window.location.href = 'export_absences.php?date=' + date;
        }

        // Auto-hide success message after 5 seconds
        setTimeout(function() {
            const alert = document.querySelector('.success-alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.style.display = 'none';
                    }
                }, 500);
            }
        }, 5000);

        // Confirm before leaving with unsaved changes
        let formChanged = false;
        const form = document.getElementById('absenceForm');
        
        if (form) {
            form.addEventListener('change', function() {
                formChanged = true;
            });
            
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            form.addEventListener('submit', function() {
                formChanged = false;
            });
        }
    </script>
</body>
</html>