<?php
include '../db/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_id = $_POST['caseId'];
    $case_title = $_POST['caseTitle'];
    $client_name = $_POST['clientName'];
    $case_type = $_POST['caseType'];
    $case_description = $_POST['caseDescription'];

    $sql = "UPDATE cases SET case_title='$case_title', client_name='$client_name', case_type='$case_type', case_description='$case_description' WHERE id='$case_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Case updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Case</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar code (same as in lawyer_dashboard.php) -->
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header (same as in lawyer_dashboard.php) -->

            <!-- Edit Case Form -->
            <section id="edit_case">
                <h1>Edit Case</h1>
                <form action="edit_case.php" method="POST">
                    <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">

                    <div class="form-group">
                        <label>Client Name</label>
                        <input type="text" name="client_name" value="<?php echo htmlspecialchars($case['client_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Case Type</label>
                        <select name="case_type" required>
                            <option value="Civil Litigation" <?php if ($case['case_type'] == 'Civil Litigation') echo 'selected'; ?>>Civil Litigation</option>
                            <option value="Criminal Defense" <?php if ($case['case_type'] == 'Criminal Defense') echo 'selected'; ?>>Criminal Defense</option>
                            <option value="Family Law" <?php if ($case['case_type'] == 'Family Law') echo 'selected'; ?>>Family Law</option>
                            <option value="Corporate Law" <?php if ($case['case_type'] == 'Corporate Law') echo 'selected'; ?>>Corporate Law</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Case Description</label>
                        <textarea name="case_description" required><?php echo htmlspecialchars($case['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="In Progress" <?php if ($case['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                            <option value="Resolved" <?php if ($case['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
