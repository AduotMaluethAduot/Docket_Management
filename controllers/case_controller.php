<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display

// Debug logging
error_log("Request received in case_controller.php");
error_log("POST data: " . file_get_contents('php://input'));

// At the top after session_start()
error_log("Session data: " . print_r($_SESSION, true));
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
}

error_log("Decoded input: " . print_r($input, true));

$response = ['success' => false, 'message' => ''];

try {
    // Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'add':
                    // Log the required fields check
                    error_log("Checking required fields...");
                    $required = ['case_number', 'case_title', 'client_id', 'lawyer_id', 'case_type', 'filing_date'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                        }
                    }

                    // Check database connection
                    if (!$conn) {
                        throw new Exception("Database connection failed");
                    }

                    // Check if case number already exists
                    error_log("Checking for duplicate case number...");
                    $check_stmt = $conn->prepare("SELECT id FROM cases WHERE case_number = ?");
                    if (!$check_stmt) {
                        throw new Exception("Prepare check statement failed: " . $conn->error);
                    }
                    $check_stmt->bind_param("s", $input['case_number']);
                    $check_stmt->execute();
                    if ($check_stmt->get_result()->num_rows > 0) {
                        throw new Exception("Case number already exists");
                    }

                    // Insert new case
                    error_log("Inserting new case...");
                    $query = "INSERT INTO cases (
                        case_number, 
                        case_title, 
                        client_id, 
                        lawyer_id, 
                        case_type, 
                        case_status,
                        description, 
                        filing_date
                    ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)";
                    
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Prepare insert statement failed: " . $conn->error);
                    }

                    // Log the values being bound
                    error_log("Binding parameters with values: " . print_r([
                        'case_number' => $input['case_number'],
                        'case_title' => $input['case_title'],
                        'client_id' => $input['client_id'],
                        'lawyer_id' => $input['lawyer_id'],
                        'case_type' => $input['case_type'],
                        'description' => $input['description'] ?? '',
                        'filing_date' => $input['filing_date']
                    ], true));

                    // Before bind_param, validate data types
                    if (!is_numeric($input['client_id']) || !is_numeric($input['lawyer_id'])) {
                        throw new Exception("Invalid client or lawyer ID");
                    }

                    // Validate date format
                    if (!strtotime($input['filing_date'])) {
                        throw new Exception("Invalid filing date format");
                    }

                    // Log data types
                    error_log("Data types: " . print_r([
                        'client_id' => gettype($input['client_id']),
                        'lawyer_id' => gettype($input['lawyer_id']),
                        'filing_date' => gettype($input['filing_date'])
                    ], true));

                    $stmt->bind_param(
                        "ssiisss",
                        $input['case_number'],
                        $input['case_title'],
                        $input['client_id'],
                        $input['lawyer_id'],
                        $input['case_type'],
                        $input['description'] ?? '',
                        $input['filing_date']
                    );

                    if ($stmt->execute()) {
                        $case_id = $conn->insert_id;
                        error_log("Case inserted successfully with ID: " . $case_id);
                        
                        // Add to case history
                        $history_query = "INSERT INTO case_history (case_id, action, description, created_by) 
                                        VALUES (?, 'Case Created', 'New case added', ?)";
                        $history_stmt = $conn->prepare($history_query);
                        if (!$history_stmt) {
                            throw new Exception("Prepare history statement failed: " . $conn->error);
                        }
                        $history_stmt->bind_param('ii', $case_id, $_SESSION['user_id']);
                        $history_stmt->execute();

                        $response = [
                            'success' => true,
                            'message' => 'Case added successfully',
                            'case_id' => $case_id
                        ];
                    } else {
                        throw new Exception("Execute failed: " . $stmt->error);
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
    error_log("Error in case_controller.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $response['message'] = $e->getMessage();
}

// Log the response
error_log("Sending response: " . json_encode($response));

// Ensure clean output
ob_clean();
echo json_encode($response);
exit;
?> 