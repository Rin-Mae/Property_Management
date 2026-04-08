// ============================================
// CHART INSTANCE
// ============================================

let bookingsChart = null;

// ============================================
// STATE MANAGEMENT
// ============================================

let reportsState = {
    fromDate: null,
    toDate: null,
    filter: 'all',
    bookings: {
        data: [],
        currentPage: 1,
        totalPages: 1,
        perPage: 5
    }
};

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    updateProfileName();
    initializeDatePickers();
    loadSummaryStatistics();
    loadBookingsChart();
    loadBookingsTable();
});

// ============================================
// LOADER FUNCTIONS
// ============================================

function showReportLoader(message = 'Loading...') {
    let loader = document.getElementById('reportLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'reportLoader';
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

function hideReportLoader() {
    const loader = document.getElementById('reportLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

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
// DATE PICKER INITIALIZATION
// ============================================

function initializeDatePickers() {
    const fromInput = document.getElementById('fromDate');
    const toInput = document.getElementById('toDate');

    // Set default dates (last 4 months)
    const now = new Date();
    const fourMonthsAgo = new Date();
    fourMonthsAgo.setMonth(fourMonthsAgo.getMonth() - 4);

    fromInput.valueAsDate = fourMonthsAgo;
    toInput.valueAsDate = now;

    reportsState.fromDate = formatDate(fourMonthsAgo);
    reportsState.toDate = formatDate(now);

    // Add event listeners
    fromInput.addEventListener('change', function () {
        reportsState.fromDate = formatDate(this.valueAsDate);
        loadBookingsChart();
    });

    toInput.addEventListener('change', function () {
        reportsState.toDate = formatDate(this.valueAsDate);
        loadBookingsChart();
    });
}

// ============================================
// SUMMARY STATISTICS
// ============================================

async function loadSummaryStatistics() {
    try {
        const response = await axios.get('/api/reports/summary');
        const data = response.data;

        document.getElementById('totalBookings').textContent = data.total_bookings;
        document.getElementById('totalRevenue').textContent = '₱' + data.total_revenue;
        document.getElementById('approvedBookings').textContent = data.approved_bookings;
        document.getElementById('pendingPayments').textContent = data.pending_payments;
    } catch (error) {
        console.error('Error loading summary statistics:', error);
    }
}

// ============================================
// BOOKINGS CHART
// ============================================

async function loadBookingsChart() {
    try {
        const params = {
            from: reportsState.fromDate,
            to: reportsState.toDate
        };

        const response = await axios.get('/api/reports/chart', { params });
        const data = response.data;

        renderChart(data.labels, data.data);
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
}

function renderChart(labels, data) {
    const ctx = document.getElementById('bookingsChart').getContext('2d');

    if (bookingsChart) {
        bookingsChart.destroy();
    }

    bookingsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Bookings',
                data: data,
                backgroundColor: [
                    '#FFA500',
                    '#FF9800',
                    '#FF8C00',
                    '#FF7F00',
                    '#FF6347',
                    '#FF5722',
                    '#FF4500',
                    '#FF3300'
                ],
                borderColor: '#FF6347',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: '#FF4500'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 13
                        },
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: '#f0f0f0'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// ============================================
// BOOKINGS TABLE
// ============================================

async function loadBookingsTable(page = 1) {
    try {
        const response = await axios.get('/api/reports/bookings', {
            params: { page: page }
        });
        
        const bookings = response.data.data || [];
        const pagination = response.data.pagination || {};

        reportsState.bookings = {
            data: bookings,
            currentPage: pagination.current_page || page,
            totalPages: pagination.last_page || 1,
            perPage: pagination.per_page || 5
        };

        renderBookingsTable(bookings);
        updateBookingsPaginationControls();
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById('bookingsTableBody');

    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No bookings found</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => {
        // Show Review Feedback button only for completed/checked_out bookings
        let actionCell = '<td></td>'; // Empty by default
        if (booking.status === 'checked_out') {
            const reservationId = booking.reservation_id || booking.id.replace('PMS-', '');
            actionCell = `<td><button class="btn-action btn-review-feedback" onclick="openFeedbackReviewModal(${reservationId}, '${booking.id}')"><i class="fas fa-comment-dots"></i> Review Feedback</button></td>`;
        }

        return `
            <tr>
                <td>${booking.id || booking.reference_no}</td>
                <td>${booking.guest_name}</td>
                <td>${booking.room_name || booking.room_type}</td>
                <td>${booking.check_in || booking.check_in_raw} - ${booking.check_out || booking.check_out_raw}</td>
                <td>₱${parseFloat(booking.total_price).toFixed(2)}</td>
                <td>
                    <span class="status-badge ${getStatusClass(booking.status)}">
                        ${formatStatus(booking.status)}
                    </span>
                </td>
                ${actionCell}
            </tr>
        `;
    }).join('');
}

/**
 * Update pagination controls for bookings
 */
function updateBookingsPaginationControls() {
    const pageInfo = document.getElementById('bookingsPageInfo');
    const prevBtn = document.getElementById('bookingsPrevBtn');
    const nextBtn = document.getElementById('bookingsNextBtn');
    const pagination = document.getElementById('bookingsPagination');
    const pageNumbersContainer = document.getElementById('bookingsPageNumbers');

    // Show pagination if there's data
    if (reportsState.bookings.data.length > 0) {
        if (pagination) {
            pagination.style.display = 'flex';
        }
    } else {
        if (pagination) {
            pagination.style.display = 'none';
        }
        return;
    }

    if (pageInfo) {
        pageInfo.textContent = `Page ${reportsState.bookings.currentPage} of ${reportsState.bookings.totalPages}`;
    }

    if (prevBtn) {
        prevBtn.disabled = reportsState.bookings.currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = reportsState.bookings.currentPage >= reportsState.bookings.totalPages;
    }

    // Generate page numbers - always show all pages even if <= 5
    if (pageNumbersContainer) {
        pageNumbersContainer.innerHTML = '';
        const totalPages = reportsState.bookings.totalPages;
        const currentPage = reportsState.bookings.currentPage;
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
            
            if (start > 2) {
                pages.push('...');
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            if (end < totalPages - 1) {
                pages.push('...');
            }
            
            pages.push(totalPages);
        }

        pages.forEach(page => {
            if (page === '...') {
                const span = document.createElement('span');
                span.className = 'page-ellipsis';
                span.textContent = '...';
                pageNumbersContainer.appendChild(span);
            } else {
                const btn = document.createElement('button');
                btn.className = 'page-btn';
                btn.textContent = page;
                
                if (page === currentPage) {
                    btn.classList.add('active');
                    btn.disabled = true;
                } else {
                    btn.onclick = () => goToBookingsPage(page);
                }
                
                pageNumbersContainer.appendChild(btn);
            }
        });
    }
}

/**
 * Go to specific page for bookings
 */
function goToBookingsPage(page) {
    loadBookingsTable(page);
}

/**
 * Go to previous page for bookings
 */
function previousBookingsPage() {
    if (reportsState.bookings.currentPage > 1) {
        loadBookingsTable(reportsState.bookings.currentPage - 1);
    }
}

/**
 * Go to next page for bookings
 */
function nextBookingsPage() {
    if (reportsState.bookings.currentPage < reportsState.bookings.totalPages) {
        loadBookingsTable(reportsState.bookings.currentPage + 1);
    }
}

// ============================================
// EXPORT AND PRINT
// ============================================

async function exportToPDF() {
    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        let yPosition = 15;

        // Title
        pdf.setFontSize(18);
        pdf.text('Hotel Management System', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;

        pdf.setFontSize(14);
        pdf.text('Booking Reports', pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 8;

        // Date range
        pdf.setFontSize(10);
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;
        pdf.text(`Report Period: ${fromDate} to ${toDate}`, pageWidth / 2, yPosition, { align: 'center' });
        yPosition += 10;

        // Summary cards data
        pdf.setFontSize(12);
        pdf.text('Summary Statistics', 15, yPosition);
        yPosition += 8;

        pdf.setFontSize(10);
        const totalBookings = document.getElementById('totalBookings').textContent;
        const totalRevenue = document.getElementById('totalRevenue').textContent;
        const approvedBookings = document.getElementById('approvedBookings').textContent;
        const pendingPayments = document.getElementById('pendingPayments').textContent;

        const summaryData = [
            `Total Bookings: ${totalBookings}`,
            `Total Revenue: ${totalRevenue}`,
            `Approved Bookings: ${approvedBookings}`,
            `Pending Payments: ${pendingPayments}`
        ];

        summaryData.forEach((text, index) => {
            pdf.text(text, 20, yPosition + (index * 6));
        });
        yPosition += 30;

        // Chart image
        const chartCanvas = document.getElementById('bookingsChart');
        const chartImage = chartCanvas.toDataURL('image/png');
        const chartWidth = pageWidth - 30;
        const chartHeight = (chartWidth * chartCanvas.height) / chartCanvas.width;

        // Check if chart fits on current page
        if (yPosition + chartHeight > pageHeight - 10) {
            pdf.addPage();
            yPosition = 15;
        }

        pdf.text('Bookings per Month', 15, yPosition);
        yPosition += 10;
        pdf.addImage(chartImage, 'PNG', 15, yPosition, chartWidth, chartHeight);
        yPosition += chartHeight + 10;

        // Table data
        if (yPosition > pageHeight - 50) {
            pdf.addPage();
            yPosition = 15;
        }

        pdf.setFontSize(12);
        pdf.text('Booking Details', 15, yPosition);
        yPosition += 10;

        // Get table data
        const tableBody = document.getElementById('bookingsTableBody');
        const rows = tableBody.querySelectorAll('tr');
        
        if (rows.length === 0) {
            pdf.setFontSize(10);
            pdf.text('No bookings found for the selected period', 20, yPosition);
        } else {
            // Table headers
            pdf.setFontSize(9);
            const headers = ['ID', 'Guest', 'Room', 'Dates', 'Amount', 'Status'];
            const columnWidths = [20, 30, 20, 40, 25, 25];
            let xPos = 15;
            
            // Header row
            pdf.setFont(undefined, 'bold');
            headers.forEach((header, index) => {
                pdf.text(header, xPos, yPosition);
                xPos += columnWidths[index];
            });
            yPosition += 8;

            // Data rows
            pdf.setFont(undefined, 'normal');
            rows.forEach((row, rowIndex) => {
                if (yPosition > pageHeight - 15) {
                    pdf.addPage();
                    yPosition = 15;
                }

                const cells = row.querySelectorAll('td');
                xPos = 15;
                cells.forEach((cell, cellIndex) => {
                    const cellText = cell.textContent.trim();
                    pdf.text(cellText, xPos, yPosition, { maxWidth: columnWidths[cellIndex] - 2 });
                    xPos += columnWidths[cellIndex];
                });
                yPosition += 7;
            });
        }

        // Footer
        const totalPages = pdf.internal.pages.length - 1;
        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(8);
            pdf.text(`Page ${i} of ${totalPages}`, pageWidth / 2, pageHeight - 5, { align: 'center' });
            pdf.text(new Date().toLocaleString(), 15, pageHeight - 5);
        }

        // Download PDF
        pdf.save(`hotel-reports-${fromDate}-to-${toDate}.pdf`);
    } catch (error) {
        console.error('Error generating PDF:', error);
        showModalAlert('Failed to generate PDF. Please try again.', 'error');
    }
}

function printReport() {
    window.print();
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function formatDate(date) {
    if (!date) return new Date().toISOString().split('T')[0];
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatStatus(status) {
    const statusMap = {
        'pending': 'Pending',
        'confirmed': 'Approved',
        'checked_in': 'Checked In',
        'checked_out': 'Checked Out',
        'cancelled': 'Rejected'
    };
    return statusMap[status] || status;
}

function getStatusClass(status) {
    const classMap = {
        'confirmed': 'approved',
        'checked_in': 'approved',
        'checked_out': 'approved',
        'pending': 'pending',
        'cancelled': 'rejected'
    };
    return classMap[status] || 'pending';
}

/**
 * Feedback Review Modal Functions
 */
function openFeedbackReviewModal(reservationId, bookingId) {
    // Create the modal
    const modalHtml = `
        <div class="modal-overlay" id="feedbackReviewModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Review Feedback</h3>
                    <button class="modal-close-btn" onclick="closeFeedbackReviewModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="feedback-loader">
                        <div class="spinner"></div>
                        <p>Loading feedback...</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove any existing modal
    const existingModal = document.getElementById('feedbackReviewModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Insert modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Add overlay click to close
    const overlay = document.getElementById('feedbackReviewModal');
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeFeedbackReviewModal();
        }
    });

    // Fetch feedback
    fetchFeedback(reservationId, bookingId);
}

function closeFeedbackReviewModal() {
    const modal = document.getElementById('feedbackReviewModal');
    if (modal) {
        modal.remove();
    }
}

async function fetchFeedback(reservationId, bookingId) {
    try {
        // Ensure reservation ID is numeric
        const numericReservationId = parseInt(reservationId);
        if (isNaN(numericReservationId)) {
            throw new Error('Invalid reservation ID');
        }

        const url = `/api/bookings/${numericReservationId}/feedback`;
        const response = await axios.get(url, {
            timeout: 10000,
            headers: {
                'Accept': 'application/json'
            }
        });

        const feedbackData = response.data.data || [];
        const bookingInfo = response.data.booking || {};

        const modalBody = document.querySelector('#feedbackReviewModal .modal-body');
        
        if (!feedbackData || feedbackData.length === 0) {
            modalBody.innerHTML = `
                <div class="feedback-info">
                    <p><strong>Booking:</strong> ${bookingId}</p>
                    <p><strong>Guest:</strong> ${bookingInfo.guest_name || 'N/A'}</p>
                    <p><strong>Room:</strong> ${bookingInfo.room_name || 'N/A'}</p>
                </div>
                <div class="no-feedback">
                    <i class="fas fa-comment"></i>
                    <p>No feedback submitted for this booking yet.</p>
                </div>
            `;
            return;
        }

        // Display feedback
        let feedbackHTML = `
            <div class="feedback-info">
                <p><strong>Booking:</strong> ${bookingId}</p>
                <p><strong>Guest:</strong> ${bookingInfo.guest_name || 'N/A'}</p>
                <p><strong>Room:</strong> ${bookingInfo.room_name || 'N/A'}</p>
            </div>
        `;

        feedbackData.forEach(feedback => {
            if (!feedback || !feedback.rating) return;
            
            const stars = '⭐'.repeat(parseInt(feedback.rating)) + '☆'.repeat(5 - parseInt(feedback.rating));
            const submittedDate = feedback.created_at ? new Date(feedback.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'Unknown';

            feedbackHTML += `
                <div class="feedback-item">
                    <div class="feedback-header">
                        <div class="feedback-rating">${stars} ${feedback.rating}/5</div>
                        <div class="feedback-date">${submittedDate}</div>
                    </div>
                    <div class="feedback-user"><strong>${feedback.user?.name || 'Anonymous'}</strong></div>
                    <div class="feedback-comments">${feedback.comments && feedback.comments.trim() ? feedback.comments : '<em>No comments provided</em>'}</div>
                </div>
            `;
        });

        modalBody.innerHTML = feedbackHTML;
    } catch (error) {
        console.error('Error fetching feedback:', error);
        const modalBody = document.querySelector('#feedbackReviewModal .modal-body');
        let errorMsg = 'Error loading feedback. Please try again.';
        if (error.response?.data?.message) {
            errorMsg = error.response.data.message;
        }
        modalBody.innerHTML = `
            <div class="feedback-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>${errorMsg}</p>
            </div>
        `;
    }
}
