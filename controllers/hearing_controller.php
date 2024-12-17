<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'add':
                    // Validate required fields
                    $required = ['hearing_date', 'hearing_type', 'location', 'status'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            throw new Exception(ucfirst($field) . ' is required');
                        }
                    }

                    // Prepare and execute insert query
                    $query = "INSERT INTO case_hearings (
                        hearing_date, 
                        hearing_type, 
                        location, 
                        status, 
                        notes
                    ) VALUES (?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $stmt->bind_param(
                        'sssss',
                        $input['hearing_date'],
                        $input['hearing_type'],
                        $input['location'],
                        $input['status'],
                        $input['notes'] ?? ''
                    );

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Hearing scheduled successfully',
                            'hearing_id' => $conn->insert_id
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                    break;

                case 'delete':
                    if (!isset($input['id'])) {
                        throw new Exception('Hearing ID is required');
                    }

                    $query = "DELETE FROM case_hearings WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $stmt->bind_param('i', $input['id']);

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Hearing deleted successfully'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                    break;

                default:
                    throw new Exception('Invalid action');
            }
        } else {
            throw new Exception('Action is required');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Hearing controller error: " . $e->getMessage());
}

// Ensure clean output
ob_clean();
echo json_encode($response);
exit;
?> 