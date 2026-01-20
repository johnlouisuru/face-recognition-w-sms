<?php 
if(file_exists('db-config/security.php')){
    require_once 'db-config/security.php';
} else {
    die('Database configuration file not found.');
}
// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .auth-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
            margin: 0 auto;
        }
        .auth-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .auth-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,.25);
        }
        .btn-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #3a56c4, #1d3e8f);
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .input-group-text {
            background-color: #f8f9fc;
        }
        .password-toggle {
            cursor: pointer;
        }
        .teacher-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <div class="teacher-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h2>Teacher Login</h2>
                <p class="mb-0">Welcome back to Attendance System</p>
            </div>
            <div class="auth-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="loginBtn">
                            <span id="loginText">Login</span>
                            <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                    
                    <div class="register-link">
                        <p class="mb-0">Don't have an account? <a href="register-teacher.php">Register here</a></p>
                    </div>
                </form>
                
                <div id="loginMessage" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePassword').click(function() {
            const input = $('#password');
            const icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Form submission
        $('#loginForm').submit(function(e) {
            e.preventDefault();
            
            const email = $('#email').val().trim();
            const password = $('#password').val();
            
            // Validation
            if (!email || !password) {
                showMessage('Please fill in all fields.', 'danger');
                return;
            }
            
            // Show loading state
            $('#loginBtn').prop('disabled', true);
            $('#loginText').addClass('d-none');
            $('#loginSpinner').removeClass('d-none');
            
            // Prepare data
            const formData = {
                email: email,
                password: password,
                rememberMe: $('#rememberMe').is(':checked')
            };
            
            // Send AJAX request
            $.ajax({
                url: 'login_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        setTimeout(() => {
                            window.location.href = response.redirect || 'dashboard.php';
                        }, 1000);
                    } else {
                        showMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    showMessage('An error occurred. Please try again.', 'danger');
                },
                complete: function() {
                    // Reset button state
                    $('#loginBtn').prop('disabled', false);
                    $('#loginText').removeClass('d-none');
                    $('#loginSpinner').addClass('d-none');
                }
            });
        });
        
        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            $('#loginMessage').html(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        }
        
        // Check for saved credentials
        if (localStorage.getItem('rememberEmail')) {
            $('#email').val(localStorage.getItem('rememberEmail'));
            $('#rememberMe').prop('checked', true);
        }
    });
    </script>
</body>
</html>