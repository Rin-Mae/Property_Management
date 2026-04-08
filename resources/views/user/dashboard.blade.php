<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Dashboard - Hotel Management</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="user-dashboard">
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
                <a href="/user/dashboard" class="sidebar-link active">
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
                <a href="/user/profile" class="sidebar-link">
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
            <div class="container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1>Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card bookings-card">
                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-label">My Bookings</div>
                        <div class="stat-number" id="myBookingsCount">0</div>
                    </div>
                    <div class="stat-card pending-card">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-label">Pending Requests</div>
                        <div class="stat-number" id="pendingRequestsCount">0</div>
                    </div>
                    <div class="stat-card payments-card">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-label">Pending Payments</div>
                        <div class="stat-number" id="pendingPaymentsCount">0</div>
                    </div>
                    <div class="stat-card confirmed-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-label">Confirmed Bookings</div>
                        <div class="stat-number" id="confirmedBookingsCount">0</div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="dashboard-card">
                    <h3>Booking Calendar</h3>
                    <div class="calendar-container">
                        <div class="calendar-controls">
                            <button onclick="previousMonth()" class="calendar-btn">← Previous</button>
                            <h4 id="currentMonthYear">April 2026</h4>
                            <button onclick="nextMonth()" class="calendar-btn">Next →</button>
                        </div>
                        <div class="calendar-wrapper">
                            <div class="calendar-header">
                                <div class="calendar-weekday">Sun</div>
                                <div class="calendar-weekday">Mon</div>
                                <div class="calendar-weekday">Tue</div>
                                <div class="calendar-weekday">Wed</div>
                                <div class="calendar-weekday">Thu</div>
                                <div class="calendar-weekday">Fri</div>
                                <div class="calendar-weekday">Sat</div>
                            </div>
                            <div class="calendar-grid" id="calendarGrid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Details Section -->
                <div class="dashboard-card" id="calendarDetailsSection" style="display: none;">
                    <h3>Bookings for <span id="selectedDateDisplay">Selected Date</span></h3>
                    <div id="calendarBookingsContainer">
                        <div class="loading">Loading bookings...</div>
                    </div>
                </div>

                <!-- Recent Bookings Section -->
                <div class="dashboard-card">
                    <h3>Recent Bookings</h3>

                    <div id="bookingsLoading" class="loading">Loading bookings...</div>
                    <div id="bookingsContainer" style="display: none;">
                        <div class="bookings-wrapper">
                            <table class="bookings-table">
                                <thead>
                                    <tr>
                                        <th>Reference No.</th>
                                        <th>Room Type</th>
                                        <th>Check-in / Checkout</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bookingsTableBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="noBookingsMsg" style="text-align: center; padding: 2rem; color: #666;">
                            No bookings found
                        </div>

                        <!-- Pagination Controls for Bookings -->
                        <div id="bookingsPagination" class="pagination-controls" style="display: none;">
                            <button id="bookingsPrevBtn" onclick="previousUserBookingsPage()" class="pagination-btn">←
                                Previous</button>
                            <div id="bookingsPageNumbers" class="page-numbers">
                            </div>
                            <span id="bookingsPageInfo" class="pagination-info">Page 1 of 1</span>
                            <button id="bookingsNextBtn" onclick="nextUserBookingsPage()" class="pagination-btn">Next
                                →</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingDetailsModal" class="modal">
        <div class="modal-content booking-modal">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <button class="modal-close" onclick="closeBookingModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="booking-details-container">
                    <div class="details-row">
                        <div class="detail-item">
                            <label>Reference Number</label>
                            <p id="modalReferenceNo">-</p>
                        </div>
                        <div class="detail-item">
                            <label>Booking Status</label>
                            <p id="modalBookingStatus">-</p>
                        </div>
                    </div>

                    <div class="details-row">
                        <div class="detail-item">
                            <label>Room Type</label>
                            <p id="modalRoomType">-</p>
                        </div>
                        <div class="detail-item">
                            <label>Number of Nights</label>
                            <p id="modalNights">-</p>
                        </div>
                    </div>

                    <div class="details-row">
                        <div class="detail-item">
                            <label>Check-in Date</label>
                            <p id="modalCheckIn">-</p>
                        </div>
                        <div class="detail-item">
                            <label>Check-out Date</label>
                            <p id="modalCheckOut">-</p>
                        </div>
                    </div>

                    <div class="details-row">
                        <div class="detail-item">
                            <label>Guest Name</label>
                            <p id="modalGuestName">-</p>
                        </div>
                        <div class="detail-item">
                            <label>Guest Email</label>
                            <p id="modalGuestEmail">-</p>
                        </div>
                    </div>

                    <div class="details-row">
                        <div class="detail-item">
                            <label>Guest Phone</label>
                            <p id="modalGuestPhone">-</p>
                        </div>
                        <div class="detail-item">
                            <label>Total Price</label>
                            <p id="modalTotalPrice" class="price-highlight">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeBookingModal()">Close</button>
                <button class="btn-primary" id="modalActionBtn" onclick="handleBookingAction()">
                    <span id="actionBtnText">View More</span>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/responsive.js') }}"></script>
    <script src="{{ asset('js/user-dashboard.js') }}"></script>
</body>

</html>