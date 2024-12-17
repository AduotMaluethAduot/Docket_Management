<?php
session_start();
require_once '../db/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            try {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                
                if (!$insert_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['success_message'] = "Registration successful! Please login.";
                    error_log("User registered successfully: " . $username);
                    header("Location: login.php");
                    exit();
                } else {
                    throw new Exception("Execute failed: " . $insert_stmt->error);
                }
            } catch (Exception $e) {
                $error = "Registration failed: " . $e->getMessage();
                error_log("Registration error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Case Management System</title>
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h2>Sign Up</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn-primary">Sign Up</button>
                
                <div class="auth-links">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 