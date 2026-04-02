<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Room Management - Hotel Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-rooms.css') }}">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1 id="profileName">Admin</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="sidebar-link">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/admin/rooms" class="sidebar-link active">
                    <span>Rooms</span>
                </a>
            </li>
            <li>
                <a href="/admin/bookings" class="sidebar-link">
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="/admin/payments" class="sidebar-link">
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="/admin/reports" class="sidebar-link">
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="/admin/users" class="sidebar-link">
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="/admin/clients" class="sidebar-link">
                    <span>Clients</span>
                </a>
            </li>
            <li>
                <a href="/admin/clients" class="sidebar-link">
                    <span>Clients</span>
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
                    <h1>Rooms</h1>
                    <button class="btn btn-primary" onclick="openCreateRoomModal()"><i class="fas fa-plus-circle"></i>
                        Add New Room</button>
                </div>

                <!-- Alerts -->
                <div id="alertContainer"></div>

                <!-- Search and Filter -->
                <div class="dashboard-card">
                    <div class="search-filter-section">
                        <input type="text" id="searchInput" placeholder="Search by room name or number..."
                            class="search-input">
                        <select id="typeFilter" class="type-filter">
                            <option value="">All Types</option>
                        </select>
                        <select id="statusFilter" class="status-filter">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Rooms Grid -->
                <div id="roomsLoading" class="loading">Loading rooms...</div>
                <div id="roomsContainer" style="display: none;">
                    <div class="rooms-grid" id="roomsGrid">
                        <!-- Rooms cards will be populated here -->
                    </div>

                    <!-- Pagination -->
                    <div id="roomsPagination" class="pagination-controls" style="display: none;">
                        <button id="roomsPrevBtn" onclick="previousRoomsPage()" class="pagination-btn">←
                            Previous</button>
                        <span id="roomsPageInfo" class="pagination-info">Page 1 of 1</span>
                        <button id="roomsNextBtn" onclick="nextRoomsPage()" class="pagination-btn">Next →</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create/Edit Room Modal -->
    <div id="roomModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="roomModalTitle">Create New Room</h2>
                <button class="close-btn" onclick="closeRoomModal()">&times;</button>
            </div>

            <form id="roomForm" onsubmit="handleRoomSubmit(event)" class="modal-form">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="roomId" name="room_id">

                    <div class="form-section-title">Room Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="roomName">Room Name *</label>
                            <input type="text" id="roomName" name="name" placeholder="e.g., Deluxe Room" required>
                            <div class="error-message" id="roomNameError"></div>
                        </div>

                        <div class="form-group">
                            <label for="roomNumber">Room Number *</label>
                            <input type="text" id="roomNumber" name="room_number" placeholder="e.g., 101" required>
                            <div class="error-message" id="roomNumberError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="roomType">Room Type *</label>
                            <select id="roomType" name="type_id" required>
                                <option value="">Select a type</option>
                            </select>
                            <div class="error-message" id="roomTypeError"></div>
                        </div>

                        <div class="form-group">
                            <label for="capacity">Capacity (Guests) *</label>
                            <input type="number" id="capacity" name="capacity" min="1" max="10" placeholder="e.g., 2"
                                required>
                            <div class="error-message" id="capacityError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price (₹) *</label>
                            <input type="number" id="price" name="price" min="0" step="0.01" placeholder="e.g., 2500"
                                required>
                            <div class="error-message" id="priceError"></div>
                        </div>

                        <div class="form-group">
                            <label for="roomStatus">Status *</label>
                            <select id="roomStatus" name="status" required>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <div class="error-message" id="roomStatusError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="roomImageUrl">Image URL</label>
                            <input type="url" id="roomImageUrl" name="image_url"
                                placeholder="https://example.com/image.jpg">
                            <div class="error-message" id="roomImageUrlError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="roomDescription">Description</label>
                            <textarea id="roomDescription" name="description" rows="4"
                                placeholder="Room description..."></textarea>
                            <div class="error-message" id="roomDescriptionError"></div>
                        </div>
                    </div>

                    <div class="form-section-title">Amenities</div>
                    <div class="amenities-grid" id="amenitiesGrid">
                        <!-- Amenity checkboxes will be populated here -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRoomModal()"><i
                            class="fas fa-times"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-check-circle"></i>
                        Create Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this room? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()"><i
                        class="fas fa-times"></i> Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()"><i class="fas fa-trash-alt"></i>
                    Delete</button>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Update Room Status</h2>
                <button class="close-btn" onclick="closeStatusModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="newStatus">New Status *</label>
                    <select id="newStatus" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStatusModal()"><i
                        class="fas fa-times"></i> Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusUpdate()"><i
                        class="fas fa-sync-alt"></i> Update</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/admin-rooms.js') }}"></script>
</body>

</html>