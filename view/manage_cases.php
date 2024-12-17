<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Initialize search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Build query with search and filters
$query = "SELECT 
    c.*, 
    l.name as lawyer_name,
    CONCAT(cl.first_name, ' ', cl.last_name) as client_name
    FROM cases c
    LEFT JOIN lawyers l ON c.lawyer_id = l.id
    LEFT JOIN clients cl ON c.client_id = cl.id
    WHERE 1=1";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (c.case_number LIKE ? OR c.case_title LIKE ? OR l.name LIKE ? OR CONCAT(cl.first_name, ' ', cl.last_name) LIKE ?)";
}
if (!empty($status_filter)) {
    $query .= " AND c.case_status = ?";
}
if (!empty($type_filter)) {
    $query .= " AND c.case_type = ?";
}

$query .= " ORDER BY c.created_at DESC";

// Prepare and execute query
try {
    $stmt = $conn->prepare($query);
    
    // Bind parameters if they exist
    if (!empty($search)) {
        if (!empty($status_filter) && !empty($type_filter)) {
            $stmt->bind_param("ssssss", $search, $search, $search, $search, $status_filter, $type_filter);
        } elseif (!empty($status_filter)) {
            $stmt->bind_param("sssss", $search, $search, $search, $search, $status_filter);
        } elseif (!empty($type_filter)) {
            $stmt->bind_param("sssss", $search, $search, $search, $search, $type_filter);
        } else {
            $stmt->bind_param("ssss", $search, $search, $search, $search);
        }
    } elseif (!empty($status_filter) && !empty($type_filter)) {
        $stmt->bind_param("ss", $status_filter, $type_filter);
    } elseif (!empty($status_filter)) {
        $stmt->bind_param("s", $status_filter);
    } elseif (!empty($type_filter)) {
        $stmt->bind_param("s", $type_filter);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $cases = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Query failed: " . $e->getMessage());
    $cases = [];
}
?>

<div class="container">
    <h1>Manage Cases</h1>
    
    <!-- Search and Filter Form -->
    <div class="search-filters">
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search cases..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="form-group">
                <select name="type">
                    <option value="">All Types</option>
                    <option value="civil" <?php echo $type_filter === 'civil' ? 'selected' : ''; ?>>Civil</option>
                    <option value="criminal" <?php echo $type_filter === 'criminal' ? 'selected' : ''; ?>>Criminal</option>
                    <option value="corporate" <?php echo $type_filter === 'corporate' ? 'selected' : ''; ?>>Corporate</option>
                    <option value="family" <?php echo $type_filter === 'family' ? 'selected' : ''; ?>>Family</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Search</button>
            <a href="manage_cases.php" class="btn-secondary">Clear</a>
        </form>
    </div>

    <div class="actions">
        <a href="add_case.php" class="btn-primary">Add New Case</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Title</th>
                <th>Client</th>
                <th>Lawyer</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($cases)): ?>
                <tr>
                    <td colspan="7">No cases found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($cases as $case): ?>
                <tr id="case-row-<?php echo $case['id']; ?>">
                    <td><?php echo htmlspecialchars($case['case_number'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($case['case_title'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($case['client_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($case['lawyer_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($case['case_type'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($case['case_status'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="edit_case.php?id=<?php echo $case['id']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteCase(<?php echo $case['id']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add these styles to your admin_dashboard.css -->
<style>
.search-filters {
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-form {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.search-form .form-group {
    flex: 1;
    min-width: 200px;
}

.search-form input,
.search-form select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.actions {
    margin: 20px 0;
    display: flex;
    justify-content: flex-end;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #7f8c8d;
}
</style>

<script>
function deleteCase(id) {
    if (confirm('Are you sure you want to delete this case?')) {
        fetch('manage_cases.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`case-row-${id}`);
                if (row) {
                    row.remove();
                }
                alert('Case deleted successfully');
            } else {
                alert(data.message || 'Failed to delete case');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the case');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>