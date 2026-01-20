let refreshInterval = null;
let lastUpdateTime = null;
let currentFilters = {
    grade: '',
    section: '',
    status: '',
    time: 'all'
};

$(document).ready(function() {
    // Initialize
    loadMonitoringData();
    setupAutoRefresh();
    
    // Setup event listeners
    $('#autoRefreshToggle').change(function() {
        if ($(this).is(':checked')) {
            setupAutoRefresh();
        } else {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    });
    
    $('#refreshInterval').change(function() {
        if ($('#autoRefreshToggle').is(':checked')) {
            setupAutoRefresh();
        }
    });
});

function setupAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    const interval = parseInt($('#refreshInterval').val());
    refreshInterval = setInterval(loadMonitoringData, interval);
    
    // Add pulse animation to refresh button
    $('.refresh-btn i').addClass('pulse');
}

function loadMonitoringData() {
    // Update current filters
    currentFilters = {
        grade: $('#filterGrade').val(),
        section: $('#filterSection').val(),
        status: $('#filterStatus').val(),
        time: $('#filterTime').val()
    };
    
    // Show loading state
    $('#attendanceGrid').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading attendance data...</p>
        </div>
    `);
    
    // Load data via AJAX
    $.ajax({
        url: 'api/get_monitoring_data.php',
        method: 'GET',
        data: currentFilters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateMonitoringDisplay(response);
                playNotificationSound();
            } else {
                showError('Failed to load monitoring data');
            }
        },
        error: function() {
            showError('Failed to connect to server');
        }
    });
}

function updateMonitoringDisplay(data) {
    // Update last updated time
    lastUpdateTime = new Date();
    $('#lastUpdatedTime').text(lastUpdateTime.toLocaleTimeString());
    
    // Update statistics
    $('#totalStudents').text(data.stats.total_students);
    $('#presentCount').text(data.stats.present_count);
    $('#lateCount').text(data.stats.late_count);
    $('#absentCount').text(data.stats.absent_count);
    $('#attendanceCount').text(`${data.stats.total_students} students`);
    
    // Update attendance grid
    updateAttendanceGrid(data.students);
    
    // Update recent activity
    updateRecentActivity(data.recent_activity);
    
    // Update section summary
    updateSectionSummary(data.section_summary);
}

function updateAttendanceGrid(students) {
    let html = '';
    
    if (students.length === 0) {
        html = `
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h4>No students found</h4>
                <p>Try changing your filters to see more results.</p>
            </div>
        `;
    } else {
        // Group students by section
        const groupedBySection = {};
        
        students.forEach(student => {
            const section = student.section_name || 'No Section';
            if (!groupedBySection[section]) {
                groupedBySection[section] = [];
            }
            groupedBySection[section].push(student);
        });
        
        // Create HTML for each section
        for (const [section, sectionStudents] of Object.entries(groupedBySection)) {
            html += `
                <div class="section-header mb-3">
                    <h6 class="mb-0">
                        <i class="bi bi-collection"></i> ${section}
                        <span class="badge bg-light text-dark">${sectionStudents.length} students</span>
                    </h6>
                </div>
                <div class="row mb-4">
            `;
            
            sectionStudents.forEach((student, index) => {
                // Determine status and styling
                let statusClass = '';
                let statusText = '';
                let timeInBadge = '';
                let timeOutBadge = '';
                
                if (student.time_in) {
                    const timeIn = new Date(student.time_in);
                    const isLate = timeIn.getHours() > 8 || (timeIn.getHours() === 8 && timeIn.getMinutes() > 0);
                    
                    if (isLate) {
                        statusClass = 'status-late';
                        statusText = 'Late';
                    } else {
                        statusClass = 'status-present';
                        statusText = 'Present';
                    }
                    
                    timeInBadge = `
                        <div class="time-badge mt-1">
                            <i class="bi bi-arrow-right-circle text-success"></i>
                            IN: ${timeIn.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                    `;
                } else {
                    statusClass = 'status-absent';
                    statusText = 'Absent';
                }
                
                if (student.time_out) {
                    const timeOut = new Date(student.time_out);
                    timeOutBadge = `
                        <div class="time-badge mt-1">
                            <i class="bi bi-arrow-left-circle text-danger"></i>
                            OUT: ${timeOut.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                    `;
                }
                
                // Profile picture or placeholder
                const avatar = student.profile_picture 
                    ? `<img src="${student.profile_picture}" class="student-avatar" alt="${student.student_name}">`
                    : `<div class="student-avatar bg-secondary d-flex align-items-center justify-content-center">
                         <i class="bi bi-person-fill text-white fs-4"></i>
                       </div>`;
                
                html += `
                    <div class="col-md-4 col-lg-3 mb-3 new-entry">
                        <div class="student-row ${statusText.toLowerCase()} rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    ${avatar}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${student.student_name}</h6>
                                    <div class="small text-muted mb-2">
                                        <i class="bi bi-person-badge"></i> ${student.student_id}
                                        <br>
                                        <i class="bi bi-mortarboard"></i> Grade ${student.grade}
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="${statusClass} status-badge">${statusText}</span>
                                        ${timeInBadge}
                                        ${timeOutBadge}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
        }
    }
    
    $('#attendanceGrid').html(html);
    
    // Remove new-entry class after animation
    setTimeout(() => {
        $('.new-entry').removeClass('new-entry');
    }, 1000);
}

function updateRecentActivity(activities) {
    let html = '';
    
    if (activities.length === 0) {
        html = `
            <div class="text-center text-muted py-3">
                <i class="bi bi-chat-square"></i>
                <p>No recent activity</p>
            </div>
        `;
    } else {
        activities.forEach(activity => {
            const time = new Date(activity.timestamp);
            const isLate = activity.status === 'Late';
            const activityClass = isLate ? 'activity-late' : 'activity-present';
            
            html += `
                <div class="activity-item ${activityClass}">
                    <div class="d-flex justify-content-between">
                        <strong>${activity.student_name}</strong>
                        <small class="text-muted">${time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                    </div>
                    <div class="small">
                        <span class="badge ${isLate ? 'bg-warning' : 'bg-success'}">${activity.status}</span>
                        ${activity.time_out ? 'Time Out' : 'Time In'}
                        <span class="badge bg-info">${activity.section_name}</span>
                    </div>
                </div>
            `;
        });
    }
    
    $('#recentActivity').html(html);
    
    // Auto-scroll to top
    $('#recentActivity').scrollTop(0);
}

function updateSectionSummary(summary) {
    let html = '';
    
    if (summary.length === 0) {
        html = '<p class="text-muted text-center">No section data available</p>';
    } else {
        summary.forEach(section => {
            const presentRate = section.total_students > 0 
                ? Math.round((section.present_count / section.total_students) * 100) 
                : 0;
            
            html += `
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span><strong>${section.section_name}</strong></span>
                        <span>${presentRate}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: ${presentRate}%">
                        </div>
                    </div>
                    <div class="small text-muted mt-1">
                        ${section.present_count} present / ${section.total_students} total
                    </div>
                </div>
            `;
        });
    }
    
    $('#sectionSummary').html(html);
}

function applyFilters() {
    loadMonitoringData();
}

function playNotificationSound() {
    // Only play sound if we have new data and auto-refresh is on
    if ($('#autoRefreshToggle').is(':checked') && lastUpdateTime) {
        const now = new Date();
        const secondsSinceLastUpdate = (now - lastUpdateTime) / 1000;
        
        // Play sound if last update was more than 5 seconds ago
        if (secondsSinceLastUpdate > 5) {
            const sound = document.getElementById('notificationSound');
            sound.currentTime = 0;
            sound.play().catch(e => console.log('Audio play failed:', e));
        }
    }
}

function showError(message) {
    $('#attendanceGrid').html(`
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> ${message}
        </div>
    `);
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl+R or F5 to refresh
    if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
        e.preventDefault();
        loadMonitoringData();
    }
    
    // Space to toggle auto-refresh
    if (e.key === ' ' && !$(e.target).is('input, textarea, select')) {
        e.preventDefault();
        $('#autoRefreshToggle').click();
    }
});