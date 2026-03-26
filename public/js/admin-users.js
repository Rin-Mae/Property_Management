/**
 * Load all users
 */
async function loadUsers() {
    try {
        const response = await api.get('/api/admin/users');
        const users = response.data.users;

        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No users found</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.first_name}</td>
                <td>${user.middle_name || '-'}</td>
                <td>${user.last_name}</td>
                <td>${user.email}</td>
                <td>${user.student_id || '-'}</td>
                <td><span class="role-badge ${user.role}">${user.role}</span></td>
                <td>
                    <button onclick="editUser(${user.id})" class="btn-edit">Edit</button>
                    <button onclick="deleteUser(${user.id})" class="btn-delete">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        showError('Failed to load users: ' + (error.response?.data?.message || error.message));
    }
}

/**
 * Open modal to add new user
 */
function openAddUserModal() {
    document.getElementById('userId').value = '';
    document.getElementById('userForm').reset();
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('passwordHint').style.display = 'block';
    document.getElementById('password').required = true;
    document.getElementById('passwordConfirm').required = true;
    document.getElementById('userModal').style.display = 'block';
    clearAllErrors();
}

/**
 * Open modal to edit user
 */
async function editUser(userId) {
    try {
        const response = await api.get(`/api/admin/users/${userId}`);
        const user = response.data.user;

        document.getElementById('userId').value = user.id;
        document.getElementById('firstName').value = user.first_name;
        document.getElementById('middleName').value = user.middle_name || '';
        document.getElementById('lastName').value = user.last_name;
        document.getElementById('email').value = user.email;
        document.getElementById('studentId').value = user.student_id || '';
        document.getElementById('role').value = user.role;
        document.getElementById('password').value = '';
        document.getElementById('passwordConfirm').value = '';

        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('passwordHint').style.display = 'inline';
        document.getElementById('password').required = false;
        document.getElementById('passwordConfirm').required = false;
        document.getElementById('userModal').style.display = 'block';
        clearAllErrors();
    } catch (error) {
        showError('Failed to load user: ' + (error.response?.data?.message || error.message));
    }
}

/**
 * Close user modal
 */
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

/**
 * Handle user form submission
 */
async function handleUserFormSubmit(event) {
    event.preventDefault();
    clearAllErrors();

    const userId = document.getElementById('userId').value;
    const formData = {
        first_name: document.getElementById('firstName').value,
        middle_name: document.getElementById('middleName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        student_id: document.getElementById('studentId').value,
        password: document.getElementById('password').value,
        password_confirmation: document.getElementById('passwordConfirm').value,
        role: document.getElementById('role').value,
    };

    try {
        let response;
        if (userId) {
            response = await api.put(`/api/admin/users/${userId}`, formData);
        } else {
            response = await api.post('/api/admin/users', formData);
        }

        showSuccess(response.data.message);
        closeUserModal();
        loadUsers();
    } catch (error) {
        if (error.response?.status === 422 && error.response?.data?.errors) {
            const errors = error.response.data.errors;
            for (const field in errors) {
                showFieldError(field, errors[field][0]);
            }
        } else {
            showError(error.response?.data?.message || 'Failed to save user');
        }
    }
}

/**
 * Delete user
 */
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    try {
        const response = await api.delete(`/api/admin/users/${userId}`);
        showSuccess(response.data.message);
        loadUsers();
    } catch (error) {
        showError(error.response?.data?.message || 'Failed to delete user');
    }
}

/**
 * Show error message
 */
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

/**
 * Show success message
 */
function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 3000);
}

/**
 * Show field-specific error
 */
function showFieldError(fieldName, message) {
    const fieldMap = {
        'first_name': 'firstNameError',
        'last_name': 'lastNameError',
        'middle_name': 'middleNameError',
        'email': 'emailError',
        'student_id': 'studentIdError',
        'password': 'passwordError',
        'password_confirmation': 'passwordConfirmError',
        'role': 'roleError',
    };

    const errorElementId = fieldMap[fieldName];
    if (errorElementId) {
        const errorElement = document.getElementById(errorElementId);
        if (errorElement) {
            errorElement.textContent = message;
        }
    }
}

/**
 * Clear all error messages
 */
function clearAllErrors() {
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(el => el.textContent = '');
}

/**
 * Close modal when clicking outside of it
 */
window.onclick = function (event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeUserModal();
    }
};

/**
 * Load users on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    loadUserInfo();
    loadUsers();
});
