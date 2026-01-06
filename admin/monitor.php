<!-- monitor.php - Real-Time Section Attendance Monitor -->
<?php
session_start();

// Check if section is set in session
if (!isset($_POST['section_id']) || !isset($_POST['section_name'])) {
    // Redirect to section selection page or set default
    header('Location: select-section.php');
    exit;
}

$section_id = $_POST['section_id'];
$section_name = $_POST['section_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Monitor - <?php echo htmlspecialchars($section_name); ?></title>
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
            max-width: 1600px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header .section-info {
            font-size: 1.5em;
            color: #666;
            margin-bottom: 10px;
        }
        .header .last-update {
            font-size: 0.9em;
            color: #999;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.total {
            border-left: 5px solid #667eea;
        }
        .stat-card.present {
            border-left: 5px solid #28a745;
        }
        .stat-card.absent {
            border-left: 5px solid #dc3545;
        }
        .stat-card h3 {
            font-size: 1em;
            color: #666;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card .number {
            font-size: 4em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-card.total .number {
            color: #667eea;
        }
        .stat-card.present .number {
            color: #28a745;
        }
        .stat-card.absent .number {
            color: #dc3545;
        }
        .stat-card .percentage {
            font-size: 1.2em;
            color: #999;
        }
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .content-card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .refresh-indicator {
            font-size: 0.8em;
            color: #28a745;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .refresh-indicator.loading {
            color: #ffc107;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .attendance-table th,
        .attendance-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .attendance-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .attendance-table tbody tr {
            transition: background 0.3s;
        }
        .attendance-table tbody tr:hover {
            background: #f8f9fa;
        }
        .attendance-table tbody tr.new-entry {
            animation: highlight 2s;
        }
        @keyframes highlight {
            0% { background: #d4edda; }
            100% { background: transparent; }
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            display: inline-block;
        }
        .status-present {
            background: #d4edda;
            color: #155724;
        }
        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }
        .status-incomplete {
            background: #fff3cd;
            color: #856404;
        }
        .status-complete {
            background: #d1ecf1;
            color: #0c5460;
        }
        .time-badge {
            font-weight: 600;
            color: #667eea;
        }
        .duration {
            font-weight: 600;
            color: #28a745;
        }
        .settings-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }
        .settings-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Real-Time Attendance Monitor</h1>
            <div class="section-info">
                📚 Section: <strong><?php echo htmlspecialchars($section_name); ?></strong>
            </div>
            <div class="last-update">
                Last updated: <span id="lastUpdate">Loading...</span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <h3>Total Students</h3>
                <div class="number" id="totalStudents">0</div>
                <div class="percentage">In this section</div>
            </div>
            <div class="stat-card present">
                <h3>Present Today</h3>
                <div class="number" id="presentToday">0</div>
                <div class="percentage" id="presentPercentage">0%</div>
            </div>
            <div class="stat-card absent">
                <h3>Absent Today</h3>
                <div class="number" id="absentToday">0</div>
                <div class="percentage" id="absentPercentage">0%</div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="content-card">
            <h2>
                Today's Attendance
                <span class="refresh-indicator" id="refreshIndicator">
                    <span class="spinner">🔄</span> Auto-refreshing...
                </span>
            </h2>
            <div id="attendanceTable"></div>
        </div>
    </div>

    <button class="settings-btn" onclick="window.location.href='select-section.php'">
        ⚙️ Change Section
    </button>

    <script>
        const REFRESH_INTERVAL = 5000; // 5 seconds
        const sectionId = '<?php echo addslashes($section_id); ?>';
        const sectionName = '<?php echo addslashes($section_name); ?>';
        let previousAttendanceIds = new Set();

        // Load data immediately
        loadData();

        // Set up auto-refresh
        setInterval(loadData, REFRESH_INTERVAL);

        async function loadData() {
            try {
                const indicator = document.getElementById('refreshIndicator');
                indicator.classList.add('loading');

                // Fetch data from PHP endpoint
                const response = await fetch('get_section_attendance.php?section_id=' + encodeURIComponent(sectionId));
                const data = await response.json();

                updateStatistics(data);
                displayAttendanceTable(data);
                updateLastRefreshTime();

                indicator.classList.remove('loading');
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateStatistics(data) {
            const totalStudents = data.total_students;
            const presentToday = data.present_today;
            const absentToday = totalStudents - presentToday;
            const presentPercentage = totalStudents > 0 ? ((presentToday / totalStudents) * 100).toFixed(1) : 0;
            const absentPercentage = totalStudents > 0 ? ((absentToday / totalStudents) * 100).toFixed(1) : 0;

            document.getElementById('totalStudents').textContent = totalStudents;
            document.getElementById('presentToday').textContent = presentToday;
            document.getElementById('absentToday').textContent = absentToday;
            document.getElementById('presentPercentage').textContent = presentPercentage + '%';
            document.getElementById('absentPercentage').textContent = absentPercentage + '%';
        }

        function displayAttendanceTable(data) {
            const students = data.students;
            const currentAttendanceIds = new Set();

            let html = `<table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Grade</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>`;

            students.forEach(student => {
                const isNewEntry = student.attendance_id && !previousAttendanceIds.has(student.attendance_id);
                const rowClass = isNewEntry ? 'new-entry' : '';
                
                if (student.attendance_id) {
                    currentAttendanceIds.add(student.attendance_id);
                }

                const timeIn = student.time_in ? formatTime(student.time_in) : '-';
                const timeOut = student.time_out ? formatTime(student.time_out) : '-';
                const duration = calculateDuration(student.time_in, student.time_out);
                const status = getStatus(student);

                html += `<tr class="${rowClass}">
                    <td><strong>${student.student_id}</strong></td>
                    <td>${student.student_name}</td>
                    <td>${student.grade}</td>
                    <td><span class="time-badge">${timeIn}</span></td>
                    <td><span class="time-badge">${timeOut}</span></td>
                    <td><span class="duration">${duration}</span></td>
                    <td><span class="status-badge ${status.class}">${status.text}</span></td>
                </tr>`;
            });

            html += `</tbody></table>`;

            document.getElementById('attendanceTable').innerHTML = html;
            previousAttendanceIds = currentAttendanceIds;
        }

        function formatTime(timeString) {
            if (!timeString) return '-';
            const date = new Date(timeString);
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
        }

        function calculateDuration(timeIn, timeOut) {
            if (!timeIn || !timeOut) return '-';

            const start = new Date(timeIn);
            const end = new Date(timeOut);
            const diff = end - start;

            if (diff < 0) return '-';

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            return `${hours}h ${minutes}m`;
        }

        function getStatus(student) {
            if (student.time_in && student.time_out) {
                return { text: 'Complete ✓', class: 'status-complete' };
            } else if (student.time_in) {
                return { text: 'Present ⏱', class: 'status-incomplete' };
            } else {
                return { text: 'Absent ✗', class: 'status-absent' };
            }
        }

        function updateLastRefreshTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            document.getElementById('lastUpdate').textContent = timeString;
        }
    </script>
</body>
</html>