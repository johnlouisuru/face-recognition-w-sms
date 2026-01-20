<?php
require_once 'db-config/security.php';
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 0.375rem 0.375rem;
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-bottom-color: #fff;
        }
        .modal-lg-custom {
            max-width: 700px;
        }
        .profile-image-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            cursor: pointer;
        }
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        .profile-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .profile-image-container:hover .profile-image-overlay {
            opacity: 1;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .face-upload-area {
            border: 2px dashed #6c757d;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
        }
        .face-upload-area:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-people-fill"></i> Accounts Management
            </a>
            <div class="navbar-nav">
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="accountsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="teachers-tab" data-bs-toggle="tab" data-bs-target="#teachers" type="button" role="tab">
                    <i class="bi bi-person-badge"></i> Teachers
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
                    <i class="bi bi-mortarboard"></i> Students
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="accountsTabContent">
            <!-- Teachers Tab -->
            <div class="tab-pane fade show active" id="teachers" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Teacher Accounts</h4>
                    <button class="btn btn-primary" onclick="showAddTeacherModal()">
                        <i class="bi bi-plus-circle"></i> Add Teacher
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="teachersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teachersTableBody">
                            <!-- Teachers data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Students Tab -->
            <div class="tab-pane fade" id="students" role="tabpanel">
                <!-- <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Student Accounts</h4>
                    <button class="btn btn-primary" onclick="showAddStudentModal()">
                        <i class="bi bi-plus-circle"></i> Add Student
                    </button>
                </div> -->

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Student Accounts</h4>
                    <a href="register.html" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Student
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Grade</th>
                                <th>Section</th>
                                <th>Parent</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody">
                            <!-- Students data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teacherModalTitle">Add Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="teacherForm">
                    <input type="hidden" id="teacher_id" name="teacher_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="fullname" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span id="passwordRequired">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="password" name="password">
                                <span class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            <small class="text-muted" id="passwordHelp">Required for new teachers. Leave blank to keep current password when editing.</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span id="confirmRequired">*</span></label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTeacher()">Save Teacher</button>
            </div>
        </div>
    </div>
</div>

    <!-- Add/Edit Student Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalTitle">Add Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="studentForm">
                        <input type="hidden" id="student_db_id" name="student_db_id">
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="student_id" class="form-label">Student ID *</label>
                                <input type="text" class="form-control" pattern="\d{1,12}" maxlength="12" id="student_id" name="student_id" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="grade" class="form-label">Grade *</label>
                                <select class="form-select" id="grade" name="grade" required>
                                    <option value="">Select Grade</option>
                                    <?php for($i = 7; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>">Grade <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="section_name" class="form-label">Section Name *</label>
                                <input type="text" class="form-control" id="section_name" name="section_name" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="student_last" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="student_last" name="student_last" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="student_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="student_mi" class="form-label">Middle Initial</label>
                                <input type="text" class="form-control" id="student_mi" name="student_mi" maxlength="1">
                            </div>
                            
                            <!-- <div class="col-md-12 mb-3">
                                <label class="form-label">Profile Picture</label>
                                <div class="profile-image-container">
                                    <img id="profileImagePreview" src="https://via.placeholder.com/120x120?text=Upload+Photo" 
                                         class="profile-image-preview" onclick="document.getElementById('profile_picture').click()">
                                    <div class="profile-image-overlay">
                                        <span class="text-white">
                                            <i class="bi bi-camera fs-4"></i><br>
                                            Click to upload
                                        </span>
                                    </div>
                                </div>
                                <input type="file" class="form-control d-none" id="profile_picture" name="profile_picture" 
                                       accept="image/*" onchange="previewProfileImage(this)">
                                <small class="text-muted">Click image to upload (optional)</small>
                            </div> -->
                            
                            <!-- Parent Information -->
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2">Parent Information (Optional)</h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="parent_fullname" class="form-label">Parent Full Name</label>
                                <input type="text" class="form-control" id="parent_fullname" name="parent_fullname">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="parent_contact" class="form-label">Parent Contact Number</label>
                                <input type="text" class="form-control" id="parent_contact" name="parent_contact">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="parent_password" class="form-label">Parent Account Password</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="parent_password" name="parent_password">
                                    <span class="password-toggle" onclick="togglePassword('parent_password')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Face Recognition Data -->
                            <!-- <div class="col-md-12">
                                <h6 class="border-bottom pb-2">Face Recognition Data</h6>
                                <div class="face-upload-area" onclick="document.getElementById('face_data').click()">
                                    <i class="bi bi-camera-video fs-1 text-muted"></i>
                                    <p class="mt-2 mb-1">Upload Face Data File</p>
                                    <small class="text-muted">Click to upload face descriptor data (JSON format)</small>
                                </div>
                                <input type="file" class="form-control d-none" id="face_data" name="face_data" 
                                       accept=".json,.txt" onchange="handleFaceDataUpload(this)">
                                <div id="faceDataInfo" class="mt-2"></div>
                            </div> -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveStudent()">Save Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for Delete -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Are you sure you want to delete this record?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/accounts_management.js"></script>
</body>
</html>