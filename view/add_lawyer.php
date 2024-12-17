<?php
require_once '../includes/header.php';
require_once '../db/config.php';
?>

<div class="container">
    <h1>Add New Lawyer</h1>
    
    <form id="addLawyerForm" method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
        </div>

        <div class="form-group">
            <label for="specialization">Specialization</label>
            <select id="specialization" name="specialization" required>
                <option value="">Select Specialization</option>
                <option value="civil">Civil Law</option>
                <option value="criminal">Criminal Law</option>
                <option value="corporate">Corporate Law</option>
                <option value="family">Family Law</option>
                <option value="tax">Tax Law</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="bar_number">Bar Number</label>
            <input type="text" id="bar_number" name="bar_number" required>
        </div>

        <div class="form-group">
            <label for="years_experience">Years of Experience</label>
            <input type="number" id="years_experience" name="years_experience" min="0" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Add Lawyer</button>
            <a href="manage_lawyers.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<style>
.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.btn-primary {
    background: #3498db;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-cancel {
    background: #e74c3c;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
}

.btn-cancel:hover {
    background: #c0392b;
}

.error-message {
    color: #e74c3c;
    font-size: 14px;
    margin-top: 5px;
}
</style>

<script>
document.getElementById('addLawyerForm').addEventListener('submit', async function(e) {
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
                action: 'add',
                ...data
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        
        if (result.success) {
            alert('Lawyer added successfully');
            window.location.href = 'manage_lawyers.php';
        } else {
            throw new Error(result.message || 'Failed to add lawyer');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while adding the lawyer');
    }
});

// Phone number validation
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
});

// Bar number validation
document.getElementById('bar_number').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
});
</script>

<?php require_once '../includes/footer.php'; ?>