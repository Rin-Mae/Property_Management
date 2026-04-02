// Client Management State
let clientManagementState = {
    clients: [],
    currentPage: 1,
    totalPages: 1,
    perPage: 10,
    filteredClients: [],
    editingClientId: null,
    deleteClientId: null,
    searchTerm: ''
};

/**
 * Load all clients
 */
async function loadClients() {
    try {
        const clientsLoading = document.getElementById('clientsLoading');
        const clientsContainer = document.getElementById('clientsContainer');

        if (clientsLoading) {
            clientsLoading.style.display = 'block';
        }
        if (clientsContainer) {
            clientsContainer.style.display = 'none';
        }

        // API call to get clients
        const response = await axios.get('/api/clients');
        clientManagementState.clients = response.data.data || response.data;
        clientManagementState.filteredClients = clientManagementState.clients;

        applyFilters();

        if (clientsLoading) {
            clientsLoading.style.display = 'none';
        }
        if (clientsContainer) {
            clientsContainer.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading clients:', error);
        showAlert('Failed to load clients', 'error');
    }
}

/**
 * Apply search filter
 */
function applyFilters() {
    let filtered = clientManagementState.clients;

    // Apply search filter
    if (clientManagementState.searchTerm) {
        const term = clientManagementState.searchTerm.toLowerCase();
        filtered = filtered.filter(client => {
            const fullName = `${client.first_name} ${client.last_name}`.toLowerCase();
            const email = (client.email || '').toLowerCase();
            const company = (client.company_name || '').toLowerCase();
            return fullName.includes(term) || email.includes(term) || company.includes(term);
        });
    }

    clientManagementState.filteredClients = filtered;
    clientManagementState.currentPage = 1;
    clientManagementState.totalPages = Math.ceil(filtered.length / clientManagementState.perPage);

    updateClientsTable();
}

/**
 * Update clients table display
 */
function updateClientsTable() {
    const tbody = document.getElementById('clientsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    const start = (clientManagementState.currentPage - 1) * clientManagementState.perPage;
    const end = start + clientManagementState.perPage;
    const paginatedClients = clientManagementState.filteredClients.slice(start, end);

    if (paginatedClients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No clients found</td></tr>';
        document.getElementById('clientsPagination').style.display = 'none';
        return;
    }

    paginatedClients.forEach(client => {
        const fullName = `${client.first_name}${client.middle_name ? ' ' + client.middle_name : ''} ${client.last_name}`;
        const status = client.deleted_at ? 'Inactive' : 'Active';
        const statusClass = client.deleted_at ? 'inactive' : 'active';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${fullName}</td>
            <td>${client.email}</td>
            <td>${client.contact_number || '-'}</td>
            <td>${client.address || '-'}</td>
            <td><span class="status-badge ${statusClass}">${status}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon edit" onclick="openEditClientModal(${client.id})" title="Edit">
                        ✎ Edit
                    </button>
                    <button class="btn-icon delete" onclick="openDeleteModal(${client.id})" title="Delete">
                        🗑 Delete
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePaginationControls();
}

/**
 * Update pagination controls
 */
function updatePaginationControls() {
    const pageInfo = document.getElementById('clientsPageInfo');
    const prevBtn = document.getElementById('clientsPrevBtn');
    const nextBtn = document.getElementById('clientsNextBtn');
    const pagination = document.getElementById('clientsPagination');

    if (clientManagementState.totalPages > 1) {
        if (pagination) {
            pagination.style.display = 'flex';
        }
    } else {
        if (pagination) {
            pagination.style.display = 'none';
        }
    }

    if (pageInfo) {
        pageInfo.textContent = `Page ${clientManagementState.currentPage} of ${clientManagementState.totalPages}`;
    }

    if (prevBtn) {
        prevBtn.disabled = clientManagementState.currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = clientManagementState.currentPage >= clientManagementState.totalPages;
    }
}

/**
 * Go to previous page
 */
function previousClientsPage() {
    if (clientManagementState.currentPage > 1) {
        clientManagementState.currentPage--;
        updateClientsTable();
    }
}

/**
 * Go to next page
 */
function nextClientsPage() {
    if (clientManagementState.currentPage < clientManagementState.totalPages) {
        clientManagementState.currentPage++;
        updateClientsTable();
    }
}

/**
 * Prevent client creation - editing only
 */
function openCreateClientModal() {
    showAlert('Client creation is disabled. You can only view and edit existing clients.', 'warning');
}

/**
 * Open edit client modal
 */
async function openEditClientModal(clientId) {
    try {
        const response = await axios.get(`/api/clients/${clientId}`);
        const client = response.data;

        clientManagementState.editingClientId = clientId;
        document.getElementById('clientModalTitle').textContent = 'Edit Client';
        document.getElementById('submitBtn').textContent = 'Update Client';
        document.getElementById('clientId').value = client.id;
        document.getElementById('firstName').value = client.first_name || '';
        document.getElementById('middleName').value = client.middle_name || '';
        document.getElementById('lastName').value = client.last_name || '';
        document.getElementById('email').value = client.email || '';
        document.getElementById('contactNumber').value = client.contact_number || '';
        document.getElementById('address').value = client.address || '';

        clearFormErrors();
        document.getElementById('clientModal').classList.add('show');
    } catch (error) {
        console.error('Error loading client:', error);
        showAlert('Failed to load client details', 'error');
    }
}

/**
 * Close client modal
 */
function closeClientModal() {
    document.getElementById('clientModal').classList.remove('show');
}

/**
 * Handle client form submission
 */
async function handleClientSubmit(event) {
    event.preventDefault();
    clearFormErrors();

    const clientId = document.getElementById('clientId').value;
    const formData = {
        first_name: document.getElementById('firstName').value,
        middle_name: document.getElementById('middleName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        contact_number: document.getElementById('contactNumber').value,
        address: document.getElementById('address').value,
    };

    const submitBtn = event.target.querySelector('#submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';

    try {
        let response;
        if (clientId) {
            // Update existing client (editing only)
            response = await axios.put(`/api/clients/${clientId}`, formData);
            showAlert('Client updated successfully', 'success');
        } else {
            // Creation not allowed
            showAlert('Client creation is disabled. You can only edit existing clients.', 'warning');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }

        closeClientModal();
        loadClients();
    } catch (error) {
        console.error('Error submitting form:', error);
        console.error('Error response:', error.response);
        
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(`${field}Error`);
                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                    errorElement.classList.add('show');
                }
            });
        } else if (error.response && error.response.data && error.response.data.error) {
            showAlert(error.response.data.error, 'error');
        } else {
            showAlert('Error processing client. Please try again.', 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

/**
 * Open delete confirmation modal
 */
function openDeleteModal(clientId) {
    clientManagementState.deleteClientId = clientId;
    document.getElementById('deleteModal').classList.add('show');
}

/**
 * Close delete modal
 */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    clientManagementState.deleteClientId = null;
}

/**
 * Confirm and delete client
 */
async function confirmDelete() {
    const clientId = clientManagementState.deleteClientId;
    if (!clientId) return;

    try {
        await axios.delete(`/api/clients/${clientId}`);
        showAlert('Client deleted successfully', 'success');
        closeDeleteModal();
        loadClients();
    } catch (error) {
        console.error('Error deleting client:', error);
        showAlert('Failed to delete client', 'error');
    }
}

/**
 * Clear form errors
 */
function clearFormErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
}

/**
 * Show alert message
 */
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    `;

    alertContainer.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

/**
 * Handle search input
 */
function handleSearch(event) {
    clientManagementState.searchTerm = event.target.value;
    applyFilters();
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Set up event listeners
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Load clients on page load
    loadClients();

    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Close modals with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                modal.classList.remove('show');
            });
        }
    });
});
