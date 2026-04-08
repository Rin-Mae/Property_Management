<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Profile - Hotel Management</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-profile.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .profile-card h2 {
            color: #333;
            margin-bottom: 25px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .form-section-title {
            font-weight: 600;
            color: #333;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .d-none {
            display: none !important;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #666;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .invalid-feedback.d-block {
            display: block;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 id="profileName">{{ Auth::user()->name ?? 'User' }}</h1>
            <p class="user-info" id="userInfo">{{ Auth::user()->email ?? 'Guest' }}</p>
        </div>

        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <ul class="sidebar-menu" id="sidebarMenu">
            <li>
                <a href="/user/dashboard" class="sidebar-link">
                    <i class="fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/user/bookings" class="sidebar-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="/user/reports" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="/user/profile" class="sidebar-link active">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <button class="logout-btn" onclick="handleLogout()"
                    style="width: 100%; margin-top: auto; margin-bottom: 0;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </li>
        </ul>
    </aside>

    <div class="main-layout">
        <main class="main-content">
            <div class="container profile-container">
                <div class="profile-card">
                    <h2>Edit Profile</h2>

                    <!-- Messages -->
                    <div id="successMessage" class="alert alert-success d-none" role="alert"></div>
                    <div id="errorMessage" class="alert alert-danger d-none" role="alert"></div>

                    <!-- Profile Form -->
                    <form id="profileForm">
                        <!-- Personal Information -->
                        <div class="form-section-title">Personal Information</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="first_name" name="first_name" required>
                                <div class="invalid-feedback" id="first_name-error"></div>
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name">
                                <div class="invalid-feedback" id="middle_name-error"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="last_name" name="last_name" required>
                                <div class="invalid-feedback" id="last_name-error"></div>
                            </div>
                            <div class="form-group">
                                <label for="suffix">Suffix</label>
                                <input type="text" id="suffix" name="suffix" placeholder="e.g. Jr., Sr.">
                                <div class="invalid-feedback" id="suffix-error"></div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section-title">Contact Information</div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number">
                            <div class="invalid-feedback" id="contact_number-error"></div>
                        </div>

                        <!-- Password Section -->
                        <div class="form-section-title">Change Password <span class="text-muted">(Optional)</span></div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password">
                            <small>Leave blank to keep current password. Minimum 8 characters if changing.</small>
                            <div class="invalid-feedback" id="password-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation">
                            <div class="invalid-feedback" id="password_confirmation-error"></div>
                        </div>

                        <!-- Buttons -->
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="/user/dashboard" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/responsive.js') }}"></script>
    <script src="{{ asset('js/user-profile.js') }}"></script>
</body>

</html>