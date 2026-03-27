// Configure axios
const api = axios.create({
    baseURL: window.location.origin,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    }
});

// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    api.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

// Add auth token to requests if available
api.interceptors.request.use(config => {
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
        config.headers.Authorization = `Bearer ${authToken}`;
    }
    return config;
});

// Handle response errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

let requests = [];

/**
 * Load user information from API
 */
async function loadUserInfo() {
    try {
        const response = await api.get('/api/user');
        const user = response.data;
        document.getElementById('profileName').textContent = user.full_name;
        document.getElementById('profileAvatar').textContent = user.full_name.charAt(0).toUpperCase();
    } catch (error) {
        console.error('Failed to load user:', error);
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
    }
}

/**
 * Load all TOR requests
 */
async function loadRequests() {
    try {
        const response = await api.get('/api/tor-requests');
        requests = response.data;
        displayRequests();
    } catch (error) {
        console.error('Failed to load requests:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
}

/**
 * Display requests in table or empty state
 */
function displayRequests() {
    const loading = document.getElementById('loading');
    const emptyState = document.getElementById('emptyState');
    const table = document.getElementById('requestsTable');
    const tbody = document.getElementById('requestsBody');

    loading.style.display = 'none';

    if (requests.length === 0) {
        emptyState.style.display = 'block';
        table.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        table.style.display = 'table';
        tbody.innerHTML = requests.map(req => `
            <tr>
                <td>${req.student_id}</td>
                <td>${req.course}</td>
                <td><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></td>
                <td>${new Date(req.created_at).toLocaleDateString()}</td>
                <td class="actions">
                    <button class="btn-view" onclick="viewDetails('${req.id}')">View</button>
                    ${req.status === 'pending' ? `<button class="btn-delete" onclick="deleteRequest('${req.id}')">Delete</button>` : ''}
                </td>
            </tr>
        `).join('');
    }
}

/**
 * Format status text for display
 */
function formatStatus(status) {
    const statusMap = {
        'pending': 'Pending',
        'processing': 'Processing',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'ready_for_pickup': 'Ready for Pickup'
    };
    return statusMap[status] || status;
}

/**
 * View full request details in modal
 */
window.viewDetails = function (id) {
    const req = requests.find(r => r.id == id);
    if (!req) return;

    const content = `
        <div class="detail-row">
            <div class="detail-label">Full Name:</div>
            <div class="detail-value">${req.full_name}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date of Birth:</div>
            <div class="detail-value">${new Date(req.birthdate).toLocaleDateString()}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Place of Birth:</div>
            <div class="detail-value">${req.birthplace}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Student ID:</div>
            <div class="detail-value">${req.student_id}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Course:</div>
            <div class="detail-value">${req.course}</div>
        </div>
        ${req.degree ? `<div class="detail-row">
            <div class="detail-label">Degree:</div>
            <div class="detail-value">${req.degree}</div>
        </div>` : ''}
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
 * Close details modal
 */
window.closeModal = function () {
    document.getElementById('detailsModal').classList.remove('show');
};

/**
 * Delete a TOR request
 */
window.deleteRequest = async function (id) {
    if (!confirm('Are you sure you want to delete this request?')) return;

    try {
        await api.delete(`/api/tor-requests/${id}`);
        requests = requests.filter(r => r.id != id);
        displayRequests();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to delete request');
    }
};

/**
 * Handle user logout
 */
window.handleLogout = async function () {
    try {
        await api.post('/api/logout');
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    }
};

/**
 * Navigation: Go to dashboard
 */
window.goToDashboard = function () {
    window.location.href = '/dashboard';
};

/**
 * Navigation: Create new request
 */
window.goToCreateRequest = function () {
    window.location.href = '/tor/create';
};

/**
 * Navigation: View all requests
 */
window.goToViewRequests = function () {
    window.location.href = '/tor/requests';
};

// Close modal when clicking outside
document.getElementById('detailsModal').addEventListener('click', function (e) {
    if (e.target === this) {
        window.closeModal();
    }
});

// Load data on page load
loadUserInfo();
loadRequests();
