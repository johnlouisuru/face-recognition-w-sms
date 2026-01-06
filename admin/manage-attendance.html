<!-- ============================================ -->
<!-- manage-attendance.html - Manage Attendance Records -->
<!-- ============================================ -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance Records</title>
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
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 2px solid #dee2e6;
        }
        .nav a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .content {
            padding: 40px;
        }
        .btn {
            padding: 15px 40px;
            font-size: 1.1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px 5px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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
        }
        .attendance-table tr:hover {
            background: #f8f9fa;
        }
        .action-btn {
            padding: 8px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .action-btn.edit {
            background: #17a2b8;
            color: white;
        }
        .action-btn.edit:hover {
            background: #138496;
        }
        .action-btn.delete {
            background: #dc3545;
            color: white;
        }
        .action-btn.delete:hover {
            background: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        input[readonly] {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Manage Attendance Records (TODAY)</h1>
            <p>Edit or delete attendance entries</p>
        </div>

        <div class="nav">
            <a href="index.html">← Back to Dashboard</a>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="loadAttendance()">🔄 Refresh List</button>
            </div>
            <div id="attendanceContainer">
                <p class="status info">Click "Refresh List" to load attendance records</p>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Attendance Record</h2>
            <input type="hidden" id="editAttendanceId">
            <div class="form-group">
                <label>Student ID:</label>
                <input type="text" id="editStudentId" readonly>
            </div>
            <div class="form-group">
                <label>Student Name:</label>
                <input type="text" id="editStudentName" readonly>
            </div>
            <div class="form-group">
                <label>Time In</label>
                <input type="datetime-local" id="editTimestamp">
            </div>
            <div class="form-group">
                <label>Time Out</label>
                <input type="datetime-local" id="editTimeOut">
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-success" onclick="updateAttendance()">💾 Save Changes</button>
                <button class="btn btn-danger" onclick="closeModal()">❌ Cancel</button>
            </div>
            <div id="editStatus"></div>
        </div>
    </div>

    <script>
        async function loadAttendance() {
            try {
                const response = await fetch('get_attendance.php');
                const records = await response.json();

                let html = '<table class="attendance-table"><thead><tr><th>ID</th><th>Student ID</th><th>Name</th><th>Grade</th><th>Time In</th><th>Time Out</th><th>Actions</th></tr></thead><tbody>';

                if (records.length === 0) {
                    html = '<p class="status info">No attendance records found</p>';
                } else {
                    records.forEach(record => {
                        let time_in = "-";
                        let time_out_date = "-";
                        let time_out_time = "-";
                        if(record.time_in !== null){
                            time_in = new Date(record.time_in);
                        }
                        if(record.time_out !== null){
                            time_out_date = new Date(record.time_out).toLocaleDateString();
                            time_out_time = new Date(record.time_out).toLocaleTimeString();
                        }
                        
                        html += `<tr>
                            <td>${record.attendance_id}</td>
                            <td>${record.student_id}</td>
                            <td>${record.student_name} ${record.student_mi} ${record.student_last}</td>
                            <td>${record.student_grade}</td>
                            <td>${time_in.toLocaleDateString()} ${time_in.toLocaleTimeString()}</td>
                            <td>${time_out_date} ${time_out_time}</td>
                            <td>
                                <button class="action-btn edit" onclick='editAttendance(${JSON.stringify(record)})'>✏️ Edit</button>
                                <button class="action-btn delete" onclick="deleteAttendance(${record.attendance_id}, '${record.student_name}')">🗑️ Delete</button>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                }

                document.getElementById('attendanceContainer').innerHTML = html;
            } catch (err) {
                document.getElementById('attendanceContainer').innerHTML = 
                    '<p class="status error">Error loading records: ' + err.message + '</p>';
            }
        }
        function format_the_date(date_to_be_formatted){
            const date = new Date(date_to_be_formatted);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const datetime = `${year}-${month}-${day}T${hours}:${minutes}`;
            return datetime;
        }
        function editAttendance(record) {
            //alert(record.attendance_id);
            document.getElementById('editAttendanceId').value = record.attendance_id;
            document.getElementById('editStudentId').value = record.student_id;
            document.getElementById('editStudentName').value = record.student_name;
            
            datetime = format_the_date(record.time_in);
            
            if(record.time_out !== null){
                time_out = format_the_date(record.time_out);
                document.getElementById('editTimeOut').value = time_out;
            }
            
            
            document.getElementById('editTimestamp').value = datetime;
            document.getElementById('editModal').style.display = 'block';
        }

        async function updateAttendance() {
            const id = document.getElementById('editAttendanceId').value;
            const timestamp = document.getElementById('editTimestamp').value;
            const time_out = document.getElementById('editTimeOut').value;
            // console.log(time_out);
            // return;
            if (!timestamp) {
                showStatus('error', 'Please select date and time');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('timestamp', timestamp);
                formData.append('time_out', time_out);

                const response = await fetch('update_attendance.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStatus('success', '✅ Attendance updated successfully!');
                    setTimeout(() => {
                        closeModal();
                        loadAttendance();
                    }, 1500);
                } else {
                    showStatus('error', result.message || 'Update failed');
                }
            } catch (err) {
                showStatus('error', 'Error: ' + err.message);
            }
        }

        async function deleteAttendance(id, studentName) {
            if (!confirm(`Are you sure you want to delete this attendance record for ${studentName}?`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id', id);
                //alert(id);
                //return;

                const response = await fetch('delete_attendance.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Attendance record deleted successfully!');
                    loadAttendance();
                } else {
                    alert('❌ ' + (result.message || 'Delete failed'));
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editStatus').style.display = 'none';
        }

        function showStatus(type, message) {
            const element = document.getElementById('editStatus');
            element.className = `status ${type}`;
            element.textContent = message;
            element.style.display = 'block';
        }

        loadAttendance();
    </script>
</body>
</html>