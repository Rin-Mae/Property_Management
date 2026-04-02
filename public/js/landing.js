// Set up Axios with CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// Modal Functions
function openLoginModal() {
    document.getElementById('loginModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('loginForm').reset();
    clearLoginErrors();
}

function openRegisterModal() {
    document.getElementById('registerModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeRegisterModal() {
    document.getElementById('registerModal').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('registerForm').reset();
    clearRegisterErrors();
}

function switchToLogin() {
    closeRegisterModal();
    openLoginModal();
}

function switchToRegister() {
    closeLoginModal();
    openRegisterModal();
}

// Close modals when clicking outside
window.addEventListener('click', (event) => {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    
    if (event.target === loginModal) {
        closeLoginModal();
    }
    if (event.target === registerModal) {
        closeRegisterModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeLoginModal();
        closeRegisterModal();
    }
});

// Login Form Handler
function handleLoginSubmit(event) {
    event.preventDefault();
    clearLoginErrors();

    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    axios.post('/login', {
        email: email,
        password: password
    })
    .then(response => {
        // Redirect to dashboard on successful login
        window.location.href = '/dashboard';
    })
    .catch(error => {
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            if (errors.email) {
                showLoginError('emailError', errors.email[0]);
            }
            if (errors.password) {
                showLoginError('passwordError', errors.password[0]);
            }
        } else if (error.response && error.response.status === 401) {
            showLoginError('emailError', 'Invalid email or password');
        } else {
            showLoginError('emailError', 'An error occurred. Please try again.');
        }
    });
}

// Register Form Handler
function handleRegisterSubmit(event) {
    event.preventDefault();
    clearRegisterErrors();

    const fullName = document.getElementById('registerName').value.trim();
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

    // Split full name into first and last name
    const nameParts = fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || firstName;

    axios.post('/register', {
        first_name: firstName,
        last_name: lastName,
        email: email,
        password: password,
        password_confirmation: passwordConfirm,
        role: 'user'
    })
    .then(response => {
        // Redirect to dashboard on successful registration
        window.location.href = '/dashboard';
    })
    .catch(error => {
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            if (errors.name) {
                showRegisterError('nameError', errors.name[0]);
            }
            if (errors.email) {
                showRegisterError('registerEmailError', errors.email[0]);
            }
            if (errors.password) {
                showRegisterError('registerPasswordError', errors.password[0]);
            }
        } else {
            showRegisterError('nameError', 'An error occurred. Please try again.');
        }
    });
}

function showLoginError(elementId, message) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
}

function showRegisterError(elementId, message) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('show');
    }
}

function clearLoginErrors() {
    document.getElementById('emailError').classList.remove('show');
    document.getElementById('passwordError').classList.remove('show');
}

function clearRegisterErrors() {
    document.getElementById('nameError').classList.remove('show');
    document.getElementById('registerEmailError').classList.remove('show');
    document.getElementById('registerPasswordError').classList.remove('show');
    document.getElementById('confirmPasswordError').classList.remove('show');
}

// Hamburger menu toggle
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

if (hamburger) {
    hamburger.addEventListener('click', () => {
        navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        navLinks.style.position = 'absolute';
        navLinks.style.top = '70px';
        navLinks.style.left = '0';
        navLinks.style.right = '0';
        navLinks.style.flexDirection = 'column';
        navLinks.style.background = 'white';
        navLinks.style.padding = '1rem';
        navLinks.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        navLinks.style.gap = '1rem';
    });
}

// Contact form submission
const contactForm = document.getElementById('contactForm');

if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = {
            name: contactForm.querySelector('input[type="text"]').value,
            email: contactForm.querySelector('input[type="email"]').value,
            message: contactForm.querySelector('textarea').value
        };

        try {
            // Show success message (in a real app, this would send to a server)
            alert('Thank you for your message! We will get back to you soon.');
            contactForm.reset();
        } catch (error) {
            console.error('Error:', error);
            alert('There was an error sending your message. Please try again.');
        }
    });
}

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Add scroll animation for elements
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe feature cards, room cards, and amenity items
document.querySelectorAll('.feature-card, .room-card, .amenity-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

// Mobile menu close on link click
if (navLinks) {
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                navLinks.style.display = 'none';
            }
        });
    });
}

// Close mobile menu on window resize
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        navLinks.style.display = 'flex';
        navLinks.style.position = 'relative';
        navLinks.style.top = 'auto';
        navLinks.style.left = 'auto';
        navLinks.style.right = 'auto';
        navLinks.style.flexDirection = 'row';
        navLinks.style.background = 'transparent';
        navLinks.style.padding = '0';
        navLinks.style.boxShadow = 'none';
    }
});

