// ============================================
// STATE MANAGEMENT
// ============================================

let paymentsState = {
    currentPage: 1,
    totalPages: 1,
    status: 'all',
    search: '',
    data: [],
    selectedPayment: null
};

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    updateProfileName();
    loadPayments();
});

// ============================================
// UPDATE PROFILE
// ============================================

async function updateProfileName() {
    try {
        const response = await axios.get('/api/user');
        const user = response.data;
        
        const profileName = document.getElementById('profileName');
        if (profileName && user.name) {
            profileName.textContent = user.name;
        }
        
        const userInfo = document.getElementById('userInfo');
        if (userInfo && user.role) {
            userInfo.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
        }
    } catch (error) {
        console.error('Error loading profile:', error);
    }
}

// ============================================
// LOAD PAYMENTS
// ============================================

async function loadPayments() {
    try {
        const params = {
            page: paymentsState.currentPage,
            status: paymentsState.status,
            search: paymentsState.search
        };

        const response = await axios.get('/api/payments', { params });
        const data = response.data;

        paymentsState.data = data.data || data;
        
        // Handle both single data array and paginated response
        if (data.pagination) {
            paymentsState.totalPages = data.pagination.last_page || 1;
            renderPaymentsTable(paymentsState.data);
            updatePagination(data.pagination);
        } else {
            paymentsState.totalPages = 1;
            renderPaymentsTable(paymentsState.data);
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        showError('Failed to load payments');
    }
}

// ============================================
// RENDER TABLE
// ============================================

function renderPaymentsTable(payments) {
    const tbody = document.getElementById('paymentsTableBody');

    if (!tbody || !payments || payments.length === 0) {
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #999;">No payments found</td></tr>';
        }
        return;
    }

    tbody.innerHTML = payments.map(payment => {
        const paymentId = payment.reservation_id || payment.id;
        const guestName = payment.guest_name || 'N/A';
        const bookingRef = payment.booking_ref || 'PMS-' + String(payment.reservation_id || payment.id).padStart(5, '0');
        const roomName = payment.room?.type?.name || payment.room?.name || payment.room_name || 'N/A';
        const amount = parseFloat(payment.amount_raw || payment.total_amount || payment.amount || 0).toFixed(2);
        const method = payment.method || 'Not specified';
        const date = payment.date_raw || new Date(payment.payment_date || payment.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        
        // Determine status
        let status = payment.status === 'Pending' ? 'Pending' : (payment.status === 'Paid' ? 'Verified' : 'Rejected');
        let statusClass = payment.status === 'Pending' ? 'pending' : (payment.status === 'Paid' ? 'verified' : 'rejected');

        return `
            <tr data-payment-id="${paymentId}">
                <td>${bookingRef}</td>
                <td>${guestName}</td>
                <td>${roomName}</td>
                <td>₱${amount}</td>
                <td>${method}</td>
                <td>${date}</td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${status}
                    </span>
                </td>
                <td>
                    <button class="btn-view" onclick="openPaymentModal(${paymentId})" title="View Details">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// ============================================
// PAGINATION
// ============================================

function updatePagination(pagination) {
    const pageInfo = document.getElementById('pageInfo');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (pageInfo) {
        pageInfo.textContent = `Page ${pagination.current_page} of ${pagination.last_page}`;
    }

    if (prevBtn) {
        prevBtn.disabled = pagination.current_page === 1;
    }

    if (nextBtn) {
        nextBtn.disabled = pagination.current_page === pagination.last_page;
    }
}

function previousPage() {
    if (paymentsState.currentPage > 1) {
        paymentsState.currentPage--;
        loadPayments();
    }
}

function nextPage() {
    if (paymentsState.currentPage < paymentsState.totalPages) {
        paymentsState.currentPage++;
        loadPayments();
    }
}

// ============================================
// FILTERS AND SEARCH
// ============================================

function handleStatusChange() {
    paymentsState.status = document.getElementById('statusFilter').value;
    paymentsState.currentPage = 1;
    loadPayments();
}

function handleSearch() {
    paymentsState.search = document.getElementById('searchInput').value;
    paymentsState.currentPage = 1;
    loadPayments();
}

// ============================================
// PAYMENT MODAL
// ============================================

async function openPaymentModal(reservationId) {
    try {
        // Extract numeric reservation ID from formatted ID if needed
        let numericId;
        if (typeof reservationId === 'string' && reservationId.startsWith('PMS-PM')) {
            // Extract numeric part from 'PMS-PM0000' format
            numericId = parseInt(reservationId.replace('PMS-PM', ''), 10);
        } else {
            numericId = reservationId;
        }
        
        console.log('Opening payment modal with reservation ID:', numericId, 'Original ID:', reservationId);
        const response = await axios.get(`/api/payments/${numericId}`);
        const payment = response.data;

        console.log('Payment details loaded:', payment);
        paymentsState.selectedPayment = payment;

        // Generate booking reference if not present
        const bookingRef = payment.booking_ref || 'PMS-' + String(numericId).padStart(5, '0');

        // Populate modal fields
        document.getElementById('modalBookingRef').textContent = bookingRef;
        document.getElementById('modalGuestName').textContent = payment.guest_name || 'N/A';
        document.getElementById('modalRoom').textContent = payment.room_name || 'N/A';
        document.getElementById('modalAmount').textContent = '₱' + parseFloat(payment.amount_raw || payment.amount || 0).toFixed(2);
        document.getElementById('modalMethod').textContent = payment.method || 'Not specified';
        document.getElementById('modalDate').textContent = payment.payment_date || 'Not submitted';
        
        // Add check-in and check-out dates
        document.getElementById('modalCheckIn').textContent = payment.check_in || 'N/A';
        document.getElementById('modalCheckOut').textContent = payment.check_out || 'N/A';
        
        // Add payment status
        const statusText = payment.status === 'Pending' ? 'Pending Verification' : (payment.status === 'Paid' ? 'Verified' : 'Rejected');
        document.getElementById('modalPaymentStatus').textContent = statusText;

        // Display payment proof from user submission
        const proofImage = document.getElementById('proofImage');
        const noProof = document.getElementById('noProof');
        
        if (payment.payment_proof) {
            // Use the actual proof file uploaded by the user
            const imagePath = payment.payment_proof.startsWith('/') ? payment.payment_proof : `/storage/${payment.payment_proof}`;
            proofImage.src = imagePath;
            
            // Add error handler for failed image load
            proofImage.onerror = function() {
                proofImage.style.display = 'none';
                noProof.style.display = 'block';
                noProof.textContent = 'Failed to load payment proof image';
            };
            
            proofImage.onload = function() {
                proofImage.style.display = 'block';
                noProof.style.display = 'none';
            };
            
            // Try to load the image
            proofImage.style.display = 'block';
            noProof.style.display = 'none';
        } else {
            proofImage.style.display = 'none';
            noProof.style.display = 'block';
        }

        // Update footer buttons visibility
        updateModalFooter(payment.status);

        // Show modal
        document.getElementById('paymentModal').classList.add('show');
    } catch (error) {
        console.error('Error loading payment details:', error);
        showModalAlert('Failed to load payment details: ' + (error.response?.data?.message || error.message), 'error');
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    paymentsState.selectedPayment = null;
}

function updateModalFooter(status) {
    const footer = document.getElementById('modalFooter');
    
    // Show buttons only for pending payments
    if (status === 'pending' || status === 'Pending') {
        footer.style.display = 'flex';
    } else {
        footer.style.display = 'none';
    }
}

// ============================================
// PAYMENT ACTIONS
// ============================================

async function approvePayment() {
    if (!paymentsState.selectedPayment) return;

    // Show loader
    showPaymentLoader('Approving payment...');

    try {
        // Use the reservation_id from the payment details, or extract from the formatted id
        let reservationId = paymentsState.selectedPayment.reservation_id;
        
        if (!reservationId) {
            // Fallback: extract from formatted ID
            const formattedId = paymentsState.selectedPayment.id;
            if (typeof formattedId === 'string' && formattedId.startsWith('PMS-PM')) {
                reservationId = parseInt(formattedId.replace('PMS-PM', ''), 10);
            }
        }
        
        console.log('Approving payment with reservation ID:', reservationId);
        const response = await axios.patch(
            `/api/payments/${reservationId}/approve`
        );

        console.log('Approve response:', response.data);
        hidePaymentLoader();
        if (response.status === 200) {
            showModalAlert('Payment approved successfully!', 'success');
            closePaymentModal();
            loadPayments();
        }
    } catch (error) {
        hidePaymentLoader();
        console.error('Error approving payment:', error);
        console.error('Error response:', error.response?.data);
        showModalAlert('Failed to approve payment: ' + (error.response?.data?.message || error.message), 'error');
    }
}

async function rejectPayment() {
    if (!paymentsState.selectedPayment) return;

    // Show loader
    showPaymentLoader('Rejecting payment...');

    try {
        // Use the reservation_id from the payment details, or extract from the formatted id
        let reservationId = paymentsState.selectedPayment.reservation_id;
        
        if (!reservationId) {
            // Fallback: extract from formatted ID
            const formattedId = paymentsState.selectedPayment.id;
            if (typeof formattedId === 'string' && formattedId.startsWith('PMS-PM')) {
                reservationId = parseInt(formattedId.replace('PMS-PM', ''), 10);
            }
        }
        
        console.log('Rejecting payment with reservation ID:', reservationId);
        const response = await axios.patch(
            `/api/payments/${reservationId}/reject`
        );

        console.log('Reject response:', response.data);
        hidePaymentLoader();
        if (response.status === 200) {
            showModalAlert('Payment rejected successfully!', 'success');
            closePaymentModal();
            loadPayments();
        }
    } catch (error) {
        hidePaymentLoader();
        console.error('Error rejecting payment:', error);
        console.error('Error response:', error.response?.data);
        showModalAlert('Failed to reject payment: ' + (error.response?.data?.message || error.message), 'error');
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function showSuccess(message) {
    console.log('Success:', message);
    // You can add a toast notification here
}

function showError(message) {
    console.error('Error:', message);
    // You can add error toast notification here
}

// Close modal when clicking outside
document.addEventListener('click', function (event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
});

// ============================================
// LOADER FUNCTIONS
// ============================================

function showPaymentLoader(message = 'Processing...') {
    let loader = document.getElementById('adminPaymentLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'adminPaymentLoader';
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

function hidePaymentLoader() {
    const loader = document.getElementById('adminPaymentLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}
