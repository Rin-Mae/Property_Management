<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All TOR Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-avatar" id="profileAvatar">A</div>
            <h1 id="profileName">Admin</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <button onclick="window.location.href='/dashboard'" type="button">
                    <span>Dashboard</span>
                </button>
            </li>
            <li>
                <button class="active" onclick="window.location.href='/admin/all-requests'" type="button">
                    <span>All Requests</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='/admin/pending-requests'" type="button">
                    <span>Pending Requests</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='/admin/processing'" type="button">
                    <span>Processing</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='/admin/users'" type="button">
                    <span>User Management</span>
                </button>
            </li>
        </ul>

        <button class="logout-btn" onclick="handleLogout()">Logout</button>
    </aside>

    <main class="main-content">
        <div class="container">

            <div class="requests-section">
                <h3 style="color: #333; margin-bottom: 1rem;">All Requests</h3>

                <div class="filter-controls">
                    <div class="filter-group" style="flex: 2;">
                        <label for="searchInput">Search (Student ID, Name, Course)</label>
                        <input type="text" id="searchInput" placeholder="Search by student ID, name, or course..."
                            onkeyup="applyFilters()">
                    </div>
                    <div class="filter-group">
                        <label for="statusFilter">Filter by Status</label>
                        <select id="statusFilter" onchange="applyFilters()">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="approved">Approved</option>
                            <option value="ready_for_pickup">For Release</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sortInput">Sort by</label>
                        <select id="sortInput" onchange="applyFilters()">
                            <option value="created_at_desc">Newest First</option>
                            <option value="created_at_asc">Oldest First</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                        </select>
                    </div>
                </div>

                <div id="loading" class="loading">Loading all requests...</div>
                <table class="requests-table" id="requestsTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsBody">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- View TOR Request Details Modal -->
    <div id="torRequestModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="closeTORRequestModal()">&times;</span>
            <h2>TOR Request Details</h2>
            <div id="torRequestContent" style="display: flex; flex-direction: column; gap: 1rem;">
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn-primary" onclick="openEditTORModal()">Edit Status</button>
                <button type="button" class="btn-cancel" onclick="closeTORRequestModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit TOR Request Status Modal -->
    <div id="editTORModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" onclick="closeEditTORModal()">&times;</span>
            <h2>Update Request Status</h2>
            <form id="editTORForm" onsubmit="handleEditTORSubmit(event)">
                <div class="form-group">
                    <label for="torStatus">Status</label>
                    <select id="torStatus" name="status" required>
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="approved">Approved</option>
                        <option value="ready_for_pickup">For Release</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="error-message" id="statusError"></div>
                </div>
                <div class="form-group">
                    <label for="torRemarks">Remarks</label>
                    <textarea id="torRemarks" name="remarks" rows="4" placeholder="Add any remarks..."></textarea>
                    <div class="error-message" id="remarksError"></div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary">Update Status</button>
                    <button type="button" class="btn-cancel" onclick="closeEditTORModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-all-requests.js') }}"></script>
</body>
</body>

</html>