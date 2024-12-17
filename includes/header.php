<?php
session_start();
require_once '../db/config.php';

// Check admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
                <p>Case Management System</p>
            </div>
            <nav class="menu">
                <a href="admin_dashboard.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="manage_cases.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) === 'manage_cases.php' ? 'active' : ''; ?>">
                    <i class="fas fa-briefcase"></i> Cases
                </a>
                <a href="manage_lawyers.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) === 'manage_lawyers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Lawyers
                </a>
                <a href="manage_documents.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) === 'manage_documents.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Documents
                </a>
                <a href="manage_hearings.php" class="nav-btn <?php echo basename($_SERVER['PHP_SELF']) === 'manage_hearings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-gavel"></i> Hearings
                </a>
                <a href="logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        <main class="main-content">
    </div>
</body>
</html>
