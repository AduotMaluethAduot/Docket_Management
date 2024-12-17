<?php
require_once '../includes/header.php';
?>

<div class="container">
    <div class="error-page">
        <h1>Unauthorized Access</h1>
        <p>You do not have permission to access this page.</p>
        <a href="dashboard.php" class="btn-primary">Return to Dashboard</a>
    </div>
</div>

<style>
.error-page {
    text-align: center;
    padding: 50px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.error-page h1 {
    color: #e74c3c;
    margin-bottom: 20px;
}

.error-page p {
    color: #666;
    margin-bottom: 30px;
}
</style>

<?php require_once '../includes/footer.php'; ?> 