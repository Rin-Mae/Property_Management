/**
 * Load all TOR requests
 */
let allRequests = [];
let filteredRequests = [];

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
                <td>${req.id}</td>
                <td>${req.student_id || '-'}</td>
                <td>${req.full_name}</td>
                <td>${req.course}</td>
                <td>${req.purpose || '-'}</td>
                <td><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></td>
                <td class="actions">
                    <button class="btn btn-view" onclick="viewRequestDetails('${req.id}')">View</button>
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
        ${req.remarks ? `<div class="detail-row">
            <div class="detail-label">Remarks:</div>
            <div class="detail-value">${req.remarks}</div>
        </div>` : ''}
    `;

    document.getElementById('detailsContent').innerHTML = content;
    document.getElementById('detailsModal').classList.add('show');
};

/**
 * Apply search filter
 */
window.applySearch = function () {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const sortBy = document.getElementById('sortBy')?.value || 'date';

    let results = allRequests.filter(req => {
        const matchesSearch = !searchValue ||
            req.student_id.toLowerCase().includes(searchValue) ||
            req.full_name.toLowerCase().includes(searchValue) ||
            req.course.toLowerCase().includes(searchValue);

        const matchesStatus = !statusFilter || req.status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    // Apply sorting
    if (sortBy === 'date') {
        results.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } else if (sortBy === 'status') {
        results.sort((a, b) => a.status.localeCompare(b.status));
    } else if (sortBy === 'student-id') {
        results.sort((a, b) => a.student_id.localeCompare(b.student_id));
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
    document.getElementById('sortBy').value = 'date';
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

/**
 * Setup event listeners for filters
 */
function setupFilterListeners() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortBy = document.getElementById('sortBy');

    if (searchInput) searchInput.addEventListener('input', window.applySearch);
    if (statusFilter) statusFilter.addEventListener('change', window.applySearch);
    if (sortBy) sortBy.addEventListener('change', window.applySearch);
}

// Load data on page load
loadUserInfo();
loadAllRequests();
setupSidebarActive();
setupFilterListeners();
setupModalCloseOnClickOutside('detailsModal');
