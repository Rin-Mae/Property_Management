<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports - Hotel Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('NC Logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
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
                <a href="/admin/reports" class="sidebar-link active">
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
                <div class="page-header">
                    <h1>Reports</h1>
                </div>

                <!-- Summary Statistics Section -->
                <div class="summary-section">
                    <div class="summary-card blue">
                        <div class="summary-icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="summary-content">
                            <div class="summary-number" id="totalBookings">0</div>
                            <div class="summary-label">Total Bookings</div>
                        </div>
                    </div>

                    <div class="summary-card green">
                        <div class="summary-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="summary-content">
                            <div class="summary-number" id="totalRevenue">$0</div>
                            <div class="summary-label">Total Revenue</div>
                        </div>
                    </div>

                    <div class="summary-card orange">
                        <div class="summary-icon"><i class="fas fa-check"></i></div>
                        <div class="summary-content">
                            <div class="summary-number" id="approvedBookings">0</div>
                            <div class="summary-label">Approved Bookings</div>
                        </div>
                    </div>

                    <div class="summary-card red">
                        <div class="summary-icon"><i class="fas fa-clock"></i></div>
                        <div class="summary-content">
                            <div class="summary-number" id="pendingPayments">0</div>
                            <div class="summary-label">Pending Payments</div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-section">
                    <div class="chart-header">
                        <h2>Bookings per Month</h2>
                        <div class="chart-controls">
                            <label>From:</label>
                            <input type="date" id="fromDate" class="date-input">

                            <label>to</label>
                            <input type="date" id="toDate" class="date-input">

                            <select id="filterSelect" class="filter-select">
                                <option value="all">All</option>
                            </select>

                            <button class="export-btn" onclick="exportToPDF()">
                                <i class="fas fa-download"></i> Export to PDF
                            </button>
                            <button class="print-btn" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <div class="chart-container">
                        <canvas id="bookingsChart"></canvas>
                    </div>
                </div>

                <!-- Bookings Table Section -->
                <div class="table-section">
                    <div class="table-wrapper">
                        <table class="bookings-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Dates</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-reports.js') }}"></script>
</body>

</html>