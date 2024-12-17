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
        throw new Exception("Database connection failed");
    }

    // Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    if (!isset($_POST['action'])) {
        throw new Exception("Action not specified");
    }

    if ($_POST['action'] === 'upload') {
        // Validate required fields
        if (!isset($_POST['case_id']) || !isset($_POST['document_type'])) {
            throw new Exception("Case ID and document type are required");
        }

        if (!isset($_FILES['files'])) {
            throw new Exception("No files were uploaded");
        }

        // Create upload directory
        $upload_dir = "../uploads/documents/{$_POST['case_id']}/";
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        $uploaded_files = [];
        $files = $_FILES['files'];

        // Handle multiple files
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $name = basename($files['name'][$i]);
                $file_type = $files['type'][$i];
                $file_size = $files['size'][$i];
                
                // Generate unique filename
                $filename = uniqid() . '_' . $name;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    // Insert document record
                    $query = "INSERT INTO documents (
                        title, 
                        case_id, 
                        document_type, 
                        file_path, 
                        file_size, 
                        file_type, 
                        notes, 
                        uploaded_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        throw new Exception("Failed to prepare statement: " . $conn->error);
                    }

                    $stmt->bind_param(
                        "sisssssi",
                        $name,
                        $_POST['case_id'],
                        $_POST['document_type'],
                        $file_path,
                        $file_size,
                        $file_type,
                        $_POST['notes'] ?? '',
                        $_SESSION['user_id']
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to save document record: " . $stmt->error);
                    }

                    $uploaded_files[] = $name;
                } else {
                    throw new Exception("Failed to move uploaded file: " . $name);
                }
            } else {
                throw new Exception("Upload error for file " . $files['name'][$i]);
            }
        }

        if (!empty($uploaded_files)) {
            // Add to case history
            $history_query = "INSERT INTO case_history (case_id, action, description, created_by) 
                            VALUES (?, 'Document Upload', ?, ?)";
            
            $history_stmt = $conn->prepare($history_query);
            if (!$history_stmt) {
                throw new Exception("Failed to prepare history statement: " . $conn->error);
            }

            $description = count($uploaded_files) . " document(s) uploaded: " . implode(", ", $uploaded_files);
            $history_stmt->bind_param("isi", $_POST['case_id'], $description, $_SESSION['user_id']);
            
            if (!$history_stmt->execute()) {
                throw new Exception("Failed to add case history: " . $history_stmt->error);
            }

            $response = [
                'success' => true,
                'message' => 'Documents uploaded successfully',
                'files' => $uploaded_files
            ];
        }
    } else {
        throw new Exception("Invalid action");
    }

} catch (Exception $e) {
    error_log("Error in document_controller.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?> 