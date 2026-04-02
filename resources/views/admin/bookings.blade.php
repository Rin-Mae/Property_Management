<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Bookings Management - Hotel Management System</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-bookings.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="/admin/bookings" class="sidebar-link active">
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
                <a href="/admin/clients" class="sidebar-link">
                    <span>Clients</span>
                </a>
            </li>
            <li>
                <a href="/admin/clients" class="sidebar-link">
                    <span>Clients</span>
                </a>
            </li>
        </ul>

        <button class="logout-btn" onclick="handleLogout()">Logout</button>
    </aside>

    <div class="main-layout">
        <main class="main-content">
            <div class="container">
                <div class="bookings-header">
                    <h1>Bookings</h1>
                </div>

                <div class="bookings-card">
                    <div class="filter-section">
                        <div class="search-bar">
                            <input type="text" id="searchInput" placeholder="Search..." class="search-input">
                            <i class="fas fa-search"></i>
                        </div>
                        <label for="statusFilter">Status:</label>
                        <select id="statusFilter" class="status-select">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Approved</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="table-wrapper">
                        <table class="bookings-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Dates</th>
                                    <th>Guests</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                <tr>
                                    <td colspan="7" class="loading-text">Loading bookings...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-controls" id="paginationControls">
                        <!-- Pagination will be added here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- View Booking Modal -->
    <div id="viewBookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <button class="modal-close" onclick="closeModal('viewBookingModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewBookingContent">
                <!-- Booking details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('viewBookingModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateStatusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Booking Status</h2>
                <button class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="bookingId">
                    <div class="form-group">
                        <label for="newStatus">New Status:</label>
                        <select id="newStatus" required>
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Approved</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
                <button class="btn btn-primary" onclick="updateBookingStatus()">Update</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-bookings.js') }}"></script>
</body>

</html>