/**
 * Get initials from a name
 */
function getInitials(name) {
    if (!name) return 'S';
    return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2);
}

/**
 * Load user information from API
 */
async function loadUserInfo() {
    try {
        const response = await fetch('/api/user', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (response.status === 401) {
            console.warn('API authentication failed, using fallback');
            return;
        }

        if (!response.ok) {
            console.warn('Failed to fetch user info:', response.status);
            return;
        }

        const user = await response.json();

        // Safely update elements only if they exist
        const userNameEl = document.getElementById('userName');
        const profileNameEl = document.getElementById('profileName');
        const userInfoEl = document.getElementById('userInfo');
        const profileAvatarEl = document.getElementById('profileAvatar');

        if (userNameEl && user.full_name) {
            userNameEl.textContent = user.full_name;
        }
        if (profileNameEl && user.full_name) {
            profileNameEl.textContent = user.full_name;
        }
        if (userInfoEl) {
            userInfoEl.textContent = user.student_id ? `ID: ${user.student_id}` : user.email;
        }
        if (profileAvatarEl && user.full_name) {
            profileAvatarEl.textContent = getInitials(user.full_name);
        }
    } catch (error) {
        console.error('Failed to load user:', error);
    }
}

/**
 * Load student request statistics
 */
async function loadStatistics() {
    try {
        const response = await fetch('/api/tor-requests', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to load statistics: ${response.status}`);
        }

        const requests = await response.json();

        const total = requests.length;
        const pending = requests.filter(r => r.status === 'pending').length;
        const processing = requests.filter(r => r.status === 'processing').length;
        const completed = requests.filter(r => r.status === 'approved' || r.status === 'ready_for_pickup').length;

        document.getElementById('totalRequests').textContent = total;
        document.getElementById('pendingRequests').textContent = pending;
        document.getElementById('processingRequests').textContent = processing;
        document.getElementById('completedRequests').textContent = completed;

        document.getElementById('statsLoading').style.display = 'none';
        document.getElementById('statsGrid').style.display = 'grid';
    } catch (error) {
        console.error('Failed to load statistics:', error);
        document.getElementById('statsLoading').textContent = 'No requests yet. Start by creating a new one!';
    }
}

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

/**
 * Handle user logout
 */
window.handleLogout = async function () {
    try {
        await fetch('/api/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    }
};

// Load data on page load
loadUserInfo();
loadStatistics();
