// Room Management JavaScript
let currentRoomsPage = 1;
let roomsPerPage = 12;
let allRooms = [];
let filteredRooms = [];
let roomTypes = [];
let amenities = [];
let deleteRoomId = null;
let statusRoomId = null;

// Initialize
document.addEventListener('DOMContentLoaded', function () {
    loadUserInfo();
    loadRoomTypes();
    loadAmenities();
    loadRooms();
    setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) searchInput.addEventListener('input', filterRooms);
    if (typeFilter) typeFilter.addEventListener('change', filterRooms);
    if (statusFilter) statusFilter.addEventListener('change', filterRooms);
}

// Load user information
function loadUserInfo() {
    fetch('/api/user')
        .then(response => response.json())
        .then(data => {
            document.getElementById('profileName').textContent = data.first_name || 'Admin';
            document.getElementById('userInfo').textContent = data.email || '';
        })
        .catch(error => console.error('Error loading user:', error));
}

// Load room types
function loadRoomTypes() {
    fetch('/api/rooms-types')
        .then(response => response.json())
        .then(data => {
            roomTypes = data;
            populateTypeFilter();
            populateTypeSelect();
        })
        .catch(error => console.error('Error loading room types:', error));
}

// Load amenities
function loadAmenities() {
    fetch('/api/amenities')
        .then(response => response.json())
        .then(data => {
            amenities = data;
            populateAmenitiesGrid();
        })
        .catch(error => console.error('Error loading amenities:', error));
}

// Load rooms
function loadRooms() {
    document.getElementById('roomsLoading').style.display = 'block';
    document.getElementById('roomsContainer').style.display = 'none';

    fetch(`/api/rooms?page=${currentRoomsPage}`)
        .then(response => response.json())
        .then(data => {
            allRooms = data.data;
            filteredRooms = [...allRooms];
            displayRooms();
            setupPagination(data);
            document.getElementById('roomsLoading').style.display = 'none';
            document.getElementById('roomsContainer').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            showAlert('Error loading rooms', 'error');
            document.getElementById('roomsLoading').style.display = 'none';
        });
}

// Filter rooms
function filterRooms() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeId = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;

    filteredRooms = allRooms.filter(room => {
        const matchesSearch = room.name.toLowerCase().includes(searchTerm) ||
            room.room_number.toLowerCase().includes(searchTerm);
        const matchesType = !typeId || room.type_id == typeId;
        const matchesStatus = !status || room.status === status;

        return matchesSearch && matchesType && matchesStatus;
    });

    currentRoomsPage = 1;
    displayRooms();
}

// Display rooms
function displayRooms() {
    const roomsGrid = document.getElementById('roomsGrid');
    roomsGrid.innerHTML = '';

    if (filteredRooms.length === 0) {
        roomsGrid.innerHTML = `
            <div style="grid-column: 1 / -1;">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-building"></i></div>
                    <div class="empty-state-text">No rooms found</div>
                </div>
            </div>
        `;
        document.getElementById('roomsPagination').style.display = 'none';
        return;
    }

    // Get paginated rooms
    const start = (currentRoomsPage - 1) * roomsPerPage;
    const end = start + roomsPerPage;
    const paginatedRooms = filteredRooms.slice(start, end);

    paginatedRooms.forEach(room => {
        const roomCard = createRoomCard(room);
        roomsGrid.appendChild(roomCard);
    });

    // Update pagination
    const totalPages = Math.ceil(filteredRooms.length / roomsPerPage);
    if (totalPages > 1) {
        document.getElementById('roomsPagination').style.display = 'flex';
        document.getElementById('roomsPageInfo').textContent = `Page ${currentRoomsPage} of ${totalPages}`;
        document.getElementById('roomsPrevBtn').disabled = currentRoomsPage === 1;
        document.getElementById('roomsNextBtn').disabled = currentRoomsPage === totalPages;
    } else {
        document.getElementById('roomsPagination').style.display = 'none';
    }
}

// Create room card
function createRoomCard(room) {
    const typeName = room.type ? room.type.name : 'Unknown';

    const card = document.createElement('div');
    card.className = 'room-card';

    const statusClass = room.status.toLowerCase();
    const imageUrl = room.image_url || getPlaceholderImage(typeName);

    // Determine status icon
    let statusIcon = '';
    switch (statusClass) {
        case 'available':
            statusIcon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'occupied':
            statusIcon = '<i class="fas fa-door-open"></i>';
            break;
        case 'maintenance':
            statusIcon = '<i class="fas fa-wrench"></i>';
            break;
        default:
            statusIcon = '<i class="fas fa-info-circle"></i>';
    }

    card.innerHTML = `
        <img src="${imageUrl}" alt="${room.name}" class="room-image" onerror="this.style.display='none'">
        <div class="room-content">
            <div class="room-header">
                <div>
                    <h3 class="room-name">${escapeHtml(room.name)}</h3>
                    <p class="room-number">${escapeHtml(room.room_number)}</p>
                    <span class="room-type">${escapeHtml(typeName)}</span>
                </div>
            </div>
            
            <div class="room-details">
                <div class="room-detail-item">
                    <span>Capacity:</span>
                    <strong>${room.capacity} guest${room.capacity > 1 ? 's' : ''}</strong>
                </div>
            </div>

            <div class="room-price">
                <span class="room-price-label">Price</span>
                ₱${parseFloat(room.price).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}
            </div>

            <span class="room-status ${statusClass}">${statusIcon} ${room.status.charAt(0).toUpperCase() + room.status.slice(1)}</span>

            <div class="room-actions">
                <button class="btn btn-edit" onclick="openEditRoomModal(${room.id})"><i class="fas fa-edit"></i> Edit</button>
                <button class="btn btn-status" onclick="openStatusModal(${room.id})"><i class="fas fa-sync-alt"></i> Status</button>
                <button class="btn btn-delete" onclick="openDeleteModal(${room.id})"><i class="fas fa-trash-alt"></i> Delete</button>
            </div>
        </div>
    `;

    return card;
}

// Get placeholder image based on room type
function getPlaceholderImage(typeName) {
    // You can customize this based on room types
    const placeholders = {
        'standard': 'https://via.placeholder.com/400x200?text=Standard+Room',
        'deluxe': 'https://via.placeholder.com/400x200?text=Deluxe+Room',
        'suite': 'https://via.placeholder.com/400x200?text=Suite',
        'luxury': 'https://via.placeholder.com/400x200?text=Luxury+Room',
    };
    return placeholders[typeName.toLowerCase()] || 'https://via.placeholder.com/400x200?text=Room';
}

// Populate type filter dropdown
function populateTypeFilter() {
    const typeFilter = document.getElementById('typeFilter');
    roomTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type.id;
        option.textContent = type.name;
        typeFilter.appendChild(option);
    });
}

// Populate type select in modal
function populateTypeSelect() {
    const roomType = document.getElementById('roomType');
    roomTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type.id;
        option.textContent = type.name;
        roomType.appendChild(option);
    });
}

// Populate amenities checkboxes
function populateAmenitiesGrid() {
    const amenitiesGrid = document.getElementById('amenitiesGrid');
    amenitiesGrid.innerHTML = '';

    amenities.forEach(amenity => {
        const div = document.createElement('div');
        div.className = 'amenity-checkbox';
        div.innerHTML = `
            <input type="checkbox" id="amenity-${amenity.id}" name="amenities" value="${amenity.id}">
            <label for="amenity-${amenity.id}">${escapeHtml(amenity.name)}</label>
        `;
        amenitiesGrid.appendChild(div);
    });
}

// Setup pagination
function setupPagination(data) {
    // Note: This is for API pagination integration if needed
}

// Open create room modal
function openCreateRoomModal() {
    document.getElementById('roomId').value = '';
    document.getElementById('roomForm').reset();
    document.getElementById('roomModalTitle').textContent = 'Create New Room';
    document.getElementById('submitBtn').textContent = 'Create Room';
    document.getElementById('roomStatus').value = 'available';

    // Clear amenities checkboxes
    document.querySelectorAll('.amenity-checkbox input').forEach(checkbox => {
        checkbox.checked = false;
    });

    document.getElementById('roomModal').style.display = 'block';
}

// Open edit room modal
function openEditRoomModal(roomId) {
    const room = allRooms.find(r => r.id === roomId);
    if (!room) return;

    document.getElementById('roomId').value = room.id;
    document.getElementById('roomName').value = room.name;
    document.getElementById('roomNumber').value = room.room_number;
    document.getElementById('roomType').value = room.type_id;
    document.getElementById('capacity').value = room.capacity;
    document.getElementById('price').value = room.price;
    document.getElementById('roomDescription').value = room.description || '';
    document.getElementById('roomStatus').value = room.status;
    document.getElementById('roomImageUrl').value = room.image_url || '';

    // Set amenities
    document.querySelectorAll('.amenity-checkbox input').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (room.amenities && Array.isArray(room.amenities)) {
        room.amenities.forEach(amenity => {
            const checkbox = document.getElementById(`amenity-${amenity.id}`);
            if (checkbox) checkbox.checked = true;
        });
    }

    document.getElementById('roomModalTitle').textContent = 'Edit Room';
    document.getElementById('submitBtn').textContent = 'Update Room';
    document.getElementById('roomModal').style.display = 'block';
}

// Close room modal
function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
    document.getElementById('roomForm').reset();
    clearErrors();
}

// Handle room form submit
function handleRoomSubmit(event) {
    event.preventDefault();
    clearErrors();

    const roomId = document.getElementById('roomId').value;
    const formData = new FormData(document.getElementById('roomForm'));

    // Get selected amenities
    const amenityIds = Array.from(document.querySelectorAll('.amenity-checkbox input:checked'))
        .map(checkbox => checkbox.value);

    const data = {
        name: formData.get('name'),
        room_number: formData.get('room_number'),
        type_id: formData.get('type_id'),
        capacity: formData.get('capacity'),
        price: formData.get('price'),
        description: formData.get('description'),
        status: formData.get('status'),
        image_url: formData.get('image_url'),
        amenities: amenityIds,
    };

    const url = roomId ? `/api/rooms/${roomId}` : '/api/rooms';
    const method = roomId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
    })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(data => {
                    displayErrors(data.errors);
                    throw new Error('Validation failed');
                });
            }
            return response.json();
        })
        .then(data => {
            closeRoomModal();
            loadRooms();
            showAlert(data.message || (roomId ? 'Room updated successfully' : 'Room created successfully'), 'success');
        })
        .catch(error => {
            if (error.message !== 'Validation failed') {
                console.error('Error:', error);
                showAlert('Error saving room', 'error');
            }
        });
}

// Open delete modal
function openDeleteModal(roomId) {
    deleteRoomId = roomId;
    document.getElementById('deleteModal').style.display = 'block';
}

// Close delete modal
function closeDeleteModal() {
    deleteRoomId = null;
    document.getElementById('deleteModal').style.display = 'none';
}

// Confirm delete
function confirmDelete() {
    if (!deleteRoomId) return;

    fetch(`/api/rooms/${deleteRoomId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
        .then(response => response.json())
        .then(data => {
            closeDeleteModal();
            loadRooms();
            showAlert(data.message || 'Room deleted successfully', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting room', 'error');
        });
}

// Open status modal
function openStatusModal(roomId) {
    statusRoomId = roomId;
    const room = allRooms.find(r => r.id === roomId);
    if (room) {
        document.getElementById('newStatus').value = room.status;
    }
    document.getElementById('statusModal').style.display = 'block';
}

// Close status modal
function closeStatusModal() {
    statusRoomId = null;
    document.getElementById('statusModal').style.display = 'none';
}

// Confirm status update
function confirmStatusUpdate() {
    if (!statusRoomId) return;

    const newStatus = document.getElementById('newStatus').value;

    fetch(`/api/rooms/${statusRoomId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ status: newStatus }),
    })
        .then(response => response.json())
        .then(data => {
            closeStatusModal();
            loadRooms();
            showAlert(data.message || 'Room status updated successfully', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error updating room status', 'error');
        });
}

// Pagination functions
function previousRoomsPage() {
    if (currentRoomsPage > 1) {
        currentRoomsPage--;
        displayRooms();
        window.scrollTo(0, 0);
    }
}

function nextRoomsPage() {
    const totalPages = Math.ceil(filteredRooms.length / roomsPerPage);
    if (currentRoomsPage < totalPages) {
        currentRoomsPage++;
        displayRooms();
        window.scrollTo(0, 0);
    }
}

// Display errors
function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(`${field}Error`);
        if (errorElement) {
            errorElement.textContent = errors[field][0];
        }
    });
}

// Clear errors
function clearErrors() {
    document.querySelectorAll('.error-message').forEach(element => {
        element.textContent = '';
    });
}

// Show alert
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.padding = '12px 16px';
    alertDiv.style.borderRadius = '4px';
    alertDiv.style.marginBottom = '16px';
    alertDiv.style.animation = 'slideIn 0.3s ease';

    if (type === 'success') {
        alertDiv.style.backgroundColor = '#e8f5e9';
        alertDiv.style.color = '#2e7d32';
        alertDiv.style.borderLeft = '4px solid #2e7d32';
    } else {
        alertDiv.style.backgroundColor = '#ffebee';
        alertDiv.style.color = '#c62828';
        alertDiv.style.borderLeft = '4px solid #c62828';
    }

    alertContainer.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Handle logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
            .then(() => {
                window.location.href = '/';
            })
            .catch(error => console.error('Error:', error));
    }
}

// Close modals on background click
window.onclick = function (event) {
    const roomModal = document.getElementById('roomModal');
    const deleteModal = document.getElementById('deleteModal');
    const statusModal = document.getElementById('statusModal');

    if (event.target === roomModal) {
        closeRoomModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
};
