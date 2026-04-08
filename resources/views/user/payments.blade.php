<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Payments - Hotel Management</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-payments.css') }}">
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
                    <h1>Payments Management</h1>
                </div>

                <!-- Pending Payments Section -->
                <div id="pendingPaymentsLoading" class="loading">Loading pending payments...</div>
                <div id="noPendingPayments" class="no-payments" style="display: none;">
                    <p><i class="fas fa-check"></i> All bookings are paid up.</p>
                </div>
                <div id="pendingPaymentsContainer" style="display: none;">
                    <div id="pendingPaymentsList"></div>
                </div>

                <!-- Payment History Section -->
                <div class="payments-history">
                    <h3>Payment History</h3>
                    <div id="paymentHistoryLoading" class="loading">Loading payment history...</div>
                    <div id="noPaymentHistory" class="no-payments" style="display: none;">
                        <p><i class="fas fa-inbox"></i> No payment history yet.</p>
                    </div>
                    <div id="paymentHistoryContainer" style="display: none;">
                        <div class="payments-wrapper">
                            <table class="payments-table">
                                <thead>
                                    <tr>
                                        <th>Reference No</th>
                                        <th>Room Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentHistoryTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End Payment History Section -->
            </div>
        </main>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal" id="paymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Payment Details</h2>
                <button class="modal-close" onclick="closePaymentModal()">×</button>
            </div>
            <div class="modal-body">
                <!-- Booking Information -->
                <div class="detail-section">
                    <h3>Booking Information</h3>
                    <div class="detail-items">
                        <div class="detail-item">
                            <span class="detail-item-label">Reference No.</span>
                            <span class="detail-item-value" id="modalRefNumber">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Room Type</span>
                            <span class="detail-item-value" id="modalRoomType">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Check-in</span>
                            <span class="detail-item-value" id="modalCheckIn">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Check-out</span>
                            <span class="detail-item-value" id="modalCheckOut">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Number of Guests</span>
                            <span class="detail-item-value" id="modalGuests">N/A</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="detail-section">
                    <h3>Payment Information</h3>
                    <div class="detail-items">
                        <div class="detail-item">
                            <span class="detail-item-label">Payment Method</span>
                            <span class="detail-item-value" id="modalPaymentMethod">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Payment Date</span>
                            <span class="detail-item-value" id="modalPaymentDate">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Amount</span>
                            <span class="detail-item-value" id="modalAmount">₱0.00</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Status</span>
                            <span class="detail-item-value" id="modalStatus">N/A</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Proof -->
                <div class="detail-section">
                    <h3>Payment Proof</h3>
                    <div id="modalProofContainer" style="display: none;">
                        <div class="image-preview-container show" style="margin-bottom: 1rem;">
                            <img id="modalProofImage" src="" alt="Payment Proof"
                                style="max-width: 100%; height: auto; border-radius: 4px;">
                            <div class="preview-filename" id="modalProofFilename"></div>
                        </div>
                        <a id="modalProofDownload" href="" target="_blank" class="modal-btn modal-btn-primary"
                            style="display: inline-block;">
                            <i class="fas fa-download"></i> Download Proof of Payment
                        </a>
                    </div>
                    <div id="modalNoProof" style="padding: 1rem; color: #666;">
                        No proof file available for this payment.
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="detail-section">
                    <h3>Additional Details</h3>
                    <div class="detail-items full">
                        <div class="detail-item">
                            <span class="detail-item-label">Full Name</span>
                            <span class="detail-item-value" id="modalFullName">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Notes</span>
                            <span class="detail-item-value" id="modalNotes">N/A</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-print" onclick="printPaymentDetails()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="modal-btn modal-btn-close" onclick="closePaymentModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Payment Method Modal -->
    <div class="modal" id="editPaymentModal">
        <div class="modal-content edit-modal-large">
            <div class="modal-header">
                <h2>Edit Payment Details</h2>
                <button class="modal-close" onclick="closeEditPaymentModal()">×</button>
            </div>
            <div class="modal-body">
                <!-- Booking Information -->
                <div class="detail-section">
                    <h3>Booking Information</h3>
                    <div class="detail-items">
                        <div class="detail-item">
                            <span class="detail-item-label">Reference No.</span>
                            <span class="detail-item-value" id="editModalRefNumber">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Room Type</span>
                            <span class="detail-item-value" id="editModalRoomType">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Check-in</span>
                            <span class="detail-item-value" id="editModalCheckIn">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Check-out</span>
                            <span class="detail-item-value" id="editModalCheckOut">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Number of Guests</span>
                            <span class="detail-item-value" id="editModalGuests">N/A</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-item-label">Amount</span>
                            <span class="detail-item-value" id="editModalAmount">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Information (Editable) -->
                <div class="detail-section">
                    <h3>Payment Information</h3>
                    <form id="editPaymentForm" class="edit-form">
                        <div class="detail-items">
                            <div class="detail-item">
                                <span class="detail-item-label">Full Name</span>
                                <span class="detail-item-value" id="editModalFullName">N/A</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-item-label">Payment Date</span>
                                <span class="detail-item-value" id="editModalPaymentDate">N/A</span>
                            </div>
                        </div>

                        <div class="form-row full" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label for="editPaymentMethod">Payment Method <span
                                        style="color: #d9534f;">*</span></label>
                                <select id="editPaymentMethod" name="payment_method" required
                                    onchange="toggleEditProofUpload()">
                                    <option value="">Select Payment Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <!-- Edit Proof of Payment Section (Hidden for Cash) -->
                        <div class="form-section" id="editProofSection" style="display: none; margin-top: 1.5rem;">
                            <h4>Update Proof of Payment</h4>
                            <div class="form-group">
                                <label for="editProofFile">Upload Proof of Payment <span
                                        style="color: #d9534f;">*</span></label>
                                <div class="file-input-wrapper">
                                    <label for="editFileInput" class="file-input-label">Choose File</label>
                                    <input type="file" id="editFileInput" name="proof_file"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <div class="file-info">Accepted: JPG, PNG, PDF</div>
                                </div>
                                <div class="image-preview-container" id="editImagePreview">
                                    <img id="editPreviewImage" src="" alt="Preview">
                                    <div class="preview-filename" id="editPreviewFilename"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-close" onclick="closeEditPaymentModal()">Cancel</button>
                <button class="modal-btn modal-btn-primary" onclick="submitEditPayment()">Update Payment</button>
            </div>
        </div>
    </div>

    <!-- Cancel Reservation Confirmation Modal -->
    <div class="modal" id="cancelConfirmModal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Cancel Reservation</h2>
                <button class="modal-close" onclick="closeCancelConfirmModal()">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this reservation?</p>
                <p style="color: #666; font-size: 0.9rem; margin-top: 1rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeCancelConfirmModal()">No, Keep It</button>
                <button class="modal-btn modal-btn-danger" onclick="confirmCancelReservation()">Yes, Cancel
                    Reservation</button>
            </div>
        </div>
    </div>

    <!-- Cancel Payment Confirmation Modal -->
    <div class="modal" id="cancelPaymentModal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Clear Payment Form</h2>
                <button class="modal-close" onclick="closeCancelPaymentModal()">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to clear this payment form?</p>
                <p style="color: #666; font-size: 0.9rem; margin-top: 1rem;">All entered information will be reset.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-secondary" onclick="closeCancelPaymentModal()">No, Keep
                    Editing</button>
                <button class="modal-btn modal-btn-danger" onclick="confirmCancelPaymentForm()">Yes, Clear Form</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/responsive.js') }}"></script>
    <script src="{{ asset('js/user-payments.js') }}"></script>
</body>

</html>