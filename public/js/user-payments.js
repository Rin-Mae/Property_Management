/* ========== User Payments Page Scripts ========== */

let selectedPaymentId = null;
let paymentsData = {}; // Store payment data for modal

// Load user info
async function loadUserInfo() {
    try {
        const response = await fetch('/api/user');
        const data = await response.json();

        if (response.ok && data) {
            document.getElementById('profileName').textContent = data.name || 'User';
            document.getElementById('userInfo').textContent = data.role || 'Guest';
        }
    } catch (error) {
        console.error('Error loading user info:', error);
    }
}

// Load pending payments
async function loadPendingPayments() {
    // Show loader
    document.getElementById('pendingPaymentsLoading').style.display = 'block';
    document.getElementById('pendingPaymentsContainer').style.display = 'none';
    document.getElementById('noPendingPayments').style.display = 'none';

    try {
        const response = await fetch('/api/user/payments/pending');
        const data = await response.json();

        if (response.ok) {
            // Filter to only show payments without payment_date (not yet submitted)
            const unsubmittedPayments = data.data.filter(p => !p.payment_date);
            
            if (unsubmittedPayments && unsubmittedPayments.length > 0) {
                displayPendingPayments(unsubmittedPayments);
                document.getElementById('pendingPaymentsLoading').style.display = 'none';
                document.getElementById('pendingPaymentsContainer').style.display = 'block';
            } else {
                document.getElementById('pendingPaymentsLoading').style.display = 'none';
                document.getElementById('noPendingPayments').style.display = 'block';
            }
        } else {
            console.error('Error loading pending payments:', data);
            document.getElementById('pendingPaymentsLoading').style.display = 'none';
            document.getElementById('pendingPaymentsLoading').innerHTML = '<div style="color: #c62828;">Error loading pending payments</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('pendingPaymentsLoading').style.display = 'none';
        document.getElementById('pendingPaymentsLoading').innerHTML = '<div style="color: #c62828;">Error loading pending payments</div>';
    }
}

// Display pending payments
function displayPendingPayments(payments) {
    const container = document.getElementById('pendingPaymentsList');
    container.innerHTML = '';

    payments.forEach((payment, index) => {
        const paymentHtml = `
            <div class="dashboard-card">
                <div class="payment-section">
                    <!-- Booking Summary -->
                    <div>
                        <div class="booking-summary">
                            <h3>Booking Summary</h3>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <img src="/${payment.room?.image_url || ''}" alt="${payment.room?.name}" class="booking-image">
                                    <div class="booking-header-info">
                                        <div class="booking-ref">Reference No.</div>
                                        <div class="booking-ref-number">${payment.reference_number}</div>
                                    </div>
                                </div>
                                <div class="booking-details">
                                    <div class="detail-row">
                                        <div class="detail-label">Room Type</div>
                                        <div class="detail-value">${payment.room?.type?.name || 'N/A'}</div>
                                    </div>
                                    <div class="detail-row">
                                        <div class="detail-label">Check-in / Checkout</div>
                                        <div class="detail-value">${formatDate(payment.check_in)} - ${formatDate(payment.check_out)}</div>
                                    </div>
                                </div>
                                <div class="total-amount">
                                    <div class="total-label">Total Amount</div>
                                    <div class="total-value">₱${parseFloat(payment.total_amount).toFixed(2)}</div>
                                    <span class="status-badge status-pending">Pending Verification</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions & Form Side by Side -->
                    <div class="payment-instructions-form-container">
                        <!-- Payment Instructions -->
                        <div class="payment-instructions">
                            <h3>Payment Instructions</h3>
                            <div class="payment-methods">
                                <div class="payment-method-item">
                                    <i class="fas fa-money-bill"></i>
                                    <span>Cash</span>
                                </div>
                                <div class="payment-method-item">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                </div>
                                <div class="payment-method-item">
                                    <i class="fas fa-university"></i>
                                    <span>Bank Transfer</span>
                                </div>
                            </div>
                            <div class="payment-steps">
                                <h4><i class="fas fa-tasks"></i> Steps for payment method:</h4>
                                <ol class="steps-list" style="counter-reset: step;">
                                    <li>Send payment to the provided account.</li>
                                    <li>Upload proof of payment</li>
                                    <li>Wait for verification</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form class="payment-form" data-payment-id="${payment.id}">
                            <!-- Payment Information Section -->
                            <div class="form-section">
                                <h3>Payment Information</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fullName_${payment.id}">Full Name <span style="color: #d9534f;">*</span></label>
                                        <input type="text" id="fullName_${payment.id}" name="full_name" placeholder="Full Name" value="${payment.guest_name || ''}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="refNumber_${payment.id}">Booking Reference Number <span style="color: #d9534f;">*</span></label>
                                        <input type="text" id="refNumber_${payment.id}" name="reference_number" value="${payment.reference_number}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details Section -->
                            <div class="form-section">
                                <h3>Payment Details</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="paymentMethod_${payment.id}">Payment Method <span style="color: #d9534f;">*</span></label>
                                        <select id="paymentMethod_${payment.id}" name="payment_method" required onchange="toggleProofUpload(${payment.id})">
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="gcash">GCash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="amountToPay_${payment.id}">Amount to Pay <span style="color: #d9534f;">*</span></label>
                                        <input type="number" id="amountToPay_${payment.id}" name="amount" value="${payment.total_amount}" readonly step="0.01">
                                    </div>
                                </div>
                            </div>

                            <!-- Proof of Payment Section (Hidden for Cash) -->
                            <div class="form-section" id="proofSection_${payment.id}" style="display: none;">
                                <h3>Proof of Payment</h3>
                                <div class="form-group">
                                    <label for="proofFile_${payment.id}">Upload Proof of Payment <span style="color: #d9534f;">*</span></label>
                                    <div class="file-input-wrapper">
                                        <label for="fileInput_${payment.id}" class="file-input-label">Choose File</label>
                                        <input type="file" id="fileInput_${payment.id}" name="proof_file" accept=".jpg,.jpeg,.png,.pdf">
                                        <div class="file-info">Accepted: JPG, PNG, PDF</div>
                                    </div>
                                    <div class="image-preview-container" id="imagePreview_${payment.id}">
                                        <img id="previewImage_${payment.id}" src="" alt="Preview">
                                        <div class="preview-filename" id="previewFilename_${payment.id}"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit and Cancel Buttons -->
                            <div class="form-buttons">
                                <button type="button" class="cancel-btn" onclick="cancelPayment(${payment.id})">Cancel</button>
                                <button type="submit" class="submit-btn">Submit Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += paymentHtml;

        // Add form submission handler
        const form = container.querySelector(`form[data-payment-id="${payment.id}"]`);
        form.addEventListener('submit', (e) => submitPayment(e, payment.id));

        // Handle file input display and preview
        const fileInput = document.getElementById(`fileInput_${payment.id}`);
        const fileLabel = document.querySelector(`label[for="fileInput_${payment.id}"]`);
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                fileLabel.textContent = file.name;
                
                // Show preview for images
                const previewContainer = document.getElementById(`imagePreview_${payment.id}`);
                const previewImage = document.getElementById(`previewImage_${payment.id}`);
                const previewFilename = document.getElementById(`previewFilename_${payment.id}`);
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        previewImage.src = event.target.result;
                        previewFilename.textContent = file.name;
                        previewContainer.classList.add('show');
                    };
                    reader.readAsDataURL(file);
                } else {
                    // For non-image files (PDF, etc)
                    previewContainer.classList.remove('show');
                }
            }
        });
    });
}

// Toggle proof upload section based on payment method
function toggleProofUpload(paymentId) {
    const method = document.getElementById(`paymentMethod_${paymentId}`).value;
    const proofSection = document.getElementById(`proofSection_${paymentId}`);
    const fileInput = document.getElementById(`fileInput_${paymentId}`);
    
    if (method === 'cash') {
        proofSection.style.display = 'none';
        fileInput.removeAttribute('required');
    } else {
        proofSection.style.display = 'block';
        fileInput.setAttribute('required', 'required');
    }
}

// Submit payment
async function submitPayment(e, paymentId) {
    e.preventDefault();

    // Show loader
    showPaymentLoader('Submitting payment...');

    const form = e.target;
    const formData = new FormData(form);
    const paymentMethod = document.getElementById(`paymentMethod_${paymentId}`).value;
    
    // Automatically add today's date
    const today = new Date().toISOString().split('T')[0];
    formData.set('payment_date', today);
    
    // Validate: if not cash, proof_file must be present
    if (paymentMethod !== 'cash') {
        const fileInput = document.getElementById(`fileInput_${paymentId}`);
        if (!fileInput.files || fileInput.files.length === 0) {
            hidePaymentLoader();
            showModalAlert('Please upload payment proof', 'warning');
            return;
        }
    }

    try {
        const response = await fetch(`/api/user/payments/${paymentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        const data = await response.json();

        hidePaymentLoader();

        if (response.ok) {
            showModalAlert('Payment submitted successfully!', 'success');
            setTimeout(() => {
                loadPendingPayments();
                loadPaymentHistory();
            }, 1000);
        } else {
            showModalAlert('Error: ' + (data.message || 'Failed to submit payment'), 'error');
        }
    } catch (error) {
        hidePaymentLoader();
        console.error('Error:', error);
        showModalAlert('Error submitting payment: ' + error.message, 'error');
    }
}

// Cancel payment form - show confirmation modal
function cancelPayment(paymentId) {
    // Store the payment ID for the confirmation
    window.paymentIdToCancelForm = paymentId;
    
    // Show confirmation modal
    const modal = document.getElementById('cancelPaymentModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Confirm cancel payment - actually cancel the reservation
async function confirmCancelPaymentForm() {
    const paymentId = window.paymentIdToCancelForm;
    
    if (!paymentId) {
        showModalAlert('Error: Payment ID not found', 'error');
        return;
    }
    
    closeCancelPaymentModal();
    showPaymentLoader('Cancelling booking...');
    
    try {
        // Call the API to cancel/reject the payment which cancels the reservation
        const response = await fetch(`/api/payments/${paymentId}/reject`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        const data = await response.json();
        hidePaymentLoader();

        if (response.ok) {
            showModalAlert('Booking cancelled successfully!', 'success');
            setTimeout(() => {
                loadPendingPayments();
                loadPaymentHistory();
            }, 1500);
        } else {
            showModalAlert('Error: ' + (data.message || 'Failed to cancel booking'), 'error');
        }
    } catch (error) {
        hidePaymentLoader();
        console.error('Error:', error);
        showModalAlert('Error cancelling booking: ' + error.message, 'error');
    }
}

// Close cancel payment modal
function closeCancelPaymentModal() {
    const modal = document.getElementById('cancelPaymentModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Load payment history
async function loadPaymentHistory() {
    // Show loader
    document.getElementById('paymentHistoryLoading').style.display = 'block';
    document.getElementById('paymentHistoryContainer').style.display = 'none';
    document.getElementById('noPaymentHistory').style.display = 'none';

    try {
        const response = await fetch('/api/user/payments');
        const data = await response.json();

        if (response.ok) {
            // Filter to show payments with payment_date (already submitted) OR rejected/cancelled payments
            const submittedPayments = data.data.filter(p => p.payment_date || p.reservation_status === 'cancelled');
            
            if (submittedPayments && submittedPayments.length > 0) {
                displayPaymentHistory(submittedPayments);
                document.getElementById('paymentHistoryLoading').style.display = 'none';
                document.getElementById('paymentHistoryContainer').style.display = 'block';
            } else {
                document.getElementById('paymentHistoryLoading').style.display = 'none';
                document.getElementById('noPaymentHistory').style.display = 'block';
            }
        } else {
            console.error('Error loading payment history:', data);
            document.getElementById('paymentHistoryLoading').style.display = 'none';
            document.getElementById('paymentHistoryLoading').innerHTML = '<div style="color: #c62828;">Error loading payment history</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('paymentHistoryLoading').style.display = 'none';
        document.getElementById('paymentHistoryLoading').innerHTML = '<div style="color: #c62828;">Error loading payment history</div>';
    }
}

// Display payment history
function displayPaymentHistory(payments) {
    const tbody = document.getElementById('paymentHistoryTableBody');
    tbody.innerHTML = '';

    payments.forEach(payment => {
        // Store payment data for modal
        paymentsData[payment.id] = payment;

        const row = document.createElement('tr');
        
        // Determine status based on reservation status
        let statusClass, statusText;
        if (payment.reservation_status === 'cancelled') {
            statusClass = 'status-cancelled';
            statusText = 'Cancelled';
        } else if (payment.status === 'verified') {
            statusClass = 'status-verified';
            statusText = 'Verified';
        } else {
            statusClass = 'status-pending';
            statusText = 'Pending Verification';
        }

        row.innerHTML = `
            <td>${payment.reference_number}</td>
            <td>${payment.room?.type?.name || 'N/A'}</td>
            <td>₱${parseFloat(payment.amount).toFixed(2)}</td>
            <td>${statusText === 'Cancelled' ? 'N/A' : formatDate(payment.payment_date)}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <button class="action-btn" onclick="viewPaymentDetails('${payment.id}')">View</button>
                ${(statusText === 'Cancelled' || statusText === 'Verified') ? '' : `<button class="action-btn action-btn-edit" onclick="openEditPaymentModal('${payment.id}')">Edit</button>`}
            </td>
        `;

        tbody.appendChild(row);
    });
}

// View payment details
function viewPaymentDetails(paymentId) {
    const payment = paymentsData[paymentId];
    if (!payment) {
        showModalAlert('Payment details not found', 'error');
        return;
    }

    // Determine status based on reservation status
    let statusText, statusClass;
    if (payment.reservation_status === 'cancelled') {
        statusText = 'Cancelled';
        statusClass = 'status-cancelled-modal';
    } else if (payment.status === 'verified') {
        statusText = 'Verified';
        statusClass = 'status-verified-modal';
    } else {
        statusText = 'Pending Verification';
        statusClass = 'status-pending-modal';
    }

    // Capitalize payment method
    const paymentMethodMap = {
        'cash': 'Cash',
        'gcash': 'GCash',
        'bank_transfer': 'Bank Transfer'
    };
    const paymentMethod = paymentMethodMap[payment.payment_method] || payment.payment_method || 'N/A';

    // Populate modal fields
    document.getElementById('modalRefNumber').textContent = payment.reference_number || 'N/A';
    document.getElementById('modalRoomType').textContent = payment.room?.type?.name || 'N/A';
    document.getElementById('modalCheckIn').textContent = formatDate(payment.check_in) || 'N/A';
    document.getElementById('modalCheckOut').textContent = formatDate(payment.check_out) || 'N/A';
    document.getElementById('modalPaymentMethod').textContent = paymentMethod;
    document.getElementById('modalPaymentDate').textContent = formatDate(payment.payment_date) || (statusText === 'Cancelled' ? '—' : 'N/A');
    document.getElementById('modalAmount').textContent = '₱' + parseFloat(payment.amount || 0).toFixed(2);
    document.getElementById('modalStatus').innerHTML = `<span class="status-badge-modal ${statusClass}">${statusText}</span>`;
    document.getElementById('modalFullName').textContent = payment.guest_name || 'N/A';
    document.getElementById('modalNotes').textContent = payment.notes || 'No additional notes';

    // Populate payment proof
    console.log('Payment object:', payment);
    console.log('Payment proof value:', payment.payment_proof);
    const proofContainer = document.getElementById('modalProofContainer');
    const noProofContainer = document.getElementById('modalNoProof');
    
    if (payment.payment_proof) {
        proofContainer.style.display = 'block';
        noProofContainer.style.display = 'none';
        
        const proofUrl = `/storage/${payment.payment_proof}`;
        const proofImage = document.getElementById('modalProofImage');
        const proofDownload = document.getElementById('modalProofDownload');
        const proofFilename = document.getElementById('modalProofFilename');
        
        console.log('Proof URL:', proofUrl);
        proofImage.src = proofUrl;
        proofDownload.href = proofUrl;
        proofFilename.textContent = payment.payment_proof.split('/').pop() || 'Payment Proof';
    } else {
        proofContainer.style.display = 'none';
        noProofContainer.style.display = 'block';
        console.log('No payment proof for this payment');
    }

    // Show modal
    document.getElementById('paymentModal').classList.add('show');
}

// Close payment modal
function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
}

// Open edit payment modal
function openEditPaymentModal(paymentId) {
    const payment = paymentsData[paymentId];
    if (!payment) {
        showModalAlert('Payment details not found', 'error');
        return;
    }

    // Store the payment ID for later use
    window.editingPaymentId = paymentId;

    // Populate booking information (read-only)
    document.getElementById('editModalRefNumber').textContent = payment.reference_number || 'N/A';
    document.getElementById('editModalRoomType').textContent = payment.room?.type?.name || 'N/A';
    document.getElementById('editModalCheckIn').textContent = formatDate(payment.check_in) || 'N/A';
    document.getElementById('editModalCheckOut').textContent = formatDate(payment.check_out) || 'N/A';
    document.getElementById('editModalAmount').textContent = '₱' + parseFloat(payment.amount || 0).toFixed(2);
    
    // Populate payment information
    document.getElementById('editModalFullName').textContent = payment.guest_name || 'N/A';
    document.getElementById('editModalPaymentDate').textContent = formatDate(payment.payment_date) || 'N/A';

    // Set the current payment method
    const paymentMethodSelect = document.getElementById('editPaymentMethod');
    paymentMethodSelect.value = payment.payment_method || '';

    // Reset file input and preview
    document.getElementById('editFileInput').value = '';
    document.getElementById('editImagePreview').classList.remove('show');
    document.getElementById('editFileInput').previousElementSibling.textContent = 'Choose File';

    // Toggle proof section based on payment method
    toggleEditProofUpload();

    // Setup file preview listener
    const editFileInput = document.getElementById('editFileInput');
    editFileInput.onchange = handleEditFilePreview;

    // Show modal
    document.getElementById('editPaymentModal').classList.add('show');
}

// Handle file preview for edit modal
function handleEditFilePreview(e) {
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        const fileLabel = e.target.previousElementSibling;
        fileLabel.textContent = file.name;
        
        // Show preview for images
        const previewContainer = document.getElementById('editImagePreview');
        const previewImage = document.getElementById('editPreviewImage');
        const previewFilename = document.getElementById('editPreviewFilename');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (event) => {
                previewImage.src = event.target.result;
                previewFilename.textContent = file.name;
                previewContainer.classList.add('show');
            };
            reader.readAsDataURL(file);
        } else {
            // For non-image files (PDF, etc)
            previewContainer.classList.remove('show');
        }
    }
}

// Close edit payment modal
function closeEditPaymentModal() {
    document.getElementById('editPaymentModal').classList.remove('show');
    window.editingPaymentId = null;
}

// Toggle proof upload section for edit modal
function toggleEditProofUpload() {
    const method = document.getElementById('editPaymentMethod').value;
    const proofSection = document.getElementById('editProofSection');
    const fileInput = document.getElementById('editFileInput');
    
    if (method === 'cash') {
        proofSection.style.display = 'none';
        fileInput.removeAttribute('required');
    } else {
        proofSection.style.display = 'block';
        fileInput.setAttribute('required', 'required');
    }
}

// Submit edited payment
async function submitEditPayment() {
    const paymentId = window.editingPaymentId;
    if (!paymentId) {
        showModalAlert('Payment ID not found', 'error');
        return;
    }

    const paymentMethod = document.getElementById('editPaymentMethod').value;
    const fileInput = document.getElementById('editFileInput');

    // Validate payment method
    if (!paymentMethod) {
        showModalAlert('Please select a payment method', 'warning');
        return;
    }

    // Validate: if not cash, proof_file is optional but if provided, validate
    if (paymentMethod !== 'cash' && fileInput.files.length === 0) {
        showModalAlert('Please upload payment proof for this payment method', 'warning');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('payment_method', paymentMethod);
        
        if (fileInput.files.length > 0) {
            formData.append('proof_file', fileInput.files[0]);
        }

        // Log FormData content for debugging
        console.log('Sending POST (method override to PATCH) with paymentId:', paymentId);
        console.log('Payment method:', paymentMethod);
        console.log('File count:', fileInput.files.length);

        const response = await fetch(`/api/user/payments/${paymentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PATCH',
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            showModalAlert('Payment method updated successfully!', 'success');
            closeEditPaymentModal();
            loadPaymentHistory();
        } else {
            console.error('Error response:', data);
            console.error('Response status:', response.status);
            if (data.errors) {
                console.error('Validation errors:', data.errors);
                console.error('Full errors object:', JSON.stringify(data.errors));
                let errorMsg = 'Validation errors: ';
                for (const [key, messages] of Object.entries(data.errors)) {
                    errorMsg += `${key}: ${messages.join(', ')} `;
                }
                showModalAlert(errorMsg, 'error');
            } else {
                showModalAlert('Error: ' + (data.message || 'Failed to update payment'), 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showModalAlert('Error updating payment: ' + error.message, 'error');
    }
}

// Print payment details
function printPaymentDetails() {
    const modal = document.getElementById('paymentModal');
    const modalContent = modal.querySelector('.modal-content');

    const printWindow = window.open('', '', 'height=800,width=900');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Payment Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    background: white;
                }
                h2 {
                    text-align: center;
                    color: #333;
                    margin-bottom: 10px;
                }
                .print-date {
                    text-align: center;
                    color: #666;
                    margin-bottom: 20px;
                    font-size: 0.9rem;
                }
                .detail-section {
                    margin-bottom: 20px;
                }
                .detail-section h3 {
                    color: #2d7f3d;
                    border-bottom: 2px solid #2d7f3d;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                }
                .detail-items {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 15px;
                }
                .detail-item {
                    margin-bottom: 10px;
                }
                .detail-item-label {
                    font-weight: 600;
                    color: #666;
                    font-size: 0.9rem;
                }
                .detail-item-value {
                    color: #333;
                }
                .status-badge-modal {
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-weight: 600;
                    font-size: 0.9rem;
                }
                .status-verified-modal {
                    background: #dcfce7;
                    color: #166534;
                }
                .status-pending-modal {
                    background: #fef3c7;
                    color: #92400e;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            <h2>Payment Details</h2>
            <div class="print-date">Printed on: ${new Date().toLocaleString()}</div>
            ${modalContent.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();

    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Close modal when clicking outside
window.onclick = function (event) {
    const paymentModal = document.getElementById('paymentModal');
    const editPaymentModal = document.getElementById('editPaymentModal');
    
    if (event.target === paymentModal) {
        paymentModal.classList.remove('show');
    }
    
    if (event.target === editPaymentModal) {
        editPaymentModal.classList.remove('show');
    }
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadUserInfo();
    loadPendingPayments();
    loadPaymentHistory();
});

// ============================================
// LOADER FUNCTIONS
// ============================================

function showPaymentLoader(message = 'Processing...') {
    let loader = document.getElementById('paymentLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'paymentLoader';
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
    const loader = document.getElementById('paymentLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}
