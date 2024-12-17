<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Get statistics
$stats = [];

// Total Lawyers Count
$lawyersQuery = $conn->query("SELECT COUNT(*) as total_lawyers FROM lawyers WHERE status = 'active'");
$stats['total_lawyers'] = $lawyersQuery ? $lawyersQuery->fetch_assoc()['total_lawyers'] : 0;

// Total Cases Count
$casesQuery = $conn->query("SELECT COUNT(*) as total_cases FROM cases");
$stats['total_cases'] = $casesQuery ? $casesQuery->fetch_assoc()['total_cases'] : 0;

// Active Cases Count
$activeCasesQuery = $conn->query("SELECT COUNT(*) as active_cases FROM cases WHERE case_status = 'active'");
$stats['active_cases'] = $activeCasesQuery ? $activeCasesQuery->fetch_assoc()['active_cases'] : 0;
?>

<!-- Statistics Cards -->
<div class="stats-container">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-info">
            <h3>Total Lawyers</h3>
            <p><?php echo $stats['total_lawyers']; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-briefcase"></i>
        <div class="stat-info">
            <h3>Total Cases</h3>
            <p><?php echo $stats['total_cases']; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <i class="fas fa-balance-scale"></i>
        <div class="stat-info">
            <h3>Active Cases</h3>
            <p><?php echo $stats['active_cases']; ?></p>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="recent-activities">
    <h2>Recent Activities</h2>
    <div class="activity-list">
        <!-- Recent cases -->
        <div class="activity-card">
            <h3>Latest Cases</h3>
            <table>
                <thead>
                    <tr>
                        <th>Case Number</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentCases = $conn->query("
                        SELECT case_number, case_title, case_status, created_at 
                        FROM cases 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    if ($recentCases) {
                        while ($case = $recentCases->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($case['case_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($case['case_title']) . "</td>";
                            echo "<td>" . htmlspecialchars($case['case_status']) . "</td>";
                            echo "<td>" . date('Y-m-d', strtotime($case['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
