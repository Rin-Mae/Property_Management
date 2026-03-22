<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All TOR Requests - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #2b2b2b;
            color: white;
            padding: 2rem 1.5rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            margin: 0 auto 0.75rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .sidebar-header h1 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            margin-bottom: 2rem;
        }

        .sidebar-menu li {
            margin-bottom: 1rem;
        }

        .sidebar-menu button {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.3s, transform 0.2s;
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-menu button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar-menu button.active {
            background: rgba(0, 165, 22, 0.3);
            border-left: 3px solid #00a516;
        }

        .user-info {
            font-size: 0.85rem;
            color: #ddd;
            word-break: break-word;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 67, 67, 0.8);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 0.95rem;
            margin-top: auto;
        }

        .logout-btn:hover {
            background: rgba(255, 67, 67, 1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .requests-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .requests-table thead {
            background: #f9f9f9;
        }

        .requests-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        .requests-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            color: #555;
        }

        .requests-table tbody tr:hover {
            background: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-ready_for_pickup {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-view {
            background: #007bff;
            color: white;
        }

        .btn-view:hover {
            background: #0056b3;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 1.5rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .requests-table {
                font-size: 0.85rem;
            }

            .requests-table th,
            .requests-table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-avatar" id="profileAvatar">A</div>
            <h1 id="profileName">Admin</h1>
            <p class="user-info" id="userInfo">Loading...</p>
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

    <script type="module">
        // Store all requests for filtering
        let allRequests = [];

        function getInitials(name) {
            if (!name) return 'A';
            return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2);
        }

        async function loadUserInfo() {
            try {
                const response = await fetch('/api/user', {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    console.warn('Failed to fetch user info');
                    return;
                }

                const user = await response.json();

                const profileAvatarEl = document.getElementById('profileAvatar');
                const profileNameEl = document.getElementById('profileName');
                const userInfoEl = document.getElementById('userInfo');

                if (profileAvatarEl && user.name) {
                    profileAvatarEl.textContent = getInitials(user.name);
                }
                if (profileNameEl && user.name) {
                    profileNameEl.textContent = user.name;
                }
                if (userInfoEl && user.email) {
                    userInfoEl.textContent = user.email;
                }
            } catch (error) {
                console.error('Failed to load user:', error);
            }
        }

        async function loadAllRequests() {
            try {
                const response = await fetch('/api/tor-requests', {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Failed to load requests: ${response.status}`);
                }

                allRequests = await response.json();

                document.getElementById('loading').style.display = 'none';

                if (allRequests.length === 0) {
                    document.getElementById('loading').textContent = 'No requests found';
                    document.getElementById('loading').style.display = 'block';
                    return;
                }

                applyFilters();
                document.getElementById('requestsTable').style.display = 'table';
            } catch (error) {
                console.error('Failed to load all requests:', error);
                document.getElementById('loading').textContent = 'Failed to load requests. Please refresh the page.';
            }
        }

        window.applyFilters = function () {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const sortBy = document.getElementById('sortInput').value;

            // Filter requests
            let filtered = allRequests.filter(request => {
                const matchesSearch =
                    !searchTerm ||
                    (request.student_id && request.student_id.toLowerCase().includes(searchTerm)) ||
                    request.full_name.toLowerCase().includes(searchTerm) ||
                    request.course.toLowerCase().includes(searchTerm);

                const matchesStatus = !statusFilter || request.status === statusFilter;

                return matchesSearch && matchesStatus;
            });

            // Sort requests
            if (sortBy === 'created_at_desc') {
                filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else if (sortBy === 'created_at_asc') {
                filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            } else if (sortBy === 'name_asc') {
                filtered.sort((a, b) => a.full_name.localeCompare(b.full_name));
            } else if (sortBy === 'name_desc') {
                filtered.sort((a, b) => b.full_name.localeCompare(a.full_name));
            }

            // Render filtered requests
            const tbody = document.getElementById('requestsBody');
            tbody.innerHTML = '';

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #999;">No requests match your filters</td></tr>';
                return;
            }

            filtered.forEach(request => {
                const row = document.createElement('tr');
                const statusClass = 'status-' + request.status.replace(/ /g, '_');
                row.innerHTML = `
                    <td>${request.id}</td>
                    <td>${request.student_id || '-'}</td>
                    <td>${request.full_name}</td>
                    <td>${request.course}</td>
                    <td>${request.purpose || '-'}</td>
                    <td><span class="status-badge ${statusClass}">${request.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-view" onclick="viewRequest(${request.id})">View</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        };

        window.viewRequest = function (requestId) {
            alert(`Viewing request #${requestId} - Coming soon!`);
        };

        window.handleLogout = async function () {
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        };

        // Load data on page load
        loadUserInfo();
        loadAllRequests();
    </script>
</body>

</html>