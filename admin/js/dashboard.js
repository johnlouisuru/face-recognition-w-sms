// Global Chart Variables
let attendanceChart, gradeChart;

$(document).ready(function() {
    loadDashboardData();
    setInterval(loadDashboardData, 30000); // Refresh every 30 seconds
});

function loadDashboardData() {
    $.ajax({
        url: 'api/get_dashboard_data.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateSummaryCards(response.summary);
                updateCharts(response.charts);
                updateRecentAttendance(response.recent_attendance);
                updatePredictions(response.predictions);
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load dashboard data:', error);
            console.log('Response:', xhr.responseText);
        }
    });
}

function updateSummaryCards(data) {
    $('#totalStudents').text(data.total_students);
    $('#presentToday').text(data.present_today);
    $('#attendanceRate').text(data.attendance_rate + '%');
}

function updateCharts(charts) {
    // Attendance Trend Chart
    const ctx1 = document.getElementById('attendanceChart').getContext('2d');
    if (attendanceChart) attendanceChart.destroy();
    
    attendanceChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: charts.trend_labels,
            datasets: [{
                label: 'Present Students',
                data: charts.trend_data,
                borderColor: '#36a2eb',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#36a2eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });

    // Grade Distribution Chart
    const ctx2 = document.getElementById('gradeChart').getContext('2d');
    if (gradeChart) gradeChart.destroy();
    
    gradeChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: charts.grade_labels,
            datasets: [{
                data: charts.grade_data,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#C9CBCF',
                    '#4D5360'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' students';
                            return label;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

function updateRecentAttendance(data) {
    let html = '';
    if (data.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No recent attendance records found</td></tr>';
    } else {
        data.forEach(record => {
            html += `
                <tr>
                    <td>${record.timestamp}</td>
                    <td>${record.student_id}</td>
                    <td>${record.student_name}</td>
                    <td>${record.grade} - ${record.section_name}</td>
                    <td>${record.time_in}</td>
                    <td>${record.time_out}</td>
                    <td>${record.duration}</td>
                </tr>
            `;
        });
    }
    $('#recentAttendance').html(html);
}

function updatePredictions(predictions) {
    let html = '';
    if (predictions.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No potential absentees identified for tomorrow</td></tr>';
    } else {
        predictions.forEach(student => {
            let riskClass = student.risk_level === 'High' ? 'danger' : 'warning';
            let riskIcon = student.risk_level === 'High' ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-circle-fill';
            
            html += `
                <tr>
                    <td>${student.student_id}</td>
                    <td>${student.student_name}</td>
                    <td>${student.grade}</td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-${riskClass}" 
                                 role="progressbar" 
                                 style="width: ${student.absence_rate}%"
                                 aria-valuenow="${student.absence_rate}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                ${student.absence_rate}%
                            </div>
                        </div>
                    </td>
                    <td>${student.last_absent}</td>
                    <td>
                        ${student.parent_contact ? 
                            `<a href="tel:${student.parent_contact}" class="text-decoration-none">
                                <i class="bi bi-telephone-fill text-primary"></i> ${student.parent_contact}
                            </a>` : 
                            'N/A'}
                    </td>
                    <td>
                        <span class="badge bg-${riskClass} risk-badge">
                            <i class="bi ${riskIcon}"></i> ${student.risk_level} Risk
                        </span>
                    </td>
                </tr>
            `;
        });
    }
    $('#absenteeTable').html(html);
}

function refreshPredictions() {
    const refreshBtn = $('.btn-light').find('i');
    refreshBtn.addClass('bi-arrow-clockwise-animate');
    
    $.ajax({
        url: 'api/get_predictions.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Refresh response:', response); // Debug log
            
            if (response.success) {
                updatePredictions(response.predictions);
                // Show success toast/notification
                showNotification('Predictions refreshed successfully!', 'success');
                console.log('Predictions refreshed at ' + response.last_updated);
            } else {
                showNotification('Failed to refresh predictions: ' + response.message, 'danger');
            }
            refreshBtn.removeClass('bi-arrow-clockwise-animate');
        },
        error: function(xhr, status, error) {
            refreshBtn.removeClass('bi-arrow-clockwise-animate');
            console.error('Failed to refresh predictions:', error);
            console.log('Full response:', xhr.responseText);
            
            // Try to parse the response even if it's malformed
            try {
                // Clean the response - remove everything before the first {
                let cleanResponse = xhr.responseText;
                const firstBrace = cleanResponse.indexOf('{');
                if (firstBrace > 0) {
                    cleanResponse = cleanResponse.substring(firstBrace);
                }
                
                const parsedResponse = JSON.parse(cleanResponse);
                if (parsedResponse.success && parsedResponse.predictions) {
                    updatePredictions(parsedResponse.predictions);
                    showNotification('Predictions refreshed!', 'success');
                    return;
                }
            } catch (e) {
                console.log('Could not parse malformed response:', e);
            }
            
            showNotification('Failed to refresh predictions. Please try again.', 'danger');
        }
    });
}

// Add notification function
function showNotification(message, type) {
    // Remove any existing notification
    $('.notification-toast').remove();
    
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    
    const notification = `
        <div class="notification-toast position-fixed top-0 end-0 m-4" style="z-index: 1050;">
            <div class="alert ${alertClass} alert-dismissible fade show shadow" role="alert">
                <i class="bi ${icon} me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        $('.notification-toast').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// Add CSS animation for refresh icon
$(document).ready(function() {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .bi-arrow-clockwise-animate {
                animation: spin 1s linear infinite;
            }
            .notification-toast {
                min-width: 300px;
            }
        `)
        .appendTo('head');
});