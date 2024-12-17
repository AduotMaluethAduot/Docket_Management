<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Get lawyer ID from URL
$lawyer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch lawyer details
$stmt = $conn->prepare("SELECT * FROM lawyers WHERE id = ?");
$stmt->bind_param("i", $lawyer_id);
$stmt->execute();
$result = $stmt->get_result();
$lawyer = $result->fetch_assoc();

// Redirect if lawyer not found
if (!$lawyer) {
    header("Location: manage_lawyers.php");
    exit();
}
?>

<div class="container">
    <h1>Edit Lawyer</h1>
    
    <form id="editLawyerForm" method="POST">
        <input type="hidden" name="id" value="<?php echo $lawyer['id']; ?>">
        
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($lawyer['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($lawyer['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($lawyer['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label for="specialization">Specialization</label>
            <select id="specialization" name="specialization" required>
                <option value="">Select Specialization</option>
                <option value="civil" <?php echo $lawyer['specialization'] === 'civil' ? 'selected' : ''; ?>>Civil Law</option>
                <option value="criminal" <?php echo $lawyer['specialization'] === 'criminal' ? 'selected' : ''; ?>>Criminal Law</option>
                <option value="corporate" <?php echo $lawyer['specialization'] === 'corporate' ? 'selected' : ''; ?>>Corporate Law</option>
                <option value="family" <?php echo $lawyer['specialization'] === 'family' ? 'selected' : ''; ?>>Family Law</option>
                <option value="tax" <?php echo $lawyer['specialization'] === 'tax' ? 'selected' : ''; ?>>Tax Law</option>
                <option value="other" <?php echo $lawyer['specialization'] === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="bar_number">Bar Number</label>
            <input type="text" id="bar_number" name="bar_number" value="<?php echo htmlspecialchars($lawyer['bar_number']); ?>" required>
        </div>

        <div class="form-group">
            <label for="years_experience">Years of Experience</label>
            <input type="number" id="years_experience" name="years_experience" value="<?php echo htmlspecialchars($lawyer['years_experience']); ?>" min="0" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="active" <?php echo $lawyer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $lawyer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($lawyer['notes']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Update Lawyer</button>
            <a href="manage_lawyers.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('editLawyerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    try {
        const response = await fetch('../controllers/lawyer_controller.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                ...data
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        
        if (result.success) {
            alert('Lawyer updated successfully');
            window.location.href = 'manage_lawyers.php';
        } else {
            throw new Error(result.message || 'Failed to update lawyer');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while updating the lawyer');
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
});

// Bar number formatting
document.getElementById('bar_number').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
});
</script>

<?php require_once '../includes/footer.php'; ?>
