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

/**
 * Load user information from API and auto-fill form fields
 */
async function loadUserInfo() {
    try {
        const response = await api.get('/api/user');
        const user = response.data;
        document.getElementById('profileName').textContent = user.full_name;
        document.getElementById('profileAvatar').textContent = user.full_name.charAt(0).toUpperCase();

        // Auto-fill user fields
        document.getElementById('fullName').value = user.full_name;
        document.getElementById('studentId').value = user.student_id || '';
    } catch (error) {
        console.error('Failed to load user:', error);
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
    }
}

/**
 * Handle form submission with file uploads
 */
window.handleSubmit = async function (event) {
    event.preventDefault();

    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    document.getElementById('errorAlert').classList.remove('show');
    document.getElementById('successMessage').classList.remove('show');

    // Validate requirements file count
    const requirementsInput = document.getElementById('requirements');
    if (requirementsInput.files.length > 5) {
        document.getElementById('requirementsError').textContent = 'Maximum 5 files allowed';
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    // Use FormData to handle file uploads
    const formData = new FormData();
    formData.append('full_name', document.getElementById('fullName').value);
    formData.append('birthplace', document.getElementById('birthplace').value);
    formData.append('permanent_address', document.getElementById('permanentAddress').value);
    formData.append('birthdate', document.getElementById('birthdate').value);
    formData.append('student_id', document.getElementById('studentId').value);
    formData.append('course', document.getElementById('course').value);
    formData.append('purpose', document.getElementById('purpose').value);

    // Add file uploads
    const birthCert = document.getElementById('birthCertificate').files[0];
    if (birthCert) formData.append('birth_certificate', birthCert);

    const receipt = document.getElementById('receipt').files[0];
    if (receipt) formData.append('receipt', receipt);

    // Add multiple files for requirements
    const requirementFiles = document.getElementById('requirements').files;
    for (let i = 0; i < requirementFiles.length; i++) {
        formData.append('requirements[]', requirementFiles[i]);
    }

    try {
        const response = await api.post('/api/tor-requests', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        // Show success message
        const successMsg = document.getElementById('successMessage');
        successMsg.textContent = '✓ TOR request submitted successfully! You can view your requests below.';
        successMsg.classList.add('show');

        // Reset form
        document.getElementById('torForm').reset();

        // Scroll to success message
        successMsg.scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit TOR Request';

        if (error.response?.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            for (const field in errors) {
                const fieldId = field.replace(/_/g, '').replace(/\[\]/g, '');
                const errorElement = document.getElementById(fieldId + 'Error');
                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                }
            }
        } else {
            const errorAlert = document.getElementById('errorAlert');
            errorAlert.textContent = error.response?.data?.message || 'An error occurred. Please try again.';
            errorAlert.classList.add('show');
        }
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
 * Navigation function - View my requests
 */
window.viewMyRequests = function () {
    window.location.href = '/tor/requests';
};

/**
 * Navigation function - Go to dashboard
 */
window.goToDashboard = function () {
    window.location.href = '/dashboard';
};

/**
 * Navigation function - View all requests
 */
window.goToViewRequests = function () {
    window.location.href = '/tor/requests';
};

/**
 * Navigation function - Create new request
 */
window.goToCreateRequest = function () {
    window.location.href = '/tor/create';
};

// Load user info when page loads
document.addEventListener('DOMContentLoaded', loadUserInfo);
