<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Initialize search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with search and filters
$query = "SELECT l.*, COUNT(c.id) as case_count 
          FROM lawyers l 
          LEFT JOIN cases c ON l.id = c.lawyer_id 
          WHERE 1=1";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)";
}
if (!empty($status_filter)) {
    $query .= " AND l.status = ?";
}

$query .= " GROUP BY l.id ORDER BY l.name ASC";

// Prepare and execute query
try {
    $stmt = $conn->prepare($query);
    
    // Bind parameters if they exist
    if (!empty($search) && !empty($status_filter)) {
        $stmt->bind_param("ssss", $search, $search, $search, $status_filter);
    } elseif (!empty($search)) {
        $stmt->bind_param("sss", $search, $search, $search);
    } elseif (!empty($status_filter)) {
        $stmt->bind_param("s", $status_filter);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $lawyers = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Query failed: " . $e->getMessage());
    $lawyers = [];
}
?>

<div class="container">
    <h1>Manage Lawyers</h1>
    
    <!-- Search and Filter Form -->
    <div class="search-filters">
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Search</button>
            <a href="manage_lawyers.php" class="btn-secondary">Clear</a>
        </form>
    </div>

    <div class="actions">
        <a href="add_lawyer.php" class="btn-primary">Add New Lawyer</a>
    </div>
    
    <?php if (empty($lawyers)): ?>
        <div class="alert alert-info">
            <p>No lawyers found.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Cases</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lawyers as $lawyer): ?>
                <tr id="lawyer-row-<?php echo $lawyer['id']; ?>">
                    <td><?php echo htmlspecialchars($lawyer['name']); ?></td>
                    <td><?php echo htmlspecialchars($lawyer['email']); ?></td>
                    <td><?php echo htmlspecialchars($lawyer['phone']); ?></td>
                    <td><?php echo $lawyer['case_count']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $lawyer['status']; ?>">
                            <?php echo ucfirst(htmlspecialchars($lawyer['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_lawyer.php?id=<?php echo $lawyer['id']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteLawyer(<?php echo $lawyer['id']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Add these additional styles -->
<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
}

.status-badge.active {
    background-color: #27ae60;
    color: white;
}

.status-badge.inactive {
    background-color: #e74c3c;
    color: white;
}
</style>

<script>
function deleteLawyer(id) {
    if (confirm('Are you sure you want to delete this lawyer? All associated cases will have their lawyer reference removed.')) {
        fetch('manage_lawyers.php', {
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
                const row = document.getElementById(`lawyer-row-${id}`);
                if (row) {
                    row.remove();
                }
                alert(data.message);
            } else {
                alert(data.message || 'Failed to delete lawyer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the lawyer');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>