<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection is null"));
    }

    // Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated");
    }

    // Get and validate JSON input
    $jsonInput = file_get_contents('php://input');
    if (empty($jsonInput)) {
        throw new Exception("No input received");
    }

    $input = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    if (!isset($input['action'])) {
        throw new Exception("Action not specified");
    }

    if ($input['action'] === 'add') {
        // Validate required fields
        $required = ['case_number', 'case_title', 'client_id', 'lawyer_id', 'case_type', 'filing_date'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate client exists
        $clientStmt = $conn->prepare("SELECT id FROM clients WHERE id = ?");
        $clientStmt->bind_param("i", $input['client_id']);
        $clientStmt->execute();
        if ($clientStmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid client ID");
        }

        // Validate lawyer exists
        $lawyerStmt = $conn->prepare("SELECT id FROM lawyers WHERE id = ?");
        $lawyerStmt->bind_param("i", $input['lawyer_id']);
        $lawyerStmt->execute();
        if ($lawyerStmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid lawyer ID");
        }

        // Insert case
        $query = "INSERT INTO cases (case_number, case_title, client_id, lawyer_id, case_type, filing_date, description) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
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
            $input['filing_date'],
            $input['description'] ?? ''
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to add case: " . $stmt->error);
        }

        $case_id = $conn->insert_id;

        // Add case history
        $historyQuery = "INSERT INTO case_history (case_id, action, description, created_by) VALUES (?, ?, ?, ?)";
        $historyStmt = $conn->prepare($historyQuery);
        if (!$historyStmt) {
            throw new Exception("Failed to prepare history statement: " . $conn->error);
        }

        $action = "Case Created";
        $description = "New case added";
        $historyStmt->bind_param("issi", $case_id, $action, $description, $_SESSION['user_id']);
        
        if (!$historyStmt->execute()) {
            throw new Exception("Failed to add case history: " . $historyStmt->error);
        }

        $response = [
            'success' => true,
            'message' => 'Case added successfully',
            'case_id' => $case_id
        ];
    } else {
        throw new Exception("Invalid action");
    }

} catch (Exception $e) {
    error_log("Error in case_controller.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?> 