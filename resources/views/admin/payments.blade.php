<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payments Management - Hotel Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('NC Logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-payments.css') }}">
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
                <a href="/admin/users" class="sidebar-link">
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
                <div class="page-header">
                    <h1>Payments</h1>
                </div>

                <div class="payments-section">
                    <div class="filter-bar">
                        <label class="filter-label">Status:</label>
                        <select id="statusFilter" class="status-select" onchange="handleStatusChange()">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="rejected">Rejected</option>
                        </select>

                        <div class="search-input">
                            <input type="text" id="searchInput" placeholder="Search..." onkeyup="handleSearch()">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="payments-table">
                            <thead>
                                <tr>
                                    <th>Reference No.</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Submitted Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <button id="prevBtn" class="pagination-btn" onclick="previousPage()">Previous</button>
                        <span id="pageInfo" class="page-info">Page 1 of 1</span>
                        <button id="nextBtn" class="pagination-btn" onclick="nextPage()">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Payment Details Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Verify Payment</h2>
                <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            </div>

            <!-- Payment Proof Section (Prominent) -->
            <div class="modal-proof-section">
                <h3>Payment Proof</h3>
                <div class="proof-container">
                    <img id="proofImage" src="" alt="Payment Proof" style="display:none;" class="proof-image"
                        loading="lazy">
                    <p id="noProof" style="text-align: center; color: #999; margin: 0;">No payment proof uploaded</p>
                </div>
            </div>

            <!-- Payment Details Grid -->
            <div class="modal-body">
                <div class="verification-grid">
                    <div class="verification-card">
                        <h4>Booking Information</h4>
                        <div class="info-row">
                            <span class="label">Reference No:</span>
                            <span class="value" id="modalBookingRef">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Guest Name:</span>
                            <span class="value" id="modalGuestName">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Room:</span>
                            <span class="value" id="modalRoom">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Check-in:</span>
                            <span class="value" id="modalCheckIn">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Check-out:</span>
                            <span class="value" id="modalCheckOut">-</span>
                        </div>
                    </div>

                    <div class="verification-card">
                        <h4>Payment Information</h4>
                        <div class="info-row highlight">
                            <span class="label">Amount:</span>
                            <span class="value amount" id="modalAmount">₱0.00</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Payment Method:</span>
                            <span class="value" id="modalMethod">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Payment Date:</span>
                            <span class="value" id="modalDate">-</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Status:</span>
                            <span class="value" id="modalPaymentStatus">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="modal-footer" id="modalFooter">
                <button class="btn-reject" onclick="rejectPayment()">
                    <i class="fas fa-times-circle"></i> Reject Payment
                </button>
                <button class="btn-approve" onclick="approvePayment()">
                    <i class="fas fa-check-circle"></i> Approve Payment
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-payments.js') }}"></script>
</body>

</html>