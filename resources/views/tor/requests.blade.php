<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My TOR Requests - Online TOR Request System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.5rem;
        }

        .logout-btn,
        .new-request-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            margin-left: 0.5rem;
        }

        .new-request-btn {
            background: rgba(255, 255, 255, 0.3);
        }

        .logout-btn:hover,
        .new-request-btn:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .empty-state p {
            margin-bottom: 1rem;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .requests-table thead {
            background: #f9f9f9;
            border-bottom: 2px solid #ddd;
        }

        .requests-table th {
            padding: 1rem;
            text-align: left;
            color: #333;
            font-weight: 600;
        }

        .requests-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .requests-table tbody tr:hover {
            background: #f9f9f9;
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
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .status-ready_for_pickup {
            background: #d1e7dd;
            color: #0f5132;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view,
        .btn-delete {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: transform 0.2s;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
        }

        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }

        .modal-header button {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        .detail-row {
            display: flex;
            padding: 0.5rem 0;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            width: 150px;
        }

        .detail-value {
            color: #666;
            flex: 1;
        }

        .user-info {
            font-size: 0.9rem;
            color: #ddd;
        }

        @media (max-width: 600px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .requests-table {
                font-size: 0.85rem;
            }

            .requests-table th,
            .requests-table td {
                padding: 0.5rem;
            }

            header {
                flex-direction: column;
                gap: 1rem;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <header>
        <div>
            <h1>🎓 My TOR Requests</h1>
            <p class="user-info" id="userInfo">Loading...</p>
        </div>
        <div>
            <button class="new-request-btn" onclick="goToCreateRequest()">New Request</button>
            <button class="logout-btn" onclick="handleLogout()">Logout</button>
        </div>
    </header>

    <div class="container">
        <h2>Your TOR Requests</h2>

        <div id="loading" class="loading">Loading your requests...</div>
        <div id="emptyState" class="empty-state" style="display: none;">
            <p>📋 No TOR requests yet</p>
            <p>Click "New Request" to submit your first request</p>
        </div>

        <table id="requestsTable" class="requests-table" style="display: none;">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Course</th>
                    <th>Copies</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="requestsBody">
            </tbody>
        </table>
    </div>

    <!-- Modal for request details -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Details</h3>
                <button onclick="closeModal()">&times;</button>
            </div>
            <div id="detailsContent"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configure axios
        const api = axios.create({
            baseURL: window.location.origin,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            api.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }

        // Add auth token to requests if available
        api.interceptors.request.use(config => {
            const authToken = localStorage.getItem('auth_token');
            if (authToken) {
                config.headers.Authorization = `Bearer ${authToken}`;
            }
            return config;
        });

        // Handle response errors
        api.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            }
        );
    </script>
    <script>

        let requests = [];

        async function loadRequests() {
            try {
                const response = await api.get('/api/tor-requests');
                requests = response.data;
                displayRequests();
            } catch (error) {
                console.error('Failed to load requests:', error);
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                }
            }
        }

        function displayRequests() {
            const loading = document.getElementById('loading');
            const emptyState = document.getElementById('emptyState');
            const table = document.getElementById('requestsTable');
            const tbody = document.getElementById('requestsBody');

            loading.style.display = 'none';

            if (requests.length === 0) {
                emptyState.style.display = 'block';
                table.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                table.style.display = 'table';
                tbody.innerHTML = requests.map(req => `
                    <tr>
                        <td>${req.student_id}</td>
                        <td>${req.course}</td>
                        <td>${req.number_of_copies}</td>
                        <td><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></td>
                        <td>${new Date(req.created_at).toLocaleDateString()}</td>
                        <td class="actions">
                            <button class="btn-view" onclick="viewDetails('${req.id}')">View</button>
                            ${req.status === 'pending' ? `<button class="btn-delete" onclick="deleteRequest('${req.id}')">Delete</button>` : ''}
                        </td>
                    </tr>
                `).join('');
            }
        }

        function formatStatus(status) {
            const statusMap = {
                'pending': 'Pending',
                'processing': 'Processing',
                'approved': 'Approved',
                'rejected': 'Rejected',
                'ready_for_pickup': 'Ready for Pickup'
            };
            return statusMap[status] || status;
        }

        window.viewDetails = function (id) {
            const req = requests.find(r => r.id == id);
            if (!req) return;

            const content = `
                <div class="detail-row">
                    <div class="detail-label">Full Name:</div>
                    <div class="detail-value">${req.full_name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Birth:</div>
                    <div class="detail-value">${new Date(req.birthdate).toLocaleDateString()}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Place of Birth:</div>
                    <div class="detail-value">${req.birthplace}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Student ID:</div>
                    <div class="detail-value">${req.student_id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Course:</div>
                    <div class="detail-value">${req.course}</div>
                </div>
                ${req.degree ? `<div class="detail-row">
                    <div class="detail-label">Degree:</div>
                    <div class="detail-value">${req.degree}</div>
                </div>` : ''}
                ${req.year_of_graduation ? `<div class="detail-row">
                    <div class="detail-label">Year of Graduation:</div>
                    <div class="detail-value">${req.year_of_graduation}</div>
                </div>` : ''}
                <div class="detail-row">
                    <div class="detail-label">Copies Requested:</div>
                    <div class="detail-value">${req.number_of_copies}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="status-badge status-${req.status}">${formatStatus(req.status)}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Requested Date:</div>
                    <div class="detail-value">${new Date(req.created_at).toLocaleDateString()}</div>
                </div>
                ${req.remarks ? `<div class="detail-row">
                    <div class="detail-label">Remarks:</div>
                    <div class="detail-value">${req.remarks}</div>
                </div>` : ''}
            `;

            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').classList.add('show');
        };

        window.closeModal = function () {
            document.getElementById('detailsModal').classList.remove('show');
        };

        window.deleteRequest = async function (id) {
            if (!confirm('Are you sure you want to delete this request?')) return;

            try {
                await api.delete(`/api/tor-requests/${id}`);
                requests = requests.filter(r => r.id != id);
                displayRequests();
            } catch (error) {
                alert(error.response?.data?.message || 'Failed to delete request');
            }
        };

        window.handleLogout = async function () {
            try {
                await api.post('/api/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        };

        window.goToCreateRequest = function () {
            window.location.href = '/tor/create';
        };

        // Load user info
        async function loadUserInfo() {
            try {
                const response = await api.get('/api/user');
                const user = response.data;
                document.getElementById('userInfo').textContent = `Welcome, ${user.name}`;
            } catch (error) {
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        }

        // Close modal when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Load data on page load
        loadUserInfo();
        loadRequests();
    </script>
</body>

</html>