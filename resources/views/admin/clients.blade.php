<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Client Management - Hotel Management System</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 id="profileName">Admin</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="sidebar-link">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/admin/rooms" class="sidebar-link">
                    <span>Rooms</span>
                </a>
            </li>
            <li>
                <a href="/admin/bookings" class="sidebar-link">
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="/admin/payments" class="sidebar-link">
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="/admin/reports" class="sidebar-link">
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="/admin/users" class="sidebar-link">
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="/admin/clients" class="sidebar-link active">
                    <span>Clients</span>
                </a>
            </li>
            <li>
                <a href="/admin/clients" class="sidebar-link active">
                    <span>Clients</span>
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
                    <h1>Client Management</h1>
                </div>

                <!-- Alerts -->
                <div id="alertContainer"></div>

                <!-- Clients Table -->
                <div class="dashboard-card">
                    <h3>All Clients</h3>

                    <!-- Search and Filter -->
                    <div class="search-filter-section">
                        <input type="text" id="searchInput" placeholder="Search by name..." class="search-input">
                    </div>

                    <div id="clientsLoading" class="loading">Loading clients...</div>
                    <div id="clientsContainer" style="display: none;">
                        <div class="users-table-wrapper">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact Number</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="clientsTableBody">
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="clientsPagination" class="pagination-controls" style="display: none;">
                            <button id="clientsPrevBtn" onclick="previousClientsPage()" class="pagination-btn">←
                                Previous</button>
                            <span id="clientsPageInfo" class="pagination-info">Page 1 of 1</span>
                            <button id="clientsNextBtn" onclick="nextClientsPage()" class="pagination-btn">Next
                                →</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Client Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="clientModalTitle">Edit Client</h2>
                <button class="close-btn" onclick="closeClientModal()">&times;</button>
            </div>

            <form id="clientForm" onsubmit="handleClientSubmit(event)" class="modal-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="clientId" name="client_id">

                    <!-- Personal Information Section -->
                    <div class="form-section-title">Personal Information</div>

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
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section-title">Contact Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                            <div class="error-message" id="emailError"></div>
                        </div>

                        <div class="form-group">
                            <label for="contactNumber">Contact Number *</label>
                            <input type="tel" id="contactNumber" name="contact_number" required>
                            <div class="error-message" id="contactNumberError"></div>
                        </div>
                    </div>

                    <!-- Address Information Section -->
                    <div class="form-section-title">Address Information</div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="address">Home Address *</label>
                            <input type="text" id="address" name="address" required>
                            <div class="error-message" id="addressError"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Update Client</button>
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
                <p>Are you sure you want to delete this client? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Client</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-clients.js') }}"></script>
</body>

</html>