<?php
require_once 'db-config/security.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher information
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

// Update profile if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    
    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE teachers SET fullname = ?, email = ?, password = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $fullname, $email, $new_password, $teacher_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE teachers SET fullname = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $fullname, $email, $teacher_id);
    }
    
    if ($update_stmt->execute()) {
        $_SESSION['teacher_name'] = $fullname;
        $_SESSION['teacher_email'] = $email;
        $success_message = "Profile updated successfully!";
        // Refresh teacher data
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();
    } else {
        $error_message = "Failed to update profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-icon {
            font-size: 4rem;
            color: #4e73df;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'dashboard_nav.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>Teacher Profile</h3>
                <p class="text-muted">Manage your account information</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" 
                               value="<?php echo htmlspecialchars($teacher['fullname']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password (leave blank to keep unchanged)</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">Leave password fields blank if you don't want to change password</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="account-info">
                <h5>Account Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Member Since:</strong><br>
                            <?php echo date('F j, Y', strtotime($teacher['created_at'])); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Last Updated:</strong><br>
                            <?php echo date('F j, Y h:i A'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>