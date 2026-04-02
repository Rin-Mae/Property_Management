// Dashboard state
let dashboardState = {
    activityLogs: {
        data: [],
        allData: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 10,
        filter: 'all'
    },
    stats: {
        arrivingToday: 0,
        departingToday: 0,
        bookingsToday: 0,
        currentlyStaying: 0
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

/**
 * Load dashboard statistics from API
 */
async function loadDashboardStats() {
    try {
        const response = await axios.get('/api/reports/summary');
        
        dashboardState.stats = {
            arrivingToday: response.data.arriving_today || 0,
            departingToday: response.data.departing_today || 0,
            bookingsToday: response.data.bookings_today || 0,
            currentlyStaying: response.data.currently_staying || 0
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
    } else {
        dashboardState.activityLogs.data = dashboardState.activityLogs.allData.filter(log => log.type === filter);
    }

    dashboardState.activityLogs.totalPages = Math.ceil(dashboardState.activityLogs.data.length / dashboardState.activityLogs.perPage);
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
        const response = await axios.get('/api/reports/booking-history', {
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
            <td>${log.total_price ? '₱' + log.total_price : '-'}</td>
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

    if (dashboardState.activityLogs.totalPages > 1) {
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
