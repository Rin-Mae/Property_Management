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
                <a href="/admin/payments" class="sidebar-link active">
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
                                    <th>Payment ID</th>
                                    <th>Guest Name</th>
                                    <th>Booking Ref No.</th>
                                    <th>Room</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
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
                <h2>Payment Details</h2>
                <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Full Name:</label>
                            <p id="modalGuestName"></p>
                        </div>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Booking Ref No:</label>
                            <p id="modalBookingRef"></p>
                        </div>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Room:</label>
                            <p id="modalRoom"></p>
                        </div>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Amount Paid:</label>
                            <p id="modalAmount"></p>
                        </div>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Payment Method:</label>
                            <p id="modalMethod"></p>
                        </div>
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>Date of Payment:</label>
                            <p id="modalDate"></p>
                        </div>
                    </div>
                </div>

                <div class="modal-receipt">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 280'%3E%3Crect fill='%23f5f5f5' width='200' height='280'/%3E%3Ctext x='100' y='40' font-size='16' font-weight='bold' text-anchor='middle' fill='%23333'%3ERECEIPT%3C/text%3E%3Cline x1='20' y1='50' x2='180' y2='50' stroke='%23ddd' stroke-width='1'/%3E%3Ctext x='100' y='80' font-size='12' text-anchor='middle' fill='%23666'%3EPayment Confirmation%3C/text%3E%3Ctext x='100' y='110' font-size='14' font-weight='bold' text-anchor='middle' fill='%23333' id='receiptAmount'%3E₱0.00%3C/text%3E%3Ctext x='100' y='140' font-size='10' text-anchor='middle' fill='%23999'%3EThank You%3C/text%3E%3C/svg%3E"
                        alt="Receipt" class="receipt-image">
                </div>
            </div>

            <div class="modal-footer" id="modalFooter">
                <button class="btn-approve" onclick="approvePayment()">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn-reject" onclick="rejectPayment()">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-payments.js') }}"></script>
</body>

</html>