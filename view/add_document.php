<?php
require_once '../includes/header.php';
require_once '../db/config.php';



// Get cases for dropdown
$casesQuery = $conn->query("
    SELECT c.id, c.case_number, c.case_title 
    FROM cases c 
    WHERE c.case_status != 'closed'
    ORDER BY c.created_at DESC
");
$cases = $casesQuery ? $casesQuery->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container">
    <h2>Upload Document</h2>
    <form id="uploadDocumentForm" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="case_id">Select Case</label>
            <select id="case_id" name="case_id" required>
                <option value="">Select Case</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?php echo $case['id']; ?>">
                        <?php echo htmlspecialchars($case['case_number'] . ' - ' . $case['case_title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="document_type">Document Type</label>
            <select id="document_type" name="document_type" required>
                <option value="">Select Type</option>
                <option value="contract">Contract</option>
                <option value="evidence">Evidence</option>
                <option value="court_order">Court Order</option>
                <option value="correspondence">Correspondence</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Upload Documents</label>
            <div class="upload-area" id="uploadArea">
                <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                       style="display: none;" onchange="handleFileSelect(event)">
                <div class="upload-prompt" onclick="document.getElementById('files').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click or drag files here to upload</p>
                    <span>Supported formats: PDF, DOC, DOCX, JPG, PNG</span>
                </div>
                <div id="fileList" class="file-list"></div>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn-primary">Upload Documents</button>
            <a href="manage_documents.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<style>
.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f9f9f9;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #3498db;
    background: #f0f9ff;
}

.upload-prompt {
    padding: 20px;
}

.upload-prompt i {
    font-size: 48px;
    color: #3498db;
    margin-bottom: 10px;
}

.upload-prompt p {
    margin: 10px 0;
    font-size: 16px;
}

.upload-prompt span {
    font-size: 12px;
    color: #666;
}

.file-list {
    margin-top: 20px;
    text-align: left;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: white;
    border: 1px solid #eee;
    margin: 5px 0;
    border-radius: 4px;
}

.file-item i {
    margin-right: 10px;
    color: #3498db;
}

.file-item .file-name {
    flex: 1;
}

.file-item .remove-file {
    color: #e74c3c;
    cursor: pointer;
    padding: 5px;
}

.drag-over {
    border-color: #3498db;
    background: #f0f9ff;
}
</style>

<script>
let files = new Set();

// Handle drag and drop
const uploadArea = document.getElementById('uploadArea');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    uploadArea.classList.add('drag-over');
}

function unhighlight(e) {
    uploadArea.classList.remove('drag-over');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const newFiles = dt.files;
    handleFiles(newFiles);
}

function handleFileSelect(event) {
    handleFiles(event.target.files);
}

function handleFiles(fileList) {
    for (const file of fileList) {
        if (isValidFile(file)) {
            files.add(file);
        }
    }
    updateFileList();
}

function isValidFile(file) {
    const validTypes = ['application/pdf', 'application/msword', 
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'image/jpeg', 'image/png'];
    if (!validTypes.includes(file.type)) {
        alert('Invalid file type: ' + file.name);
        return false;
    }
    if (file.size > 10 * 1024 * 1024) { // 10MB limit
        alert('File too large: ' + file.name);
        return false;
    }
    return true;
}

function updateFileList() {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    files.forEach(file => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <i class="fas fa-file"></i>
            <span class="file-name">${file.name}</span>
            <i class="fas fa-times remove-file" onclick="removeFile('${file.name}')"></i>
        `;
        fileList.appendChild(fileItem);
    });
}

function removeFile(fileName) {
    files = new Set([...files].filter(file => file.name !== fileName));
    updateFileList();
}

// Form submission
document.getElementById('uploadDocumentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const caseId = document.getElementById('case_id').value;
    if (!caseId) {
        alert('Please select a case');
        return;
    }

    if (files.size === 0) {
        alert('Please select at least one file to upload');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('case_id', caseId);
    formData.append('document_type', document.getElementById('document_type').value);
    formData.append('notes', document.getElementById('notes').value);
    
    files.forEach(file => {
        formData.append('files[]', file);
    });

    try {
        const response = await fetch('../controllers/document_controller.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        if (data.success) {
            alert('Documents uploaded successfully');
            window.location.href = 'manage_documents.php';
        } else {
            throw new Error(data.message || 'Failed to upload documents');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while uploading documents');
    }
});
</script>
</body>
</html> 