// Dashboard state
let userDashboardState = {
    bookings: {
        data: [],
        totalCount: 0,
        currentPage: 1,
        totalPages: 1,
        perPage: 5
    },
    stats: {
        myBookings: 0,
        pendingRequests: 0,
        pendingPayments: 0,
        confirmedBookings: 0
    },
    calendar: {
        currentDate: new Date(),
        bookings: [],
        selectedDate: null
    }
};

/**
 * Initialize dashboard on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    updateProfileName();
    loadDashboardStats();
    loadUserBookings();
    initializeUserCalendar();
});

/**
 * Update profile name from API
 */
async function updateProfileName() {
    try {
        const response = await axios.get('/api/user');
        const user = response.data;
        
        const profileName = document.getElementById('profileName');
        if (profileName && user.name) {
            profileName.textContent = user.name;
        }
        
        const userInfo = document.getElementById('userInfo');
        if (userInfo && user.email) {
            userInfo.textContent = user.email;
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

/**
 * Load user's booking statistics
 */
async function loadDashboardStats() {
    try {
        const response = await axios.get('/api/bookings');
        const bookings = response.data.data || response.data;
        
        // Calculate stats from bookings
        const stats = {
            myBookings: bookings.length,
            pendingRequests: bookings.filter(b => b.status === 'pending').length,
            pendingPayments: bookings.filter(b => b.payment_status === 'pending' || (b.status === 'confirmed' && !b.payment_date)).length,
            confirmedBookings: bookings.filter(b => b.status === 'confirmed' || b.status === 'checked_in').length
        };

        userDashboardState.stats = stats;

        // Update stat cards
        const myBookingsEl = document.getElementById('myBookingsCount');
        const pendingRequestsEl = document.getElementById('pendingRequestsCount');
        const pendingPaymentsEl = document.getElementById('pendingPaymentsCount');
        const confirmedBookingsEl = document.getElementById('confirmedBookingsCount');

        if (myBookingsEl) myBookingsEl.textContent = stats.myBookings;
        if (pendingRequestsEl) pendingRequestsEl.textContent = stats.pendingRequests;
        if (pendingPaymentsEl) pendingPaymentsEl.textContent = stats.pendingPayments;
        if (confirmedBookingsEl) confirmedBookingsEl.textContent = stats.confirmedBookings;
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

/**
 * Load user's bookings from API
 */
async function loadUserBookings(page = 1) {
    const bookingsLoading = document.getElementById('bookingsLoading');
    const bookingsContainer = document.getElementById('bookingsContainer');
    const bookingsTableBody = document.getElementById('bookingsTableBody');
    const bookingsWrapper = document.querySelector('.bookings-wrapper');
    const noBookingsMsg = document.getElementById('noBookingsMsg');

    if (bookingsLoading) {
        bookingsLoading.style.display = 'block';
    }
    if (bookingsContainer) {
        bookingsContainer.style.display = 'none';
    }

    try {
        // Use the reports endpoint for paginated bookings
        const response = await axios.get('/api/reports/bookings', {
            params: { page: page }
        });
        
        const bookingsData = response.data.data || [];
        const pagination = response.data.pagination || {};

        if (bookingsLoading) bookingsLoading.style.display = 'none';
        if (bookingsContainer) bookingsContainer.style.display = 'block';

        if (!bookingsData || bookingsData.length === 0) {
            if (bookingsWrapper) bookingsWrapper.style.display = 'none';
            if (noBookingsMsg) noBookingsMsg.style.display = 'block';
            // Hide pagination if no bookings
            const paginationControls = document.getElementById('bookingsPagination');
            if (paginationControls) paginationControls.style.display = 'none';
            return;
        }

        if (bookingsWrapper) bookingsWrapper.style.display = 'block';
        if (noBookingsMsg) noBookingsMsg.style.display = 'none';

        // Update dashboard state
        userDashboardState.bookings = {
            data: bookingsData,
            totalCount: pagination.total || bookingsData.length,
            currentPage: pagination.current_page || page,
            totalPages: pagination.last_page || 1,
            perPage: pagination.per_page || 10
        };

        // Display bookings in table
        if (bookingsTableBody) {
            bookingsTableBody.innerHTML = bookingsData.map(booking => `
                <tr>
                    <td><strong>${booking.id || booking.reference_no || 'N/A'}</strong></td>
                    <td>${booking.room_type || 'N/A'}</td>
                    <td>
                        ${booking.check_in || booking.check_in_date || '-'} - ${booking.check_out || booking.check_out_date || '-'}
                    </td>
                    <td>
                        <span class="booking-status ${booking.status.toLowerCase()}">
                            ${capitalizeFirst(booking.status)}
                        </span>
                    </td>
                    <td>
                        <button class="table-action-btn view" onclick="viewBookingDetails('${booking.reservation_id || booking.id}')">
                            View
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Update pagination controls
        updateUserBookingsPagination();
    } catch (error) {
        console.error('Error loading bookings:', error);
        if (bookingsLoading) {
            bookingsLoading.textContent = 'Error loading bookings. Please try again.';
            bookingsLoading.style.display = 'block';
        }
    }
}

/**
 * Update pagination controls for user bookings
 */
function updateUserBookingsPagination() {
    const paginationControls = document.getElementById('bookingsPagination');
    const pageInfo = document.getElementById('bookingsPageInfo');
    const prevBtn = document.getElementById('bookingsPrevBtn');
    const nextBtn = document.getElementById('bookingsNextBtn');
    const pageNumbersContainer = document.getElementById('bookingsPageNumbers');

    if (!paginationControls) return;

    // Show pagination if there's data
    if (userDashboardState.bookings.data.length > 0) {
        paginationControls.style.display = 'flex';
    } else {
        paginationControls.style.display = 'none';
        return;
    }
    
    if (pageInfo) {
        pageInfo.textContent = `Page ${userDashboardState.bookings.currentPage} of ${userDashboardState.bookings.totalPages}`;
    }
    
    if (prevBtn) {
        prevBtn.disabled = userDashboardState.bookings.currentPage <= 1;
    }
    
    if (nextBtn) {
        nextBtn.disabled = userDashboardState.bookings.currentPage >= userDashboardState.bookings.totalPages;
    }

    // Generate page numbers - always show all pages even if <= 5
    if (pageNumbersContainer) {
        pageNumbersContainer.innerHTML = '';
        const totalPages = userDashboardState.bookings.totalPages;
        const currentPage = userDashboardState.bookings.currentPage;
        const maxVisible = 5;

        let pages = [];
        if (totalPages <= maxVisible) {
            // Show all pages if 5 or less
            pages = Array.from({length: totalPages}, (_, i) => i + 1);
        } else {
            // For more than 5 pages, show smart ellipsis
            pages.push(1);
            
            let start = Math.max(2, currentPage - 1);
            let end = Math.min(totalPages - 1, currentPage + 1);
            
            if (start > 2) {
                pages.push('...');
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            if (end < totalPages - 1) {
                pages.push('...');
            }
            
            pages.push(totalPages);
        }

        pages.forEach(page => {
            if (page === '...') {
                const span = document.createElement('span');
                span.className = 'page-ellipsis';
                span.textContent = '...';
                pageNumbersContainer.appendChild(span);
            } else {
                const btn = document.createElement('button');
                btn.className = 'page-btn';
                btn.textContent = page;
                
                if (page === currentPage) {
                    btn.classList.add('active');
                    btn.disabled = true;
                } else {
                    btn.onclick = () => goToUserBookingsPage(page);
                }
                
                pageNumbersContainer.appendChild(btn);
            }
        });
    }
}

/**
 * Go to specific page for user bookings
 */
function goToUserBookingsPage(page) {
    loadUserBookings(page);
}

/**
 * Go to previous page for user bookings
 */
function previousUserBookingsPage() {
    if (userDashboardState.bookings.currentPage > 1) {
        loadUserBookings(userDashboardState.bookings.currentPage - 1);
    }
}

/**
 * Go to next page for user bookings
 */
function nextUserBookingsPage() {
    if (userDashboardState.bookings.currentPage < userDashboardState.bookings.totalPages) {
        loadUserBookings(userDashboardState.bookings.currentPage + 1);
    }
}

/**
 * Format date to readable format (shorter version for tables)
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Capitalize first letter
 */
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * View booking details
 */
function viewBookingDetails(bookingId) {
    // Find booking in the data
    const booking = userDashboardState.bookings.data.find(b => b.id === parseInt(bookingId) || b.reservation_id === parseInt(bookingId));
    
    if (!booking) {
        alert('Booking not found');
        return;
    }

    // Calculate number of nights
    const checkInDate = new Date(booking.check_in_date || booking.check_in);
    const checkOutDate = new Date(booking.check_out_date || booking.check_out);
    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

    // Format dates for display
    const checkInFormatted = formatDate(booking.check_in_date || booking.check_in);
    const checkOutFormatted = formatDate(booking.check_out_date || booking.check_out);

    // Populate modal fields
    document.getElementById('modalReferenceNo').textContent = booking.reference_no || booking.id;
    document.getElementById('modalRoomType').textContent = booking.room_type || '-';
    document.getElementById('modalCheckIn').textContent = checkInFormatted;
    document.getElementById('modalCheckOut').textContent = checkOutFormatted;
    document.getElementById('modalNights').textContent = nights + (nights === 1 ? ' night' : ' nights');
    document.getElementById('modalTotalPrice').textContent = 'PHP ' + (booking.total_price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // Guest details if available
    if (booking.guest_name) {
        document.getElementById('modalGuestName').textContent = booking.guest_name;
    } else {
        document.getElementById('modalGuestName').textContent = '-';
    }
    
    if (booking.guest_email) {
        document.getElementById('modalGuestEmail').textContent = booking.guest_email;
    } else {
        document.getElementById('modalGuestEmail').textContent = '-';
    }
    
    if (booking.guest_phone) {
        document.getElementById('modalGuestPhone').textContent = booking.guest_phone;
    } else {
        document.getElementById('modalGuestPhone').textContent = '-';
    }

    // Set status with appropriate styling
    const statusElement = document.getElementById('modalBookingStatus');
    statusElement.textContent = capitalizeFirst(booking.status || '-');
    statusElement.className = 'status-badge status-' + (booking.status || 'unknown').toLowerCase();

    // Set action button based on status
    const actionBtn = document.getElementById('modalActionBtn');
    const actionBtnText = document.getElementById('actionBtnText');
    
    if (booking.status === 'pending') {
        actionBtnText.textContent = 'Confirm Booking';
        actionBtn.style.display = 'block';
    } else if (booking.status === 'confirmed') {
        actionBtnText.textContent = 'Make Payment';
        actionBtn.style.display = 'block';
    } else if (booking.status === 'cancelled') {
        actionBtn.style.display = 'none';
    } else {
        actionBtnText.textContent = 'View More';
        actionBtn.style.display = 'block';
    }

    // Store current booking ID for action handling
    document.body.currentBookingId = booking.id || bookingId;

    // Show modal
    openBookingModal();
}

/**
 * Open booking modal
 */
function openBookingModal() {
    const modal = document.getElementById('bookingDetailsModal');
    
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close booking modal
 */
function closeBookingModal() {
    const modal = document.getElementById('bookingDetailsModal');
    
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

/**
 * Handle booking action (confirm, payment, etc.)
 */
function handleBookingAction() {
    const bookingId = document.body.currentBookingId;
    const booking = userDashboardState.bookings.data.find(b => b.id === bookingId);
    
    if (!booking) return;

    if (booking.status === 'pending') {
        // Redirect to confirm booking page or show confirmation dialog
        window.location.href = `/user/bookings/${bookingId}/confirm`;
    } else if (booking.status === 'confirmed') {
        // Redirect to payment page
        window.location.href = `/user/payments?booking_id=${bookingId}`;
    } else {
        // Default action
        alert('Action not available for this booking status');
    }
}

/**
 * Quick action functions
 */
function goToBookRoom() {
    window.location.href = '/rooms';
}

function goToViewRooms() {
    window.location.href = '/rooms';
}

function goToPayments() {
    window.location.href = '/user/payments';
}

/* ===== USER CALENDAR FUNCTIONS ===== */

/**
 * Initialize user calendar
 */
async function initializeUserCalendar() {
    try {
        // Fetch all bookings (same as admin)
        const response = await axios.get('/api/reports/bookings', { params: { page: 1, per_page: 1000 } });
        userDashboardState.calendar.bookings = response.data.data || response.data;
        
        // Render calendar
        renderUserCalendar();
    } catch (error) {
        console.error('Error initializing calendar:', error);
    }
}

/**
 * Render user calendar for current month
 */
function renderUserCalendar() {
    const year = userDashboardState.calendar.currentDate.getFullYear();
    const month = userDashboardState.calendar.currentDate.getMonth();
    
    // Update month/year display
    const monthYearEl = document.getElementById('currentMonthYear');
    if (monthYearEl) {
        const monthName = new Date(year, month).toLocaleString('en-US', { month: 'long', year: 'numeric' });
        monthYearEl.textContent = monthName;
    }
    
    // Get calendar grid
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) return;
    
    calendarGrid.innerHTML = '';
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    // Add previous month's days
    for (let i = firstDay - 1; i >= 0; i--) {
        const dayEl = createUserDayElement(daysInPrevMonth - i, true);
        calendarGrid.appendChild(dayEl);
    }
    
    // Add current month's days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(year, month, day);
        const dayEl = createUserDayElement(day, false);
        
        // Check if today
        if (dateObj.toDateString() === today.toDateString()) {
            dayEl.classList.add('today');
        }
        
        // Check for bookings
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        
        dayEl.addEventListener('click', () => selectUserDate(dateStr, dayEl));
        calendarGrid.appendChild(dayEl);
    }
    
    // Add next month's days
    const totalCells = calendarGrid.children.length;
    const remainingCells = 42 - totalCells; // 6 rows * 7 days
    for (let day = 1; day <= remainingCells; day++) {
        const dayEl = createUserDayElement(day, true);
        calendarGrid.appendChild(dayEl);
    }
}

/**
 * Create a day element for user calendar
 */
function createUserDayElement(day, isOtherMonth) {
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day' + (isOtherMonth ? ' other-month' : '');
    dayEl.innerHTML = `
        <div class="calendar-day-number">${day}</div>
        <div class="calendar-day-bookings"></div>
    `;
    return dayEl;
}

/**
 * Get user's bookings for a specific date
 */
function getUserBookingsForDate(dateStr) {
    return userDashboardState.calendar.bookings.filter(booking => {
        const checkIn = booking.check_in_raw || booking.check_in;
        const checkOut = booking.check_out_raw || booking.check_out;
        return dateStr >= checkIn && dateStr <= checkOut;
    });
}

/**
 * Select a date and show user's bookings
 */
function selectUserDate(dateStr, dayEl) {
    // Remove previous selection
    const prevSelected = document.querySelector('.calendar-day.selected');
    if (prevSelected) prevSelected.classList.remove('selected');
    
    // Add selection to current
    dayEl.classList.add('selected');
    userDashboardState.calendar.selectedDate = dateStr;
    
    // Show bookings for this date
    showUserBookingsForDate(dateStr);
}

/**
 * Show user's bookings for selected date
 */
function showUserBookingsForDate(dateStr) {
    const bookings = getUserBookingsForDate(dateStr);
    const detailsSection = document.getElementById('calendarDetailsSection');
    const container = document.getElementById('calendarBookingsContainer');
    const displayEl = document.getElementById('selectedDateDisplay');
    
    if (!detailsSection || !container) return;
    
    // Update display
    const dateObj = new Date(dateStr + 'T00:00:00');
    const displayDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    if (displayEl) displayEl.textContent = displayDate;
    
    // Show details section
    detailsSection.style.display = 'block';
    detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    if (bookings.length === 0) {
        container.innerHTML = '<div class="no-bookings-msg">No bookings for this date</div>';
        return;
    }
    
    container.innerHTML = bookings.map(booking => `
        <div class="booking-item">
            <div class="booking-info">
                <div class="booking-room-name">${booking.room_name || booking.room || 'N/A'}</div>
                <div class="booking-time">
                    ${formatUserBookingTime(booking.check_in_raw || booking.check_in_date || booking.check_in)} 
                    → 
                    ${formatUserBookingTime(booking.check_out_raw || booking.check_out_date || booking.check_out)}
                </div>
                <div class="booking-guest">${booking.guest_name || booking.guest || 'N/A'}</div>
            </div>
            <span class="booking-status ${(booking.status || 'pending').toLowerCase()}">
                ${capitalizeUserFirst(booking.status || 'pending')}
            </span>
        </div>
    `).join('');
}

/**
 * Format date for user booking display
 */
function formatUserBookingTime(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

/**
 * Capitalize first letter for user calendar
 */
function capitalizeUserFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Previous month for user calendar
 */
function previousMonth() {
    userDashboardState.calendar.currentDate.setMonth(userDashboardState.calendar.currentDate.getMonth() - 1);
    renderUserCalendar();
}

/**
 * Next month for user calendar
 */
function nextMonth() {
    userDashboardState.calendar.currentDate.setMonth(userDashboardState.calendar.currentDate.getMonth() + 1);
    renderUserCalendar();
}
