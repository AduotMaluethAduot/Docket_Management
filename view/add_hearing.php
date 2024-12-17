<?php
require_once '../includes/header.php';
require_once '../db/config.php';
?>

<div class="container">
    <h1>Schedule New Hearing</h1>
    
    <form id="addHearingForm" method="POST">
        <div class="form-group">
            <label for="hearing_date">Date & Time</label>
            <input type="datetime-local" id="hearing_date" name="hearing_date" required>
        </div>

        <div class="form-group">
            <label for="hearing_type">Hearing Type</label>
            <select id="hearing_type" name="hearing_type" required>
                <option value="">Select Type</option>
                <option value="Initial Hearing">Initial Hearing</option>
                <option value="Pre-Trial Conference">Pre-Trial Conference</option>
                <option value="Trial">Trial</option>
                <option value="Mediation">Mediation</option>
                <option value="Settlement Conference">Settlement Conference</option>
                <option value="Motion Hearing">Motion Hearing</option>
                <option value="Status Conference">Status Conference</option>
                <option value="Sentencing">Sentencing</option>
                <option value="Appeal Hearing">Appeal Hearing</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required 
                   placeholder="e.g., Room 302, County Courthouse">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="postponed">Postponed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4" 
                      placeholder="Enter any additional notes or details about the hearing"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Schedule Hearing</button>
            <a href="manage_hearings.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const offset = today.getTimezoneOffset();
    today.setMinutes(today.getMinutes() - offset);
    
    const dateInput = document.getElementById('hearing_date');
    dateInput.min = today.toISOString().slice(0, 16);
    
    // Set default time to next hour
    const nextHour = new Date();
    nextHour.setHours(nextHour.getHours() + 1, 0, 0, 0);
    dateInput.value = nextHour.toISOString().slice(0, 16);
});

// Form submission
document.getElementById('addHearingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    try {
        const response = await fetch('../controllers/hearing_controller.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'add',
                ...data
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            alert('Hearing scheduled successfully');
            window.location.href = 'manage_hearings.php';
        } else {
            throw new Error(result.message || 'Failed to schedule hearing');
        }
    } catch (error) {
        console.error('Error details:', error);
        alert('An error occurred while scheduling the hearing. Please check the console for details.');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
