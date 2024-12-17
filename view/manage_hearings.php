<?php
require_once '../includes/header.php';
require_once '../db/config.php';

// Initialize search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build query with search and filters
$query = "SELECT 
    h.*,
    c.case_number,
    c.case_title,
    CONCAT(cl.first_name, ' ', cl.last_name) as client_name,
    l.name as lawyer_name
    FROM case_hearings h
    LEFT JOIN cases c ON h.case_id = c.id
    LEFT JOIN clients cl ON c.client_id = cl.id
    LEFT JOIN lawyers l ON c.lawyer_id = l.id
    WHERE 1=1";

if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (c.case_number LIKE ? OR c.case_title LIKE ? OR h.hearing_type LIKE ?)";
}
if (!empty($status_filter)) {
    $query .= " AND h.status = ?";
}
if (!empty($date_filter)) {
    $query .= " AND DATE(h.hearing_date) = ?";
}

$query .= " ORDER BY h.hearing_date ASC";

// Initialize hearings array
$hearings = [];

try {
    // Prepare statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters if they exist
        if (!empty($params)) {
            $types = '';
            $params = [];
            
            if (!empty($search)) {
                $types .= 'sss';
                $params = array_merge($params, [$search, $search, $search]);
            }
            if (!empty($status_filter)) {
                $types .= 's';
                $params[] = $status_filter;
            }
            if (!empty($date_filter)) {
                $types .= 's';
                $params[] = $date_filter;
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
        }

        // Execute and get results
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $hearings = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Query failed: " . $e->getMessage());
}

?>

<div class="container">
    <h1>Manage Hearings</h1>
    
    <!-- Search and Filter Form -->
    <div class="search-filters">
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search hearings..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="postponed" <?php echo $status_filter === 'postponed' ? 'selected' : ''; ?>>Postponed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            <a href="manage_hearings.php" class="btn-secondary">Clear</a>
        </form>
    </div>

    <div class="actions">
        <a href="add_hearing.php" class="btn-primary">Schedule New Hearing</a>
    </div>

    <?php if (empty($hearings)): ?>
        <div class="alert alert-info">
            <p>No hearings found.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Case</th>
                    <th>Client</th>
                    <th>Lawyer</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hearings as $hearing): ?>
                <tr id="hearing-row-<?php echo $hearing['id']; ?>">
                    <td><?php echo date('Y-m-d H:i', strtotime($hearing['hearing_date'])); ?></td>
                    <td>
                        <?php echo htmlspecialchars($hearing['case_number'] . ' - ' . $hearing['case_title']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($hearing['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($hearing['lawyer_name']); ?></td>
                    <td><?php echo htmlspecialchars($hearing['hearing_type']); ?></td>
                    <td><?php echo htmlspecialchars($hearing['location']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $hearing['status']; ?>">
                            <?php echo ucfirst(htmlspecialchars($hearing['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_hearing.php?id=<?php echo $hearing['id']; ?>" class="btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteHearing(<?php echo $hearing['id']; ?>)" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
}

.status-badge.scheduled {
    background-color: #3498db;
    color: white;
}

.status-badge.completed {
    background-color: #27ae60;
    color: white;
}

.status-badge.postponed {
    background-color: #f1c40f;
    color: black;
}

.status-badge.cancelled {
    background-color: #e74c3c;
    color: white;
}
</style>

<script>
function deleteHearing(id) {
    if (confirm('Are you sure you want to delete this hearing?')) {
        fetch('../controllers/hearing_controller.php', {
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
                const row = document.getElementById(`hearing-row-${id}`);
                if (row) {
                    row.remove();
                }
                alert('Hearing deleted successfully');
            } else {
                throw new Error(data.message || 'Failed to delete hearing');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'An error occurred while deleting the hearing');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 