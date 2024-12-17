<?php
require_once '../db/config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add':
                // Validate required fields
                $required = ['case_number', 'case_title', 'client_id', 'lawyer_id', 'case_type', 'filing_date'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                        echo json_encode($response);
                        exit;
                    }
                }

                // Prepare and execute insert query
                $query = "INSERT INTO cases (case_number, case_title, client_id, lawyer_id, case_type, case_status, description, filing_date) 
                         VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)";
                
                try {
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }

                    $stmt->bind_param(
                        'ssiisss',
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
                        $history_query = "INSERT INTO case_history (case_id, action, description, created_by) VALUES (?, 'Case Created', 'New case added', ?)";
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
                } catch (Exception $e) {
                    $response['message'] = 'Failed to add case: ' . $e->getMessage();
                    error_log("Case addition error: " . $e->getMessage());
                }
                break;

            case 'update':
                // Similar structure for updating cases
                break;

            case 'delete':
                if (!isset($input['id'])) {
                    $response['message'] = 'Case ID is required';
                    break;
                }

                try {
                    // Start transaction
                    $conn->begin_transaction();

                    // Delete related records first
                    $tables = ['case_history', 'case_hearings', 'documents'];
                    foreach ($tables as $table) {
                        $query = "DELETE FROM $table WHERE case_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $input['id']);
                        $stmt->execute();
                    }

                    // Delete the case
                    $query = "DELETE FROM cases WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $input['id']);

                    if ($stmt->execute()) {
                        $conn->commit();
                        $response = [
                            'success' => true,
                            'message' => 'Case deleted successfully'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Failed to delete case: ' . $e->getMessage();
                    error_log("Case deletion error: " . $e->getMessage());
                }
                break;

            default:
                $response['message'] = 'Invalid action';
                break;
        }
    } else {
        $response['message'] = 'Action is required';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?> 