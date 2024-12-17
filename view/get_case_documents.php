<?php
require_once '../db/config.php';

// Check if case_id is provided
if (!isset($_GET['case_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Case ID is required'
    ]);
    exit();
}

$case_id = intval($_GET['case_id']);

// Get documents for the case
$query = "SELECT id, title, file_path, document_type, upload_date 
          FROM documents 
          WHERE case_id = ?
          ORDER BY upload_date DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documents = $result->fetch_all(MYSQLI_ASSOC);

    // Format the response
    $formattedDocs = array_map(function($doc) {
        return [
            'id' => $doc['id'],
            'title' => htmlspecialchars($doc['title']),
            'file_path' => htmlspecialchars($doc['file_path']),
            'document_type' => htmlspecialchars($doc['document_type']),
            'upload_date' => date('Y-m-d', strtotime($doc['upload_date']))
        ];
    }, $documents);

    echo json_encode([
        'success' => true,
        'documents' => $formattedDocs
    ]);

} catch (Exception $e) {
    error_log("Error fetching case documents: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch documents'
    ]);
} 