<?php
// At the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all requests
error_log("Lawyer controller accessed. Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Raw input: " . file_get_contents('php://input'));

session_start();
require_once '../db/config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add':
                // Validate required fields
                $required = ['name', 'email', 'phone', 'specialization', 'bar_number', 'years_experience'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                        echo json_encode($response);
                        exit;
                    }
                }

                try {
                    // Check if email or bar number already exists
                    $check_stmt = $conn->prepare("SELECT id FROM lawyers WHERE email = ? OR bar_number = ?");
                    $check_stmt->bind_param("ss", $input['email'], $input['bar_number']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();

                    if ($result->num_rows > 0) {
                        throw new Exception("Email or Bar Number already exists");
                    }

                    // Insert new lawyer
                    $query = "INSERT INTO lawyers (
                        name, email, phone, specialization, bar_number, 
                        years_experience, status, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($query);
                    $status = $input['status'] ?? 'active';
                    $stmt->bind_param(
                        "ssssssss",
                        $input['name'],
                        $input['email'],
                        $input['phone'],
                        $input['specialization'],
                        $input['bar_number'],
                        $input['years_experience'],
                        $status,
                        $input['notes'] ?? ''
                    );

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Lawyer added successfully',
                            'lawyer_id' => $conn->insert_id
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Failed to add lawyer: ' . $e->getMessage();
                    error_log("Lawyer addition error: " . $e->getMessage());
                }
                break;

            case 'update':
                if (!isset($input['id'])) {
                    $response['message'] = 'Lawyer ID is required';
                    break;
                }

                try {
                    // Check if email or bar number already exists for other lawyers
                    $check_query = "SELECT id FROM lawyers WHERE (email = ? OR bar_number = ?) AND id != ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param("ssi", $input['email'], $input['bar_number'], $input['id']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();

                    if ($result->num_rows > 0) {
                        throw new Exception("Email or Bar Number already exists");
                    }

                    // Update lawyer
                    $query = "UPDATE lawyers SET 
                        name = ?, 
                        email = ?, 
                        phone = ?, 
                        specialization = ?, 
                        bar_number = ?, 
                        years_experience = ?, 
                        status = ?, 
                        notes = ?
                        WHERE id = ?";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param(
                        "ssssssssi",
                        $input['name'],
                        $input['email'],
                        $input['phone'],
                        $input['specialization'],
                        $input['bar_number'],
                        $input['years_experience'],
                        $input['status'],
                        $input['notes'] ?? '',
                        $input['id']
                    );

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Lawyer updated successfully'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Failed to update lawyer: ' . $e->getMessage();
                    error_log("Lawyer update error: " . $e->getMessage());
                }
                break;

            case 'delete':
                if (!isset($input['id'])) {
                    $response['message'] = 'Lawyer ID is required';
                    break;
                }

                try {
                    // Start transaction
                    $conn->begin_transaction();

                    // Update cases to remove lawyer reference
                    $update_cases = "UPDATE cases SET lawyer_id = NULL WHERE lawyer_id = ?";
                    $case_stmt = $conn->prepare($update_cases);
                    $case_stmt->bind_param('i', $input['id']);
                    $case_stmt->execute();

                    // Delete the lawyer
                    $delete_query = "DELETE FROM lawyers WHERE id = ?";
                    $delete_stmt = $conn->prepare($delete_query);
                    $delete_stmt->bind_param('i', $input['id']);

                    if ($delete_stmt->execute()) {
                        $conn->commit();
                        $response = [
                            'success' => true,
                            'message' => 'Lawyer deleted successfully'
                        ];
                    } else {
                        throw new Exception($delete_stmt->error);
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Failed to delete lawyer: ' . $e->getMessage();
                    error_log("Lawyer deletion error: " . $e->getMessage());
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

// Before sending response
error_log("Sending response: " . json_encode($response));
echo json_encode($response);
?> 