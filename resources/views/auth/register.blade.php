<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Online TOR Request System</title>
    <style>
        body {
            background: linear-gradient(135deg, #00a516 0%, #007810 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
        }

        .register-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #00a516 0%, #007810 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 0.8rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="{{ asset('images/NC Logo.png') }}" alt="NC Logo" style="max-width: 150px; height: auto;">
        </div>
        <div id="successMessage" class="success-message"></div>

        <form id="registerForm" method="POST" action="/register" onsubmit="handleRegister(event)">
            @csrf
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
                <div class="error-message" id="nameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
                <div class="error-message" id="passwordError"></div>
            </div>

            <div class="form-group">
                <label for="passwordConfirmation">Confirm Password</label>
                <input type="password" id="passwordConfirmation" name="password_confirmation" required minlength="8">
                <div class="error-message" id="passwordConfirmationError"></div>
            </div>

            <button type="submit" id="registerBtn">Create Account</button>
        </form>

        <div style="text-align: center; margin-top: 1rem; color: #666;">
            <p>Already have an account? <a href="/login" style="color: #667eea; text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
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
    </script>
    <script>

        window.handleRegister = async function (event) {
            event.preventDefault();

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));

            const registerBtn = document.getElementById('registerBtn');
            registerBtn.disabled = true;
            registerBtn.textContent = 'Creating Account...';

            const form = document.getElementById('registerForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    // Show success message
                    const successMsg = document.getElementById('successMessage');
                    successMsg.textContent = 'Account created successfully! Redirecting...';
                    successMsg.classList.add('show');

                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 500);
                } else if (response.status === 422) {
                    // Validation errors
                    const errorData = await response.json();
                    const errors = errorData.errors || {};

                    for (const field in errors) {
                        const fieldId = field === 'password_confirmation' ? 'passwordConfirmation' : field;
                        const errorElement = document.getElementById(fieldId + 'Error');
                        if (errorElement) {
                            errorElement.textContent = errors[field][0];
                            errorElement.classList.add('show');
                        }
                    }

                    registerBtn.disabled = false;
                    registerBtn.textContent = 'Create Account';
                } else {
                    const errorData = await response.json();
                    alert(errorData.message || 'Registration failed');
                    registerBtn.disabled = false;
                    registerBtn.textContent = 'Create Account';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                registerBtn.disabled = false;
                registerBtn.textContent = 'Create Account';
            }
        };
    </script>
</body>

</html>