/**
 * Load processing TOR requests
 */
let allRequests = [];
let filteredRequests = [];

async function loadProcessingRequests() {
    try {
        const response = await api.get('/api/tor-requests');
        allRequests = response.data.filter(r => r.status === 'processing' || r.status === 'approved');
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
                <td>${req.student_id || '-'}</td>
                <td>${req.full_name}</td>
                <td>${req.course}</td>
                <td>${req.purpose || '-'}</td>
                <td>${req.number_of_copies || 1}</td>
                <td><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></td>
                <td class="actions">
                    <button class="btn btn-view" onclick="viewRequestDetails('${req.id}')">View</button>
                    <button class="btn btn-release" onclick="releaseRequest('${req.id}')">Mark as Ready</button>
                </td>
            </tr>
        `).join('');
    }
}

/**
 * View request details in modal
 */
window.viewRequestDetails = function (id) {
    const req = allRequests.find(r => r.id == id);
    if (!req) return;

    const content = `
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value">${req.full_name}</div>
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
            <div class="detail-label">Student ID:</div>
            <div class="detail-value">${req.student_id}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Course:</div>
            <div class="detail-value">${req.course}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Number of Copies:</div>
            <div class="detail-value">${req.number_of_copies}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value"><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Requested Date:</div>
            <div class="detail-value">${new Date(req.created_at).toLocaleDateString()}</div>
        </div>
    `;

    document.getElementById('detailsContent').innerHTML = content;
    document.getElementById('detailsModal').classList.add('show');
};

/**
 * Mark request as ready for pickup
 */
window.releaseRequest = async function (id) {
    if (!confirm('Are you sure you want to mark this request as ready for pickup?')) return;

    try {
        await api.patch(`/api/tor-requests/${id}`, { status: 'ready_for_pickup' });
        allRequests = allRequests.filter(r => r.id != id);
        filteredRequests = filteredRequests.filter(r => r.id != id);
        displayRequests();
        window.closeModal();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to update request');
    }
};

/**
 * Apply search filter
 */
window.applySearch = function () {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    filteredRequests = allRequests.filter(req =>
        req.student_id.toLowerCase().includes(searchValue) ||
        req.full_name.toLowerCase().includes(searchValue) ||
        req.course.toLowerCase().includes(searchValue)
    );
    displayRequests();
};

/**
 * Clear search filter
 */
window.clearSearch = function () {
    document.getElementById('searchInput').value = '';
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

window.goToAllRequests = function () {
    window.location.href = '/admin/all-requests';
};

/**
 * Setup sidebar active state
 */
function setupSidebarActive() {
    const buttons = document.querySelectorAll('.sidebar-menu button');
    buttons.forEach(button => {
        button.classList.remove('active');
        if (button.textContent.includes('Processing')) {
            button.classList.add('active');
        }
    });
}

// Load data on page load
loadUserInfo();
loadProcessingRequests();
setupSidebarActive();
setupModalCloseOnClickOutside('detailsModal');
