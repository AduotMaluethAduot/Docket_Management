<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Get documents list with error handling
try {
    $query = "SELECT 
        d.*, 
        c.case_number,
        c.case_title
        FROM documents d
        LEFT JOIN cases c ON d.case_id = c.id
        ORDER BY d.upload_date DESC";
    $result = $conn->query($query);
    $documents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Exception $e) {
    error_log("Error fetching documents: " . $e->getMessage());
    $documents = [];
}
?>

<div class="container">
    <h1>Manage Documents</h1>
    <a href="add_document.php" class="btn-primary">Upload New Document</a>
    
    <?php if (empty($documents)): ?>
        <div class="alert alert-info">
            <p>No documents found. Upload a new document to get started.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Case</th>
                    <th>Type</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr id="document-row-<?php echo $doc['id']; ?>">
                    <td><?php echo htmlspecialchars($doc['title']); ?></td>
                    <td><?php echo htmlspecialchars($doc['case_title'] ?? 'No Case'); ?></td>
                    <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($doc['upload_date'])); ?></td>
                    <td>
                        <?php if (!empty($doc['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn-view" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        <?php endif; ?>
                        <button onclick="deleteDocument(<?php echo $doc['id']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        fetch('manage_documents.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`document-row-${id}`);
                if (row) {
                    row.remove();
                }
                
                // Check if table is now empty
                const tableBody = document.querySelector('tbody');
                if (tableBody && tableBody.children.length === 0) {
                    const container = document.querySelector('.container');
                    container.innerHTML = `
                        <h1>Manage Documents</h1>
                        <a href="add_document.php" class="btn-primary">Upload New Document</a>
                        <div class="alert alert-info">
                            <p>No documents found. Upload a new document to get started.</p>
                        </div>
                    `;
                }
                
                alert('Document deleted successfully');
            } else {
                throw new Error(data.message || 'Failed to delete document');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'An error occurred while deleting the document');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>