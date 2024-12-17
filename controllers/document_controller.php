<?php
session_start();
require_once '../db/config.php';

// Get JSON input or form data
$input = $_POST;
if (empty($_POST)) {
    $input = json_decode(file_get_contents('php://input'), true);
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'upload':
                // Validate required fields
                if (!isset($input['case_id']) || !isset($input['document_type'])) {
                    $response['message'] = 'Case ID and document type are required';
                    break;
                }

                if (!isset($_FILES['files'])) {
                    $response['message'] = 'No files were uploaded';
                    break;
                }

                try {
                    // Create upload directory if it doesn't exist
                    $upload_dir = '../uploads/documents/' . $input['case_id'] . '/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
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
                            
                            // Move uploaded file
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
                                $stmt->bind_param(
                                    'sisssssi',
                                    $name,
                                    $input['case_id'],
                                    $input['document_type'],
                                    $file_path,
                                    $file_size,
                                    $file_type,
                                    $input['notes'] ?? '',
                                    $_SESSION['user_id']
                                );
                                
                                if ($stmt->execute()) {
                                    $uploaded_files[] = $name;
                                } else {
                                    throw new Exception("Failed to save document record: " . $stmt->error);
                                }
                            } else {
                                throw new Exception("Failed to move uploaded file: " . $name);
                            }
                        } else {
                            throw new Exception("Upload error for file " . $files['name'][$i] . ": " . $files['error'][$i]);
                        }
                    }

                    if (!empty($uploaded_files)) {
                        // Add to case history
                        $history_query = "INSERT INTO case_history (
                            case_id, 
                            action, 
                            description, 
                            created_by
                        ) VALUES (?, 'Document Upload', ?, ?)";
                        
                        $history_stmt = $conn->prepare($history_query);
                        $description = count($uploaded_files) . " document(s) uploaded: " . implode(", ", $uploaded_files);
                        $history_stmt->bind_param('isi', $input['case_id'], $description, $_SESSION['user_id']);
                        $history_stmt->execute();

                        $response = [
                            'success' => true,
                            'message' => 'Documents uploaded successfully',
                            'files' => $uploaded_files
                        ];
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Upload failed: ' . $e->getMessage();
                    error_log("Document upload error: " . $e->getMessage());
                }
                break;

            case 'delete':
                if (!isset($input['id'])) {
                    $response['message'] = 'Document ID is required';
                    break;
                }

                try {
                    // Get document info before deletion
                    $query = "SELECT file_path, case_id FROM documents WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $input['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $document = $result->fetch_assoc();

                    if ($document) {
                        // Delete file from filesystem
                        if (file_exists($document['file_path'])) {
                            unlink($document['file_path']);
                        }

                        // Delete database record
                        $delete_query = "DELETE FROM documents WHERE id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bind_param('i', $input['id']);

                        if ($delete_stmt->execute()) {
                            // Add to case history
                            $history_query = "INSERT INTO case_history (
                                case_id, 
                                action, 
                                description, 
                                created_by
                            ) VALUES (?, 'Document Deleted', 'Document was deleted', ?)";
                            
                            $history_stmt = $conn->prepare($history_query);
                            $history_stmt->bind_param('ii', $document['case_id'], $_SESSION['user_id']);
                            $history_stmt->execute();

                            $response = [
                                'success' => true,
                                'message' => 'Document deleted successfully'
                            ];
                        } else {
                            throw new Exception($delete_stmt->error);
                        }
                    } else {
                        throw new Exception("Document not found");
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Failed to delete document: ' . $e->getMessage();
                    error_log("Document deletion error: " . $e->getMessage());
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