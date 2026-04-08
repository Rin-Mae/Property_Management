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
            <h1 id="profileName">{{ Auth::user()->name ?? 'Admin' }}</h1>
            <p class="user-info" id="userInfo"></p>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/dashboard" class="sidebar-link">
                    <i class="fas fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/admin/rooms" class="sidebar-link active">
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
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="roomImage">Room Image</label>
                            <div class="image-upload-wrapper">
                                <input type="file" id="roomImage" name="image" accept="image/*" class="image-input">
                                <label for="roomImage" class="image-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click to upload or drag image (Max 25MB)</span>
                                </label>
                                <div id="imagePreview" class="image-preview" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview"
                                        style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                                </div>
                            </div>
                            <div class="error-message" id="roomImageError"></div>
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

    <!-- View Room Modal -->
    <div id="viewRoomModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Room Details</h2>
                <button class="close-btn" onclick="closeViewRoomModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="room-details-container">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Room Name</h3>
                            <p id="viewRoomName" style="font-size: 18px; font-weight: 600; color: #333;"></p>
                        </div>
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Room Number</h3>
                            <p id="viewRoomNumber" style="font-size: 18px; font-weight: 600; color: #333;"></p>
                        </div>
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Room Type</h3>
                            <p id="viewRoomType" style="font-size: 18px; font-weight: 600; color: #333;"></p>
                        </div>
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Capacity</h3>
                            <p id="viewRoomCapacity" style="font-size: 18px; font-weight: 600; color: #333;"></p>
                        </div>
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Price</h3>
                            <p id="viewRoomPrice" style="font-size: 18px; font-weight: 600; color: #333;"></p>
                        </div>
                        <div>
                            <h3 style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 8px;">
                                Status</h3>
                            <p id="viewRoomStatus"
                                style="font-size: 14px; font-weight: 600; display: inline-block; padding: 6px 12px; border-radius: 20px;">
                            </p>
                        </div>
                    </div>
                    <div style="margin-bottom: 24px;">
                        <h3
                            style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #f0f0f0;">
                            Description</h3>
                        <p id="viewRoomDescription" style="font-size: 14px; color: #555; line-height: 1.6;"></p>
                    </div>
                    <div>
                        <h3
                            style="font-size: 14px; color: #999; text-transform: uppercase; margin-bottom: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #f0f0f0;">
                            Amenities</h3>
                        <div id="viewRoomAmenities"
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewRoomModal()"><i
                        class="fas fa-times"></i> Close</button>
                <button type="button" class="btn btn-primary" onclick="openCreateRoomModal()"><i
                        class="fas fa-plus-circle"></i> Add Rooms</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin-common.js') }}"></script>
    <script src="{{ asset('js/admin-rooms.js') }}"></script>
</body>

</html>