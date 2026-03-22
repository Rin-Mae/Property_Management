<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request TOR - Online TOR Request System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.5rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            color: #667eea;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group.required label::after {
            content: " *";
            color: #e74c3c;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .error-alert.show {
            display: block;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        button[type="submit"],
        .view-requests-btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
        }

        button[type="submit"]:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .view-requests-btn {
            background: #f0f0f0;
            color: #333;
        }

        .view-requests-btn:hover {
            background: #e0e0e0;
        }

        .user-info {
            font-size: 0.9rem;
            color: #ddd;
        }

        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            header {
                flex-direction: column;
                gap: 1rem;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <header>
        <div>
            <h1>🎓 Online TOR Request System</h1>
            <p class="user-info" id="userInfo">Loading...</p>
        </div>
        <button class="logout-btn" onclick="handleLogout()">Logout</button>
    </header>

    <div class="container">
        <h2>Request Transcript of Records (TOR)</h2>

        <div id="successMessage" class="success-message"></div>
        <div id="errorAlert" class="error-alert"></div>

        <form id="torForm" onsubmit="handleSubmit(event)">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group required">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="full_name" required>
                        <span class="error-message" id="fullNameError"></span>
                    </div>
                    <div class="form-group required">
                        <label for="birthdate">Date of Birth</label>
                        <input type="date" id="birthdate" name="birthdate" required>
                        <span class="error-message" id="birthdateError"></span>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group required" style="grid-column: 1 / -1;">
                        <label for="birthplace">Place of Birth</label>
                        <input type="text" id="birthplace" name="birthplace" required>
                        <span class="error-message" id="birthplaceError"></span>
                    </div>
                </div>
            </div>

            <!-- Academic Information Section -->
            <div class="form-section">
                <h3>Academic Information</h3>
                <div class="form-grid">
                    <div class="form-group required">
                        <label for="studentId">Student ID</label>
                        <input type="text" id="studentId" name="student_id" required>
                        <span class="error-message" id="studentIdError"></span>
                    </div>
                    <div class="form-group required">
                        <label for="course">Course/Program</label>
                        <input type="text" id="course" name="course" required>
                        <span class="error-message" id="courseError"></span>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="degree">Degree Earned</label>
                        <input type="text" id="degree" name="degree" placeholder="e.g., Bachelor of Science">
                        <span class="error-message" id="degreeError"></span>
                    </div>
                    <div class="form-group">
                        <label for="yearOfGraduation">Year of Graduation</label>
                        <input type="number" id="yearOfGraduation" name="year_of_graduation" placeholder="e.g., 2023"
                            min="1900" max="">
                        <span class="error-message" id="yearOfGraduationError"></span>
                    </div>
                </div>
            </div>

            <!-- Request Details Section -->
            <div class="form-section">
                <h3>Request Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="purpose">Purpose of Request</label>
                        <textarea id="purpose" name="purpose"
                            placeholder="e.g., Scholarship application, Job application"></textarea>
                        <span class="error-message" id="purposeError"></span>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group required">
                        <label for="numberOfCopies">Number of Copies Needed</label>
                        <input type="number" id="numberOfCopies" name="number_of_copies" value="1" min="1" max="10"
                            required>
                        <span class="error-message" id="numberOfCopiesError"></span>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" id="submitBtn">Submit TOR Request</button>
                <button type="button" class="view-requests-btn" onclick="viewMyRequests()">View My Requests</button>
            </div>
        </form>
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

        // Load current year
        document.getElementById('yearOfGraduation').max = new Date().getFullYear();

        // Load user info on page load
        async function loadUserInfo() {
            try {
                const response = await api.get('/api/user');
                const user = response.data;
                document.getElementById('userInfo').textContent = `Welcome, ${user.name}`;
            } catch (error) {
                console.error('Failed to load user:', error);
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }

        window.handleSubmit = async function (event) {
            event.preventDefault();

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.getElementById('errorAlert').classList.remove('show');
            document.getElementById('successMessage').classList.remove('show');

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = {
                full_name: document.getElementById('fullName').value,
                birthplace: document.getElementById('birthplace').value,
                birthdate: document.getElementById('birthdate').value,
                student_id: document.getElementById('studentId').value,
                course: document.getElementById('course').value,
                degree: document.getElementById('degree').value || null,
                year_of_graduation: document.getElementById('yearOfGraduation').value ?
                    parseInt(document.getElementById('yearOfGraduation').value) : null,
                purpose: document.getElementById('purpose').value || null,
                number_of_copies: parseInt(document.getElementById('numberOfCopies').value),
            };

            try {
                const response = await api.post('/api/tor-requests', formData);

                // Show success message
                const successMsg = document.getElementById('successMessage');
                successMsg.textContent = '✓ TOR request submitted successfully! You can view your requests below.';
                successMsg.classList.add('show');

                // Reset form
                document.getElementById('torForm').reset();
                document.getElementById('numberOfCopies').value = '1';

                // Scroll to success message
                successMsg.scrollIntoView({ behavior: 'smooth' });
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit TOR Request';

                if (error.response?.status === 422) {
                    // Validation errors
                    const errors = error.response.data.errors;
                    for (const field in errors) {
                        const fieldId = field.replace(/_/g, '');
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

        window.viewMyRequests = function () {
            window.location.href = '/tor/requests';
        };

        // Load user info when page loads
        loadUserInfo();
    </script>
</body>

</html>