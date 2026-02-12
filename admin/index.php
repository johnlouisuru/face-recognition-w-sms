<?php
if(file_exists('db-config/security.php')){
    require_once 'db-config/security.php';
} else {
    die('Database configuration file not found.');
}
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
    header('Location: login.php');
    exit;
} 

?>
<!-- index.html - Main Dashboard -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facial Recognition Attendance</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .content {
            padding: 50px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .menu-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }
        .menu-card .icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        .menu-card h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.8em;
        }
        .menu-card p {
            color: #666;
            font-size: 1.1em;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Facial Recognition with SMS Notification</h1>
            <p>AI-Powered Student Attendance Platform</p>
        </div>

        <div class="content">
            <h2 style="color: #333; margin-bottom: 20px;">Select an Option</h2>
            
            <div class="menu-grid">
            <a href="dashboard.php" class="menu-card">
                    <div class="icon">🗓️</div>
                    <h2>AI Predictive Analytics</h2>
                    <p>Monitor Predictive Analytics for Absentees</p>
                </a>

                <a href="register.html" class="menu-card">
                    <div class="icon">➕</div>
                    <h2>Register Student</h2>
                    <p>Add new students to the system with facial recognition</p>
                </a>

                <a href="attendance.html" class="menu-card">
                    <div class="icon">✅</div>
                    <h2>Mark Attendance</h2>
                    <p>Automatically record student attendance using face recognition</p>
                </a>

                <a href="view-records.html" class="menu-card">
                    <div class="icon">📊</div>
                    <h2>View Records</h2>
                    <p>View attendance history and reports</p>
                </a>

                <!-- <a href="manage-students.html" class="menu-card">
                    <div class="icon">👥</div>
                    <h2>Manage Students</h2>
                    <p>Edit or delete student records</p>
                </a> -->

                <a href="accounts_management.php" class="menu-card">
                    <div class="icon">👥</div>
                    <h2>Manage Accounts</h2>
                    <p>Edit or delete All Account records</p>
                </a>

                <a href="manage-attendance.html" class="menu-card">
                    <div class="icon">📝</div>
                    <h2>Manage Attendance</h2>
                    <p>Edit or delete attendance records</p>
                </a>

                <a href="monitoring.php" class="menu-card">
                    <div class="icon">📈</div>
                    <h2>Principal/Head Monitoring</h2>
                    <p>Monitor Attendance in Real-time. Dedicated to Authorized View</p>
                </a>
                <a href="absence_tracking.php" class="menu-card">
                    <div class="icon">👁️</div>
                    <h2>Absenteeism Remarks</h2>
                    <p>View all absences reasons/remarks</p>
                </a>

                
            </div>
        </div>
    </div>
</body>
</html>