/* ========== User Bookings Page Scripts ========== */

let selectedRoom = null;

// Load available rooms
async function loadRooms() {
    // Show loader
    document.getElementById('roomsLoading').style.display = 'block';
    document.getElementById('roomsContainer').style.display = 'none';

    try {
        const response = await fetch('/api/rooms');
        const data = await response.json();

        if (response.ok) {
            displayRooms(data.data);
            document.getElementById('roomsLoading').style.display = 'none';
            document.getElementById('roomsContainer').style.display = 'block';
        } else {
            console.error('Error loading rooms:', data);
            document.getElementById('roomsLoading').innerHTML = 'Error loading rooms';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('roomsLoading').innerHTML = 'Error loading rooms';
    }
}

// Display rooms
function displayRooms(rooms) {
    const container = document.getElementById('roomSelection');
    container.innerHTML = '';

    rooms.forEach(room => {
        const roomCard = document.createElement('div');
        const isOccupied = room.status === 'occupied';
        roomCard.className = `room-card ${isOccupied ? 'room-occupied' : ''}`;
        const imageHtml = room.image_url 
            ? `<img src="${room.image_url}" alt="${room.name}" class="room-image">`
            : `<div class="room-placeholder"><i class="fas fa-image"></i></div>`;
        
        const statusBadge = isOccupied ? `<div class="room-status-badge occupied">Occupied</div>` : '';
        
        roomCard.innerHTML = `
            ${imageHtml}
            ${statusBadge}
            <div class="room-info">
                <div class="room-name">${room.name}</div>
                <div class="room-capacity">
                    <i class="fas fa-users"></i> ${room.capacity} persons
                </div>
                <div class="room-price">₱${parseFloat(room.price).toFixed(2)}/night</div>
            </div>
        `;

        // Only allow clicking if room is not occupied
        if (!isOccupied) {
            roomCard.addEventListener('click', () => selectRoom(room, roomCard));
            roomCard.style.cursor = 'pointer';
        } else {
            roomCard.style.cursor = 'not-allowed';
        }
        container.appendChild(roomCard);
    });
}

// Select room and show details first
function selectRoom(room, roomCard) {
    // Prevent selection if room is occupied
    if (room.status === 'occupied') {
        alert('This room is currently occupied and cannot be booked.');
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.room-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Mark as selected
    roomCard.classList.add('selected');
    selectedRoom = room;

    // Load booked dates for this room and disable them in calendar
    loadBookedDatesForRoom(room.id);

    // Show room details modal
    showRoomDetails(room);
    openRoomDetailsModal();
}

// Show room details in modal
function showRoomDetails(room) {
    // Populate room details
    document.getElementById('detailsRoomImage').src = room.image_url || '';
    document.getElementById('detailsRoomImage').alt = room.name;
    document.getElementById('detailsRoomName').textContent = room.name || 'N/A';
    document.getElementById('detailsRoomType').textContent = (room.type && room.type.name) ? room.type.name : 'N/A';
    document.getElementById('detailsRoomCapacity').textContent = room.capacity ? `${room.capacity} persons` : 'N/A';
    document.getElementById('detailsRoomPrice').textContent = `₱${parseFloat(room.price).toFixed(2)}/night`;
    document.getElementById('detailsRoomStatus').textContent = room.status ? room.status.charAt(0).toUpperCase() + room.status.slice(1) : 'N/A';
    document.getElementById('detailsRoomDescription').textContent = room.description || 'No description available.';

    // Populate amenities
    const amenitiesContainer = document.getElementById('detailsRoomAmenities');
    amenitiesContainer.innerHTML = '';

    if (room.amenities && room.amenities.length > 0) {
        room.amenities.forEach(amenity => {
            const amenityElement = document.createElement('div');
            amenityElement.style.cssText = 'display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: white; border-radius: 4px;';
            amenityElement.innerHTML = `
                <i class="fas fa-check" style="color: #2d7f3d;"></i>
                <span>${amenity.name}</span>
            `;
            amenitiesContainer.appendChild(amenityElement);
        });
    } else {
        amenitiesContainer.innerHTML = '<p style="color: #666;">No amenities listed for this room.</p>';
    }
}

// Open room details modal
function openRoomDetailsModal() {
    const modal = document.getElementById('roomDetailsModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close room details modal
function closeRoomDetailsModal() {
    const modal = document.getElementById('roomDetailsModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Proceed to booking from details modal
function proceedToBook() {
    if (!selectedRoom) {
        alert('Please select a room');
        return;
    }

    // Close details modal
    closeRoomDetailsModal();

    // Update booking form with room details
    document.getElementById('roomType').value = selectedRoom.type_id || '';
    document.getElementById('selectedRoomDisplay').innerHTML = `<strong>${selectedRoom.name}</strong> - ₱${parseFloat(selectedRoom.price).toFixed(2)}/night`;
    document.getElementById('roomType').innerHTML = `<option value="${selectedRoom.type_id}" selected>${selectedRoom.name}</option>`;

    // Open booking modal
    openBookingModal();
}

// Open booking modal
function openBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

// Close booking modal
function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.style.overflow = 'auto'; // Restore scrolling
}



// Form submission
async function submitBooking(e) {
    e.preventDefault();

    // Prevent double submission
    const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
    if (submitBtn && submitBtn.disabled) {
        return;
    }

    if (!selectedRoom) {
        alert('Please select a room');
        return;
    }

    const checkInDate = document.getElementById('checkInDate').value;
    const checkInTime = document.getElementById('checkInTime').value;
    const checkOutDate = document.getElementById('checkOutDate').value;
    const checkOutTime = document.getElementById('checkOutTime').value;

    // Validate dates are selected
    if (!checkInDate || !checkOutDate || !checkInTime || !checkOutTime) {
        alert('Please select check-in and check-out dates and times');
        return;
    }

    // Validate dates don't overlap with booked dates (client-side check)
    if (window.bookedDates && window.bookedDates.length > 0) {
        const checkInDate = document.getElementById('checkInDate').value;
        const checkOutDate = document.getElementById('checkOutDate').value;
        
        // Generate all dates in the selected range (excluding checkout date)
        const selectedDates = [];
        let current = new Date(checkInDate);
        const end = new Date(checkOutDate);
        while (current < end) {
            selectedDates.push(current.toISOString().split('T')[0]);
            current.setDate(current.getDate() + 1);
        }
        
        // Check if any selected date is booked
        const hasOverlap = selectedDates.some(date => window.bookedDates.includes(date));
        if (hasOverlap) {
            alert('⚠️ The selected dates overlap with existing bookings. Please choose different dates.');
            return;
        }
    }

    showBookingLoader('Processing your booking...');
    
    // Disable submit button to prevent double submission
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
    }

    // Combine date and time into datetime strings (ISO format)
    const checkInDateTime = `${checkInDate}T${checkInTime}:00`;
    const checkOutDateTime = `${checkOutDate}T${checkOutTime}:00`;

    const formData = {
        room_id: selectedRoom.id,
        full_name: document.getElementById('fullName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('contactNumber').value,
        check_in: checkInDateTime,
        check_out: checkOutDateTime,
    };

    try {
        const response = await fetch('/api/user/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(formData),
        });

        // Check content type and parse accordingly
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            hideBookingLoader();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Booking Request';
            }
            const responseText = await response.text();
            console.error('Server Error - Status:', response.status);
            console.error('Response:', responseText);
            alert('Server error (Status ' + response.status + '). Please check the browser console for details and contact support if the issue persists.');
            return;
        }

        if (response.ok) {
            hideBookingLoader();
            closeBookingModal();
            // Reload booked dates after successful booking
            if (selectedRoom) {
                loadBookedDatesForRoom(selectedRoom.id);
            }
            showSuccessModal();
        } else {
            hideBookingLoader();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Booking Request';
            }
            // Check for validation errors (overlapping bookings)
            if (data.message && (data.message.includes('already booked') || data.message.includes('overlap'))) {
                alert('⚠️ ' + data.message);
            } else if (data.errors) {
                // Show validation errors
                const errorMessages = Object.values(data.errors).flat().join('\n');
                alert('Validation Error:\n' + errorMessages);
            } else {
                alert('Error: ' + (data.message || 'Failed to submit booking'));
            }
        }
    } catch (error) {
        hideBookingLoader();
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Booking Request';
        }
        console.error('Fetch error:', error);
        alert('Error submitting booking request: ' + error.message);
    }
}

/**
 * Show booking loader
 */
function showBookingLoader(message = 'Processing...') {
    let loader = document.getElementById('bookingLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'bookingLoader';
        loader.className = 'modal-loader-overlay';
        document.body.appendChild(loader);
    }
    
    loader.innerHTML = `
        <div class="modal-loader-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    loader.style.display = 'flex';
}

/**
 * Hide booking loader
 */
function hideBookingLoader() {
    const loader = document.getElementById('bookingLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// Show success modal
function showSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close success modal
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Show error modal
function showErrorModal(message) {
    const modal = document.getElementById('errorModal');
    document.getElementById('errorMessage').textContent = message || 'An error occurred. Please try again.';
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close error modal
function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Reset after booking
function resetAfterBooking() {
    closeSuccessModal();
    
    // Reset form fields
    document.getElementById('bookingForm').reset();
    document.getElementById('fullName').value = '';
    document.getElementById('email').value = '';
    document.getElementById('contactNumber').value = '';
    document.getElementById('checkInDate').value = '';
    document.getElementById('checkInTime').value = '14:00';
    document.getElementById('checkOutDate').value = '';
    document.getElementById('checkOutTime').value = '11:00';
    
    // Clear selected room
    selectedRoom = null;
    document.querySelectorAll('.room-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Reset room selection display
    document.getElementById('selectedRoomDisplay').innerHTML = '<strong>Select a room to continue</strong>';
    document.getElementById('roomType').innerHTML = '<option value="">Select Room Type</option>';
    
    // Reload user info to refresh the form
    loadUserInfo();
    
    // Reload rooms to show updated availability
    loadRooms();
}

// Load user info
async function loadUserInfo() {
    try {
        const response = await fetch('/api/user');
        const data = await response.json();

        if (response.ok && data) {
            document.getElementById('profileName').textContent = data.name || 'User';
            document.getElementById('userInfo').textContent = data.role || 'Guest';
            document.getElementById('fullName').value = data.name || '';
            document.getElementById('email').value = data.email || '';
        }
    } catch (error) {
        console.error('Error loading user info:', error);
    }
}

// Logout function
async function handleLogout() {
    showModalConfirm('Are you sure you want to logout?', async () => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
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
            console.error('Error logging out:', error);
        }
    }, null, 'Confirm Logout');
}

// Global variables for Flatpickr instances
let checkInPicker = null;
let checkOutPicker = null;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadRooms();
    loadUserInfo();
    initializeDatePickers();

    // Attach form submission handler
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', submitBooking);
    }
});

// Initialize Flatpickr date pickers
function initializeDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    
    // Initialize check-in date picker
    checkInPicker = flatpickr('#checkInDate', {
        minDate: today,
        dateFormat: 'Y-m-d',
        onChange: function(selectedDates) {
            // Update checkout minimum date
            if (selectedDates.length > 0) {
                const minCheckout = new Date(selectedDates[0]);
                minCheckout.setDate(minCheckout.getDate() + 1);
                if (checkOutPicker) {
                    checkOutPicker.set('minDate', minCheckout);
                }
                validateSelectedDates();
            }
        }
    });

    // Initialize check-out date picker
    checkOutPicker = flatpickr('#checkOutDate', {
        minDate: today,
        dateFormat: 'Y-m-d',
        onChange: function() {
            validateSelectedDates();
        }
    });
}

// Load booked dates for a room and disable them in the calendar
async function loadBookedDatesForRoom(roomId) {
    try {
        const response = await fetch(`/api/rooms/${roomId}/booked-dates`);
        const data = await response.json();

        if (response.ok && data.booked_ranges) {
            // Store booked dates globally
            window.bookedDates = data.booked_dates || [];
            window.bookedRanges = data.booked_ranges || [];
            
            // Convert booked dates strings to Date objects for Flatpickr
            const disabledDateObjects = (window.bookedDates || []).map(dateStr => new Date(dateStr));
            
            // Update both date pickers with disabled dates
            if (checkInPicker) {
                checkInPicker.set('disable', disabledDateObjects);
            }
            if (checkOutPicker) {
                checkOutPicker.set('disable', disabledDateObjects);
            }
            
            // Validate current selections
            validateSelectedDates();

            if (window.bookedDates.length > 0) {
                console.log(`Room ${roomId} has ${window.bookedDates.length} unavailable dates`);
            }
        }
    } catch (error) {
        console.error('Error loading booked dates:', error);
    }
}

// Validate that selected dates don't overlap with booked dates
function validateSelectedDates() {
    if (!window.bookedDates || window.bookedDates.length === 0) return;

    const checkInInput = document.getElementById('checkInDate');
    const checkOutInput = document.getElementById('checkOutDate');
    const checkInDate = checkInInput.value;
    const checkOutDate = checkOutInput.value;

    if (!checkInDate || !checkOutDate) return;

    // Check if any date in the selected range is booked
    const userStart = new Date(checkInDate);
    const userEnd = new Date(checkOutDate);
    
    // Generate all dates in the selected range (excluding checkout date)
    const selectedDates = [];
    let current = new Date(userStart);
    while (current < userEnd) {
        selectedDates.push(current.toISOString().split('T')[0]);
        current.setDate(current.getDate() + 1);
    }
    
    // Check if any selected date is in the booked dates array
    const hasOverlap = selectedDates.some(date => window.bookedDates.includes(date));

    // Show or hide warning message
    let warningMsg = document.getElementById('dateConflictWarning');
    if (hasOverlap) {
        if (!warningMsg) {
            warningMsg = document.createElement('div');
            warningMsg.id = 'dateConflictWarning';
            warningMsg.style.cssText = 'color: #e74c3c; margin: 10px 0; padding: 10px; background: #ffe6e6; border-radius: 5px; border-left: 4px solid #e74c3c;';
            checkOutInput.parentNode.appendChild(warningMsg);
        }
        warningMsg.textContent = '⚠️ The selected dates overlap with existing bookings. Please choose different dates.';
    } else if (warningMsg) {
        warningMsg.textContent = '';
    }
}
