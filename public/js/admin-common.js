/**
 * Configure axios with CSRF token and auth
 */
const api = axios.create({
    baseURL: window.location.origin,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    }
});

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    api.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

api.interceptors.request.use(config => {
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
        config.headers.Authorization = `Bearer ${authToken}`;
    }
    return config;
});

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

/**
 * Get initials from a name
 */
function getInitials(name) {
    if (!name) return 'A';
    return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2);
}

/**
 * Load admin user information
 */
async function loadUserInfo() {
    try {
        const response = await api.get('/api/user');
        const user = response.data;
        
        const profileNameEl = document.getElementById('profileName');
        const profileAvatarEl = document.getElementById('profileAvatar');
        const userInfoEl = document.getElementById('userInfo');
        
        // Construct full name from first_name and last_name
        const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim();
        
        if (profileNameEl && fullName) {
            profileNameEl.textContent = fullName;
        }
        if (profileAvatarEl && fullName) {
            profileAvatarEl.textContent = getInitials(fullName);
        }
        
        // Update user info with role
        if (userInfoEl) {
            userInfoEl.textContent = user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User';
        }
    } catch (error) {
        console.error('Failed to load user:', error);
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
    }
}

/**
 * Format request status for display
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
 * Handle admin logout
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
 * Close modal dialog
 */
window.closeModal = function (modalId = 'detailsModal') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
};

/**
 * Setup modal click-outside closing
 */
function setupModalCloseOnClickOutside(modalId = 'detailsModal') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                window.closeModal(modalId);
            }
        });
    }
}
