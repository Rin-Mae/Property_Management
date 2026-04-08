// Modal Functions
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
    document.getElementById('registerModal').style.display = 'none';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

function openRegisterModal() {
    document.getElementById('registerModal').style.display = 'block';
    document.getElementById('loginModal').style.display = 'none';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
}

function switchToLogin() {
    closeRegisterModal();
    openLoginModal();
}

function switchToRegister() {
    closeLoginModal();
    openRegisterModal();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    
    if (event.target == loginModal) {
        loginModal.style.display = 'none';
    }
    if (event.target == registerModal) {
        registerModal.style.display = 'none';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Don't intercept modal links
            if (href === '#' || href.includes('registerModal') || href.includes('loginModal')) {
                return;
            }
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({behavior: 'smooth'});
            }
        });
    });

    // Setup form handlers only if axios is available
    setupFormHandlers();
});

// Form handlers wrapper
function setupFormHandlers() {
    // Check if axios is available, if not wait a bit
    if (typeof axios === 'undefined') {
        setTimeout(setupFormHandlers, 100);
        return;
    }

    // Login Form Handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }

    // Register Form Handler
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
}

// Login handler
async function handleLoginSubmit(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        const response = await axios.post('/api/login', {
            email: email,
            password: password
        });
        
        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
            window.location.href = '/dashboard';
        }
    } catch (error) {
        const errorMessage = document.getElementById('loginError');
        if (error.response && error.response.data.message) {
            errorMessage.textContent = error.response.data.message;
        } else {
            errorMessage.textContent = 'Login failed. Please try again.';
        }
    }
}

// Register handler
async function handleRegisterSubmit(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('registerName').value,
        email: document.getElementById('registerEmail').value,
        password: document.getElementById('registerPassword').value,
        password_confirmation: document.getElementById('registerPasswordConfirm').value
    };
    
    try {
        const response = await axios.post('/api/register', formData);
        
        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
            window.location.href = '/dashboard';
        }
    } catch (error) {
        const errorElements = {
            'name': 'registerNameError',
            'email': 'registerEmailError',
            'password': 'registerPasswordError',
            'password_confirmation': 'confirmPasswordError'
        };
        
        Object.keys(errorElements).forEach(key => {
            const element = document.getElementById(errorElements[key]);
            if (element) {
                element.textContent = '';
            }
        });
        
        if (error.response && error.response.data.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(key => {
                const elementId = errorElements[key];
                if (elementId && document.getElementById(elementId)) {
                    document.getElementById(elementId).textContent = errors[key][0];
                }
            });
        }
    }
}
