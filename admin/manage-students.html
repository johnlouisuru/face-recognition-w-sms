<!-- ============================================ -->
<!-- manage-students.html - Manage Students -->
<!-- ============================================ -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
        }
        select:focus {
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
            <h1>👥 Manage Students</h1>
            <p>Edit or delete student records</p>
        </div>

        <div class="nav">
            <a href="index.html">← Back to Dashboard</a>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="loadStudents()">🔄 Refresh List</button>
            </div>
            <div id="studentsContainer">
                <p class="status info">Click "Refresh List" to load students</p>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Student</h2>
            <input type="hidden" id="editStudentDbId">
            <div class="form-group">
                <label>Student ID:</label>
                <input type="text" id="editStudentId" readonly>
            </div>
            <div class="form-group">
                <label>Student First Name:</label>
                <input type="text" id="editStudentName">
            </div>
            <div class="form-group">
                <label>Student Last Name:</label>
                <input type="text" id="editStudentLast">
            </div>
            <div class="form-group">
                <label>Student Middle Name:</label>
                <input type="text" id="editStudentMiddle">
            </div>
            <div class="form-group">
                <label for="editStudentGrade">Grade/Year: *</label>
                <select id="editStudentGrade" required>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>
            <!-- <div class="form-group">
                <label>Grade/Year:</label>
                <input type="text" id="editStudentGrade">
            </div> -->
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-success" onclick="updateStudent()">💾 Save Changes</button>
                <button class="btn btn-danger" onclick="closeModal()">❌ Cancel</button>
            </div>
            <div id="editStatus"></div>
        </div>
    </div>

    <script>
        async function loadStudents() {
            try {
                const response = await fetch('get_students.php');
                const students = await response.json();

                let html = '<table class="attendance-table"><thead><tr><th>Student ID</th><th>FirstName</th><th>LastName</th><th>MiddleName</th><th>Grade</th><th>Registered</th><th>Actions</th></tr></thead><tbody>';

                if (students.length === 0) {
                    html = '<p class="status info">No students registered yet</p>';
                } else {
                    students.forEach(student => {
                        const date = new Date(student.created_at);
                        html += `<tr>
                            <td>${student.student_id}</td>
                            <td>${student.student_name}</td>
                            <td>${student.student_last}</td>
                            <td>${student.student_mi}</td>
                            <td>${student.grade}</td>
                            <td>${date.toLocaleDateString()} ${date.toLocaleTimeString()}</td>
                            <td>
                                <button class="action-btn edit" onclick='editStudent(${JSON.stringify(student)})'>✏️ Edit</button>
                                <button class="action-btn delete" onclick="deleteStudent('${student.student_id}', '${student.student_name}')">🗑️ Delete</button>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                }

                document.getElementById('studentsContainer').innerHTML = html;
            } catch (err) {
                document.getElementById('studentsContainer').innerHTML = 
                    '<p class="status error">Error loading students: ' + err.message + '</p>';
            }
        }

        function editStudent(student) {
            document.getElementById('editStudentDbId').value = student.id;
            document.getElementById('editStudentId').value = student.student_id;
            document.getElementById('editStudentName').value = student.student_name;
            document.getElementById('editStudentLast').value = student.student_last;
            document.getElementById('editStudentMiddle').value = student.student_mi;
            document.getElementById('editStudentGrade').value = student.grade;
            document.getElementById('editModal').style.display = 'block';
        }

        async function updateStudent() {
            const id = document.getElementById('editStudentDbId').value;
            const name = document.getElementById('editStudentName').value.trim();
            const lastname = document.getElementById('editStudentLast').value.trim();
            const middlename = document.getElementById('editStudentMiddle').value.trim();
            const grade = document.getElementById('editStudentGrade').value.trim();

            if (!name || !grade) {
                showStatus('error', 'Please fill in all fields');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('student_name', name);
                formData.append('student_last', lastname);
                formData.append('student_middle', middlename);
                formData.append('grade', grade);

                const response = await fetch('update_student.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showStatus('success', '✅ Student updated successfully!');
                    setTimeout(() => {
                        closeModal();
                        loadStudents();
                    }, 1500);
                } else {
                    showStatus('error', result.message || 'Update failed');
                }
            } catch (err) {
                showStatus('error', 'Error: ' + err.message);
            }
        }

        async function deleteStudent(studentId, studentName) {
            if (!confirm(`Are you sure you want to delete ${studentName}? This will also delete all attendance records for this student.`)) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('student_id', studentId);

                const response = await fetch('delete_student.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Student deleted successfully!');
                    loadStudents();
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
        loadStudents();
    </script>
</body>
</html>