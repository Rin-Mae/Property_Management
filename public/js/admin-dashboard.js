// Dashboard state
let dashboardState = {
    activityLogs: {
        data: [],
        allData: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 5,
        filter: 'all'
    },
    stats: {
        arrivingToday: 0,
        departingToday: 0,
        bookingsToday: 0,
        currentlyStaying: 0
    },
    calendar: {
        currentDate: new Date(),
        bookings: [],
        selectedDate: null
    }
};

/**
 * Initialize dashboard
 */
document.addEventListener('DOMContentLoaded', () => {
    updateProfileName();
    loadDashboardStats();
    loadActivityLogs();
    setupFilterButtons();
    initializeCalendar();
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
        if (userInfo && user.role) {
            userInfo.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

async function loadDashboardStats() {
    try {
        const response = await axios.get('/api/reports/summary');
        const today = new Date().toISOString().split('T')[0];
        
        // Calculate today's stats from booking history
        const bookingsResponse = await axios.get('/api/reports/bookings', { params: { page: 1 } });
        const allBookings = bookingsResponse.data.data || [];
        
        const arrivingCount = allBookings.filter(b => b.check_in_raw === today).length;
        const departingCount = allBookings.filter(b => b.check_out_raw === today).length;
        const bookingsTodayCount = allBookings.filter(b => b.date_booked === new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })).length;
        const stayingCount = allBookings.filter(b => b.status === 'checked_in').length;
        
        dashboardState.stats = {
            arrivingToday: arrivingCount,
            departingToday: departingCount,
            bookingsToday: bookingsTodayCount,
            currentlyStaying: stayingCount
        };

        // Update stat cards
        const arrivingEl = document.getElementById('arrivingToday');
        const departingEl = document.getElementById('departingToday');
        const bookingsEl = document.getElementById('bookingsToday');
        const stayingEl = document.getElementById('currentlyStaying');

        if (arrivingEl) arrivingEl.textContent = dashboardState.stats.arrivingToday;
        if (departingEl) departingEl.textContent = dashboardState.stats.departingToday;
        if (bookingsEl) bookingsEl.textContent = dashboardState.stats.bookingsToday;
        if (stayingEl) stayingEl.textContent = dashboardState.stats.currentlyStaying;
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

/**
 * Setup filter button event listeners
 */
function setupFilterButtons() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');
            // Update filter
            dashboardState.activityLogs.filter = btn.getAttribute('data-filter');
            dashboardState.activityLogs.currentPage = 1;
            applyFilter();
        });
    });
}

/**
 * Apply filter to activity logs
 */
function applyFilter() {
    const filter = dashboardState.activityLogs.filter;
    
    if (filter === 'all') {
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData;
    } else if (filter === 'arriving') {
        const today = new Date().toISOString().split('T')[0];
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData.filter(log => log.check_in_raw === today);
    } else if (filter === 'departing') {
        const today = new Date().toISOString().split('T')[0];
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData.filter(log => log.check_out_raw === today);
    } else if (filter === 'staying') {
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData.filter(log => log.status === 'checked_in');
    } else {
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData;
    }

    dashboardState.activityLogs.totalPages = Math.ceil(dashboardState.activityLogs.data.length / dashboardState.activityLogs.perPage);
    if (dashboardState.activityLogs.currentPage > dashboardState.activityLogs.totalPages && dashboardState.activityLogs.totalPages > 0) {
        dashboardState.activityLogs.currentPage = dashboardState.activityLogs.totalPages;
    }
    updateActivityLogsTable();
}

/**
 * Load activity logs from API
 */
async function loadActivityLogs(page = 1) {
    try {
        const activityLoading = document.getElementById('activityLoading');
        const activityLogsContainer = document.getElementById('activityLogsContainer');
        
        if (activityLoading) {
            activityLoading.style.display = 'block';
        }
        if (activityLogsContainer) {
            activityLogsContainer.style.display = 'none';
        }

        // Fetch booking history from API
        const response = await axios.get('/api/reports/bookings', {
            params: { page: page }
        });
        
        const bookings = response.data.data || response.data;
        dashboardState.activityLogs.allData = bookings;
        
        applyFilter();

        if (activityLoading) {
            activityLoading.style.display = 'none';
        }
        if (activityLogsContainer) {
            activityLogsContainer.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading activity logs:', error);
        const activityLogsContainer = document.getElementById('activityLogsContainer');
        if (activityLogsContainer) {
            activityLogsContainer.innerHTML = '<p style="color: #e74c3c; text-align: center;">Failed to load activity logs</p>';
        }
    }
}

/**
 * Update activity logs table display
 */
function updateActivityLogsTable() {
    const tbody = document.getElementById('activityLogsBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    const start = (dashboardState.activityLogs.currentPage - 1) * dashboardState.activityLogs.perPage;
    const end = start + dashboardState.activityLogs.perPage;
    const paginatedLogs = dashboardState.activityLogs.data.slice(start, end);

    if (paginatedLogs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No data found</td></tr>';
        return;
    }

    paginatedLogs.forEach(log => {
        const status = log.status || 'pending';
        const statusBadgeClass = status.toLowerCase().replace(' ', '_');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${log.guest_name || log.guest || '-'}</td>
            <td>${log.room_name || log.room || '-'}</td>
            <td>${log.check_in || log.checkInDate || '-'}</td>
            <td>${log.check_out || log.checkOutDate || '-'}</td>
            <td><span class="status-badge ${statusBadgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
            <td title="₱${log.total_price || 0}">${log.total_price ? '₱' + parseFloat(log.total_price).toFixed(2) : '-'}</td>
        `;
        tbody.appendChild(row);
    });

    updatePaginationControls();
}

/**
 * Update pagination controls
 */
function updatePaginationControls() {
    const pageInfo = document.getElementById('activityLogsPageInfo');
    const prevBtn = document.getElementById('activityLogsPrevBtn');
    const nextBtn = document.getElementById('activityLogsNextBtn');
    const pagination = document.getElementById('activityLogsPagination');
    const pageNumbersContainer = document.getElementById('activityLogsPageNumbers');

    // Show pagination if there's data
    if (dashboardState.activityLogs.data.length > 0) {
        if (pagination) {
            pagination.style.display = 'flex';
        }
    } else {
        if (pagination) {
            pagination.style.display = 'none';
        }
    }

    if (pageInfo) {
        pageInfo.textContent = `Page ${dashboardState.activityLogs.currentPage} of ${dashboardState.activityLogs.totalPages}`;
    }

    if (prevBtn) {
        prevBtn.disabled = dashboardState.activityLogs.currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = dashboardState.activityLogs.currentPage >= dashboardState.activityLogs.totalPages;
    }

    // Generate page numbers - always show all pages even if <= 5
    if (pageNumbersContainer) {
        pageNumbersContainer.innerHTML = '';
        const totalPages = dashboardState.activityLogs.totalPages;
        const currentPage = dashboardState.activityLogs.currentPage;
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
                    btn.onclick = () => goToActivityLogsPage(page);
                }
                
                pageNumbersContainer.appendChild(btn);
            }
        });
    }
}

/**
 * Go to specific page
 */
function goToActivityLogsPage(page) {
    dashboardState.activityLogs.currentPage = page;
    updateActivityLogsTable();
}

/**
 * Go to previous page
 */
function previousActivityLogsPage() {
    if (dashboardState.activityLogs.currentPage > 1) {
        dashboardState.activityLogs.currentPage--;
        updateActivityLogsTable();
    }
}

/**
 * Go to next page
 */
function nextActivityLogsPage() {
    if (dashboardState.activityLogs.currentPage < dashboardState.activityLogs.totalPages) {
        dashboardState.activityLogs.currentPage++;
        updateActivityLogsTable();
    }
}

/**
 * Format date to readable format
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Show error message
 */
function showError(message) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alert = document.createElement('div');
    alert.className = 'alert error';
    alert.innerHTML = `
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">×</button>
    `;

    alertContainer.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}

/* ===== CALENDAR FUNCTIONS ===== */

/**
 * Initialize calendar
 */
async function initializeCalendar() {
    try {
        // Fetch all bookings
        const response = await axios.get('/api/reports/bookings', { params: { page: 1, per_page: 1000 } });
        dashboardState.calendar.bookings = response.data.data || response.data;
        
        // Render calendar
        renderCalendar();
        
        // Refresh calendar every 30 seconds to catch status updates
        setInterval(refreshCalendar, 30000);
    } catch (error) {
        console.error('Error initializing calendar:', error);
    }
}

/**
 * Refresh calendar data from API
 */
async function refreshCalendar() {
    try {
        const response = await axios.get('/api/reports/bookings', { params: { page: 1, per_page: 1000 } });
        dashboardState.calendar.bookings = response.data.data || response.data;
        
        // Re-render calendar with updated data
        renderCalendar();
    } catch (error) {
        console.error('Error refreshing calendar:', error);
    }
}

/**
 * Render calendar for current month
 */
function renderCalendar() {
    const year = dashboardState.calendar.currentDate.getFullYear();
    const month = dashboardState.calendar.currentDate.getMonth();
    
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
        const dayEl = createDayElement(daysInPrevMonth - i, true);
        calendarGrid.appendChild(dayEl);
    }
    
    // Add current month's days
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dateObj = new Date(year, month, day);
        const dayEl = createDayElement(day, false);
        
        // Check if today
        if (dateObj.toDateString() === today.toDateString()) {
            dayEl.classList.add('today');
        }
        
        // Check for bookings
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        
        dayEl.addEventListener('click', () => selectDate(dateStr, dayEl));
        calendarGrid.appendChild(dayEl);
    }
    
    // Add next month's days
    const totalCells = calendarGrid.children.length;
    const remainingCells = 42 - totalCells; // 6 rows * 7 days
    for (let day = 1; day <= remainingCells; day++) {
        const dayEl = createDayElement(day, true);
        calendarGrid.appendChild(dayEl);
    }
    
    // Update calendar indicators for bookings
    updateCalendarWithBookings();
}

/**
 * Create a day element
 */
function createDayElement(day, isOtherMonth) {
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day' + (isOtherMonth ? ' other-month' : '');
    dayEl.innerHTML = `
        <div class="calendar-day-number">${day}</div>
        <div class="calendar-day-bookings"></div>
    `;
    return dayEl;
}

/**
 * Get bookings for a specific date
 */
function getBookingsForDate(dateStr) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dateObj = new Date(dateStr + 'T00:00:00');
    
    return dashboardState.calendar.bookings.filter(booking => {
        // Exclude cancelled and checked_out bookings
        if (booking.status && (booking.status.toLowerCase() === 'cancelled' || booking.status.toLowerCase() === 'checked_out')) {
            return false;
        }
        
        const checkIn = booking.check_in_raw || booking.check_in;
        const checkOut = booking.check_out_raw || booking.check_out;
        
        // Include bookings that include this date and haven't been checked out
        return dateStr >= checkIn && dateStr < checkOut;
    });
}

/**
 * Update calendar grid with booking indicators
 */
function updateCalendarWithBookings() {
    const year = dashboardState.calendar.currentDate.getFullYear();
    const month = dashboardState.calendar.currentDate.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // For each day in the current month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const bookings = getBookingsForDate(dateStr);
        
        // Find the calendar day element
        const calendarDays = document.querySelectorAll('.calendar-day:not(.other-month)');
        
        if (calendarDays.length >= day) {
            const dayEl = calendarDays[day - 1];
            
            if (bookings.length > 0) {
                dayEl.classList.add('has-booking');
                
                // Build tooltip with all booking info
                const bookingInfo = bookings.map(b => {
                    const checkInTime = formatBookingTime(b.check_in_raw || b.check_in);
                    const checkOutTime = formatBookingTime(b.check_out_raw || b.check_out);
                    const guest = b.guest_name || b.guest || 'Guest';
                    return `${guest}\nCheckin: ${checkInTime}\nCheckout: ${checkOutTime}`;
                }).join('\n---\n');
                
                dayEl.title = bookingInfo;
                
                // Add booking count indicator
                const bookingDiv = dayEl.querySelector('.calendar-day-bookings');
                if (bookingDiv) {
                    bookingDiv.innerHTML = `<div class="booking-indicator">${bookings.length} Booking${bookings.length > 1 ? 's' : ''}</div>`;
                }
            }
        }
    }
}

/**
 * Select a date and show its bookings
 */
function selectDate(dateStr, dayEl) {
    // Remove previous selection
    const prevSelected = document.querySelector('.calendar-day.selected');
    if (prevSelected) prevSelected.classList.remove('selected');
    
    // Add selection to current
    dayEl.classList.add('selected');
    dashboardState.calendar.selectedDate = dateStr;
    
    // Show bookings for this date
    showBookingsForDate(dateStr);
}

/**
 * Show bookings for selected date
 */
function showBookingsForDate(dateStr) {
    const bookings = getBookingsForDate(dateStr);
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
    
    container.innerHTML = bookings.map(booking => {
        const checkInStr = booking.check_in_raw || booking.check_in;
        const checkOutStr = booking.check_out_raw || booking.check_out;
        const checkInDate = new Date(checkInStr);
        const checkOutDate = new Date(checkOutStr);
        
        // Calculate duration
        const durationMs = checkOutDate - checkInDate;
        const durationDays = Math.ceil(durationMs / (1000 * 60 * 60 * 24));
        
        return `
        <div class="booking-item">
            <div class="booking-info">
                <div class="booking-room-name">${booking.room_name || booking.room || 'N/A'}</div>
                <div class="booking-guest"><strong>${booking.guest_name || booking.guest || 'N/A'}</strong></div>
                <div class="booking-time" style="margin-top: 0.75rem; font-size: 0.9rem; color: #007a0f; font-weight: 600; border-top: 1px solid #ddd; padding-top: 0.5rem;">
                    <div><strong>Duration: ${durationDays} night${durationDays > 1 ? 's' : ''}</strong></div>
                    <div style="margin-top: 0.5rem;">
                        <div><strong>Check-In:</strong></div>
                        <div style="color: #333; font-weight: normal;">${formatBookingTime(checkInStr)}</div>
                    </div>
                    <div style="margin-top: 0.25rem;">
                        <div><strong>Check-Out:</strong></div>
                        <div style="color: #333; font-weight: normal;">${formatBookingTime(checkOutStr)}</div>
                    </div>
                </div>
            </div>
            <span class="booking-status ${(booking.status || 'pending').toLowerCase()}">
                ${capitalizeFirst(booking.status || 'pending')}
            </span>
        </div>
    `}).join('');
}

/**
 * Format date and time for booking display
 */
function formatBookingTime(dateStr) {
    if (!dateStr) return 'N/A';
    
    // Parse the datetime string
    const date = new Date(dateStr);
    
    // Format as "Apr 10, 2026 2:00 PM"
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

/**
 * Capitalize first letter
 */
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Previous month
 */
function previousMonth() {
    dashboardState.calendar.currentDate.setMonth(dashboardState.calendar.currentDate.getMonth() - 1);
    renderCalendar();
}

/**
 * Next month
 */
function nextMonth() {
    dashboardState.calendar.currentDate.setMonth(dashboardState.calendar.currentDate.getMonth() + 1);
    renderCalendar();
}
