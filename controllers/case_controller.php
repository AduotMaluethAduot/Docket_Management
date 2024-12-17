<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'add':
                    // Validate required fields
                    $required = ['case_number', 'case_title', 'client_id', 'lawyer_id', 'case_type', 'filing_date'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                        }
                    }

                    // Check if case number already exists
                    $check_stmt = $conn->prepare("SELECT id FROM cases WHERE case_number = ?");
                    $check_stmt->bind_param("s", $input['case_number']);
                    $check_stmt->execute();
                    if ($check_stmt->get_result()->num_rows > 0) {
                        throw new Exception("Case number already exists");
                    }

                    // Insert new case
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
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

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
                        
                        // Add to case history
                        $history_query = "INSERT INTO case_history (case_id, action, description, created_by) 
                                        VALUES (?, 'Case Created', 'New case added', ?)";
                        $history_stmt = $conn->prepare($history_query);
                        $history_stmt->bind_param('ii', $case_id, $_SESSION['user_id']);
                        $history_stmt->execute();

                        $response = [
                            'success' => true,
                            'message' => 'Case added successfully',
                            'case_id' => $case_id
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
    error_log("Case controller error: " . $e->getMessage());
}

// Ensure clean output
ob_clean();
echo json_encode($response);
exit;
?> 