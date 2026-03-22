<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Online TOR Request System</title>
    <style>
        body {
            background: linear-gradient(135deg, #00a516 0%, #007810 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
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
            background: #007810 100%;
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
    <div class="login-container">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <img src="{{ asset('images/NC Logo.png') }}" alt="NC Logo" style="max-width: 150px; height: auto;">
        </div>
        <div id="successMessage" class="success-message"></div>

        <form id="loginForm" action="/login" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email" required>
                <div class="error-message" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="error-message" id="passwordError"></div>
            </div>

            <button type="submit" id="loginBtn">Login</button>
        </form>

        <div style="text-align: center; margin-top: 1rem; color: #666;">
            <p>Don't have an account? <a href="/register" style="color: #667eea; text-decoration: none;">Register
                    here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configure axios for API calls
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
        // Handle login form with validation and role-based redirect
        document.getElementById('loginForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Clear previous errors
            document.getElementById('emailError').classList.remove('show');
            document.getElementById('passwordError').classList.remove('show');

            loginBtn.disabled = true;
            loginBtn.textContent = 'Logging in...';

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    }),
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (response.ok) {
                    // Show success message
                    const successMsg = document.getElementById('successMessage');
                    successMsg.textContent = 'Login successful! Redirecting to your dashboard...';
                    successMsg.classList.add('show');

                    // Redirect based on user role from response
                    setTimeout(() => {
                        const redirectUrl = data.role === 'admin' ? '/dashboard' : '/dashboard';
                        window.location.href = redirectUrl;
                    }, 500);
                } else if (response.status === 422) {
                    // Validation errors
                    const errors = data.errors || {};

                    if (errors.email) {
                        document.getElementById('emailError').textContent = errors.email[0];
                        document.getElementById('emailError').classList.add('show');
                    }
                    if (errors.password) {
                        document.getElementById('passwordError').textContent = errors.password[0];
                        document.getElementById('passwordError').classList.add('show');
                    }

                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Login';
                } else {
                    alert(data.message || 'Login failed');
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Login';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                loginBtn.disabled = false;
                loginBtn.textContent = 'Login';
            }
        });
    </script>
</body>

</html>