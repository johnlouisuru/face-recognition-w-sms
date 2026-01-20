let teachersTable, studentsTable;
let currentDeleteId = null;
let currentDeleteType = null;
let currentFaceData = null;

$(document).ready(function() {
    loadTeachers();
    loadStudents();
    initializeDataTables();
});

function initializeDataTables() {
    // Initialize Teachers Table
    teachersTable = $('#teachersTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        responsive: true,
        language: {
            emptyTable: "No teacher accounts found"
        }
    });

    // Initialize Students Table
    studentsTable = $('#studentsTable').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        responsive: true,
        language: {
            emptyTable: "No student accounts found"
        }
    });
}

function loadTeachers() {
    $.ajax({
        url: 'api/get_teachers.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateTeachersTable(response.teachers);
            }
        },
        error: function() {
            console.error('Failed to load teachers');
        }
    });
}

function loadStudents() {
    $.ajax({
        url: 'api/get_students.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStudentsTable(response.students);
            }
        },
        error: function() {
            console.error('Failed to load students');
        }
    });
}

function updateTeachersTable(teachers) {
    teachersTable.clear();
    
    teachers.forEach(teacher => {
        teachersTable.row.add([
            teacher.id,
            teacher.fullname,
            teacher.email,
            new Date(teacher.created_at).toLocaleDateString(),
            `
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-warning" onclick="editTeacher(${teacher.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmDelete(${teacher.id}, 'teacher')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `
        ]);
    });
    
    teachersTable.draw();
}

function updateStudentsTable(students) {
    studentsTable.clear();
    
    students.forEach(student => {
        // Use the display_name from the query or construct it
        let displayName = student.display_name || 
                         `${student.student_name} ${student.student_mi ? student.student_mi + '.' : ''} ${student.student_last}`;
        
        studentsTable.row.add([
            student.student_id,
            displayName,
            student.grade,
            student.section_name || 'N/A',
            student.parent_name || 'N/A',
            new Date(student.created_at).toLocaleDateString(),
            `
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-warning" onclick="editStudent(${student.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger" onclick="confirmDelete(${student.id}, 'student')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `
        ]);
    });
    
    studentsTable.draw();
}

function showAddTeacherModal() {
    $('#teacherForm')[0].reset();
    $('#teacher_id').val('');
    $('#teacherModalTitle').text('Add Teacher');
    $('#password').attr('required', true);
    $('#confirm_password').attr('required', true);
    $('#passwordRequired').show();
    $('#confirmRequired').show();
    $('#passwordHelp').text('Password must be at least 6 characters long.');
    new bootstrap.Modal('#teacherModal').show();
}

function editTeacher(id) {
    $.ajax({
        url: 'api/get_teacher.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const teacher = response.teacher;
                $('#teacher_id').val(teacher.id);
                $('#fullname').val(teacher.fullname);
                $('#email').val(teacher.email);
                $('#password').attr('required', false);
                $('#confirm_password').attr('required', false);
                $('#passwordRequired').hide();
                $('#confirmRequired').hide();
                $('#passwordHelp').text('Leave blank to keep current password.');
                $('#teacherModalTitle').text('Edit Teacher: ' + teacher.fullname);
                new bootstrap.Modal('#teacherModal').show();
            }
        }
    });
}

function saveTeacher() {
    // Get form values
    const teacherId = $('#teacher_id').val() || 0;
    const fullname = $('#fullname').val().trim();
    const email = $('#email').val().trim();
    const password = $('#password').val();
    const confirmPassword = $('#confirm_password').val();
    
    // Validate
    if (!fullname) {
        showNotification('Full name is required', 'danger');
        return;
    }
    
    if (!email) {
        showNotification('Email is required', 'danger');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Please enter a valid email address', 'danger');
        return;
    }
    
    // Password validation for new teachers
    if (teacherId == 0) {
        if (!password) {
            showNotification('Password is required for new teacher', 'danger');
            return;
        }
        if (password.length < 6) {
            showNotification('Password must be at least 6 characters long', 'danger');
            return;
        }
        if (password !== confirmPassword) {
            showNotification('Passwords do not match', 'danger');
            return;
        }
    } else if (password && password.length < 6) {
        // For updates, if password is provided, validate it
        showNotification('Password must be at least 6 characters long', 'danger');
        return;
    }
    
    // Prepare data
    const postData = {
        teacher_id: teacherId,
        fullname: fullname,
        email: email,
        password: password,
        confirm_password: confirmPassword
    };
    
    // Show loading
    const saveBtn = $('#teacherModal').find('.btn-primary');
    const originalText = saveBtn.html();
    saveBtn.html('<span class="spinner-border spinner-border-sm"></span> Saving...');
    saveBtn.prop('disabled', true);
    
    $.ajax({
        url: 'api/save_teacher.php',
        method: 'POST',
        data: postData,
        dataType: 'json',
        success: function(response) {
            saveBtn.html(originalText);
            saveBtn.prop('disabled', false);
            
            if (response.success) {
                showNotification(response.message, 'success');
                $('#teacherModal').modal('hide');
                loadTeachers();
            } else {
                showNotification(response.message, 'danger');
            }
        },
        error: function(xhr, status, error) {
            saveBtn.html(originalText);
            saveBtn.prop('disabled', false);
            showNotification('Failed to save teacher. Please try again.', 'danger');
            console.error('Error:', error);
        }
    });
}



// Student Functions
function showAddStudentModal() {
    $('#studentForm')[0].reset();
    $('#student_db_id').val('');
    $('#profileImagePreview').attr('src', 'https://via.placeholder.com/120x120?text=Upload+Photo');
    $('#studentModalTitle').text('Add Student');
    currentFaceData = null;
    $('#faceDataInfo').html('');
    new bootstrap.Modal('#studentModal').show();
}

function editStudent(id) {
    $.ajax({
        url: 'api/get_student.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const student = response.student;
                $('#student_db_id').val(student.id);
                $('#student_id').val(student.student_id);
                $('#grade').val(student.grade);
                $('#section_name').val(student.section_name);
                $('#student_last').val(student.student_last);
                $('#student_name').val(student.student_name);
                $('#student_mi').val(student.student_mi);
                
                if (student.profile_picture) {
                    $('#profileImagePreview').attr('src', student.profile_picture);
                }
                
                // FIXED: Use parent_fullname and parent_contact from the query
                if (student.parent_fullname && student.parent_contact) {
                    $('#parent_fullname').val(student.parent_fullname);
                    $('#parent_contact').val(student.parent_contact);
                }
                
                $('#studentModalTitle').text('Edit Student');
                new bootstrap.Modal('#studentModal').show();
            }
        }
    });
}

function saveStudent() {
    const formData = new FormData($('#studentForm')[0]);
    
    // Add face data if available
    if (currentFaceData) {
        formData.append('face_descriptor', JSON.stringify(currentFaceData));
    }
    
    $.ajax({
        url: 'api/save_student.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Student saved successfully!', 'success');
                $('#studentModal').modal('hide');
                loadStudents();
            } else {
                showNotification('Error: ' + response.message, 'danger');
            }
        },
        error: function() {
            showNotification('Failed to save student', 'danger');
        }
    });
}

// Utility Functions
function confirmDelete(id, type) {
    currentDeleteId = id;
    currentDeleteType = type;
    
    const message = type === 'teacher' 
        ? 'Are you sure you want to delete this teacher account? This action cannot be undone.'
        : 'Are you sure you want to delete this student account? This will also delete their attendance records.';
    
    $('#deleteMessage').text(message);
    new bootstrap.Modal('#confirmDeleteModal').show();
}

$('#confirmDeleteBtn').click(function() {
    if (!currentDeleteId || !currentDeleteType) return;
    
    const url = currentDeleteType === 'teacher' 
        ? 'api/delete_teacher.php' 
        : 'api/delete_student.php';
    
    $.ajax({
        url: url,
        method: 'POST',
        data: { id: currentDeleteId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Record deleted successfully!', 'success');
                if (currentDeleteType === 'teacher') {
                    loadTeachers();
                } else {
                    loadStudents();
                }
            } else {
                showNotification('Error: ' + response.message, 'danger');
            }
            $('#confirmDeleteModal').modal('hide');
            currentDeleteId = null;
            currentDeleteType = null;
        },
        error: function() {
            showNotification('Failed to delete record', 'danger');
            $('#confirmDeleteModal').modal('hide');
        }
    });
});

function togglePassword(fieldId) {
    const field = $('#' + fieldId);
    const toggle = field.next('.password-toggle').find('i');
    
    if (field.attr('type') === 'password') {
        field.attr('type', 'text');
        toggle.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
        field.attr('type', 'password');
        toggle.removeClass('bi-eye-slash').addClass('bi-eye');
    }
}

function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#profileImagePreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleFaceDataUpload(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                currentFaceData = JSON.parse(e.target.result);
                $('#faceDataInfo').html(`
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> 
                        Face data loaded successfully! ${Object.keys(currentFaceData).length} descriptors found.
                    </div>
                `);
            } catch (error) {
                $('#faceDataInfo').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        Invalid JSON file format!
                    </div>
                `);
                currentFaceData = null;
            }
        };
        reader.readAsText(input.files[0]);
    }
}

function showNotification(message, type) {
    // Remove any existing notification
    $('.notification-toast').remove();
    
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    
    const notification = `
        <div class="notification-toast position-fixed top-0 end-0 m-4" style="z-index: 1050;">
            <div class="alert ${alertClass} alert-dismissible fade show shadow" role="alert">
                <i class="bi ${icon} me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.notification-toast').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Initialize modals when hidden
$('#teacherModal, #studentModal').on('hidden.bs.modal', function() {
    $(this).find('form')[0].reset();
    currentFaceData = null;
    $('#faceDataInfo').html('');
});