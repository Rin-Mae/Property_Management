<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Admin Dashboard - Online TOR Request System</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 id="profileName">{{ Auth::user()->name ?? 'Admin' }}</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="sidebar-link active">
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
                <!-- Page Header -->
                <div class="page-header">
                    <h1>Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card arriving">
                        <div class="stat-icon"><i class="fas fa-sign-in-alt"></i></div>
                        <div class="stat-label">Arriving Today</div>
                        <div class="stat-number" id="arrivingToday">0</div>
                    </div>
                    <div class="stat-card departing">
                        <div class="stat-icon"><i class="fas fa-sign-out-alt"></i></div>
                        <div class="stat-label">Departing Today</div>
                        <div class="stat-number" id="departingToday">0</div>
                    </div>
                    <div class="stat-card bookings">
                        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-label">Bookings Today</div>
                        <div class="stat-number" id="bookingsToday">0</div>
                    </div>
                    <div class="stat-card staying">
                        <div class="stat-icon"><i class="fas fa-hotel"></i></div>
                        <div class="stat-label">Currently Staying</div>
                        <div class="stat-number" id="currentlyStaying">0</div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="dashboard-card">
                    <h3>Booking Calendar</h3>
                    <div class="calendar-container">
                        <div class="calendar-controls">
                            <button onclick="previousMonth()" class="calendar-btn">← Previous</button>
                            <h4 id="currentMonthYear">April 2026</h4>
                            <button onclick="nextMonth()" class="calendar-btn">Next →</button>
                        </div>
                        <div class="calendar-wrapper">
                            <div class="calendar-header">
                                <div class="calendar-weekday">Sun</div>
                                <div class="calendar-weekday">Mon</div>
                                <div class="calendar-weekday">Tue</div>
                                <div class="calendar-weekday">Wed</div>
                                <div class="calendar-weekday">Thu</div>
                                <div class="calendar-weekday">Fri</div>
                                <div class="calendar-weekday">Sat</div>
                            </div>
                            <div class="calendar-grid" id="calendarGrid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Details Section -->
                <div class="dashboard-card" id="calendarDetailsSection" style="display: none;">
                    <h3>Bookings for <span id="selectedDateDisplay">Selected Date</span></h3>
                    <div id="calendarBookingsContainer">
                        <div class="loading">Loading bookings...</div>
                    </div>
                </div>

                <!-- Activity Section -->
                <div class="dashboard-card">
                    <h3>Today's Activity</h3>

                    <!-- Activity Filters -->
                    <div class="activity-filters">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="arriving">Arriving</button>
                        <button class="filter-btn" data-filter="departing">Departing</button>
                        <button class="filter-btn" data-filter="staying">Staying</button>
                    </div>

                    <div id="activityLoading" class="loading">Loading activity logs...</div>
                    <div id="activityLogsContainer" style="display: none;">
                        <div class="activity-logs-wrapper">
                            <table class="activity-table">
                                <thead>
                                    <tr>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Check-In Date</th>
                                        <th>Check-Out Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="activityLogsBody">
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls for Activity Logs -->
                        <div id="activityLogsPagination" class="pagination-controls" style="display: none;">
                            <button id="activityLogsPrevBtn" onclick="previousActivityLogsPage()"
                                class="pagination-btn">← Previous</button>
                            <div id="activityLogsPageNumbers" class="page-numbers">
                            </div>
                            <span id="activityLogsPageInfo" class="pagination-info">Page 1 of 1</span>
                            <button id="activityLogsNextBtn" onclick="nextActivityLogsPage()"
                                class="pagination-btn">Next →</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
</body>

</html>