// Set up axios with CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.post['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.put['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.patch['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.delete['X-CSRF-TOKEN'] = csrfToken;
    console.log('CSRF Token configured for axios');
}

// ========== LOADER SYSTEM ==========

/**
 * Show full page loader
 */
function showLoader(message = 'Loading...') {
    let loader = document.getElementById('pageLoader');
    
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'pageLoader';
        loader.innerHTML = `
            <div class="loader-overlay">
                <div class="loader-content">
                    <div class="spinner"></div>
                    <p id="loaderMessage">${message}</p>
                </div>
            </div>
        `;
        document.body.appendChild(loader);
        
        // Add loader styles
        const style = document.createElement('style');
        style.textContent = `
            #pageLoader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            
            #pageLoader.active {
                display: flex;
            }
            
            .loader-overlay {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .loader-content {
                background: white;
                padding: 40px;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            }
            
            .spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #0f6b0f;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            #loaderMessage {
                margin: 0;
                color: #333;
                font-size: 16px;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.getElementById('loaderMessage').textContent = message;
    loader.classList.add('active');
}

/**
 * Hide page loader
 */
function hideLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.remove('active');
    }
}

/**
 * Show loader on button
 */
function showButtonLoader(buttonElement, originalText = null) {
    if (!buttonElement) return;
    
    buttonElement.disabled = true;
    if (!buttonElement.dataset.originalText) {
        buttonElement.dataset.originalText = originalText || buttonElement.innerHTML;
    }
    buttonElement.innerHTML = '<span class="btn-loader"></span> Processing...';
}

/**
 * Hide loader on button
 */
function hideButtonLoader(buttonElement) {
    if (!buttonElement) return;
    
    buttonElement.disabled = false;
    buttonElement.innerHTML = buttonElement.dataset.originalText || 'Update';
}

// ========== UNIVERSAL MODAL ALERT SYSTEM ==========

/**
 * Show modal alert
 */
function showModalAlert(message, type = 'info', title = null) {
    let modal = document.getElementById('universalAlertModal');
    
    if (!modal) {
        // Create modal if it doesn't exist
        modal = document.createElement('div');
        modal.id = 'universalAlertModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header">
                    <h2 id="alertModalTitle">Alert</h2>
                    <button class="modal-close" onclick="closeModalAlert()">×</button>
                </div>
                <div class="modal-body">
                    <p id="alertModalMessage"></p>
                </div>
                <div class="modal-footer" style="justify-content: flex-end;">
                    <button class="btn-primary" onclick="closeModalAlert()">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    const titleEl = document.getElementById('alertModalTitle');
    const messageEl = document.getElementById('alertModalMessage');
    
    // Set title
    if (title) {
        titleEl.textContent = title;
    } else {
        titleEl.textContent = type.charAt(0).toUpperCase() + type.slice(1);
    }
    
    // Set message
    messageEl.textContent = message;
    
    // Set color based on type
    messageEl.style.color = getTypeColor(type);
    
    // Show modal
    modal.classList.add('show');
}

/**
 * Close modal alert
 */
function closeModalAlert() {
    const modal = document.getElementById('universalAlertModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Show success modal with callback
 */
function showSuccessModal(title, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content modal-success">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close-btn" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="this.closest('.modal-overlay').remove()">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    });
    
    const closeBtn = modal.querySelector('.btn-primary');
    closeBtn.addEventListener('click', function() {
        modal.remove();
        if (callback) callback();
    });
}

/**
 * Show error modal with callback
 */
function showErrorModal(title, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content modal-error">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close-btn" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    });
    
    const closeBtn = modal.querySelector('.btn-secondary');
    closeBtn.addEventListener('click', function() {
        modal.remove();
        if (callback) callback();
    });
}

/**
 * Show modal confirmation
 */
function showModalConfirm(message, onConfirm, onCancel = null, title = 'Confirm') {
    let modal = document.getElementById('universalConfirmModal');
    
    if (!modal) {
        // Create modal if it doesn't exist
        modal = document.createElement('div');
        modal.id = 'universalConfirmModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header">
                    <h2 id="confirmModalTitle">Confirm</h2>
                    <button class="modal-close" onclick="closeModalConfirm()">×</button>
                </div>
                <div class="modal-body">
                    <p id="confirmModalMessage"></p>
                </div>
                <div class="modal-footer" style="justify-content: flex-end; gap: 10px;">
                    <button class="btn-secondary" onclick="closeModalConfirm()">Cancel</button>
                    <button class="btn-danger" id="confirmModalBtn">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalBtn');
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    // Remove old event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add event listeners
    document.getElementById('confirmModalBtn').addEventListener('click', () => {
        closeModalConfirm();
        if (onConfirm) onConfirm();
    });
    
    // Show modal
    modal.classList.add('show');
}

/**
 * Close modal confirm
 */
function closeModalConfirm() {
    const modal = document.getElementById('universalConfirmModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Get color for alert type
 */
function getTypeColor(type) {
    const colors = {
        'success': '#155724',
        'error': '#721c24',
        'warning': '#856404',
        'info': '#0c5460',
    };
    return colors[type] || '#000';
}

/**
 * Handle logout
 */
async function handleLogout() {
    showModalConfirm('Are you sure you want to logout?', async () => {
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
            showModalAlert('Error logging out. Please try again.', 'error');
        }
    });
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
