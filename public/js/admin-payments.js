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
        const guestName = payment.guest_name || payment.guest || '-';
        const bookingRef = payment.id || payment.booking_ref || '-';
        const roomName = payment.room_name || payment.room || '-';
        const amount = payment.total_price || payment.amount || 0;
        const method = payment.method || 'N/A';
        const date = payment.created_at || payment.date || '-';
        const status = payment.payment_status || payment.status || 'Pending';

        return `
            <tr onclick="openPaymentModal(${paymentId})">
                <td>${bookingRef}</td>
                <td>${guestName}</td>
                <td>${roomName}</td>
                <td>₱${amount}</td>
                <td>${method}</td>
                <td>${date}</td>
                <td>
                    <span class="status-badge ${status.toLowerCase().replace(/\s+/g, '_')}">
                        ${status}
                    </span>
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
        const response = await axios.get(`/api/payments/${reservationId}`);
        const payment = response.data;

        paymentsState.selectedPayment = payment;

        // Populate modal
        document.getElementById('modalGuestName').textContent = payment.guest_name;
        document.getElementById('modalBookingRef').textContent = payment.booking_ref;
        document.getElementById('modalRoom').textContent = payment.room_name;
        document.getElementById('modalAmount').textContent = '₱' + payment.amount;
        document.getElementById('modalMethod').textContent = payment.method;
        document.getElementById('modalDate').textContent = payment.date;
        document.getElementById('receiptAmount').textContent = '₱' + payment.amount;

        // Update footer buttons visibility
        updateModalFooter(payment.status);

        // Show modal
        document.getElementById('paymentModal').classList.add('show');
    } catch (error) {
        console.error('Error loading payment details:', error);
        showError('Failed to load payment details');
    }
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    paymentsState.selectedPayment = null;
}

function updateModalFooter(status) {
    const footer = document.getElementById('modalFooter');
    
    if (status === 'Pending') {
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

    try {
        const response = await axios.patch(
            `/api/payments/${paymentsState.selectedPayment.reservation_id}/approve`
        );

        if (response.status === 200) {
            showSuccess('Payment approved successfully');
            closePaymentModal();
            loadPayments();
        }
    } catch (error) {
        console.error('Error approving payment:', error);
        showError('Failed to approve payment');
    }
}

async function rejectPayment() {
    if (!paymentsState.selectedPayment) return;

    try {
        const response = await axios.patch(
            `/api/payments/${paymentsState.selectedPayment.reservation_id}/reject`
        );

        if (response.status === 200) {
            showSuccess('Payment rejected successfully');
            closePaymentModal();
            loadPayments();
        }
    } catch (error) {
        console.error('Error rejecting payment:', error);
        showError('Failed to reject payment');
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
