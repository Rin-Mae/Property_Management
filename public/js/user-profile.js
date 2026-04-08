/**
 * User Profile Management
 */

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');

    // Load user profile on page load
    loadProfile();

    // Handle form submission
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });

    /**
     * Load current user profile data
     */
    function loadProfile() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        
        fetch('/api/user/profile', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(user => {
            // Populate form with user data
            document.getElementById('first_name').value = user.first_name || '';
            document.getElementById('middle_name').value = user.middle_name || '';
            document.getElementById('last_name').value = user.last_name || '';
            document.getElementById('suffix').value = user.suffix || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('contact_number').value = user.contact_number || '';
            
            // Clear password fields (should always be empty on load)
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
        })
        .catch(error => {
            console.error('Error loading profile:', error);
            showError('Failed to load profile. Please refresh the page.');
        });
    }

    /**
     * Update user profile
     */
    function updateProfile() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const formData = {
            first_name: document.getElementById('first_name').value,
            middle_name: document.getElementById('middle_name').value,
            last_name: document.getElementById('last_name').value,
            suffix: document.getElementById('suffix').value,
            email: document.getElementById('email').value,
            contact_number: document.getElementById('contact_number').value
        };

        // Only include password if provided
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;
        
        if (password) {
            formData.password = password;
            formData.password_confirmation = passwordConfirm;
        }

        // Show loader
        showProfileLoader('Updating profile...');

        // Make API request
        fetch('/api/user/profile', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            // Clear error messages first
            clearErrors();
            
            if (!response.ok) {
                return response.json().then(error => {
                    throw error;
                });
            }
            return response.json();
        })
        .then(data => {
            hideProfileLoader();
            // Show success message
            showSuccess('Profile updated successfully!');
            
            // Clear password fields
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            
            // Optionally scroll to top to see success message
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            hideProfileLoader();
            if (error.errors) {
                // Show validation errors
                displayValidationErrors(error.errors);
            } else {
                showError(error.error || 'Failed to update profile. Please try again.');
            }
        });
    }

    /**
     * Display validation errors
     */
    function displayValidationErrors(errors) {
        clearErrors();
        
        for (const [field, messages] of Object.entries(errors)) {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement && messages.length > 0) {
                errorElement.textContent = messages[0];
                errorElement.parentElement.classList.add('has-error');
            }
        }
        
        showError('Please correct the errors below.');
    }

    /**
     * Clear all error messages
     */
    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.parentElement?.classList.remove('has-error');
        });
        errorMessage.classList.add('d-none');
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.classList.remove('d-none');
        errorMessage.classList.add('d-none');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            successMessage.classList.add('d-none');
        }, 5000);
    }

    /**
     * Show error message
     */
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
        successMessage.classList.add('d-none');
    }

    /**
     * Show profile loader
     */
    function showProfileLoader(message = 'Processing...') {
        let loader = document.getElementById('profileLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'profileLoader';
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
     * Hide profile loader
     */
    function hideProfileLoader() {
        const loader = document.getElementById('profileLoader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
});
