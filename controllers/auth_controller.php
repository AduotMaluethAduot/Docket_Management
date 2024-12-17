<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }

        switch ($input['action']) {
            case 'login':
                if (!isset($input['username']) || !isset($input['password'])) {
                    throw new Exception('Username and password are required');
                }

                $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
                $stmt->bind_param("s", $input['username']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($input['password'], $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        $response = [
                            'success' => true,
                            'message' => 'Login successful',
                            'user' => [
                                'id' => $user['id'],
                                'username' => $user['username'],
                                'role' => $user['role']
                            ]
                        ];
                    } else {
                        throw new Exception('Invalid password');
                    }
                } else {
                    throw new Exception('User not found');
                }
                break;

            case 'register':
                $required = ['username', 'email', 'password', 'confirm_password'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        throw new Exception(ucfirst($field) . ' is required');
                    }
                }

                if ($input['password'] !== $input['confirm_password']) {
                    throw new Exception('Passwords do not match');
                }

                // Validate email
                if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }

                // Check if username or email already exists
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $check_stmt->bind_param("ss", $input['username'], $input['email']);
                $check_stmt->execute();
                if ($check_stmt->get_result()->num_rows > 0) {
                    throw new Exception('Username or email already exists');
                }

                // Hash password
                $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

                // Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $insert_stmt->bind_param("sss", $input['username'], $input['email'], $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $response = [
                        'success' => true,
                        'message' => 'Registration successful'
                    ];
                } else {
                    throw new Exception('Registration failed');
                }
                break;

            case 'logout':
                session_destroy();
                $response = [
                    'success' => true,
                    'message' => 'Logout successful'
                ];
                break;

            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Auth error: " . $e->getMessage());
}

echo json_encode($response);
exit;
?> 