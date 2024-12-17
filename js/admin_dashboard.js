document.addEventListener('DOMContentLoaded', function() {
    // Initialize statistics counters
    initializeCounters();
    
    // Add active class to current nav item
    highlightCurrentPage();
    
    // Initialize event listeners
    initializeEventListeners();
});

// Function to animate statistics counters
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-info p');
    
    counters.forEach(counter => {
        const target = parseInt(counter.innerText);
        const duration = 1000; // Animation duration in milliseconds
        const step = target / (duration / 16); // 60 FPS
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.innerText = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target;
            }
        };
        
        updateCounter();
    });
}

// Function to highlight current page in navigation
function highlightCurrentPage() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-btn');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Initialize all event listeners
function initializeEventListeners() {
    // Add event listeners for action buttons
    setupActionButtons();
    
    // Add event listeners for table sorting
    setupTableSorting();
    
    // Add event listeners for search functionality
    setupSearch();
}

// Setup action buttons (edit, delete, etc.)
function setupActionButtons() {
    // Edit buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            // Handle edit action
            window.location.href = `edit_${getEntityType()}.php?id=${id}`;
        });
    });

    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            if (confirm('Are you sure you want to delete this item?')) {
                deleteItem(id);
            }
        });
    });
}

// Setup table sorting functionality
function setupTableSorting() {
    document.querySelectorAll('th[data-sort]').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.querySelector(`td[data-${column}]`).textContent;
                const bValue = b.querySelector(`td[data-${column}]`).textContent;
                return aValue.localeCompare(bValue);
            });
            
            // Toggle sort direction
            if (this.classList.contains('sort-asc')) {
                rows.reverse();
                this.classList.remove('sort-asc');
                this.classList.add('sort-desc');
            } else {
                this.classList.remove('sort-desc');
                this.classList.add('sort-asc');
            }
            
            // Update table
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}

// Setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
}

// Helper function to get current entity type (lawyers, cases, documents)
function getEntityType() {
    const path = window.location.pathname;
    if (path.includes('lawyers')) return 'lawyer';
    if (path.includes('cases')) return 'case';
    if (path.includes('documents')) return 'document';
    return '';
}

// Function to handle item deletion
function deleteItem(id) {
    const entityType = getEntityType();
    
    fetch(`../controllers/${entityType}_controller.php`, {
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
            // Remove the row from the table
            document.querySelector(`tr[data-id="${id}"]`).remove();
            showNotification('Item deleted successfully', 'success');
        } else {
            showNotification(data.message || 'Failed to delete item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 5px;
        color: white;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }
    
    .notification.success {
        background-color: #2ecc71;
    }
    
    .notification.error {
        background-color: #e74c3c;
    }
    
    .notification.info {
        background-color: #3498db;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
