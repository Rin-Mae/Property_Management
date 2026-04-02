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
    filter: 'all'
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
        document.getElementById('totalRevenue').textContent = '$' + data.total_revenue;
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

async function loadBookingsTable() {
    try {
        const response = await axios.get('/api/bookings');
        const bookings = response.data.data;

        renderBookingsTable(bookings);
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById('bookingsTableBody');

    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">No bookings found</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>${booking.id}</td>
            <td>${booking.guest_name}</td>
            <td>${booking.room_name}</td>
            <td>${booking.check_in} - ${booking.check_out}</td>
            <td>₱${booking.total_price}</td>
            <td>
                <span class="status-badge ${getStatusClass(booking.status)}">
                    ${formatStatus(booking.status)}
                </span>
            </td>
        </tr>
    `).join('');
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
        alert('Failed to generate PDF. Please try again.');
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
