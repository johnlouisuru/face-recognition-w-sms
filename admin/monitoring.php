<?php
require_once 'db-config/security.php';
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

// Get available grades and sections for filters
$grades_result = $conn->query("SELECT DISTINCT grade FROM students ORDER BY grade");
$sections_result = $conn->query("SELECT DISTINCT section_name FROM students WHERE section_name IS NOT NULL ORDER BY section_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Attendance Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --present-bg: #d4edda;
            --absent-bg: #f8d7da;
            --late-bg: #fff3cd;
            --present-border: #c3e6cb;
            --absent-border: #f5c6cb;
            --late-border: #ffeeba;
        }
        
        .monitoring-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .attendance-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
            height: 100%;
        }
        
        .attendance-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-present {
            background-color: var(--present-bg);
            color: #155724;
            border: 1px solid var(--present-border);
        }
        
        .status-absent {
            background-color: var(--absent-bg);
            color: #721c24;
            border: 1px solid var(--absent-border);
        }
        
        .status-late {
            background-color: var(--late-bg);
            color: #856404;
            border: 1px solid var(--late-border);
        }
        
        .time-badge {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
        
        .student-row {
            border-bottom: 1px solid #eee;
            padding: 15px;
            transition: background-color 0.3s;
        }
        
        .student-row:hover {
            background-color: #f8f9fa;
        }
        
        .student-row.present {
            background-color: rgba(212, 237, 218, 0.3);
        }
        
        .student-row.absent {
            background-color: rgba(248, 215, 218, 0.3);
        }
        
        .student-row.late {
            background-color: rgba(255, 243, 205, 0.3);
        }
        
        .filter-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 10px;
            border-left: 3px solid;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .activity-present {
            border-left-color: #28a745;
        }
        
        .activity-late {
            border-left-color: #ffc107;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        
        .refresh-btn:hover {
            transform: scale(1.05);
            color: white;
        }
        
        .auto-refresh-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Animation for new entries */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .new-entry {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Pulse animation for auto-refresh */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 1s infinite;
        }
        
        .last-updated {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="monitoring-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-white mb-0">
                            <i class="bi bi-camera-video"></i> Real-Time Attendance Monitoring
                        </h1>
                        <p class="text-white-50 mb-0">Live tracking of student attendance</p>
                    </div>
                    <div>
                        <a href="dashboard.php" class="btn btn-light me-2">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="accounts_management.php" class="btn btn-outline-light">
                            <i class="bi bi-people"></i> Manage Accounts
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Stats -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Filter by Grade</label>
                            <select class="form-select" id="filterGrade" onchange="applyFilters()">
                                <option value="">All Grades</option>
                                <?php while($grade = $grades_result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($grade['grade']); ?>">
                                        Grade <?php echo htmlspecialchars($grade['grade']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Filter by Section</label>
                            <select class="form-select" id="filterSection" onchange="applyFilters()">
                                <option value="">All Sections</option>
                                <?php 
                                $sections_result->data_seek(0); // Reset pointer
                                while($section = $sections_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo htmlspecialchars($section['section_name']); ?>">
                                        <?php echo htmlspecialchars($section['section_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Filter by Status</label>
                            <select class="form-select" id="filterStatus" onchange="applyFilters()">
                                <option value="">All Students</option>
                                <option value="present">Present Only</option>
                                <option value="absent">Absent Only</option>
                                <option value="late">Late Only</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Filter by Time</label>
                            <select class="form-select" id="filterTime" onchange="applyFilters()">
                                <option value="all">Time In & Out</option>
                                <option value="time_in">Time In Only</option>
                                <option value="time_out">Time Out Only</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="auto-refresh-toggle">
                                    <span>Auto-refresh:</span>
                                    <label class="switch">
                                        <input type="checkbox" id="autoRefreshToggle" checked>
                                        <span class="slider"></span>
                                    </label>
                                    <select class="form-select form-select-sm w-auto" id="refreshInterval">
                                        <option value="5000">5 seconds</option>
                                        <option value="10000" selected>10 seconds</option>
                                        <option value="30000">30 seconds</option>
                                        <option value="60000">1 minute</option>
                                    </select>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <span class="last-updated" id="lastUpdated">
                                        Last updated: <span id="lastUpdatedTime">--:--:--</span>
                                    </span>
                                    <button class="btn refresh-btn" onclick="loadMonitoringData()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-primary" id="totalStudents">0</div>
                    <div>Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-success" id="presentCount">0</div>
                    <div>Present Today</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-warning" id="lateCount">0</div>
                    <div>Late Today</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number text-danger" id="absentCount">0</div>
                    <div>Absent Today</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Attendance Grid -->
            <div class="col-lg-8">
                <div class="attendance-card p-2">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3-gap"></i> Student Attendance
                            <span class="badge bg-light text-dark ms-2" id="attendanceCount">0 students</span>
                        </h5>
                        <hr />
                    </div>
                    
                    <div class="card-body">
                        <div id="attendanceGrid">
                            <!-- Attendance grid will be loaded here -->
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <h4>Loading attendance data...</h4>
                                <p>Please wait while we fetch the latest attendance information.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="col-lg-4">
                <div class="attendance-card p-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Recent Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="recent-activity" id="recentActivity">
                            <!-- Recent activity will be loaded here -->
                            <div class="activity-item">
                                <div class="text-center text-muted">
                                    <i class="bi bi-hourglass-split"></i>
                                    <p>Loading recent activity...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <!-- Section-wise Summary -->
                <div class="attendance-card mt-4 p-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart"></i> Section Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="sectionSummary">
                            <!-- Section summary will be loaded here -->
                            <div class="text-center text-muted">
                                <i class="bi bi-pie-chart"></i>
                                <p>Loading section data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio for notifications -->
    <audio id="notificationSound" preload="auto">
        <source src="https://assets.mixkit.co/sfx/preview/mixkit-correct-answer-tone-2870.mp3" type="audio/mpeg">
    </audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/monitoring.js"></script>
</body>
</html>