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
                $required = ['hearing_date', 'hearing_type', 'location', 'status'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        $response['message'] = ucfirst($field) . ' is required';
                        echo json_encode($response);
                        exit;
                    }
                }

                // Prepare and execute insert query
                $query = "INSERT INTO case_hearings (hearing_date, hearing_type, location, status, notes) 
                         VALUES (?, ?, ?, ?, ?)";
                
                try {
                    $stmt = $conn->prepare($query);
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
                            'message' => 'Hearing scheduled successfully'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Failed to schedule hearing: ' . $e->getMessage();
                }
                break;

            case 'delete':
                if (!isset($input['id'])) {
                    $response['message'] = 'Hearing ID is required';
                    break;
                }

                $query = "DELETE FROM case_hearings WHERE id = ?";
                
                try {
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $input['id']);

                    if ($stmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Hearing deleted successfully'
                        ];
                    } else {
                        throw new Exception($stmt->error);
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Failed to delete hearing: ' . $e->getMessage();
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