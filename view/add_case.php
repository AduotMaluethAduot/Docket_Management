<?php
session_start();
require_once '../includes/header.php';
require_once '../db/config.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

error_log("User ID in session: " . $_SESSION['user_id']);

// Get lawyers for dropdown
$lawyersQuery = $conn->query("SELECT id, name FROM lawyers WHERE status = 'active'");
$lawyers = $lawyersQuery ? $lawyersQuery->fetch_all(MYSQLI_ASSOC) : [];

// Get clients for dropdown
$clientsQuery = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM clients");
$clients = $clientsQuery ? $clientsQuery->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container">
    <h1>Add New Case</h1>
    <form id="addCaseForm" method="POST">
        <div class="form-group">
            <label for="case_number">Case Number</label>
            <input type="text" id="case_number" name="case_number" required>
        </div>

        <div class="form-group">
            <label for="case_title">Case Title</label>
            <input type="text" id="case_title" name="case_title" required>
        </div>

        <div class="form-group">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id" required>
                <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['id']; ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="lawyer_id">Lawyer</label>
            <select id="lawyer_id" name="lawyer_id" required>
                <option value="">Select Lawyer</option>
                <?php foreach ($lawyers as $lawyer): ?>
                    <option value="<?php echo $lawyer['id']; ?>">
                        <?php echo htmlspecialchars($lawyer['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="case_type">Case Type</label>
            <select id="case_type" name="case_type" required>
                <option value="">Select Type</option>
                <option value="civil">Civil</option>
                <option value="criminal">Criminal</option>
                <option value="corporate">Corporate</option>
                <option value="family">Family</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="filing_date">Filing Date</label>
            <input type="date" id="filing_date" name="filing_date" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Case</button>
            <a href="manage_cases.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
// Set default filing date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('filing_date').value = today;
    
    // Auto-generate case number
    const year = new Date().getFullYear();
    const month = String(new Date().getMonth() + 1).padStart(2, '0');
    const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
    document.getElementById('case_number').value = `${year}${month}-${random}`;
});

// Form submission
document.getElementById('addCaseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            // Convert client_id and lawyer_id to integers
            if (key === 'client_id' || key === 'lawyer_id') {
                data[key] = parseInt(value);
            } else {
                data[key] = value;
            }
        });
        
        // Debug log
        console.log('Sending data:', data);
        
        const response = await fetch('../controllers/case_controller.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'add',
                ...data
            }),
            credentials: 'include' // Important for sending cookies/session
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Server response:', result);
        
        if (result.success) {
            alert('Case added successfully');
            window.location.href = 'manage_cases.php';
        } else {
            throw new Error(result.message || 'Failed to add case');
        }
    } catch (error) {
        console.error('Error details:', error);
        alert('An error occurred: ' + error.message);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
