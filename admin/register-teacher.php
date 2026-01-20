<?php
// phpinfo();
// echo "Test";
// require 'db-config/security.php';
// require_once 'db-config/security.php';
// header('location: login.php');
// exit;
// if(file_exists('db-config/security.php')){
//     require_once 'db-config/security.php';
// } else {
//     die('Database configuration file not found.');
// }
// Check if user is logged in
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true) {
//     header('Location: login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration</title>
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
            max-width: 500px;
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
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .input-group-text {
            background-color: #f8f9fc;
        }
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2><i class="fas fa-user-plus me-2"></i>Teacher Registration</h2>
                <p class="mb-0">Create your account</p>
            </div>
            <div class="auth-body">
                <form id="registerForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="firstname" name="firstname" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                            </div>
                        </div>
                    </div>
                    
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
                        <div class="form-text">Password must be at least 8 characters long</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <span class="input-group-text password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="registerBtn">
                            <span id="registerText">Register</span>
                            <span id="registerSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                    
                    <div class="login-link">
                        <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
                
                <div id="registerMessage" class="mt-3"></div>
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
        $('.password-toggle').click(function() {
            const input = $(this).closest('.input-group').find('input');
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
        $('#registerForm').submit(function(e) {
            e.preventDefault();
            
            const firstname = $('#firstname').val().trim();
            const lastname = $('#lastname').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            
            // Validation
            if (!firstname || !lastname || !email || !password) {
                showMessage('Please fill in all required fields.', 'danger');
                return;
            }
            
            if (password.length < 8) {
                showMessage('Password must be at least 8 characters long.', 'danger');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('Passwords do not match.', 'danger');
                return;
            }
            
            // Show loading state
            $('#registerBtn').prop('disabled', true);
            $('#registerText').addClass('d-none');
            $('#registerSpinner').removeClass('d-none');
            
            // Prepare data
            const formData = {
                firstname: firstname,
                lastname: lastname,
                email: email,
                password: password
            };
            
            // Send AJAX request
            $.ajax({
                url: 'register_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        $('#registerForm')[0].reset();
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        showMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    showMessage('An error occurred. Please try again.', 'danger');
                },
                complete: function() {
                    // Reset button state
                    $('#registerBtn').prop('disabled', false);
                    $('#registerText').removeClass('d-none');
                    $('#registerSpinner').addClass('d-none');
                }
            });
        });
        
        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            $('#registerMessage').html(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        }
    });
    </script>
</body>
</html>