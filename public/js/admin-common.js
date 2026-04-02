// Set up axios with CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.post['X-CSRF-TOKEN'] = csrfToken;
}

/**
 * Handle logout
 */
async function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await axios.post('/logout', {}, {
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
            window.location.href = '/';
        } catch (error) {
            console.error('Logout error:', error);
            alert('Error logging out. Please try again.');
        }
    }
}

/**
 * Show error message
 */
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message danger';
    errorDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(errorDiv, container.firstChild);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

/**
 * Show success message
 */
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'error-message';
    successDiv.style.background = '#d4edda';
    successDiv.style.color = '#155724';
    successDiv.style.borderLeftColor = '#c3e6cb';
    successDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(successDiv, container.firstChild);
        setTimeout(() => successDiv.remove(), 5000);
    }
}

/**
 * Format date to readable format
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Truncate text to specified length
 */
function truncateText(text, maxLength = 50) {
    if (text.length > maxLength) {
        return text.substring(0, maxLength) + '...';
    }
    return text;
}

/**
 * Update user profile name
 */
async function updateProfileName() {
    try {
        const response = await axios.get('/api/user');
        const user = response.data;
        
        const profileName = document.getElementById('profileName');
        if (profileName) {
            profileName.textContent = user.name || 'Admin';
        }
        
        const userInfo = document.getElementById('userInfo');
        if (userInfo) {
            userInfo.textContent = user.email || '';
        }
    } catch (error) {
        console.error('Error fetching user profile:', error);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    updateProfileName();
});
