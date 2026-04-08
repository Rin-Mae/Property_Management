/* ========== User Reports Page Scripts ========== */

// State for user reports pagination
let userReportsState = {
    bookings: {
        data: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 5
    },
    payments: {
        data: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 5
    }
};

// Modal helper functions
function showSuccessModal(title, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content modal-success">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close-btn" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="this.closest('.modal-overlay').remove()">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    });
    
    const closeBtn = modal.querySelector('.btn-primary');
    closeBtn.addEventListener('click', function() {
        modal.remove();
        if (callback) callback();
    });
}

function showErrorModal(title, message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content modal-error">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close-btn" onclick="this.closest('.modal-overlay').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    });
    
    const closeBtn = modal.querySelector('.btn-secondary');
    closeBtn.addEventListener('click', function() {
        modal.remove();
        if (callback) callback();
    });
}

// Set active sidebar link
document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Load initial data
    loadReportData();

    // Add event listeners for filters
    document.getElementById('bookingMonthFilter').addEventListener('change', () => {
        userReportsState.bookings.currentPage = 1;
        loadReportData();
    });
    document.getElementById('bookingStatusFilter').addEventListener('change', () => {
        userReportsState.bookings.currentPage = 1;
        loadReportData();
    });
    document.getElementById('paymentMonthFilter').addEventListener('change', () => {
        userReportsState.payments.currentPage = 1;
        loadReportData();
    });
    document.getElementById('paymentStatusFilter').addEventListener('change', () => {
        userReportsState.payments.currentPage = 1;
        loadReportData();
    });
});

async function loadReportData() {
    // Show loader
    const bookingTable = document.getElementById('bookingHistoryTable');
    const paymentTable = document.getElementById('paymentHistoryTable');
    
    if (bookingTable && bookingTable.parentElement) {
        bookingTable.parentElement.style.opacity = '0.6';
    }
    if (paymentTable && paymentTable.parentElement) {
        paymentTable.parentElement.style.opacity = '0.6';
    }

    showReportLoader('Loading report data...');

    try {
        const response = await axios.get('/api/user/reports', {
            params: {
                bookingMonth: document.getElementById('bookingMonthFilter').value,
                bookingStatus: document.getElementById('bookingStatusFilter').value,
                paymentMonth: document.getElementById('paymentMonthFilter').value,
                paymentStatus: document.getElementById('paymentStatusFilter').value,
                bookingPage: userReportsState.bookings.currentPage,
                paymentPage: userReportsState.payments.currentPage
            }
        });

        const data = response.data;

        // Update statistics
        document.getElementById('totalBookingsCount').textContent = data.stats.totalBookings;
        document.getElementById('totalPaymentsCount').textContent = '₱' + formatAmount(data.stats.totalPayments);
        document.getElementById('confirmedBookingsCount').textContent = data.stats.confirmedBookings;
        document.getElementById('cancelledBookingsCount').textContent = data.stats.cancelledBookings;

        // Update booking history table with pagination
        loadBookingHistoryTable(data.bookings, data.bookingsPagination);

        // Update payment history table with pagination
        loadPaymentHistoryTable(data.payments, data.paymentsPagination);

        hideReportLoader();
        
        if (bookingTable && bookingTable.parentElement) {
            bookingTable.parentElement.style.opacity = '1';
        }
        if (paymentTable && paymentTable.parentElement) {
            paymentTable.parentElement.style.opacity = '1';
        }
    } catch (error) {
        hideReportLoader();
        console.error('Error loading report data:', error);
        
        if (bookingTable && bookingTable.parentElement) {
            bookingTable.parentElement.style.opacity = '1';
        }
        if (paymentTable && paymentTable.parentElement) {
            paymentTable.parentElement.style.opacity = '1';
        }
    }
}

function loadBookingHistoryTable(bookings, pagination) {
    const tbody = document.getElementById('bookingHistoryTable');
    const noDataDiv = document.getElementById('bookingNoData');

    tbody.innerHTML = '';

    if (!bookings || bookings.length === 0) {
        noDataDiv.style.display = 'block';
        updateBookingsPaginationControls({});
        return;
    }

    noDataDiv.style.display = 'none';

    bookings.forEach(booking => {
        const statusClass = `status-${booking.status.toLowerCase()}`;
        
        // Show Submit Feedback button only for completed bookings (checked_out status)
        let actionCell = '<td></td>'; // Empty by default
        if (booking.status === 'Checked_out') {
            // Extract numeric ID from reference (PMS-00021 -> 21)
            const reservationId = parseInt(booking.reference_no.replace('PMS-', ''));
            actionCell = `<td><button class="btn-action btn-feedback" onclick="openFeedbackModal(${reservationId}, '${booking.reference_no}')"><i class="fas fa-comment"></i> Submit Feedback</button></td>`;
        }
        
        const row = `
            <tr>
                <td>${booking.reference_no || booking.id}</td>
                <td>${booking.room_type}</td>
                <td>${formatDate(booking.check_in)} - ${formatDate(booking.check_out)}</td>
                <td><span class="status-badge ${statusClass}">${capitalizeStatus(booking.status)}</span></td>
                <td>${formatDate(booking.created_at)}</td>
                ${actionCell}
            </tr>
        `;
        tbody.innerHTML += row;
    });

    // Update pagination state
    if (pagination) {
        userReportsState.bookings = {
            data: bookings,
            currentPage: pagination.current_page || 1,
            totalPages: pagination.last_page || 1,
            perPage: pagination.per_page || 5
        };
    }

    updateBookingsPaginationControls(pagination || {});
}

function loadPaymentHistoryTable(payments, pagination) {
    const tbody = document.getElementById('paymentHistoryTable');
    const noDataDiv = document.getElementById('paymentNoData');

    tbody.innerHTML = '';

    if (!payments || payments.length === 0) {
        noDataDiv.style.display = 'block';
        updatePaymentsPaginationControls({});
        return;
    }

    noDataDiv.style.display = 'none';

    payments.forEach(payment => {
        const statusClass = `status-${payment.status.toLowerCase()}`;
        const row = `
            <tr>
                <td>${payment.reference_no || payment.id}</td>
                <td>${payment.room_name}</td>
                <td>₱${formatAmount(payment.amount)}</td>
                <td>${payment.payment_date ? formatDate(payment.payment_date) : 'N/A'}</td>
                <td><span class="status-badge ${statusClass}">${capitalizeStatus(payment.status)}</span></td>
                <td>${formatDate(payment.created_at)}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    // Update pagination state
    if (pagination) {
        userReportsState.payments = {
            data: payments,
            currentPage: pagination.current_page || 1,
            totalPages: pagination.last_page || 1,
            perPage: pagination.per_page || 5
        };
    }

    updatePaymentsPaginationControls(pagination || {});
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatAmount(amount) {
    return parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function capitalizeStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function downloadPDF(reportType) {
    let element, filename, title;

    if (reportType === 'bookings') {
        element = document.getElementById('bookingHistoryTable').closest('.report-section');
        filename = 'booking_history.pdf';
        title = 'Booking History Report';
    } else if (reportType === 'payments') {
        element = document.getElementById('paymentHistoryTable').closest('.report-section');
        filename = 'payment_history.pdf';
        title = 'Payment History Report';
    }

    if (!element) {
        alert('Error: Could not find report data');
        return;
    }

    // Create a clone for PDF rendering
    const clonedElement = element.cloneNode(true);

    // Remove unnecessary elements
    const filterGroup = clonedElement.querySelector('.filter-group');
    if (filterGroup) filterGroup.remove();

    // Create wrapper with title and timestamp
    const wrapper = document.createElement('div');
    wrapper.style.padding = '20px';

    const titleElement = document.createElement('h2');
    titleElement.textContent = title;
    titleElement.style.textAlign = 'center';
    titleElement.style.marginBottom = '10px';

    const dateElement = document.createElement('p');
    dateElement.textContent = 'Generated on: ' + new Date().toLocaleString();
    dateElement.style.textAlign = 'center';
    dateElement.style.color = '#666';
    dateElement.style.marginBottom = '20px';

    wrapper.appendChild(titleElement);
    wrapper.appendChild(dateElement);
    wrapper.appendChild(clonedElement);

    const options = {
        margin: [10, 10, 10, 10],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
    };

    html2pdf().set(options).from(wrapper).save();
}

function printReport(reportType) {
    let element;

    if (reportType === 'bookings') {
        element = document.getElementById('bookingHistoryTable').closest('.report-section');
    } else if (reportType === 'payments') {
        element = document.getElementById('paymentHistoryTable').closest('.report-section');
    }

    if (!element) {
        alert('Error: Could not find report data');
        return;
    }

    // Create a new window for printing
    const printWindow = window.open('', '', 'height=800,width=900');

    // Get the title
    const title = reportType === 'bookings' ? 'Booking History Report' : 'Payment History Report';

    // Clone and prepare the content
    const clonedElement = element.cloneNode(true);
    const filterGroup = clonedElement.querySelector('.filter-group');
    if (filterGroup) filterGroup.remove();

    // Build the HTML for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>${title}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    background: white;
                }
                h1 {
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
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                th {
                    background: #f9f5f0;
                    border: 1px solid #ddd;
                    padding: 10px;
                    text-align: left;
                    font-weight: 600;
                }
                td {
                    border: 1px solid #ddd;
                    padding: 10px;
                }
                tr:nth-child(even) {
                    background: #fafafa;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 0.85rem;
                    font-weight: 500;
                }
                .status-pending {
                    background: #fef3c7;
                    color: #92400e;
                }
                .status-approved {
                    background: #dcfce7;
                    color: #166534;
                }
                .status-completed {
                    background: #dbeafe;
                    color: #0c4a6e;
                }
                .status-cancelled {
                    background: #fee2e2;
                    color: #991b1b;
                }
                .status-rejected {
                    background: #fee2e2;
                    color: #991b1b;
                }
                .status-paid {
                    background: #dcfce7;
                    color: #166534;
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
            <h1>${title}</h1>
            <div class="print-date">Generated on: ${new Date().toLocaleString()}</div>
            ${clonedElement.innerHTML}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for content to load, then print
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

/**
 * Show report loader
 */
function showReportLoader(message = 'Loading...') {
    let loader = document.getElementById('userReportLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'userReportLoader';
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
 * Hide report loader
 */
function hideReportLoader() {
    const loader = document.getElementById('userReportLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

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

/**
 * Update pagination controls for bookings
 */
function updateBookingsPaginationControls(pagination) {
    const paginationDiv = document.getElementById('bookingPagination');
    if (!paginationDiv) return;

    if (!pagination || !pagination.last_page) {
        paginationDiv.innerHTML = '';
        return;
    }

    const currentPage = pagination.current_page || 1;
    const totalPages = pagination.last_page || 1;

    let html = '<div class="pagination-controls">';
    
    // Previous button
    html += `<button class="pagination-btn" ${currentPage <= 1 ? 'disabled' : ''} onclick="previousBookingsPage()">← Previous</button>`;
    
    // Page numbers - always show all pages even if <= 5
    html += '<div class="page-numbers">';
    const maxVisible = 5;
    let pages = [];

    if (totalPages <= maxVisible) {
        // Show all pages if 5 or less
        pages = Array.from({length: totalPages}, (_, i) => i + 1);
    } else {
        // For more than 5 pages, show smart ellipsis
        pages.push(1);
        let start = Math.max(2, currentPage - 1);
        let end = Math.min(totalPages - 1, currentPage + 1);
        
        if (start > 2) pages.push('...');
        for (let i = start; i <= end; i++) pages.push(i);
        if (end < totalPages - 1) pages.push('...');
        pages.push(totalPages);
    }

    pages.forEach(page => {
        if (page === '...') {
            html += '<span class="page-ellipsis">...</span>';
        } else {
            const isActive = page === currentPage;
            html += `<button class="page-btn ${isActive ? 'active' : ''}" ${isActive ? 'disabled' : ''} onclick="goToBookingsPage(${page})">${page}</button>`;
        }
    });

    html += '</div>';
    
    // Page info
    html += `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
    
    // Next button
    html += `<button class="pagination-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="nextBookingsPage()">Next →</button>`;
    
    html += '</div>';
    paginationDiv.innerHTML = html;
}

/**
 * Update pagination controls for payments
 */
function updatePaymentsPaginationControls(pagination) {
    const paginationDiv = document.getElementById('paymentPagination');
    if (!paginationDiv) return;

    if (!pagination || !pagination.last_page) {
        paginationDiv.innerHTML = '';
        return;
    }

    const currentPage = pagination.current_page || 1;
    const totalPages = pagination.last_page || 1;

    let html = '<div class="pagination-controls">';
    
    // Previous button
    html += `<button class="pagination-btn" ${currentPage <= 1 ? 'disabled' : ''} onclick="previousPaymentsPage()">← Previous</button>`;
    
    // Page numbers - always show all pages even if <= 5
    html += '<div class="page-numbers">';
    const maxVisible = 5;
    let pages = [];

    if (totalPages <= maxVisible) {
        // Show all pages if 5 or less
        pages = Array.from({length: totalPages}, (_, i) => i + 1);
    } else {
        // For more than 5 pages, show smart ellipsis
        pages.push(1);
        let start = Math.max(2, currentPage - 1);
        let end = Math.min(totalPages - 1, currentPage + 1);
        
        if (start > 2) pages.push('...');
        for (let i = start; i <= end; i++) pages.push(i);
        if (end < totalPages - 1) pages.push('...');
        pages.push(totalPages);
    }

    pages.forEach(page => {
        if (page === '...') {
            html += '<span class="page-ellipsis">...</span>';
        } else {
            const isActive = page === currentPage;
            html += `<button class="page-btn ${isActive ? 'active' : ''}" ${isActive ? 'disabled' : ''} onclick="goToPaymentsPage(${page})">${page}</button>`;
        }
    });

    html += '</div>';
    
    // Page info
    html += `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
    
    // Next button
    html += `<button class="pagination-btn" ${currentPage >= totalPages ? 'disabled' : ''} onclick="nextPaymentsPage()">Next →</button>`;
    
    html += '</div>';
    paginationDiv.innerHTML = html;
}

/**
 * Booking pagination navigation
 */
function previousBookingsPage() {
    if (userReportsState.bookings.currentPage > 1) {
        userReportsState.bookings.currentPage--;
        loadReportData();
    }
}

function nextBookingsPage() {
    if (userReportsState.bookings.currentPage < userReportsState.bookings.totalPages) {
        userReportsState.bookings.currentPage++;
        loadReportData();
    }
}

function goToBookingsPage(page) {
    userReportsState.bookings.currentPage = page;
    loadReportData();
}

/**
 * Payment pagination navigation
 */
function previousPaymentsPage() {
    if (userReportsState.payments.currentPage > 1) {
        userReportsState.payments.currentPage--;
        loadReportData();
    }
}

function nextPaymentsPage() {
    if (userReportsState.payments.currentPage < userReportsState.payments.totalPages) {
        userReportsState.payments.currentPage++;
        loadReportData();
    }
}

function goToPaymentsPage(page) {
    userReportsState.payments.currentPage = page;
    loadReportData();
}

/**
 * Feedback submission
 */
function openFeedbackModal(bookingId, referenceNo) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal-overlay" id="feedbackModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Submit Feedback</h3>
                    <button class="modal-close-btn" onclick="closeFeedbackModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="feedback-info">Booking: <strong>${referenceNo}</strong></p>
                    <form id="feedbackForm">
                        <div class="form-group">
                            <label for="rating">Rating:</label>
                            <select id="rating" required class="form-control">
                                <option value="">Select a rating</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Good</option>
                                <option value="3">⭐⭐⭐ Fair</option>
                                <option value="2">⭐⭐ Poor</option>
                                <option value="1">⭐ Very Poor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comments">Comments:</label>
                            <textarea id="comments" placeholder="Share your experience..." rows="5" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeFeedbackModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="submitFeedback(${bookingId})">Submit Feedback</button>
                </div>
            </div>
        </div>
    `;

    // Remove any existing feedback modal
    const existingModal = document.getElementById('feedbackModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Insert modal into DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Add overlay click to close
    const overlay = document.getElementById('feedbackModal');
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeFeedbackModal();
        }
    });
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) {
        modal.remove();
    }
}

function submitFeedback(bookingId) {
    const rating = document.getElementById('rating')?.value;
    const comments = document.getElementById('comments')?.value;

    // Validate rating
    if (!rating || rating === '') {
        alert('Please select a rating');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        alert('Security token missing. Please refresh the page.');
        return;
    }

    // Ensure bookingId is a number
    const numericBookingId = parseInt(bookingId);
    if (isNaN(numericBookingId)) {
        alert('Invalid booking ID.');
        return;
    }

    const url = `/api/bookings/${numericBookingId}/feedback`;
    
    // Show loading state
    const submitBtn = event.target;
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    axios.post(url, {
        rating: parseInt(rating),
        comments: comments || ''
    }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        timeout: 10000
    })
    .then(response => {
        showSuccessModal('Thank you!', 'Your feedback has been submitted successfully.', function() {
            closeFeedbackModal();
            loadReportData();
        });
    })
    .catch(error => {
        console.error('Error submitting feedback:', error);
        console.error('Status:', error.response?.status);
        console.error('Data:', error.response?.data);
        
        let errorMessage = 'Error submitting feedback. Please try again.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        showErrorModal('Error', errorMessage);
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}
