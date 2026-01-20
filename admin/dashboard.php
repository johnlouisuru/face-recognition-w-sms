<?php
require_once 'db-config/security.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-counter {
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card-counter:hover {
            transform: translateY(-5px);
        }
        .attendance-rate {
            font-size: 2rem;
            font-weight: bold;
        }
        .chart-container {
            height: 300px;
            position: relative;
        }
        .absentee-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .risk-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-camera-reels"></i> Face Recognition Attendance
            </a>
            <a class="navbar-brand" href="index.php">
                ← <i class="bi bi-speedometer2"></i> Back to Attendance
            </a>
            
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Summary Cards - 3 columns now -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-counter bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">TOTAL STUDENTS</h6>
                                <h2 id="totalStudents" class="attendance-rate">0</h2>
                            </div>
                            <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-counter bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">PRESENT TODAY</h6>
                                <h2 id="presentToday" class="attendance-rate">0</h2>
                            </div>
                            <i class="bi bi-check-circle-fill" style="font-size: 3rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-counter bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">ATTENDANCE RATE</h6>
                                <h2 id="attendanceRate" class="attendance-rate">0%</h2>
                            </div>
                            <i class="bi bi-graph-up" style="font-size: 3rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Predictions -->
        <div class="row">
            <!-- Daily Attendance Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check"></i> Attendance Trend (Last 7 Days)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade-wise Distribution -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart-fill"></i> Grade Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gradeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Predictive Analytics Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i> Predictive Analytics: Possible Absentees Tomorrow
                            <button class="btn btn-sm btn-light float-end" onclick="refreshPredictions()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i> 
                            This prediction is based on attendance patterns over the past 7 days. Students with recent absences are more likely to be absent tomorrow.
                        </div>
                        <div class="table-responsive absentee-list">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Grade</th>
                                        <th>Absence Rate (7 Days)</th>
                                        <th>Last Absent</th>
                                        <th>Parent Contact</th>
                                        <th>Risk Level</th>
                                    </tr>
                                </thead>
                                <tbody id="absenteeTable">
                                    <!-- Predictive data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history"></i> Recent Attendance Logs (Last 20 Records)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Grade & Section</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody id="recentAttendance">
                                    <!-- Recent attendance will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>