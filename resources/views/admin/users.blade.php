<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>User Management - Hotel Management System</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 id="profileName">{{ Auth::user()->name ?? 'Admin' }}</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="sidebar-link">
                    <i class="fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/admin/rooms" class="sidebar-link">
                    <i class="fas fa-door-open"></i>
                    <span>Rooms</span>
                </a>
            </li>
            <li>
                <a href="/admin/bookings" class="sidebar-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="/admin/reports" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="/admin/users" class="sidebar-link active">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
        </ul>

        <button class="logout-btn" onclick="handleLogout()">Logout</button>
    </aside>

    <div class="main-layout">
        <main class="main-content">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1>User Management</h1>
                </div>

                <!-- Alerts -->
                <div id="alertContainer"></div>

                <!-- Combined Users & Clients Card -->
                <div class="dashboard-card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>All Users</h3>
                        <button class="btn btn-primary" onclick="openCreateUserModal()">+ Create New User</button>
                    </div>

                    <!-- Search and Filter -->
                    <div class="search-filter-section" style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <input type="text" id="searchInput" placeholder="Search by name or email..."
                            class="search-input" style="flex: 1;">
                        <select id="roleFilter" class="role-filter" style="min-width: 150px;">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="client">Client</option>
                        </select>
                    </div>

                    <div id="combinedLoading" class="loading">Loading users...</div>
                    <div id="combinedContainer" style="display: none;">
                        <div class="users-table-wrapper">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="combinedTableBody">
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="combinedPagination" class="pagination-controls" style="display: none;">
                            <button id="combinedPrevBtn" onclick="previousUsersPage()" class="pagination-btn">←
                                Previous</button>
                            <span id="combinedPageInfo" class="pagination-info">Page 1 of 1</span>
                            <button id="combinedNextBtn" onclick="nextUsersPage()" class="pagination-btn">Next
                                →</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="userModalTitle">Create New User</h2>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>

            <form id="userForm" onsubmit="handleUserSubmit(event)" class="modal-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="userId" name="user_id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="first_name" required>
                            <div class="error-message" id="firstNameError"></div>
                        </div>

                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middle_name">
                            <div class="error-message" id="middleNameError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" required>
                            <div class="error-message" id="lastNameError"></div>
                        </div>

                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <input type="text" id="suffix" name="suffix">
                            <div class="error-message" id="suffixError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                            <div class="error-message" id="emailError"></div>
                        </div>

                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="client">Client</option>
                            </select>
                            <input type="hidden" id="roleHidden" name="role">
                            <div class="error-message" id="roleError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="contactNumber">Contact Number</label>
                            <input type="tel" id="contactNumber" name="contact_number">
                            <div class="error-message" id="contactNumberError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password">
                            <div class="error-message" id="passwordError"></div>
                        </div>

                        <div class="form-group">
                            <label for="passwordConfirm">Confirm Password</label>
                            <input type="password" id="passwordConfirm" name="password_confirmation">
                            <div class="error-message" id="passwordConfirmError"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete User</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-users.js') }}"></script>

    <script>
        // Load initial users data
        document.addEventListener('DOMContentLoaded', fu nction() {
            loadUsers();
        });
    </script>
</body>

</html>