<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Reports - Hotel Management</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-reports.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="/user/reports" class="sidebar-link active">
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
                    <h1>Reports</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="reports-stats">
                    <div class="stat-box blue">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Bookings</div>
                            <div class="stat-value" id="totalBookingsCount">0</div>
                        </div>
                    </div>

                    <div class="stat-box green">
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Payments Made</div>
                            <div class="stat-value" id="totalPaymentsCount">₱0</div>
                        </div>
                    </div>

                    <div class="stat-box orange">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Confirmed Bookings</div>
                            <div class="stat-value" id="confirmedBookingsCount">0</div>
                        </div>
                    </div>

                    <div class="stat-box red">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Cancelled / Rejected</div>
                            <div class="stat-value" id="cancelledBookingsCount">0</div>
                        </div>
                    </div>
                </div>

                <!-- Booking History Section -->
                <div class="report-section">
                    <div class="section-header">
                        <h2 class="section-title">Booking History</h2>
                        <div class="filter-group">
                            <select class="filter-select" id="bookingMonthFilter">
                                <option value="">This Month</option>
                                <option value="last-3-months">Last 3 Months</option>
                                <option value="last-6-months">Last 6 Months</option>
                                <option value="last-year">Last Year</option>
                                <option value="all">All Time</option>
                            </select>
                            <select class="filter-select" id="bookingStatusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <div class="action-buttons">
                                <button class="export-btn" onclick="downloadPDF('bookings')">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                                <button class="export-btn" onclick="printReport('bookings')">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>Reference No.</th>
                                    <th>Room Type</th>
                                    <th>Check-In / Check-Out</th>
                                    <th>Status</th>
                                    <th>Date Booked</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="bookingHistoryTable">
                            </tbody>
                        </table>
                        <div id="bookingNoData" class="no-data" style="display: none;">
                            No booking records found
                        </div>
                    </div>

                    <div class="pagination" id="bookingPagination">
                    </div>
                </div>

                <!-- Payment History Section -->
                <div class="report-section">
                    <div class="section-header">
                        <h2 class="section-title">Payment History</h2>
                        <div class="filter-group">
                            <select class="filter-select" id="paymentMonthFilter">
                                <option value="">This Month</option>
                                <option value="last-3-months">Last 3 Months</option>
                                <option value="last-6-months">Last 6 Months</option>
                                <option value="last-year">Last Year</option>
                                <option value="all">All Time</option>
                            </select>
                            <select class="filter-select" id="paymentStatusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <div class="action-buttons">
                                <button class="export-btn" onclick="downloadPDF('payments')">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                                <button class="export-btn" onclick="printReport('payments')">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>Reference No.</th>
                                    <th>Room</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Date Booked</th>
                                </tr>
                            </thead>
                            <tbody id="paymentHistoryTable">
                            </tbody>
                        </table>
                        <div id="paymentNoData" class="no-data" style="display: none;">
                            No payment records found
                        </div>
                    </div>

                    <div class="pagination" id="paymentPagination">
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/responsive.js') }}"></script>
    <script src="{{ asset('js/user-reports.js') }}"></script>
</body>

</html>