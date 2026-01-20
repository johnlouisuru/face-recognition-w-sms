<?php
// session_start();
require_once 'db-config/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberMe']) ? true : false;
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }
    
    // Check if user exists
    $sql = "SELECT id, fullname, email, password FROM teachers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    $teacher = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $teacher['password'])) {
        // Set session variables
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['fullname'];
        $_SESSION['teacher_email'] = $teacher['email'];
        $_SESSION['logged_in'] = true;
        
        // Set cookie for "Remember Me" if checked
        if ($rememberMe) {
            $cookie_name = "teacher_remember";
            $cookie_value = base64_encode($teacher['id'] . '|' . $teacher['email']);
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 30 days
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful! Redirecting...',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>