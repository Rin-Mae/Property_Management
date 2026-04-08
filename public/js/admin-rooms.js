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
    const imageInput = document.getElementById('roomImage');

    if (searchInput) searchInput.addEventListener('input', filterRooms);
    if (typeFilter) typeFilter.addEventListener('change', filterRooms);
    if (statusFilter) statusFilter.addEventListener('change', filterRooms);
    
    // Image preview handler
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('imagePreview');
                    const previewImg = document.getElementById('previewImg');
                    if (preview && previewImg) {
                        previewImg.src = event.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
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
            document.getElementById('roomsLoading').innerHTML = 'Error loading rooms';
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
        <img src="${imageUrl}" alt="${room.name}" class="room-image" onerror="this.style.display='none'" style="cursor: pointer;" onclick="openViewRoomModal(${room.id})">
        <div class="room-content" style="cursor: pointer;" onclick="openViewRoomModal(${room.id})">
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
        </div>
        <div class="room-actions">
            <button class="btn btn-edit" onclick="openEditRoomModal(${room.id})"><i class="fas fa-edit"></i> Edit</button>
            <button class="btn btn-status" onclick="openStatusModal(${room.id})"><i class="fas fa-sync-alt"></i> Status</button>
            <button class="btn btn-delete" onclick="openDeleteModal(${room.id})"><i class="fas fa-trash-alt"></i> Delete</button>
        </div>
    `;

    return card;
}

// Get placeholder image based on room type
function getPlaceholderImage(typeName) {
    // Return a simple SVG placeholder instead of external URL
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="200" viewBox="0 0 400 200">
        <rect width="400" height="200" fill="#e8e1d6"/>
        <text x="50%" y="50%" font-size="20" fill="#999" text-anchor="middle" dominant-baseline="middle">No Image</text>
    </svg>`;
    return 'data:image/svg+xml;base64,' + btoa(svg);
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

    // Clear amenities checkboxes
    document.querySelectorAll('.amenity-checkbox input').forEach(checkbox => {
        checkbox.checked = false;
    });

    // Clear image preview
    const imageInput = document.getElementById('roomImage');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput) imageInput.value = '';
    if (imagePreview) imagePreview.style.display = 'none';

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

    // Show existing image or clear preview
    const imageInput = document.getElementById('roomImage');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (imageInput) imageInput.value = '';
    
    if (room.image_url && previewImg) {
        previewImg.src = room.image_url;
        if (imagePreview) imagePreview.style.display = 'block';
    } else if (imagePreview) {
        imagePreview.style.display = 'none';
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
    
    // Clear image preview
    const imageInput = document.getElementById('roomImage');
    const imagePreview = document.getElementById('imagePreview');
    if (imageInput) imageInput.value = '';
    if (imagePreview) imagePreview.style.display = 'none';
}

// Handle room form submit
function handleRoomSubmit(event) {
    event.preventDefault();
    clearErrors();

    // Show loader
    showModalLoader();

    const roomId = document.getElementById('roomId').value;
    const formElement = document.getElementById('roomForm');
    const formData = new FormData();

    // Get selected amenities
    const amenityIds = Array.from(document.querySelectorAll('.amenity-checkbox input:checked'))
        .map(checkbox => checkbox.value);

    // Add form fields to FormData
    const name = document.getElementById('roomName').value;
    const room_number = document.getElementById('roomNumber').value;
    const type_id = document.getElementById('roomType').value;
    const capacity = document.getElementById('capacity').value;
    const price = document.getElementById('price').value;
    const description = document.getElementById('roomDescription').value;

    // Debug log
    console.log('Form Data:', { name, room_number, type_id, capacity, price, description, amenityIds });

    formData.append('name', name);
    formData.append('room_number', room_number);
    formData.append('type_id', type_id);
    formData.append('capacity', capacity);
    formData.append('price', price);
    formData.append('description', description);
    formData.append('status', 'available');
    
    // Append amenities as array
    amenityIds.forEach(id => {
        formData.append('amenities[]', id);
    });

    // Add image file if selected
    const imageInput = document.getElementById('roomImage');
    if (imageInput && imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
    }

    const url = roomId ? `/api/rooms/${roomId}` : '/api/rooms';
    const method = roomId ? 'PUT' : 'POST';

    // For PUT requests with FormData, use POST with _method override
    if (roomId) {
        formData.append('_method', 'PUT');
    }

    // Debug: Log FormData contents
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}:`, value instanceof File ? `File(${value.name})` : value);
    }
    console.log('Sending to:', url, 'with method:', method);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData,
    })
        .then(response => {
            if (response.status === 422) {
                hideModalLoader();
                return response.json().then(data => {
                    console.error('Validation errors:', data.errors);
                    // Log detailed error messages
                    Object.keys(data.errors).forEach(field => {
                        console.error(`${field}:`, data.errors[field]);
                    });
                    displayErrors(data.errors);
                    throw new Error('Validation failed');
                });
            }
            return response.json();
        })
        .then(data => {
            hideModalLoader();
            closeRoomModal();
            loadRooms();
            showAlert(data.message || (roomId ? 'Room updated successfully' : 'Room created successfully'), 'success');
        })
        .catch(error => {
            hideModalLoader();
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

    // Show loader
    showModalLoader();

    fetch(`/api/rooms/${deleteRoomId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
        .then(response => response.json())
        .then(data => {
            hideModalLoader();
            closeDeleteModal();
            loadRooms();
            showAlert(data.message || 'Room deleted successfully', 'success');
        })
        .catch(error => {
            hideModalLoader();
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

    // Show loader
    showModalLoader();

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
            hideModalLoader();
            closeStatusModal();
            loadRooms();
            showAlert(data.message || 'Room status updated successfully', 'success');
        })
        .catch(error => {
            hideModalLoader();
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
    // Map field names to error element IDs
    const fieldMapping = {
        'name': 'roomNameError',
        'room_number': 'roomNumberError',
        'type_id': 'roomTypeError',
        'capacity': 'capacityError',
        'price': 'priceError',
        'description': 'roomDescriptionError',
        'status': 'roomStatusError'
    };

    Object.keys(errors).forEach(field => {
        const errorElementId = fieldMapping[field] || `${field}Error`;
        const errorElement = document.getElementById(errorElementId);
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
    showModalConfirm('Are you sure you want to logout?', async () => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                },
            });
            if (response.ok) {
                window.location.href = '/';
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
}

// Close modals on background click
window.onclick = function (event) {
    const roomModal = document.getElementById('roomModal');
    const deleteModal = document.getElementById('deleteModal');
    const statusModal = document.getElementById('statusModal');
    const viewRoomModal = document.getElementById('viewRoomModal');

    if (event.target === roomModal) {
        closeRoomModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
    if (event.target === statusModal) {
        closeStatusModal();
    }
    if (event.target === viewRoomModal) {
        closeViewRoomModal();
    }
};

// Open view room modal
function openViewRoomModal(roomId) {
    const room = allRooms.find(r => r.id === roomId);
    if (!room) return;

    const typeName = room.type ? room.type.name : 'Unknown';
    
    document.getElementById('viewRoomName').textContent = room.name;
    document.getElementById('viewRoomNumber').textContent = room.room_number;
    document.getElementById('viewRoomType').textContent = typeName;
    document.getElementById('viewRoomCapacity').textContent = `${room.capacity} guest${room.capacity > 1 ? 's' : ''}`;
    document.getElementById('viewRoomPrice').textContent = `₱${parseFloat(room.price).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    document.getElementById('viewRoomDescription').textContent = room.description || 'No description available';
    
    // Set status badge
    const statusEl = document.getElementById('viewRoomStatus');
    const statusClass = room.status.toLowerCase();
    let statusIcon = '';
    switch (statusClass) {
        case 'available':
            statusIcon = '<i class="fas fa-check-circle"></i> ';
            statusEl.style.background = 'linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%)';
            statusEl.style.color = '#2e7d32';
            break;
        case 'occupied':
            statusIcon = '<i class="fas fa-door-open"></i> ';
            statusEl.style.background = 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)';
            statusEl.style.color = '#e65100';
            break;
        case 'maintenance':
            statusIcon = '<i class="fas fa-wrench"></i> ';
            statusEl.style.background = 'linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%)';
            statusEl.style.color = '#6a1b9a';
            break;
    }
    statusEl.innerHTML = statusIcon + (room.status.charAt(0).toUpperCase() + room.status.slice(1));
    
    // Set amenities
    const amenitiesEl = document.getElementById('viewRoomAmenities');
    amenitiesEl.innerHTML = '';
    if (room.amenities && room.amenities.length > 0) {
        room.amenities.forEach(amenity => {
            const amenityTag = document.createElement('div');
            amenityTag.style.padding = '8px 12px';
            amenityTag.style.background = '#f0f0f0';
            amenityTag.style.borderRadius = '6px';
            amenityTag.style.fontSize = '13px';
            amenityTag.style.fontWeight = '500';
            amenityTag.style.color = '#333';
            amenityTag.textContent = amenity.name;
            amenitiesEl.appendChild(amenityTag);
        });
    } else {
        const noAmenities = document.createElement('p');
        noAmenities.style.color = '#999';
        noAmenities.style.fontSize = '13px';
        noAmenities.textContent = 'No amenities available';
        amenitiesEl.appendChild(noAmenities);
    }
    
    document.getElementById('viewRoomModal').style.display = 'block';
}

// Close view room modal
function closeViewRoomModal() {
    document.getElementById('viewRoomModal').style.display = 'none';
}

// ============================================
// LOADER FUNCTIONS
// ============================================

function showModalLoader() {
    // Create loader HTML if it doesn't exist
    let loader = document.getElementById('modalLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'modalLoader';
        loader.className = 'modal-loader-overlay';
        document.body.appendChild(loader);
    }
    
    loader.innerHTML = `
        <div class="modal-loader-content">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>
    `;
    loader.style.display = 'flex';
}

function hideModalLoader() {
    const loader = document.getElementById('modalLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}
