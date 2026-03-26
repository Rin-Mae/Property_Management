/**
 * Load all TOR requests
 */
let allRequests = [];
let filteredRequests = [];
let currentViewingRequestId = null;

async function loadAllRequests() {
    try {
        const response = await api.get('/api/tor-requests');
        allRequests = response.data;
        filteredRequests = [...allRequests];
        displayRequests();
    } catch (error) {
        console.error('Failed to load requests:', error);
        showEmptyState('Failed to load requests');
    }
}

/**
 * Display requests table
 */
function displayRequests() {
    const loading = document.getElementById('loading');
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('requestsTable');
    const tbody = document.getElementById('requestsBody');

    if (loading) loading.style.display = 'none';

    if (filteredRequests.length === 0) {
        if (emptyState) emptyState.style.display = 'block';
        if (table) table.style.display = 'none';
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (table) table.style.display = 'table';
        
        tbody.innerHTML = filteredRequests.map(req => `
            <tr>
                <td data-label="ID">${req.id}</td>
                <td data-label="Student ID">${req.student_id || '-'}</td>
                <td data-label="Full Name">${req.full_name}</td>
                <td data-label="Course">${req.course}</td>
                <td data-label="Purpose">${req.purpose || '-'}</td>
                <td data-label="Status"><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></td>
                <td data-label="Actions" class="actions">
                    <button class="btn btn-view" onclick="viewTORRequestDetails(${req.id})">View</button>
                </td>
            </tr>
        `).join('');
    }
}

/**
 * View TOR request details in modal
 */
window.viewTORRequestDetails = function (id) {
    const req = allRequests.find(r => r.id == id);
    if (!req) return;

    currentViewingRequestId = id;

    const content = `
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value">${req.full_name}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Student ID:</div>
            <div class="detail-value">${req.student_id || '-'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date of Birth:</div>
            <div class="detail-value">${req.birthdate ? new Date(req.birthdate).toLocaleDateString() : 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Place of Birth:</div>
            <div class="detail-value">${req.birthplace || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Permanent Address:</div>
            <div class="detail-value">${req.permanent_address || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Course:</div>
            <div class="detail-value">${req.course}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Degree:</div>
            <div class="detail-value">${req.degree || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Year of Graduation:</div>
            <div class="detail-value">${req.year_of_graduation || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Purpose:</div>
            <div class="detail-value">${req.purpose || 'N/A'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value"><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Requested Date:</div>
            <div class="detail-value">${new Date(req.created_at).toLocaleDateString()}</div>
        </div>
        ${req.remarks ? `<div class="detail-row">
            <div class="detail-label">Remarks:</div>
            <div class="detail-value">${req.remarks}</div>
        </div>` : ''}
    `;

    document.getElementById('torRequestContent').innerHTML = content;
    document.getElementById('torRequestModal').classList.add('show');
};

/**
 * Close TOR request details modal
 */
window.closeTORRequestModal = function () {
    document.getElementById('torRequestModal').classList.remove('show');
    currentViewingRequestId = null;
};

/**
 * Open edit TOR request modal
 */
window.openEditTORModal = function () {
    closeTORRequestModal();
    const req = allRequests.find(r => r.id == currentViewingRequestId);
    if (!req) return;

    document.getElementById('torStatus').value = req.status;
    document.getElementById('torRemarks').value = req.remarks || '';
    document.getElementById('editTORModal').classList.add('show');
};

/**
 * Close edit TOR request modal
 */
window.closeEditTORModal = function () {
    document.getElementById('editTORModal').classList.remove('show');
};

/**
 * Handle edit TOR request form submit
 */
window.handleEditTORSubmit = async function (event) {
    event.preventDefault();

    if (!currentViewingRequestId) return;

    const formData = {
        status: document.getElementById('torStatus').value,
        remarks: document.getElementById('torRemarks').value,
    };

    try {
        const response = await api.put(`/api/tor-requests/${currentViewingRequestId}`, formData);
        
        // Update local request data
        const index = allRequests.findIndex(r => r.id == currentViewingRequestId);
        if (index !== -1) {
            allRequests[index] = response.data;
            filteredRequests = [...allRequests];
        }

        closeEditTORModal();
        displayRequests();
        showSuccess('TOR request updated successfully');
    } catch (error) {
        console.error('Failed to update request:', error);
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(key => {
                const errorElement = document.getElementById(key + 'Error');
                if (errorElement) {
                    errorElement.textContent = errors[key][0];
                }
            });
        }
    }
};

/**
 * Show success message
 */
function showSuccess(message) {
    // You can implement a toast or alert here
    alert(message);
    loadAllRequests(); // Reload to get latest data
}

/**
 * Apply the filters function - work with existing implementation
 */
window.applyFilters = function () {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const sortInput = document.getElementById('sortInput')?.value || 'created_at_desc';

    let results = allRequests.filter(req => {
        const matchesSearch = !searchValue ||
            req.student_id.toLowerCase().includes(searchValue) ||
            req.full_name.toLowerCase().includes(searchValue) ||
            req.course.toLowerCase().includes(searchValue);

        const matchesStatus = !statusFilter || req.status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    // Apply sorting
    if (sortInput === 'created_at_desc') {
        results.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } else if (sortInput === 'created_at_asc') {
        results.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    } else if (sortInput === 'name_asc') {
        results.sort((a, b) => a.full_name.localeCompare(b.full_name));
    } else if (sortInput === 'name_desc') {
        results.sort((a, b) => b.full_name.localeCompare(a.full_name));
    }

    filteredRequests = results;
    displayRequests();
};

/**
 * Clear all filters
 */
window.clearFilters = function () {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('sortInput').value = 'created_at_desc';
    filteredRequests = [...allRequests];
    displayRequests();
};

/**
 * Show empty state message
 */
function showEmptyState(message) {
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('requestsTable');
    if (emptyState) {
        emptyState.textContent = message;
        emptyState.style.display = 'block';
    }
    if (table) table.style.display = 'none';
}

/**
 * Navigation functions
 */
window.goToDashboard = function () {
    window.location.href = '/admin/dashboard';
};

window.goToPendingRequests = function () {
    window.location.href = '/admin/pending-requests';
};

window.goToProcessingRequests = function () {
    window.location.href = '/admin/processing-requests';
};

/**
 * Setup sidebar active state
 */
function setupSidebarActive() {
    const buttons = document.querySelectorAll('.sidebar-menu button');
    buttons.forEach(button => {
        button.classList.remove('active');
        if (button.textContent.includes('All')) {
            button.classList.add('active');
        }
    });
}

// Setup modal close on outside click
document.addEventListener('click', (e) => {
    const torModal = document.getElementById('torRequestModal');
    const editModal = document.getElementById('editTORModal');
    
    if (e.target === torModal) {
        closeTORRequestModal();
    }
    if (e.target === editModal) {
        closeEditTORModal();
    }
});

// Load data on page load
loadUserInfo();
loadAllRequests();
setupSidebarActive();
