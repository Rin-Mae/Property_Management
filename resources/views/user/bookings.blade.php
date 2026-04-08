<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Bookings - Hotel Management</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-bookings.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                <a href="/user/bookings" class="sidebar-link active">
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
                    <h1>Booking</h1>
                </div>

                <!-- Room Selection -->
                <div class="dashboard-card">
                    <h3>Available Rooms</h3>
                    <p style="color: #666; margin-bottom: 20px;">Click on any room to book</p>
                    <div id="roomsLoading" class="loading">Loading available rooms...</div>
                    <div id="roomsContainer" style="display: none;">
                        <div class="room-selection" id="roomSelection"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Room Details Modal -->
    <div id="roomDetailsModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Room Details</h2>
                <button type="button" class="close-btn" onclick="closeRoomDetailsModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="room-details">
                    <div class="room-details-image">
                        <img id="detailsRoomImage" src="" alt="Room"
                            style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px;">
                    </div>

                    <div class="room-details-info" style="margin-top: 2rem;">
                        <h3 id="detailsRoomName" style="font-size: 1.5rem; margin-bottom: 1rem;"></h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                            <div>
                                <p style="color: #666; margin-bottom: 0.5rem;"><strong>Room Type:</strong></p>
                                <p id="detailsRoomType" style="font-size: 1.1rem;">N/A</p>
                            </div>
                            <div>
                                <p style="color: #666; margin-bottom: 0.5rem;"><strong>Capacity:</strong></p>
                                <p id="detailsRoomCapacity" style="font-size: 1.1rem;">N/A</p>
                            </div>
                            <div>
                                <p style="color: #666; margin-bottom: 0.5rem;"><strong>Price per Night:</strong></p>
                                <p id="detailsRoomPrice" style="font-size: 1.3rem; color: #2d7f3d; font-weight: bold;">
                                    ₱0.00</p>
                            </div>
                            <div>
                                <p style="color: #666; margin-bottom: 0.5rem;"><strong>Status:</strong></p>
                                <p id="detailsRoomStatus" style="font-size: 1.1rem;">N/A</p>
                            </div>
                        </div>

                        <div style="background: #f0f8f5; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                            <p style="color: #333; line-height: 1.6;" id="detailsRoomDescription">
                                Comfortable and spacious room with modern amenities.
                            </p>
                        </div>

                        <div>
                            <h4 style="margin-bottom: 1rem;">Amenities:</h4>
                            <div id="detailsRoomAmenities"
                                style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRoomDetailsModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="proceedToBook()">
                    <i class="fas fa-arrow-right"></i> Book this Room
                </button>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Book Your Room</h2>
                <button type="button" class="close-btn" onclick="closeBookingModal()">&times;</button>
            </div>

            <form id="bookingForm">
                <div class="modal-body">
                    <div class="booking-form">
                        <div class="booking-select-text">
                            <strong id="selectedRoomDisplay">Select a room to continue</strong>
                        </div>

                        <!-- Guest Information Section -->
                        <div class="form-section">
                            <h3>Guest Information</h3>
                            <div class="guest-info-grid">
                                <div class="form-group">
                                    <label for="fullName">Full Name <span style="color: #d9534f;">*</span></label>
                                    <input type="text" id="fullName" name="fullName" placeholder="Full Name" required>
                                </div>
                                <div class="form-group">
                                    <label for="roomType">Room Type <span style="color: #d9534f;">*</span></label>
                                    <select id="roomType" name="roomType" disabled required>
                                        <option value="">Select Room Type</option>
                                    </select>
                                </div>
                            </div>
                            <div class="guest-info-grid">
                                <div class="form-group">
                                    <label for="contactNumber">Contact Number <span
                                            style="color: #d9534f;">*</span></label>
                                    <input type="tel" id="contactNumber" name="contactNumber"
                                        placeholder="Contact Number" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address <span style="color: #d9534f;">*</span></label>
                                    <input type="email" id="email" name="email" placeholder="Email Address" required>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details Section -->
                        <div class="form-section">
                            <h3>Booking Details</h3>
                            <div class="dates-grid">
                                <div class="form-group">
                                    <label for="checkInDate">Check-in Date <span
                                            style="color: #d9534f;">*</span></label>
                                    <input type="date" id="checkInDate" name="checkInDate" placeholder="YYYY-MM-DD"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="checkInTime">Check-in Time <span
                                            style="color: #d9534f;">*</span></label>
                                    <input type="time" id="checkInTime" name="checkInTime" placeholder="HH:MM"
                                        value="14:00" required>
                                </div>
                                <div class="form-group">
                                    <label for="checkOutDate">Check-out Date <span
                                            style="color: #d9534f;">*</span></label>
                                    <input type="date" id="checkOutDate" name="checkOutDate" placeholder="YYYY-MM-DD"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label for="checkOutTime">Check-out Time <span
                                            style="color: #d9534f;">*</span></label>
                                    <input type="time" id="checkOutTime" name="checkOutTime" placeholder="HH:MM"
                                        value="11:00" required>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Booking Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Success!</h2>
                <button type="button" class="close-btn" onclick="closeSuccessModal()">&times;</button>
            </div>

            <div class="modal-body text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Booking Request Submitted</h3>
                <p>Your booking request has been successfully submitted.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-primary" onclick="resetAfterBooking()">Done</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Error</h2>
                <button type="button" class="close-btn" onclick="closeErrorModal()">&times;</button>
            </div>

            <div class="modal-body text-center">
                <div class="error-icon" style="font-size: 3rem; color: #d9534f; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <p id="errorMessage">An error occurred. Please try again.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-primary" onclick="closeErrorModal()">OK</button>
            </div>
        </div>
    </div>

    </div>
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Flatpickr Date Picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/responsive.js') }}"></script>
    <script src="{{ asset('js/user-bookings.js') }}"></script>
</body>

</html>