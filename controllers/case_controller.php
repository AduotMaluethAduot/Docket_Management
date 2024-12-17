<?php
session_start();
require_once '../db/config.php';

// Set headers
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Debug logging
error_log("=== Case Controller Started ===");
error_log("Session data: " . print_r($_SESSION, true));
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Get and validate JSON input
    $jsonInput = file_get_contents('php://input');
    error_log("Raw input: " . $jsonInput);
    
    $input = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }
    error_log("Decoded input: " . print_r($input, true));

    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection is null");
    }
    if ($conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }
    error_log("Database connection verified");

    // Basic validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    }

    if (!isset($input['action'])) {
        throw new Exception("Action not specified");
    }

    if ($input['action'] === 'add') {
        error_log("Processing add action");
        
        // Validate required fields
        $required = ['case_number', 'case_title', 'client_id', 'lawyer_id', 'case_type', 'filing_date'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                throw new Exception("$field is required");
            }
        }
        error_log("Required fields validated");

        // Insert case
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
        
        error_log("Preparing query: " . $query);
        error_log("Values to be inserted: " . print_r([
            'case_number' => $input['case_number'],
            'case_title' => $input['case_title'],
            'client_id' => $input['client_id'],
            'lawyer_id' => $input['lawyer_id'],
            'case_type' => $input['case_type'],
            'description' => $input['description'] ?? '',
            'filing_date' => $input['filing_date']
        ], true));

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

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $case_id = $conn->insert_id;
        error_log("Case inserted successfully with ID: " . $case_id);

        // Add case history
        $history_query = "INSERT INTO case_history (case_id, action, description, created_by) 
                         VALUES (?, 'Case Created', 'New case added', ?)";
        
        $history_stmt = $conn->prepare($history_query);
        if (!$history_stmt) {
            throw new Exception("History prepare failed: " . $conn->error);
        }

        $history_stmt->bind_param("ii", $case_id, $_SESSION['user_id']);
        
        if (!$history_stmt->execute()) {
            throw new Exception("History execute failed: " . $history_stmt->error);
        }

        $response = [
            'success' => true,
            'message' => 'Case added successfully',
            'case_id' => $case_id
        ];
        error_log("Case and history added successfully");
    } else {
        throw new Exception("Invalid action: " . $input['action']);
    }

} catch (Exception $e) {
    error_log("ERROR in case_controller.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $response['message'] = $e->getMessage();
}

error_log("Sending response: " . json_encode($response));
echo json_encode($response);
exit;
?> 