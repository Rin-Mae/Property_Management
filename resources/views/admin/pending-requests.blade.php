<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pending Requests - Admin Dashboard</title>
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

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
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

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-view {
            background: #007bff;
            color: white;
        }

        .btn-view:hover {
            background: #0056b3;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background: #c82333;
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

        .filter-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .filter-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                <button onclick="window.location.href='/admin/all-requests'" type="button">
                    <span>All Requests</span>
                </button>
            </li>
            <li>
                <button class="active" onclick="window.location.href='/admin/pending-requests'" type="button">
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
                <h3 style="color: #333; margin-bottom: 1rem;">All Pending Requests</h3>

                <div class="filter-controls">
                    <div class="filter-group" style="flex: 2;">
                        <label for="searchInput">Search (Student ID, Name, Course)</label>
                        <input type="text" id="searchInput" placeholder="Enter search term..." onkeyup="applySearch()">
                    </div>
                </div>

                <div id="loading" class="loading">Loading pending requests...</div>
                <table class="requests-table" id="requestsTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Purpose</th>
                            <th>Copies</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsBody">
                    </tbody>
                </table>
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="empty-state-icon">✓</div>
                    <h3 style="color: #666; margin-bottom: 0.5rem;">No Pending Requests</h3>
                    <p>All TOR requests have been processed!</p>
                </div>
            </div>
        </div>
    </main>

    <script type="module">
        let allPendingRequests = [];

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

        async function loadPendingRequests() {
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

                const allRequests = await response.json();
                allPendingRequests = allRequests.filter(r => r.status === 'pending');

                document.getElementById('loading').style.display = 'none';

                if (allPendingRequests.length === 0) {
                    document.getElementById('emptyState').style.display = 'block';
                    return;
                }

                renderPendingRequests(allPendingRequests);
                document.getElementById('requestsTable').style.display = 'table';
            } catch (error) {
                console.error('Failed to load pending requests:', error);
                document.getElementById('loading').textContent = 'Failed to load requests. Please refresh the page.';
            }
        }

        function renderPendingRequests(requests) {
            const tbody = document.getElementById('requestsBody');
            tbody.innerHTML = '';

            if (requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #999;">No requests match your search</td></tr>';
                return;
            }

            requests.forEach(request => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${request.student_id || '-'}</td>
                    <td>${request.full_name}</td>
                    <td>${request.course}</td>
                    <td>${request.purpose}</td>
                    <td>${request.number_of_copies}</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-approve" onclick="approveRequest(${request.id})">Approve</button>
                            <button class="btn btn-view" onclick="viewRequest(${request.id})">View</button>
                            <button class="btn btn-cancel" onclick="cancelRequest(${request.id})">Reject</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        window.applySearch = function () {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            const filtered = allPendingRequests.filter(request => {
                const matchesSearch =
                    !searchTerm ||
                    (request.student_id && request.student_id.toLowerCase().includes(searchTerm)) ||
                    request.full_name.toLowerCase().includes(searchTerm) ||
                    request.course.toLowerCase().includes(searchTerm);

                return matchesSearch;
            });

            renderPendingRequests(filtered);
        };




        window.approveRequest = function (requestId) {
            alert(`Approving request #${requestId} - Coming soon!`);
        };

        window.viewRequest = function (requestId) {
            alert(`Viewing request #${requestId} - Coming soon!`);
        };

        window.cancelRequest = async function (requestId) {
            // Ask admin for cancellation reason
            const reason = prompt('Enter reason for cancellation:');
            if (reason === null) return; // user cancelled prompt
            if (reason.trim() === '') {
                alert('Cancellation reason is required.');
                return;
            }

            if (!confirm('Are you sure you want to cancel this request?')) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch(`/api/tor-requests/${requestId}`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ status: 'rejected', remarks: reason })
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => null);
                    throw new Error(err?.message || `HTTP ${res.status}`);
                }

                // Refresh the list after successful cancel
                loadPendingRequests();
            } catch (error) {
                console.error('Failed to cancel request:', error);
                alert('Failed to cancel request. See console for details.');
            }
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
        loadPendingRequests();
    </script>
</body>

</html>