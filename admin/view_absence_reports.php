<?php
require_once 'db-config/security.php';

// Get date filter
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_reason = isset($_GET['reason']) ? $_GET['reason'] : '';

// Get all available dates for filter
$dates_query = "SELECT DISTINCT attendance_date FROM student_absences ORDER BY attendance_date DESC";
$dates_result = $conn->query($dates_query);

// Get all reasons for filter
$reasons_query = "SELECT DISTINCT reason FROM student_absences WHERE reason != '' ORDER BY reason";
$reasons_result = $conn->query($reasons_query);

// Get daily absences with reasons
$daily_absences_query = "
    SELECT 
        sa.*,
        s.student_name,
        s.student_last,
        s.grade,
        s.section_name
    FROM student_absences sa
    JOIN students s ON sa.student_id = s.student_id
    WHERE sa.attendance_date = ?
    " . ($selected_reason ? "AND sa.reason = ?" : "") . "
    ORDER BY s.grade, s.section_name, s.student_name";

$stmt = $conn->prepare($daily_absences_query);
if ($selected_reason) {
    $stmt->bind_param("ss", $selected_date, $selected_reason);
} else {
    $stmt->bind_param("s", $selected_date);
}
$stmt->execute();
$daily_absences = $stmt->get_result();

// Get statistics for charts
// 1. Overall reason distribution
$reason_distribution_query = "
    SELECT 
        reason,
        COUNT(*) as count,
        COUNT(DISTINCT student_id) as unique_students,
        COUNT(DISTINCT attendance_date) as days_count
    FROM student_absences 
    WHERE reason != '' 
    GROUP BY reason 
    ORDER BY count DESC";
$reason_distribution = $conn->query($reason_distribution_query);

// 2. Monthly trend
$monthly_trend_query = "
    SELECT 
        DATE_FORMAT(attendance_date, '%Y-%m') as month,
        COUNT(*) as total_absences,
        COUNT(DISTINCT student_id) as unique_students,
        COUNT(DISTINCT attendance_date) as school_days
    FROM student_absences 
    GROUP BY DATE_FORMAT(attendance_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12";
$monthly_trend = $conn->query($monthly_trend_query);

// 3. Top 10 frequently absent students
$top_absentees_query = "
    SELECT 
        s.student_id,
        s.student_name,
        s.student_last,
        s.grade,
        s.section_name,
        COUNT(sa.id) as absence_count,
        GROUP_CONCAT(DISTINCT sa.reason SEPARATOR ', ') as reasons
    FROM students s
    JOIN student_absences sa ON s.student_id = sa.student_id
    GROUP BY s.student_id
    ORDER BY absence_count DESC
    LIMIT 10";
$top_absentees = $conn->query($top_absentees_query);

// 4. Grade level distribution
$grade_distribution_query = "
    SELECT 
        s.grade,
        COUNT(sa.id) as absence_count,
        COUNT(DISTINCT s.student_id) as total_students
    FROM students s
    JOIN student_absences sa ON s.student_id = sa.student_id
    GROUP BY s.grade
    ORDER BY s.grade";
$grade_distribution = $conn->query($grade_distribution_query);

// 5. Weekly trend (last 7 days)
$weekly_trend_query = "
    SELECT 
        attendance_date,
        COUNT(*) as absence_count
    FROM student_absences 
    WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY attendance_date
    ORDER BY attendance_date";
$weekly_trend = $conn->query($weekly_trend_query);

// Calculate summary statistics
$total_absences_query = "SELECT COUNT(*) as total FROM student_absences";
$total_absences = $conn->query($total_absences_query)->fetch_assoc()['total'];

$unique_students_query = "SELECT COUNT(DISTINCT student_id) as total FROM student_absences";
$unique_students = $conn->query($unique_students_query)->fetch_assoc()['total'];

$avg_daily_query = "SELECT AVG(daily_count) as avg FROM (SELECT COUNT(*) as daily_count FROM student_absences GROUP BY attendance_date) as daily";
$avg_daily = $conn->query($avg_daily_query)->fetch_assoc()['avg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absence Reports & Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .header h1 span {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 16px;
            margin-left: 10px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }

        .filter-group select, .filter-group input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }

        .filter-group button {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .filter-group button:hover {
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card.info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .stat-info h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-info .number {
            font-size: 32px;
            font-weight: 700;
        }

        .stat-icon {
            font-size: 48px;
            opacity: 0.8;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .chart-container h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 30px;
            background: white;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .reason-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }

        .remarks-cell {
            max-width: 250px;
            word-wrap: break-word;
            white-space: normal;
        }

        .export-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .export-btn:hover {
            transform: translateY(-2px);
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select, .filter-group input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📈 Absence Reports & Analytics 
                <span><?php echo date('F Y', strtotime($selected_date)); ?></span>
            </h1>
            <button class="export-btn" onclick="exportReport()">
                📥 Export Full Report
            </button>
        </div>

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Absences</h3>
                    <div class="number"><?php echo number_format($total_absences); ?></div>
                    <small>All time records</small>
                </div>
                <div class="stat-icon">📊</div>
            </div>
            <div class="stat-card info">
                <div class="stat-info">
                    <h3>Affected Students</h3>
                    <div class="number"><?php echo number_format($unique_students); ?></div>
                    <small>Unique students</small>
                </div>
                <div class="stat-icon">👥</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-info">
                    <h3>Daily Average</h3>
                    <div class="number"><?php echo round($avg_daily, 1); ?></div>
                    <small>Absences per day</small>
                </div>
                <div class="stat-icon">📅</div>
            </div>
            <div class="stat-card success">
                <div class="stat-info">
                    <h3>Today's Absences</h3>
                    <div class="number"><?php echo $daily_absences->num_rows; ?></div>
                    <small><?php echo date('F d, Y', strtotime($selected_date)); ?></small>
                </div>
                <div class="stat-icon">📋</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="filter-group">
                <label>📅 Select Date</label>
                <select onchange="window.location.href='?date='+this.value">
                    <?php 
                    $dates_result->data_seek(0);
                    while($date_row = $dates_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $date_row['attendance_date']; ?>" 
                            <?php echo ($selected_date == $date_row['attendance_date']) ? 'selected' : ''; ?>>
                            <?php echo date('F d, Y', strtotime($date_row['attendance_date'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>🎯 Filter by Reason</label>
                <select onchange="window.location.href='?date=<?php echo $selected_date; ?>&reason='+this.value">
                    <option value="">All Reasons</option>
                    <?php 
                    $reasons_result->data_seek(0);
                    while($reason_row = $reasons_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo urlencode($reason_row['reason']); ?>" 
                            <?php echo ($selected_reason == $reason_row['reason']) ? 'selected' : ''; ?>>
                            <?php echo $reason_row['reason']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>📆 Month</label>
                <input type="month" value="<?php echo $selected_month; ?>" 
                       onchange="window.location.href='?month='+this.value">
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Reason Distribution Chart -->
            <div class="chart-container">
                <h3>🎯 Reasons for Absenteeism</h3>
                <div class="chart-wrapper">
                    <canvas id="reasonChart"></canvas>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="chart-container">
                <h3>📊 Monthly Absence Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Grade Distribution Chart -->
            <div class="chart-container">
                <h3>🏫 Absences by Grade Level</h3>
                <div class="chart-wrapper">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>

            <!-- Weekly Trend Chart -->
            <div class="chart-container">
                <h3>📅 Last 7 Days Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Absentees -->
        <div class="chart-container" style="margin-bottom: 30px;">
            <h3>⚠️ Top 10 Frequently Absent Students</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Total Absences</th>
                            <th>Common Reasons</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $top_absentees->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_name'] . ' ' . ($student['student_last'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($student['grade']); ?></td>
                            <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                            <td><span style="background: #ff4757; color: white; padding: 5px 10px; border-radius: 5px;"><?php echo $student['absence_count']; ?> days</span></td>
                            <td><?php echo htmlspecialchars(substr($student['reasons'], 0, 50)) . '...'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Absences Details -->
        <div class="chart-container">
            <h3>📋 Absences for <?php echo date('F d, Y', strtotime($selected_date)); ?></h3>
            <?php if ($selected_reason): ?>
                <p style="margin-bottom: 15px; color: #667eea;">
                    Filtered by: <strong><?php echo htmlspecialchars($selected_reason); ?></strong>
                </p>
            <?php endif; ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Reason for Absence</th>
                            <th>Remarks</th>
                            <th>Recorded Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daily_absences->num_rows > 0): ?>
                            <?php while($absence = $daily_absences->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($absence['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($absence['student_name'] . ' ' . ($absence['student_last'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($absence['grade']); ?></td>
                                <td><?php echo htmlspecialchars($absence['section_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="reason-badge">
                                        <?php echo htmlspecialchars($absence['reason']); ?>
                                    </span>
                                </td>
                                <td class="remarks-cell">
                                    <?php echo htmlspecialchars($absence['remarks'] ?? 'No remarks'); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($absence['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <span style="font-size: 48px; display: block; margin-bottom: 20px;">🎉</span>
                                    No absences recorded for this date!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        <?php
        // Reason distribution data
        $reason_labels = [];
        $reason_counts = [];
        $reason_colors = [];
        $reasons_result->data_seek(0);
        while($row = $reason_distribution->fetch_assoc()) {
            $reason_labels[] = $row['reason'];
            $reason_counts[] = $row['count'];
            // Generate random colors
            $reason_colors[] = 'hsl(' . rand(0, 360) . ', 70%, 60%)';
        }
        
        // Monthly trend data
        $monthly_labels = [];
        $monthly_counts = [];
        $monthly_unique = [];
        $monthly_trend->data_seek(0);
        while($row = $monthly_trend->fetch_assoc()) {
            $monthly_labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $monthly_counts[] = $row['total_absences'];
            $monthly_unique[] = $row['unique_students'];
        }
        
        // Grade distribution data
        $grade_labels = [];
        $grade_counts = [];
        $grade_distribution->data_seek(0);
        while($row = $grade_distribution->fetch_assoc()) {
            $grade_labels[] = 'Grade ' . $row['grade'];
            $grade_counts[] = $row['absence_count'];
        }
        
        // Weekly trend data
        $weekly_labels = [];
        $weekly_counts = [];
        $weekly_trend->data_seek(0);
        while($row = $weekly_trend->fetch_assoc()) {
            $weekly_labels[] = date('D, M d', strtotime($row['attendance_date']));
            $weekly_counts[] = $row['absence_count'];
        }
        ?>

        // 1. Reason Distribution Chart (Pie)
        new Chart(document.getElementById('reasonChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($reason_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($reason_counts); ?>,
                    backgroundColor: <?php echo json_encode($reason_colors); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 11 }
                        }
                    }
                }
            }
        });

        // 2. Monthly Trend Chart (Line)
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_reverse($monthly_labels)); ?>,
                datasets: [{
                    label: 'Total Absences',
                    data: <?php echo json_encode(array_reverse($monthly_counts)); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 3. Grade Distribution Chart (Bar)
        new Chart(document.getElementById('gradeChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($grade_labels); ?>,
                datasets: [{
                    label: 'Number of Absences',
                    data: <?php echo json_encode($grade_counts); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 4. Weekly Trend Chart (Bar)
        new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($weekly_labels); ?>,
                datasets: [{
                    label: 'Absences',
                    data: <?php echo json_encode($weekly_counts); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Export function
        function exportReport() {
            const date = '<?php echo $selected_date; ?>';
            window.location.href = 'export_full_report.php?date=' + date;
        }

        // Tab functionality
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            const tabButtons = document.querySelectorAll('.tab');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>