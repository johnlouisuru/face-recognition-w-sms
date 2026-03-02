<?php
require_once 'db-config/security.php';
// Get unique sections from students table
$sections_query = "SELECT DISTINCT section_id, section_name FROM students WHERE section_id IS NOT NULL AND section_name IS NOT NULL ORDER BY section_name";
$sections_result = $conn->query($sections_query);

// Get selected section and date
$selected_section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$selected_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d');

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $attendance_date = $_POST['attendance_date'];
    $section_id = $_POST['section_id'];
    
    foreach ($_POST['attendance'] as $student_id => $data) {
        $student_name = $data['name'];
        $time_in = !empty($data['time_in']) ? $attendance_date . ' ' . $data['time_in'] . ':00' : null;
        $time_out = !empty($data['time_out']) ? $attendance_date . ' ' . $data['time_out'] . ':00' : null;
        
        // Check if attendance record already exists for this student on this date
        $check_query = "SELECT id FROM attendance WHERE student_id = ? AND DATE(time_in) = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $student_id, $attendance_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $row = $check_result->fetch_assoc();
            $update_query = "UPDATE attendance SET time_in = ?, time_out = ?, timestamp = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssi", $time_in, $time_out, $row['id']);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_query = "INSERT INTO attendance (student_id, student_name, time_in, time_out, timestamp, is_sent, is_logout_sent) 
                            VALUES (?, ?, ?, ?, NOW(), 0, 0)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $student_id, $student_name, $time_in, $time_out);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    
    $success_message = "Attendance marked successfully for " . date('F j, Y', strtotime($attendance_date));
}

// Get students for selected section
$students = [];
$selected_section_name = '';
if ($selected_section_id > 0) {
    // Get section name
    $section_name_query = "SELECT DISTINCT section_name FROM students WHERE section_id = ? LIMIT 1";
    $section_name_stmt = $conn->prepare($section_name_query);
    $section_name_stmt->bind_param("i", $selected_section_id);
    $section_name_stmt->execute();
    $section_name_result = $section_name_stmt->get_result();
    if ($section_name_row = $section_name_result->fetch_assoc()) {
        $selected_section_name = $section_name_row['section_name'];
    }
    $section_name_stmt->close();
    
    // Get students from selected section
    $students_query = "SELECT id, student_id, student_name, student_last, student_mi, grade, section_name 
                      FROM students 
                      WHERE section_id = ? 
                      ORDER BY student_name";
    $students_stmt = $conn->prepare($students_query);
    $students_stmt->bind_param("i", $selected_section_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    while ($row = $students_result->fetch_assoc()) {
        // Check if attendance already exists for this date
        $attendance_query = "SELECT time_in, time_out FROM attendance 
                            WHERE student_id = ? AND DATE(time_in) = ?";
        $attendance_stmt = $conn->prepare($attendance_query);
        $attendance_stmt->bind_param("ss", $row['student_id'], $selected_date);
        $attendance_stmt->execute();
        $attendance_result = $attendance_stmt->get_result();
        
        if ($attendance_result->num_rows > 0) {
            $attendance = $attendance_result->fetch_assoc();
            $row['existing_time_in'] = $attendance['time_in'] ? date('H:i', strtotime($attendance['time_in'])) : '';
            $row['existing_time_out'] = $attendance['time_out'] ? date('H:i', strtotime($attendance['time_out'])) : '';
        } else {
            $row['existing_time_in'] = '';
            $row['existing_time_out'] = '';
        }
        
        $students[] = $row;
        $attendance_stmt->close();
    }
    $students_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Attendance Marking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1400px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 0 !important;
            padding: 25px 30px;
            border: none;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .section-selector {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin: -20px 20px 20px 20px;
            position: relative;
            z-index: 10;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-select, .form-control {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn {
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: var(--success-gradient);
            border: none;
            color: #333;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(132, 250, 176, 0.3);
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            border-bottom: 3px solid #dee2e6;
            color: #495057;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 15px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 15px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .time-input {
            width: 140px;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .time-input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge {
            padding: 8px 15px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 25px;
            text-transform: capitalize;
        }
        
        .student-row {
            transition: all 0.3s ease;
        }
        
        .student-row:hover {
            background: #f8f9ff;
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .summary-card {
            background: var(--danger-gradient);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            flex: 1;
            min-width: 120px;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }
        
        .btn-outline-primary, .btn-outline-warning, .btn-outline-info {
            border-width: 2px;
            font-weight: 500;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-gradient);
            border-color: transparent;
        }
        
        .btn-outline-warning:hover {
            background: var(--warning-gradient);
            border-color: transparent;
            color: white;
        }
        
        .section-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .section-selector {
                margin: -20px 10px 20px 10px;
                padding: 20px;
            }
            
            .time-input {
                width: 100%;
            }
            
            .action-buttons {
                justify-content: center;
                margin-bottom: 15px;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-attendance.php">Attendance Management</a></li>
                <li class="breadcrumb-item active" aria-current="page">Manual Attendance</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-pen-alt me-2"></i>Manual Attendance Marking
                </h4>
                <span class="section-badge">
                    <i class="far fa-calendar-alt me-2"></i><?php echo date('F j, Y'); ?>
                </span>
            </div>
            
            <div class="card-body p-0">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Section and Date Selection -->
                <div class="section-selector">
                    <form method="GET" action="" class="row g-4">
                        <div class="col-md-5">
                            <label for="section_id" class="form-label">
                                <i class="fas fa-users me-2"></i>Select Section
                            </label>
                            <select class="form-select" id="section_id" name="section_id" required>
                                <option value="">-- Choose Section --</option>
                                <?php 
                                if ($sections_result && $sections_result->num_rows > 0) {
                                    while ($section = $sections_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $section['section_id']; ?>" 
                                        <?php echo $selected_section_id == $section['section_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($section['section_name']) . ' (ID: ' . $section['section_id'] . ')'; ?>
                                    </option>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<option value="" disabled>No sections available</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="attendance_date" class="form-label">
                                <i class="far fa-calendar-alt me-2"></i>Attendance Date
                            </label>
                            <input type="date" class="form-control" id="attendance_date" 
                                   name="attendance_date" value="<?php echo $selected_date; ?>" 
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Load Students
                            </button>
                        </div>
                    </form>
                </div>

                <?php if ($selected_section_id > 0): ?>
                    <?php if (empty($students)): ?>
                        <div class="alert alert-warning m-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No students found in this section. Please add students first.
                        </div>
                    <?php else: ?>
                        <!-- Section Info -->
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?php echo htmlspecialchars($selected_section_name); ?></strong> - 
                            <?php echo count($students); ?> student(s) enrolled
                        </div>

                        <!-- Attendance Form -->
                        <form method="POST" action="" onsubmit="return validateForm()">
                            <input type="hidden" name="section_id" value="<?php echo $selected_section_id; ?>">
                            <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Student ID</th>
                                            <th width="25%">Student Name</th>
                                            <th width="15%">Grade</th>
                                            <th width="15%">Time In</th>
                                            <th width="15%">Time Out</th>
                                            <th width="10%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $index => $student): ?>
                                            <tr class="student-row">
                                                <td><span class="fw-bold"><?php echo $index + 1; ?></span></td>
                                                <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($student['student_id']); ?></span></td>
                                                <td>
                                                    <?php 
                                                    $full_name = htmlspecialchars($student['student_name']);
                                                    if (!empty($student['student_last'])) {
                                                        $full_name .= ' ' . htmlspecialchars($student['student_last']);
                                                    }
                                                    if (!empty($student['student_mi'])) {
                                                        $full_name .= ' ' . htmlspecialchars($student['student_mi']);
                                                    }
                                                    echo $full_name;
                                                    ?>
                                                    <input type="hidden" name="attendance[<?php echo $student['student_id']; ?>][name]" 
                                                           value="<?php echo $full_name; ?>">
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($student['grade'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="time" class="time-input" 
                                                           name="attendance[<?php echo $student['student_id']; ?>][time_in]" 
                                                           value="<?php echo $student['existing_time_in']; ?>"
                                                           step="60">
                                                </td>
                                                <td>
                                                    <input type="time" class="time-input" 
                                                           name="attendance[<?php echo $student['student_id']; ?>][time_out]" 
                                                           value="<?php echo $student['existing_time_out']; ?>"
                                                           step="60">
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status = '';
                                                    $badge_class = '';
                                                    
                                                    if (!empty($student['existing_time_in']) && !empty($student['existing_time_out'])) {
                                                        $status = '<i class="fas fa-check-circle me-1"></i>Complete';
                                                        $badge_class = 'bg-success';
                                                    } elseif (!empty($student['existing_time_in'])) {
                                                        $status = '<i class="fas fa-sign-in-alt me-1"></i>Present';
                                                        $badge_class = 'bg-warning text-dark';
                                                    } elseif (!empty($student['existing_time_out'])) {
                                                        $status = '<i class="fas fa-sign-out-alt me-1"></i>Timed Out';
                                                        $badge_class = 'bg-info';
                                                    } else {
                                                        $status = '<i class="fas fa-clock me-1"></i>Not Timed';
                                                        $badge_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-4 px-4 pb-4">
                                <div class="col-md-6">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-outline-primary" onclick="setCurrentTimeForAll()">
                                            <i class="fas fa-clock me-2"></i>Set Current Time
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="clearAllTimes()">
                                            <i class="fas fa-eraser me-2"></i>Clear All
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="setDefaultTime()">
                                            <i class="fas fa-clock me-2"></i>Set 8AM/5PM
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" name="mark_attendance" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save Attendance
                                    </button>
                                    <a href="manage_attendance.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Section -->
                        <div class="summary-card">
                            <h6 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Attendance Summary</h6>
                            <div class="summary-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo count($students); ?></div>
                                    <div class="stat-label">Total Students</div>
                                </div>
                                <div class="stat-item">
                                    <?php 
                                    $timed_in = array_filter($students, function($s) {
                                        return !empty($s['existing_time_in']);
                                    });
                                    ?>
                                    <div class="stat-value"><?php echo count($timed_in); ?></div>
                                    <div class="stat-label">Present</div>
                                </div>
                                <div class="stat-item">
                                    <?php 
                                    $timed_out = array_filter($students, function($s) {
                                        return !empty($s['existing_time_out']);
                                    });
                                    ?>
                                    <div class="stat-value"><?php echo count($timed_out); ?></div>
                                    <div class="stat-label">Timed Out</div>
                                </div>
                                <div class="stat-item">
                                    <?php 
                                    $complete = array_filter($students, function($s) {
                                        return !empty($s['existing_time_in']) && !empty($s['existing_time_out']);
                                    });
                                    ?>
                                    <div class="stat-value"><?php echo count($complete); ?></div>
                                    <div class="stat-label">Complete</div>
                                </div>
                                <div class="stat-item">
                                    <?php 
                                    $absent = count($students) - count($timed_in);
                                    ?>
                                    <div class="stat-value"><?php echo $absent; ?></div>
                                    <div class="stat-label">Absent</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Please select a section to view students</h5>
                        <p class="text-muted">Choose a section from the dropdown above to start marking attendance</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to get current time in HH:MM format
        function getCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return hours + ':' + minutes;
        }

        // Function to set current time for all time inputs
        function setCurrentTimeForAll() {
            const currentTime = getCurrentTime();
            const timeInputs = document.querySelectorAll('input[name$="[time_in]"]');
            timeInputs.forEach(input => {
                if (!input.value) {
                    input.value = currentTime;
                }
            });
            
            // Optional: Show feedback
            showToast('Current time set for all empty time-in fields');
        }

        // Function to clear all time inputs
        function clearAllTimes() {
            if (confirm('Are you sure you want to clear all time inputs?')) {
                const timeInputs = document.querySelectorAll('input[type="time"]');
                timeInputs.forEach(input => {
                    input.value = '';
                });
                showToast('All time inputs cleared');
            }
        }

        // Function to set default time (8:00 AM for time in, 5:00 PM for time out)
        function setDefaultTime() {
            const timeInInputs = document.querySelectorAll('input[name$="[time_in]"]');
            const timeOutInputs = document.querySelectorAll('input[name$="[time_out]"]');
            
            timeInInputs.forEach(input => {
                if (!input.value) {
                    input.value = '08:00';
                }
            });
            
            timeOutInputs.forEach(input => {
                if (!input.value) {
                    input.value = '17:00';
                }
            });
            
            showToast('Default times set (8:00 AM / 5:00 PM)');
        }

        // Simple toast notification
        function showToast(message) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'alert alert-success position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = message;
            document.body.appendChild(toast);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Form validation
        function validateForm() {
            const timeInputs = document.querySelectorAll('input[type="time"]');
            let hasValues = false;
            
            timeInputs.forEach(input => {
                if (input.value) {
                    hasValues = true;
                }
            });
            
            if (!hasValues) {
                alert('Please set at least one time in or time out before saving.');
                return false;
            }
            
            return confirm('Are you sure you want to save these attendance records?');
        }

        // Auto-submit when section or date changes
        document.getElementById('section_id').addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });

        document.getElementById('attendance_date').addEventListener('change', function() {
            if (document.getElementById('section_id').value) {
                this.form.submit();
            }
        });

        // Add keyboard shortcut (Ctrl+Shift+S) to save
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                document.querySelector('button[name="mark_attendance"]').click();
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>