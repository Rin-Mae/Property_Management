<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Online TOR Request System</title>
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
            background: #2b2b2b 100%;
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

        .sidebar-menu .icon {
            font-size: 1.2rem;
        }

        .user-info {
            font-size: 0.85rem;
            color: #ddd;
            word-break: break-word;
        }

        header {
            display: none;
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

        .dashboard-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .dashboard-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }

        .dashboard-card p {
            color: #666;
            margin-bottom: 1rem;
            min-height: 3rem;
        }

        .card-button {
            background: #007810 100%;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
            width: 100%;
        }

        .card-button:hover {
            transform: translateY(-2px);
        }

        .stats-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stats-section h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stat-box {
            text-align: center;
            padding: 1.5rem;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #00a516;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #00a516;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #999;
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

            .sidebar-header {
                margin-bottom: 1rem;
            }

            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem;
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
                <button onclick="window.location.href='/dashboard'" class="active" type="button">
                    <span>Dashboard</span>
                </button>
            </li>
            <li>
                <button onclick="window.location.href='/admin/all-requests'" type="button">
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

            <!-- Statistics Section -->
            <div class="stats-section">
                <h3>TOR Statistics</h3>
                <div id="statsLoading" class="loading">Loading statistics...</div>
                <div id="statsGrid" class="stats-grid" style="display: none;">
                    <div class="stat-box">
                        <div class="stat-number" id="pendingCount">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="forReleaseCount">0</div>
                        <div class="stat-label">For Release</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="cancelledCount">0</div>
                        <div class="stat-label">Cancelled</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script type="module">
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

                if (response.status === 401) {
                    console.warn('API authentication failed, using fallback');
                    return;
                }

                if (!response.ok) {
                    console.warn('Failed to fetch user info:', response.status);
                    return;
                }

                const user = await response.json();

                // Safely update elements only if they exist
                const userNameEl = document.getElementById('userName');
                const profileNameEl = document.getElementById('profileName');
                const userInfoEl = document.getElementById('userInfo');
                const profileAvatarEl = document.getElementById('profileAvatar');

                if (userNameEl && user.name) {
                    userNameEl.textContent = user.name;
                }
                if (profileNameEl && user.name) {
                    profileNameEl.textContent = user.name;
                }
                if (userInfoEl && user.email) {
                    userInfoEl.textContent = user.email;
                }
                if (profileAvatarEl && user.name) {
                    profileAvatarEl.textContent = getInitials(user.name);
                }
            } catch (error) {
                console.error('Failed to load user:', error);
            }
        }

        async function loadStatistics() {
            try {
                const response = await fetch('/api/tor-requests', {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Failed to load statistics: ${response.status}`);
                }

                const requests = await response.json();

                const pending = requests.filter(r => r.status === 'pending').length;
                const forRelease = requests.filter(r => r.status === 'approved' || r.status === 'ready_for_pickup').length;
                const cancelled = requests.filter(r => r.status === 'cancelled').length;

                document.getElementById('pendingCount').textContent = pending;
                document.getElementById('forReleaseCount').textContent = forRelease;
                document.getElementById('cancelledCount').textContent = cancelled;

                document.getElementById('statsLoading').style.display = 'none';
                document.getElementById('statsGrid').style.display = 'grid';
            } catch (error) {
                console.error('Failed to load statistics:', error);
                document.getElementById('statsLoading').textContent = 'Unable to load statistics. Please refresh the page.';
            }
        }

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
        loadStatistics();
    </script>
</body>

</html>