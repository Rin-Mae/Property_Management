// Bookings Management JavaScript

const BOOKINGS_PER_PAGE = 10;
let allBookings = [];
let currentPage = 1;
let filteredBookings = [];
let currentStatusFilter = '';
let currentSearchFilter = '';

// Initialize
document.addEventListener('DOMContentLoaded', function () {
    loadBookings();
    setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
    document.getElementById('statusFilter').addEventListener('change', filterBookings);
    document.getElementById('searchInput').addEventListener('input', debounce(filterBookings, 300));
}

// Load Bookings
function loadBookings() {
    showLoading();

    // Get user credentials from localStorage
    const authToken = localStorage.getItem('auth_token');
    const userId = localStorage.getItem('user_id');
    const userName = localStorage.getItem('user_name');

    document.getElementById('profileName').textContent = userName || 'Admin';
    document.getElementById('userInfo').textContent = 'Administrator';

    // Fetch bookings from API
    axios.get('/api/bookings', {
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        allBookings = response.data.data || response.data;
        filteredBookings = [...allBookings];
        displayBookings();
    })
    .catch(error => {
        console.error('Error loading bookings:', error);
        document.getElementById('bookingsTableBody').innerHTML = `
            <tr>
                <td colspan="7" class="loading-text">
                    <span style="color: #e74c3c;">Error loading bookings. Please try again.</span>
                </td>
            </tr>
        `;
    });
}



// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Filter Bookings
function filterBookings() {
    currentStatusFilter = document.getElementById('statusFilter').value;
    currentSearchFilter = document.getElementById('searchInput').value.toLowerCase();
    currentPage = 1;

    filteredBookings = allBookings.filter(booking => {
        const statusMatch = !currentStatusFilter || booking.status === currentStatusFilter;
        const searchMatch = !currentSearchFilter || 
                          booking.guest_name.toLowerCase().includes(currentSearchFilter) ||
                          booking.id.toLowerCase().includes(currentSearchFilter) ||
                          booking.room_name.toLowerCase().includes(currentSearchFilter);
        
        return statusMatch && searchMatch;
    });

    displayBookings();
}

// Display Bookings
function displayBookings() {
    const tbody = document.getElementById('bookingsTableBody');
    
    if (filteredBookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading-text">No bookings found</td></tr>';
        document.getElementById('paginationControls').innerHTML = '';
        return;
    }

    const startIndex = (currentPage - 1) * BOOKINGS_PER_PAGE;
    const endIndex = startIndex + BOOKINGS_PER_PAGE;
    const paginatedBookings = filteredBookings.slice(startIndex, endIndex);

    tbody.innerHTML = paginatedBookings.map(booking => createBookingRow(booking)).join('');
    displayPagination();
}

// Create Booking Row HTML
function createBookingRow(booking) {
    const statusBadge = getStatusBadge(booking.status);
    const actionButtons = getActionButtons(booking);

    return `
        <tr>
            <td class="booking-id">${booking.id}</td>
            <td class="guest-name">${booking.guest_name}</td>
            <td class="room-name">${booking.room_name}</td>
            <td class="dates">
                ${booking.check_in} - ${booking.check_out}
            </td>
            <td class="guest-count">${booking.guests}</td>
            <td>${statusBadge}</td>
            <td class="action-buttons">${actionButtons}</td>
        </tr>
    `;
}

// Get Status Badge HTML
function getStatusBadge(status) {
    const statusMap = {
        'pending': 'Pending',
        'confirmed': 'Approved',
        'checked_in': 'Checked In',
        'checked_out': 'Completed',
        'cancelled': 'Cancelled'
    };

    return `<span class="status-badge ${status}">${statusMap[status] || status}</span>`;
}

// Get Action Buttons HTML
function getActionButtons(booking) {
    let buttons = `<button class="btn-action btn-view" onclick="viewBooking(${booking.reservation_id})">View</button>`;

    if (booking.status === 'pending') {
        buttons += `
            <button class="btn-action btn-approve" onclick="openUpdateStatusModal(${booking.reservation_id})">Approve</button>
        `;
    } else if (booking.status === 'confirmed') {
        buttons += `
            <button class="btn-action btn-approve" onclick="openUpdateStatusModal(${booking.reservation_id})">Check In</button>
        `;
    } else if (booking.status === 'checked_in') {
        buttons += `
            <button class="btn-action btn-approve" onclick="openUpdateStatusModal(${booking.reservation_id})">Check Out</button>
        `;
    }

    if (booking.status === 'pending' || booking.status === 'confirmed') {
        buttons += `
            <button class="btn-action btn-reject" onclick="cancelBooking(${booking.reservation_id})">Reject</button>
        `;
    }

    return buttons;
}

// View Booking Details
function viewBooking(bookingId) {
    const booking = allBookings.find(b => b.reservation_id === bookingId);
    if (!booking) return;

    const statusMap = {
        'pending': 'Pending',
        'confirmed': 'Approved',
        'checked_in': 'Checked In',
        'checked_out': 'Completed',
        'cancelled': 'Cancelled'
    };

    const detailsHTML = `
        <div class="booking-details">
            <div class="detail-item">
                <span class="detail-label">Booking ID</span>
                <span class="detail-value">${booking.id}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Guest Name</span>
                <span class="detail-value">${booking.guest_name}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Room</span>
                <span class="detail-value">${booking.room_name}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">${statusMap[booking.status] || booking.status}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Check-in Date</span>
                <span class="detail-value">${booking.check_in}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Check-out Date</span>
                <span class="detail-value">${booking.check_out}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Number of Guests</span>
                <span class="detail-value">${booking.guests}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Total Price</span>
                <span class="detail-value">₱${booking.total_price.toLocaleString()}</span>
            </div>
            <div class="detail-item full-width">
                <span class="detail-label">Notes</span>
                <span class="detail-value">${booking.notes || 'No notes'}</span>
            </div>
        </div>
    `;

    document.getElementById('viewBookingContent').innerHTML = detailsHTML;
    openModal('viewBookingModal');
}

// Open Update Status Modal
function openUpdateStatusModal(bookingId) {
    const booking = allBookings.find(b => b.reservation_id === bookingId);
    if (!booking) return;

    document.getElementById('bookingId').value = bookingId;
    document.getElementById('newStatus').value = booking.status;
    openModal('updateStatusModal');
}

// Update Booking Status
function updateBookingStatus() {
    const bookingId = parseInt(document.getElementById('bookingId').value);
    const newStatus = document.getElementById('newStatus').value;

    if (!newStatus) {
        alert('Please select a status');
        return;
    }

    const booking = allBookings.find(b => b.reservation_id === bookingId);
    if (booking) {
        booking.status = newStatus;
        filterBookings();
        closeModal('updateStatusModal');
        alert('Booking status updated successfully!');
    }
}

// Cancel Booking
function cancelBooking(bookingId) {
    if (confirm('Are you sure you want to reject this booking?')) {
        const booking = allBookings.find(b => b.reservation_id === bookingId);
        if (booking) {
            booking.status = 'cancelled';
            filterBookings();
            alert('Booking rejected successfully!');
        }
    }
}

// Display Pagination
function displayPagination() {
    const totalPages = Math.ceil(filteredBookings.length / BOOKINGS_PER_PAGE);
    const paginationControls = document.getElementById('paginationControls');

    if (totalPages <= 1) {
        paginationControls.innerHTML = '';
        return;
    }

    let html = `
        <button class="pagination-btn" onclick="previousPage()" ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        <span class="pagination-info">
            Page ${currentPage} of ${totalPages}
        </span>
        <button class="pagination-btn" onclick="nextPage()" ${currentPage === totalPages ? 'disabled' : ''}>
            Next <i class="fas fa-chevron-right"></i>
        </button>
    `;

    paginationControls.innerHTML = html;
}

// Next Page
function nextPage() {
    const totalPages = Math.ceil(filteredBookings.length / BOOKINGS_PER_PAGE);
    if (currentPage < totalPages) {
        currentPage++;
        displayBookings();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Previous Page
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        displayBookings();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function () {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });
});

// Show Loading
function showLoading() {
    document.getElementById('bookingsTableBody').innerHTML = `
        <tr>
            <td colspan="7" class="loading-text">
                <i class="fas fa-spinner fa-spin"></i> Loading bookings...
            </td>
        </tr>
    `;
}
