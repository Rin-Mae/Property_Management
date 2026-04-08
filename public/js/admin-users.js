// User Management State
let userManagementState = {
    users: [],
    currentPage: 1,
    totalPages: 1,
    perPage: 10,
    filteredUsers: [],
    editingUserId: null,
    deleteUserId: null,
    searchTerm: '',
    roleFilter: ''
};

/**
 * Load all users
 */
async function loadUsers() {
    try {
        const combinedLoading = document.getElementById('combinedLoading');
        const combinedContainer = document.getElementById('combinedContainer');

        if (combinedLoading) {
            combinedLoading.style.display = 'block';
        }
        if (combinedContainer) {
            combinedContainer.style.display = 'none';
        }

        // API call to get users
        const response = await axios.get('/api/users');
        userManagementState.users = response.data;
        userManagementState.filteredUsers = response.data;

        applyFilters();

        if (combinedLoading) {
            combinedLoading.style.display = 'none';
        }
        if (combinedContainer) {
            combinedContainer.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showAlert('Failed to load users', 'error');
    }
}

/**
 * Show modal loader
 */
function showModalLoader(message = 'Processing...') {
    let loader = document.getElementById('userModalLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'userModalLoader';
        loader.className = 'modal-loader-overlay';
        document.body.appendChild(loader);
    }
    
    loader.innerHTML = `
        <div class="modal-loader-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    loader.style.display = 'flex';
}

/**
 * Hide modal loader
 */
function hideModalLoader() {
    const loader = document.getElementById('userModalLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

/**
 * Apply search and role filters
 */
function applyFilters() {
    let filtered = userManagementState.users;

    // Apply search filter
    if (userManagementState.searchTerm) {
        const term = userManagementState.searchTerm.toLowerCase();
        filtered = filtered.filter(user => {
            const fullName = `${user.first_name} ${user.last_name}`.toLowerCase();
            const email = (user.email || '').toLowerCase();
            return fullName.includes(term) || email.includes(term);
        });
    }

    // Apply role filter
    if (userManagementState.roleFilter) {
        filtered = filtered.filter(user => user.role === userManagementState.roleFilter);
    }

    userManagementState.filteredUsers = filtered;
    userManagementState.currentPage = 1;
    userManagementState.totalPages = Math.ceil(filtered.length / userManagementState.perPage);

    updateUsersTable();
}

/**
 * Update users table display
 */
function updateUsersTable() {
    const tbody = document.getElementById('combinedTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    const start = (userManagementState.currentPage - 1) * userManagementState.perPage;
    const end = start + userManagementState.perPage;
    const paginatedUsers = userManagementState.filteredUsers.slice(start, end);

    if (paginatedUsers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No users found</td></tr>';
        document.getElementById('combinedPagination').style.display = 'none';
        return;
    }

    paginatedUsers.forEach(user => {
        const fullName = `${user.first_name}${user.middle_name ? ' ' + user.middle_name : ''} ${user.last_name}${user.suffix ? ' ' + user.suffix : ''}`;
        const joinDate = formatDate(user.created_at);
        const status = user.deleted_at ? 'Inactive' : 'Active';
        const statusClass = user.deleted_at ? 'inactive' : 'active';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${fullName}</td>
            <td>${user.email}</td>
            <td><span class="role-badge ${user.role}">${user.role}</span></td>
            <td>${joinDate}</td>
            <td><span class="status-badge ${statusClass}">${status}</span></td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon edit" onclick="openEditUserModal(${user.id})" title="Edit">
                        ✎ Edit
                    </button>
                    <button class="btn-icon delete" onclick="openDeleteModal(${user.id})" title="Delete">
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
    const pageInfo = document.getElementById('combinedPageInfo');
    const prevBtn = document.getElementById('combinedPrevBtn');
    const nextBtn = document.getElementById('combinedNextBtn');
    const pagination = document.getElementById('combinedPagination');

    if (userManagementState.totalPages > 1) {
        if (pagination) {
            pagination.style.display = 'flex';
        }
    } else {
        if (pagination) {
            pagination.style.display = 'none';
        }
    }

    if (pageInfo) {
        pageInfo.textContent = `Page ${userManagementState.currentPage} of ${userManagementState.totalPages}`;
    }

    if (prevBtn) {
        prevBtn.disabled = userManagementState.currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = userManagementState.currentPage >= userManagementState.totalPages;
    }
}

/**
 * Go to previous page
 */
function previousUsersPage() {
    if (userManagementState.currentPage > 1) {
        userManagementState.currentPage--;
        updateUsersTable();
    }
}

/**
 * Go to next page
 */
function nextUsersPage() {
    if (userManagementState.currentPage < userManagementState.totalPages) {
        userManagementState.currentPage++;
        updateUsersTable();
    }
}

/**
 * Open create user modal
 */
function openCreateUserModal() {
    userManagementState.editingUserId = null;
    document.getElementById('userModalTitle').textContent = 'Create New User';
    document.getElementById('submitBtn').textContent = 'Create User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    clearFormErrors();
    
    // Set role to admin automatically for creation
    const roleField = document.getElementById('role');
    const roleHiddenField = document.getElementById('roleHidden');
    if (roleField) {
        roleField.value = 'admin';
        roleField.disabled = true;
    }
    if (roleHiddenField) {
        roleHiddenField.value = '';
    }
    
    // Make password required for new users
    const passwordField = document.getElementById('password');
    const passwordConfirmField = document.getElementById('passwordConfirm');
    if (passwordField) passwordField.required = true;
    if (passwordConfirmField) passwordConfirmField.required = true;
    
    const passwordLabel = document.querySelector('label[for="password"]');
    if (passwordLabel) {
        passwordLabel.innerHTML = 'Password * <span class="text-muted">(Required for new users)</span>';
    }
    
    document.getElementById('userModal').classList.add('show');
}

/**
 * Open edit user modal
 */
async function openEditUserModal(userId) {
    try {
        const response = await axios.get(`/api/users/${userId}`);
        const user = response.data;

        userManagementState.editingUserId = userId;
        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('submitBtn').textContent = 'Update User';
        document.getElementById('userId').value = user.id;
        document.getElementById('firstName').value = user.first_name || '';
        document.getElementById('middleName').value = user.middle_name || '';
        document.getElementById('lastName').value = user.last_name || '';
        document.getElementById('suffix').value = user.suffix || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('role').value = user.role || '';
        document.getElementById('contactNumber').value = user.contact_number || '';
        document.getElementById('password').value = '';
        document.getElementById('passwordConfirm').value = '';

        clearFormErrors();
        
        // Disable role field in edit mode and set hidden field with the role value
        const roleField = document.getElementById('role');
        const roleHiddenField = document.getElementById('roleHidden');
        if (roleField) {
            roleField.disabled = true;
        }
        if (roleHiddenField) {
            roleHiddenField.value = user.role || '';
        }
        
        // Make password optional for edits
        const passwordField = document.getElementById('password');
        const passwordConfirmField = document.getElementById('passwordConfirm');
        if (passwordField) passwordField.required = false;
        if (passwordConfirmField) passwordConfirmField.required = false;
        
        const passwordLabel = document.querySelector('label[for="password"]');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password <span class="text-muted">(Leave empty to keep current)</span>';
        }
        
        document.getElementById('userModal').classList.add('show');
    } catch (error) {
        console.error('Error loading user:', error);
        showAlert('Failed to load user details', 'error');
    }
}

/**
 * Close user modal
 */
function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

/**
 * Handle user form submission
 */
async function handleUserSubmit(event) {
    event.preventDefault();
    clearFormErrors();

    const userId = document.getElementById('userId').value;
    const isCreateMode = !userId;

    const formData = {
        first_name: document.getElementById('firstName').value,
        middle_name: document.getElementById('middleName').value,
        last_name: document.getElementById('lastName').value,
        suffix: document.getElementById('suffix').value,
        email: document.getElementById('email').value,
        role: isCreateMode ? 'admin' : (document.getElementById('roleHidden').value || document.getElementById('role').value),
        contact_number: document.getElementById('contactNumber').value,
    };

    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('passwordConfirm').value;

    // Password validation
    if (isCreateMode) {
        // For new users, password is required
        if (!password) {
            document.getElementById('passwordError').textContent = 'Password is required for new users';
            document.getElementById('passwordError').classList.add('show');
            return;
        }
        if (!passwordConfirm) {
            document.getElementById('passwordConfirmError').textContent = 'Please confirm your password';
            document.getElementById('passwordConfirmError').classList.add('show');
            return;
        }
        if (password !== passwordConfirm) {
            document.getElementById('passwordError').textContent = 'Passwords do not match';
            document.getElementById('passwordError').classList.add('show');
            return;
        }
        formData.password = password;
        formData.password_confirmation = passwordConfirm;
    } else {
        // For existing users, password is optional
        if (password) {
            if (!passwordConfirm) {
                document.getElementById('passwordConfirmError').textContent = 'Please confirm your password';
                document.getElementById('passwordConfirmError').classList.add('show');
                return;
            }
            if (password !== passwordConfirm) {
                document.getElementById('passwordError').textContent = 'Passwords do not match';
                document.getElementById('passwordError').classList.add('show');
                return;
            }
            formData.password = password;
            formData.password_confirmation = passwordConfirm;
        }
    }

    const submitBtn = event.target.querySelector('#submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    showModalLoader('Saving user...');

    try {
        let response;
        if (userId) {
            // Update existing user
            response = await axios.put(`/api/users/${userId}`, formData);
            showAlert('User updated successfully', 'success');
        } else {
            // Create new user
            response = await axios.post('/api/users', formData);
            showAlert('User created successfully', 'success');
        }

        hideModalLoader();
        closeUserModal();
        loadUsers();
    } catch (error) {
        hideModalLoader();
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
            showAlert('Error processing user. Please try again.', 'error');
        }
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

/**
 * Open delete confirmation modal
 */
function openDeleteModal(userId) {
    userManagementState.deleteUserId = userId;
    document.getElementById('deleteModal').classList.add('show');
}

/**
 * Close delete modal
 */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    userManagementState.deleteUserId = null;
}

/**
 * Confirm and delete user
 */
async function confirmDelete() {
    const userId = userManagementState.deleteUserId;
    if (!userId) return;

    try {
        await axios.delete(`/api/users/${userId}`);
        showAlert('User deleted successfully', 'success');
        closeDeleteModal();
        loadUsers();
    } catch (error) {
        console.error('Error deleting user:', error);
        showAlert('Failed to delete user', 'error');
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
    userManagementState.searchTerm = event.target.value;
    applyFilters();
}

/**
 * Handle role filter change
 */
function handleRoleFilter(event) {
    userManagementState.roleFilter = event.target.value;
    applyFilters();
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Set up event listeners
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');

    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    if (roleFilter) {
        roleFilter.addEventListener('change', handleRoleFilter);
    }

    // Load users on page load
    loadUsers();

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
