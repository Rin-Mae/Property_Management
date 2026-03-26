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
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #2b2b2b 100%;
            color: white;
            padding: 2rem 1.5rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            margin: 0 auto 0.75rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .sidebar-header h1 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .user-info {
            font-size: 0.85rem;
            color: #ddd;
            word-break: break-word;
        }

        .sidebar-menu {
            list-style: none;
            margin-bottom: 2rem;
        }

        .sidebar-menu li {
            margin-bottom: 1rem;
        }

        .sidebar-menu button {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.3s, transform 0.2s;
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-menu button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar-menu .icon {
            font-size: 1.2rem;
        }

        header {
            display: none;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 67, 67, 0.8);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 0.95rem;
            margin-top: auto;
        }

        .logout-btn:hover {
            background: rgba(255, 67, 67, 1);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
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
            color: #007810;
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

        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .form-group select optgroup {
            font-weight: 600;
            color: #333;
        }

        .form-group select option {
            padding: 0.5rem;
            font-weight: normal;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="file"] {
            padding: 0.8rem;
            border: 2px dashed #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.3s;
            cursor: pointer;
            background: #fafafa;
        }

        .form-group input[type="file"]:hover {
            border-color: #667eea;
            background: #f5f5ff;
        }

        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input[readonly] {
            background: #f5f5f5;
            color: #666;
        }

        .form-group small {
            display: block;
            color: #999;
            font-size: 0.85rem;
            margin-top: 0.25rem;
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
            background: #007810 100%;
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
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 1.5rem;
            }

            .sidebar-header {
                margin-bottom: 1rem;
            }

            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .container {
                margin: 0;
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-avatar" id="profileAvatar">S</div>
            <h1 id="profileName">Student</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <button onclick="goToDashboard()" type="button">
                    <span>Dashboard</span>
                </button>
            </li>
            <li>
                <button onclick="goToCreateRequest()" type="button">
                    <span>New Request</span>
                </button>
            </li>
            <li>
                <button onclick="goToViewRequests()" type="button">
                    <span>My Requests</span>
                </button>
            </li>
        </ul>

        <button class="logout-btn" onclick="handleLogout()">Logout</button>
    </aside>

    <main class="main-content">
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
                    <div class="form-grid">
                        <div class="form-group required" style="grid-column: 1 / -1;">
                            <label for="permanentAddress">Permanent Address</label>
                            <input type="text" id="permanentAddress" name="permanent_address" required>
                            <span class="error-message" id="permanentAddressError"></span>
                        </div>
                    </div>
                </div>

                <!-- Academic Information Section -->
                <div class="form-section">
                    <h3>Academic Information</h3>
                    <div class="form-grid">
                        <div class="form-group required">
                            <label for="studentId">Student ID</label>
                            <input type="text" id="studentId" name="student_id" required readonly>
                            <span class="error-message" id="studentIdError"></span>
                        </div>
                        <div class="form-group required">
                            <label for="course">Course/Program</label>
                            <select id="course" name="course" required>
                                <option value="">-- Select Course/Program --</option>
                                <optgroup label="College Degree">
                                    <option value="Bachelor of Science in Geodetic Engineering (BSGE)">Bachelor of
                                        Science in Geodetic Engineering (BSGE)</option>
                                    <option value="Bachelor of Science in Accountancy (BSA)">Bachelor of Science in
                                        Accountancy (BSA)</option>
                                    <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary
                                        Education (BEEd)</option>
                                    <option value="Bachelor of Secondary Education (BSEd)">Bachelor of Secondary
                                        Education (BSEd)</option>
                                    <option value="Bachelor of Secondary Education - Math (BSEd Math)">Bachelor of
                                        Secondary Education - Major in Math (BSEd Math)</option>
                                    <option value="Bachelor of Secondary Education - English (BSEd English)">Bachelor of
                                        Secondary Education - Major in English (BSEd English)</option>
                                    <option value="Bachelor of Secondary Education - Filipino (BSEd Filipino)">Bachelor
                                        of Secondary Education - Major in Filipino (BSEd Filipino)</option>
                                    <option value="Bachelor of Secondary Education - Science (BSEd Science)">Bachelor of
                                        Secondary Education - Major in Science (BSEd Science)</option>
                                    <option value="Bachelor of Science in Criminology (BSCrim)">Bachelor of Science in
                                        Criminology (BSCrim)</option>
                                    <option value="Bachelor of Science in Nursing (BSN)">Bachelor of Science in Nursing
                                        (BSN)</option>
                                    <option value="Bachelor of Arts in Political Science (AB Polsci)">Bachelor of Arts
                                        in Political Science (AB Polsci)</option>
                                    <option value="Bachelor of Arts in English Language Studies (AB English)">Bachelor
                                        of Arts in English Language Studies (AB English)</option>
                                    <option value="Bachelor of Arts in Communication (ABCom)">Bachelor of Arts in
                                        Communication (ABCom)</option>
                                    <option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of
                                        Science in Business Administration (BSBA)</option>
                                    <option
                                        value="Bachelor of Science in Business Administration - Financial Management (BSBA-FM)">
                                        Bachelor of Science in Business Administration - Major in Financial Management
                                        (BSBA-FM)</option>
                                    <option
                                        value="Bachelor of Science in Business Administration - Marketing Management (BSBA-MM)">
                                        Bachelor of Science in Business Administration - Major in Marketing Management
                                        (BSBA-MM)</option>
                                    <option
                                        value="Bachelor of Science in Business Administration - Management Accounting (BSBA-MA)">
                                        Bachelor of Science in Business Administration - Major in Management Accounting
                                        (BSBA-MA)</option>
                                    <option
                                        value="Bachelor of Science in Business Administration - Human Resource Management (BSBA-HRM)">
                                        Bachelor of Science in Business Administration - Major in Human Resource
                                        Management (BSBA-HRM)</option>
                                    <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of
                                        Science in Information Technology (BSIT)</option>
                                    <option value="Bachelor of Science in Hospitality Management (BSHM)">Bachelor of
                                        Science in Hospitality Management (BSHM)</option>
                                </optgroup>
                                <optgroup label="Post Graduate Courses">
                                    <option value="Doctor of Philosophy (Ph.D)">Doctor of Philosophy (Ph.D)</option>
                                    <option value="Doctor of Education (Ed.D)">Doctor of Education (Ed.D)</option>
                                    <option value="Master of Arts in Education (MA.Ed)">Master of Arts in Education
                                        (MA.Ed)</option>
                                    <option
                                        value="Master of Arts in Education Major in Language and Literature (MA.Ed-L.L)">
                                        Master of Arts in Education Major in Language and Literature (MA.Ed-L.L)
                                    </option>
                                    <option value="Master in Public Administration (MPA)">Master in Public
                                        Administration (MPA)</option>
                                    <option value="Master in Business Administration (MBA)">Master in Business
                                        Administration (MBA)</option>
                                </optgroup>
                            </select>
                            <span class="error-message" id="courseError"></span>
                        </div>
                    </div>
                </div>

                <!-- Request Details Section -->
                <div class="form-section">
                    <h3>Request Details</h3>
                    <div class="form-grid">
                        <div class="form-group required" style="grid-column: 1 / -1;">
                            <label for="purpose">Purpose of Request</label>
                            <textarea id="purpose" name="purpose" required
                                placeholder="e.g., Scholarship application, Job application, Further studies"></textarea>
                            <span class="error-message" id="purposeError"></span>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="form-section">
                    <h3>Required Documents</h3>

                    <div class="form-grid">
                        <div class="form-group required" style="grid-column: 1 / -1;">
                            <label for="birthCertificate">Birth Certificate or Marriage Certificate</label>
                            <input type="file" id="birthCertificate" name="birth_certificate" required
                                accept=".pdf,.jpg,.jpeg,.png" />
                            <small>PDF, JPG, or PNG format allowed</small>
                            <span class="error-message" id="birthCertificateError"></span>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group required" style="grid-column: 1 / -1;">
                            <label for="receipt">Receipt</label>
                            <input type="file" id="receipt" name="receipt" required accept=".pdf,.jpg,.jpeg,.png" />
                            <small>PDF, JPG, or PNG format allowed</small>
                            <span class="error-message" id="receiptError"></span>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group required" style="grid-column: 1 / -1;">
                            <label for="requirements">Requirements (Past TOR or Supporting Documents) - Maximum 5
                                files</label>
                            <input type="file" id="requirements" name="requirements" required multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                            <small>PDF, JPG, PNG, DOC, or DOCX format allowed. You can select up to 5 files.</small>
                            <span class="error-message" id="requirementsError"></span>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" id="submitBtn">Submit TOR Request</button>
                    <button type="button" class="view-requests-btn" onclick="viewMyRequests()">View My Requests</button>
                </div>
            </form>
        </div>
    </main>

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

        // Load user info on page load
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
                successMsg.textContent = '✓ TOR request submitted successfully!';
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

        window.goToDashboard = function () {
            window.location.href = '/dashboard';
        };

        window.goToViewRequests = function () {
            window.location.href = '/tor/requests';
        };

        window.goToCreateRequest = function () {
            window.location.href = '/tor/create';
        };

        // Load user info when page loads
        loadUserInfo();
    </script>
</body>

</html>