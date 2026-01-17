@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Calendar</h2>
            <p class="text-muted mb-0">View and manage all events and appointments</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Month Navigation -->
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="prevMonth" title="Previous Month">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <h5 class="mb-0 fw-bold" id="currentMonthDisplay">{{ \Carbon\Carbon::parse($currentMonth)->format('F Y') }}</h5>
                <button class="btn btn-outline-secondary btn-sm" id="nextMonth" title="Next Month">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <button class="btn btn-primary" id="addScheduleBtn" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle me-2"></i>Add Schedule
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-event fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="totalEvents">0</h4>
                            <small>Total Events</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-person-check fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="totalAppointments">0</h4>
                            <small>Appointments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-week fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="thisWeekEvents">0</h4>
                            <small>This Week</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="badge bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                            <small class="text-muted">Upcoming Events</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="badge bg-secondary rounded-circle" style="width: 12px; height: 12px;"></div>
                            <small class="text-muted">Completed Events</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="badge bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                            <small class="text-muted">Appointments</small>
                        </div>
                        <div class="ms-auto">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Click on any event to view details
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                    </div>

                    <!-- Calendar Body -->
                    <div class="calendar-body" id="calendarBody">
                        <!-- Calendar days will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">
                        <i class="bi bi-calendar-event me-2"></i><span id="eventModalTitleText">Event Details</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: 1px solid #dee2e6;
}

.calendar-day-header {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: white;
    border-right: 1px solid rgba(255,255,255,0.2);
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    min-height: 600px;
    border: 1px solid #dee2e6;
}

.calendar-week {
    display: contents;
}

.calendar-day {
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    min-height: 120px;
    padding: 0.75rem;
    position: relative;
    background-color: #fff;
    transition: all 0.2s ease;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day:last-child {
    border-right: none;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.other-month .calendar-day-number {
    color: #adb5bd !important;
    font-weight: normal;
}

.calendar-day.today {
    background-color: #e3f2fd;
    font-weight: bold;
    border: 2px solid #2196f3;
}

.calendar-day-number {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
    text-align: center;
    padding: 0.25rem;
    border-radius: 4px;
}

.calendar-day.today .calendar-day-number {
    background-color: #2196f3;
    color: white;
}

.calendar-events {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.calendar-event {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: white;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    text-align: left;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.calendar-event.schedule {
    color: black;
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}

.calendar-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.calendar-event.event {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.calendar-event.appointment {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

/* Event status styling */
.calendar-event.done {
    opacity: 0.7;
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
}

.calendar-event.done:hover {
    opacity: 0.8;
}



.event-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.25rem;
}

.event-dot.event {
    background-color: #0d6efd;
}

.event-dot.appointment {
    background-color: #198754;
}



.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: 1px solid #dee2e6;
}

.event-detail-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.event-detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.event-detail-icon {
    font-size: 1.25rem;
    color: #667eea;
    min-width: 25px;
    text-align: center;
}

.event-detail-content h6 {
    margin-bottom: 0.25rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.event-detail-content p {
    margin-bottom: 0;
    color: #495057;
    font-size: 0.9rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .calendar-body {
        min-height: 400px;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 0.5rem;
    }
    
    .calendar-event {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .event-detail-item {
        padding: 0.25rem 0;
        margin-bottom: 0.75rem;
    }
    
    .event-detail-icon {
        font-size: 1rem;
        min-width: 20px;
    }
}

/* Loading animation */
.calendar-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px;
    grid-column: 1 / -1;
}

.calendar-loading .spinner-border {
    color: #667eea;
}

/* Empty state */
.calendar-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.calendar-empty i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.calendar-empty h5 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.calendar-empty p {
    color: #6c757d;
    margin-bottom: 0;
}

/* Modal centering */
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

@media (min-width: 576px) {
    .modal-dialog-centered {
        min-height: calc(100% - 3.5rem);
    }
}
</style>

<script>
let currentMonth = '{{ $currentMonth ?? now()->format("Y-m") }}';
let groupedItems = @json($groupedItems);
let serverToday = '{{ now()->format("Y-m-d") }}'; // Get server's current date

// Function to format time from 24-hour to 12-hour format
function formatTime(timeString) {
    if (!timeString) return '';
    
    try {
        // Handle time strings like "08:00" or "17:00"
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const minute = parseInt(minutes);
        
        if (isNaN(hour) || isNaN(minute)) {
            return timeString;
        }
        
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour === 0 ? 12 : (hour > 12 ? hour - 12 : hour);
        const displayMinute = minute.toString().padStart(2, '0');
        
        return `${displayHour}:${displayMinute} ${period}`;
    } catch (e) {
        return timeString; // Return original if parsing fails
    }
}

// Function to format date in a user-friendly format
function formatDate(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString;
        }
        
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        
        return date.toLocaleDateString('en-US', options);
    } catch (e) {
        return dateString; // Return original if parsing fails
    }
}

// Function to format time from old format like "08:00 - 17:00"
function formatTimeFromString(timeString) {
    if (!timeString) return '';
    
    try {
        // Handle strings like "08:00 - 17:00"
        if (timeString.includes(' - ')) {
            const [startTime, endTime] = timeString.split(' - ');
            const formattedStart = formatTime(startTime);
            const formattedEnd = formatTime(endTime);
            return `${formattedStart} - ${formattedEnd}`;
        } else {
            // Single time
            return formatTime(timeString);
        }
    } catch (e) {
        return timeString; // Return original if parsing fails
    }
}

function updateStats() {
    let totalEvents = 0;
    let totalAppointments = 0;
    let thisWeekEvents = 0;
    
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    
    for (const date in groupedItems) {
        const events = groupedItems[date];
        const eventDate = new Date(date);
        
        events.forEach(event => {
            if (event.type === 'event') totalEvents++;
            else if (event.type === 'appointment') totalAppointments++;
            
            if (eventDate >= startOfWeek && eventDate <= endOfWeek) {
                thisWeekEvents++;
            }
        });
    }
    
    document.getElementById('totalEvents').textContent = totalEvents;
    document.getElementById('totalAppointments').textContent = totalAppointments;
    document.getElementById('thisWeekEvents').textContent = thisWeekEvents;
}

function generateCalendar() {
    console.log('Generating calendar for month:', currentMonth);
    console.log('Available groupedItems:', groupedItems);
    
    // Validate currentMonth
    if (!currentMonth || currentMonth === 'null' || currentMonth === 'undefined') {
        currentMonth = new Date().toISOString().slice(0, 7);
        console.log('Fixed currentMonth to:', currentMonth);
    }
    
    const startDate = new Date(currentMonth + '-01');
    const lastDay = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);
    
    console.log('Start date:', startDate);
    console.log('Last day:', lastDay);
    
    // Get the day of week for the first day (0 = Sunday, 1 = Monday, etc.)
    const firstDayOfWeek = startDate.getDay();
    
    // Calculate total weeks needed based on the month
    const totalDays = lastDay.getDate();
    const weeksNeeded = Math.ceil((firstDayOfWeek + totalDays) / 7);
    const weeks = Math.max(weeksNeeded, 5); // Minimum 5 weeks for consistent layout
    
    let calendarHTML = '';
    let allGeneratedDates = [];
    
    // Generate all dates for the current month first
    for (let day = 1; day <= totalDays; day++) {
        const date = new Date(startDate.getFullYear(), startDate.getMonth(), day);
        // Use local date calculation instead of UTC to avoid timezone issues
        const dateString = date.getFullYear() + '-' + 
            String(date.getMonth() + 1).padStart(2, '0') + '-' + 
            String(date.getDate()).padStart(2, '0');
        allGeneratedDates.push(dateString);
    }
    
    // Create a map of valid dates for quick lookup
    const validDates = new Set(allGeneratedDates);
    
    // Calculate the first day of the calendar (may be from previous month)
    const firstCalendarDay = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
    firstCalendarDay.setDate(1 - firstDayOfWeek);
    
    for (let week = 0; week < weeks; week++) {
        for (let day = 0; day < 7; day++) {
            // Calculate the actual date for this cell
            const cellDate = new Date(firstCalendarDay);
            cellDate.setDate(firstCalendarDay.getDate() + week * 7 + day);
            
            let dayClass = 'calendar-day';
            let dayContent = '';
            
            // Check if this cell is in the current month
            const isCurrentMonth = cellDate.getMonth() === startDate.getMonth();
            // Use local date calculation instead of UTC to avoid timezone issues
            const dateString = cellDate.getFullYear() + '-' + 
                String(cellDate.getMonth() + 1).padStart(2, '0') + '-' + 
                String(cellDate.getDate()).padStart(2, '0');
            
            if (!isCurrentMonth) {
                // Previous or next month - show greyed out
                dayClass += ' other-month';
                dayContent = `<div class="calendar-day-number text-muted">${cellDate.getDate()}</div>`;
            } else {
                // Current month
                // Use server's date instead of client's date to avoid timezone issues
                const isToday = dateString === serverToday;
                
                if (isToday) {
                    dayClass += ' today';
                }
                
                dayContent = `
                    <div class="calendar-day-number">${cellDate.getDate()}</div>
                    <div class="calendar-events">
                        ${generateDayEvents(dateString)}
                    </div>
                `;
            }
            
            calendarHTML += `<div class="${dayClass}">${dayContent}</div>`;
        }
    }
    
    console.log('Generated calendar HTML:', calendarHTML);
    document.getElementById('calendarBody').innerHTML = calendarHTML;
    updateStats();
}

function generateDayEvents(dateString) {
    console.log('Generating events for date:', dateString);
    console.log('Events for this date:', groupedItems[dateString]);
    
    if (!groupedItems[dateString]) {
        return '';
    }
    
    return groupedItems[dateString].map(item => {
        let eventClass = `calendar-event ${item.type}`;
        let title = item.title.length > 20 ? item.title.substring(0, 20) + '...' : item.title;
        
        // Add strikethrough and special styling for "Done" events
        if (item.status === 'Done') {
            eventClass += ' done';
            title = `<span style="text-decoration: line-through; opacity: 0.7;">${title}</span>`;
        }
        
        // Escape special characters for JavaScript
        const escapedTitle = item.title.replace(/'/g, "\\'").replace(/"/g, '\\"');
        const escapedId = item.id.replace(/'/g, "\\'").replace(/"/g, '\\"');
        
        const onclickHandler = item.type === 'event'
            ? `window.location.href='/events/${escapedId}'`
            : `showEventDetails('${escapedId}', '${item.type}')`;
        
        return `<div class="${eventClass}" onclick="${onclickHandler}" title="${escapedTitle}">${title}</div>`;
    }).join('');
}

function showEventDetails(eventId, eventType) {
    // Redirect to event details page for events
    if (eventType === 'event') {
        window.location.href = `/events/${eventId}`;
        return;
    }

    const event = findEvent(eventId, eventType);
    if (!event) return;
    
    let modalContent = '';
    const modalTitleText = document.getElementById('eventModalTitleText');
    if (modalTitleText) {
        let titleText = 'Event Details';
        if (eventType === 'appointment') {
            titleText = 'Appointment Details';
        } else if (eventType === 'schedule') {
            titleText = 'Schedule Details';
        }
        modalTitleText.textContent = titleText;
    }
    
    if (eventType === 'event') {
        // Add strikethrough for "Done" events in modal
        let eventTitle = event.title;
        if (event.status === 'Done') {
            eventTitle = `<span style="text-decoration: line-through; opacity: 0.7;">${event.title}</span>`;
        }
        
        modalContent = `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Event Name</h6>
                    <p><strong>${eventTitle}</strong></p>
                </div>
            </div>
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-calendar-date"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Date & Time</h6>
                    <p>${formatDate(event.date)}${event.start_time && event.end_time ? ', ' + formatTime(event.start_time) + ' - ' + formatTime(event.end_time) : event.time ? ', ' + formatTimeFromString(event.time) : ''}</p>
                </div>
            </div>
            ${event.location ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Venue</h6>
                    <p>${event.location}</p>
                </div>
            </div>
            ` : ''}
            ${event.in_charge ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="event-detail-content">
                    <h6>In Charge</h6>
                    <p>${event.in_charge}</p>
                </div>
            </div>
            ` : ''}
            ${event.status ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Status</h6>
                    <p>${event.status}</p>
                </div>
            </div>
            ` : ''}
            ${event.description ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Description</h6>
                    <p>${event.description}</p>
                </div>
            </div>
            ` : ''}
            ${event.isOpenToAll ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-globe"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Open to All Barangays</h6>
                    <p>Yes</p>
                </div>
            </div>
            ` : ''}
            ${event.targetAttendees ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Target Attendees</h6>
                    <p>${event.targetAttendees}</p>
                </div>
            </div>
            ` : ''}
        `;
    } else if (eventType === 'appointment') {
        modalContent = `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Appointment</h6>
                    <p><strong>${event.title}</strong></p>
                </div>
            </div>
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Time</h6>
                    <p>${event.start_time && event.end_time ? formatTime(event.start_time) + ' - ' + formatTime(event.end_time) : formatTimeFromString(event.time)}</p>
                </div>
            </div>
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-briefcase"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Service</h6>
                    <p>${event.description}</p>
                </div>
            </div>
            ${event.patient && event.patient.age ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-person"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Patient Details</h6>
                    <p>Age: ${event.patient.age}, Gender: ${event.patient.gender}</p>
                </div>
            </div>
            ` : ''}
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-chat-text"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Notes</h6>
                    <p>${event.notes ? event.notes : '-'}</p>
                </div>
            </div>
        `;
    } else if (eventType === 'schedule') {
        modalContent = `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Personnel</h6>
                    <p><strong>${event.title}</strong></p>
                </div>
            </div>
            ${event.description ? `
            <div class="event-detail-item">
                <div class="event-detail-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="event-detail-content">
                    <h6>Schedule Details</h6>
                    <p>${event.description}</p>
                </div>
            </div>
            ` : ''}
        `;
    }
    
    document.getElementById('eventModalBody').innerHTML = modalContent;
    new bootstrap.Modal(document.getElementById('eventModal')).show();
}

function findEvent(eventId, eventType) {
    for (const date in groupedItems) {
        const events = groupedItems[date];
        const event = events.find(e => e.id === eventId && e.type === eventType);
        if (event) return event;
    }
    return null;
}

function updateMonthDisplay() {
    const date = new Date(currentMonth + '-01');
    document.getElementById('currentMonthDisplay').textContent = date.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
    });
}



// Function to load month data via AJAX
function loadMonthData(month) {
    console.log('Loading data for month:', month);
    
    // Show loading state
    document.getElementById('calendarBody').innerHTML = '<div class="calendar-loading"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    fetch(`/calendars/data?month=${month}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received calendar data:', data);
            if (data.error) {
                console.error('Server error:', data.error);
                throw new Error(data.error);
            }
            groupedItems = data.groupedItems || data; // Handle both response formats
            currentMonth = month;
            updateMonthDisplay();
            generateCalendar();
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            // Fallback to current month if AJAX fails
            currentMonth = month;
            updateMonthDisplay();
            generateCalendar();
        });
}

// Event Listeners
const prevMonthBtn = document.getElementById('prevMonth');
if (prevMonthBtn) {
    prevMonthBtn.addEventListener('click', () => {
        console.log('Previous month clicked');
        const date = new Date(currentMonth + '-01');
        date.setMonth(date.getMonth() - 1);
        const newMonth = date.toISOString().slice(0, 7);
        console.log('New current month:', newMonth);
        loadMonthData(newMonth);
    });
}

const nextMonthBtn = document.getElementById('nextMonth');
if (nextMonthBtn) {
    nextMonthBtn.addEventListener('click', () => {
        console.log('Next month clicked');
        const date = new Date(currentMonth + '-01');
        date.setMonth(date.getMonth() + 1);
        const newMonth = date.toISOString().slice(0, 7);
        console.log('New current month:', newMonth);
        loadMonthData(newMonth);
    });
}

// Check if todayBtn exists before adding event listener
const todayBtn = document.getElementById('todayBtn');
if (todayBtn) {
    todayBtn.addEventListener('click', () => {
        console.log('Today button clicked');
        // Use server's date to avoid timezone issues
        const todayMonth = serverToday.substring(0, 7); // Get YYYY-MM from server date
        console.log('New current month:', todayMonth);
        loadMonthData(todayMonth);
    });
}

// Schedule Form Functions
function formatTime12Hour(time24Hour) {
    if (!time24Hour) return '';
    
    try {
        const [hours, minutes] = time24Hour.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour === 0 ? 12 : (hour > 12 ? hour - 12 : hour);
        return `${displayHour.toString().padStart(2, '0')}:${minutes} ${ampm}`;
    } catch (e) {
        console.error('Error converting time:', time24Hour, e);
        return time24Hour;
    }
}

function addTimeSlot(day) {
    const container = document.getElementById(`timeSlots_${day}`);
    const timeGroup = document.createElement('div');
    timeGroup.className = 'time-input-group mb-2';
    timeGroup.innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <input type="time" class="form-control start-time" data-day="${day}" placeholder="Start Time" onchange="updateFormattedTime(this)">
            </div>
            <div class="col-md-5">
                <input type="time" class="form-control end-time" data-day="${day}" placeholder="End Time" onchange="updateFormattedTime(this)">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimeSlot(this)">
                    <i class="bi bi-dash"></i>
                </button>
            </div>
        </div>
        <input type="hidden" name="schedule[${day}][]" class="formatted-time" value="">
    `;
    container.appendChild(timeGroup);
}

function removeTimeSlot(button) {
    // Find the time-input-group container and remove it
    const timeGroup = button.closest('.time-input-group');
    const container = timeGroup.closest('.time-slots-container');
    
    // Don't remove if it's the last time slot
    if (timeGroup && container.querySelectorAll('.time-input-group').length > 1) {
        timeGroup.remove();
    }
}

function updateFormattedTime(input) {
    const timeGroup = input.closest('.time-input-group');
    const startInput = timeGroup.querySelector('.start-time');
    const endInput = timeGroup.querySelector('.end-time');
    const formattedInput = timeGroup.querySelector('.formatted-time');
    
    const startTime = startInput.value;
    const endTime = endInput.value;
    
    if (startTime && endTime) {
        const formattedStart = formatTime12Hour(startTime);
        const formattedEnd = formatTime12Hour(endTime);
        const formattedSlot = `${formattedStart}-${formattedEnd}`;
        formattedInput.value = formattedSlot;
    } else {
        formattedInput.value = '';
    }
}

function setDefaultWeekDates() {
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
    
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6); // Sunday
    
    // Format dates for input fields
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };
    
    // Set the date inputs
    const weekStartInput = document.getElementById('weekStart');
    const weekEndInput = document.getElementById('weekEnd');
    
    if (weekStartInput && weekEndInput) {
        weekStartInput.value = formatDate(startOfWeek);
        weekEndInput.value = formatDate(endOfWeek);
    }
}

function initializeScheduleForm() {
    const personnelSearchInput = document.getElementById('personnelSearch');
    const personnelOptions = document.getElementById('personnelOptions');
    const personnelIdInput = document.getElementById('personnelId');
    const personnelNameInput = document.getElementById('personnelName');
    const personnelTypeInput = document.getElementById('personnelType');
    const personnelDesignationInput = document.getElementById('personnelDesignation');
    const capitalize = (value) => {
        if (!value || typeof value !== 'string') return '';
        const trimmed = value.trim();
        if (!trimmed) return '';
        return trimmed.charAt(0).toUpperCase() + trimmed.slice(1);
    };

    const getPersonnelName = (person) => {
        if (!person) return '';
        return (
            person.full_name ||
            person.fullName ||
            person.name ||
            [person.first_name, person.last_name].filter(Boolean).join(' ').trim() ||
            [person.firstName, person.lastName].filter(Boolean).join(' ').trim() ||
            person.email ||
            ''
        ).trim();
    };

    const normalizePersonnel = (person, defaultType) => {
        const name = getPersonnelName(person);
        const resolvedDesignation = person?.designation || person?.role || (defaultType === 'doctor' ? 'Doctor' : 'Midwife');
        return {
            id: person?.id || '',
            name: name || 'Unknown',
            type: defaultType,
            designation: capitalize(resolvedDesignation)
        };
    };

    const allPersonnel = [
        ...(availablePersonnel || []).map(person => normalizePersonnel(person, 'midwife')),
        ...(assignedDoctors || []).map(person => normalizePersonnel(person, 'doctor'))
    ].filter(person => person.id && person.name);

    const populatePersonnelOptions = () => {
        if (!personnelOptions) return;
        personnelOptions.innerHTML = '';
        allPersonnel.forEach((person, index) => {
            const option = document.createElement('option');
            option.value = person.name;
            option.textContent = `${person.name} (${person.designation})`;
            option.dataset.personIndex = index.toString();
            personnelOptions.appendChild(option);
        });
    };

    const findPersonnelByValue = (value) => {
        if (!value || !personnelOptions) return null;
        const trimmed = value.trim().toLowerCase();
        const matchingOption = Array.from(personnelOptions.options || []).find(
            option => option.value.trim().toLowerCase() === trimmed
        );
        if (matchingOption && matchingOption.dataset.personIndex) {
            const idx = parseInt(matchingOption.dataset.personIndex, 10);
            if (!Number.isNaN(idx)) {
                return allPersonnel[idx];
            }
        }
        return allPersonnel.find(person => person.name.trim().toLowerCase() === trimmed) || null;
    };

    const setPersonnelFields = (person) => {
        if (!person) {
            personnelIdInput && (personnelIdInput.value = '');
            personnelNameInput && (personnelNameInput.value = '');
            personnelTypeInput && (personnelTypeInput.value = '');
            personnelDesignationInput && (personnelDesignationInput.value = '');
            if (personnelSearchInput) {
                if (personnelSearchInput.value.trim() === '') {
                    personnelSearchInput.classList.remove('is-invalid');
                } else {
                    personnelSearchInput.classList.add('is-invalid');
                }
            }
            return;
        }

        if (personnelIdInput) personnelIdInput.value = person.id;
        if (personnelNameInput) personnelNameInput.value = person.name;
        if (personnelTypeInput) personnelTypeInput.value = person.type;
        if (personnelDesignationInput) personnelDesignationInput.value = person.designation;
        if (personnelSearchInput) {
            personnelSearchInput.classList.remove('is-invalid');
        }
    };

    if (personnelSearchInput && personnelOptions) {
        populatePersonnelOptions();

        const handlePersonnelInput = () => {
            const selectedPersonnel = findPersonnelByValue(personnelSearchInput.value);
            setPersonnelFields(selectedPersonnel);
        };

        personnelSearchInput.addEventListener('input', handlePersonnelInput);
        personnelSearchInput.addEventListener('change', handlePersonnelInput);
        personnelSearchInput.addEventListener('blur', handlePersonnelInput);
    }
    
    // Handle form submission
    const scheduleForm = document.querySelector('#addScheduleModal form');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', function(e) {
            if (personnelSearchInput && (!personnelIdInput.value || !personnelTypeInput.value)) {
                e.preventDefault();
                personnelSearchInput.classList.add('is-invalid');
                personnelSearchInput.focus();
                return;
            }

            // Update all formatted times before submission
            const timeGroups = document.querySelectorAll('.time-input-group');
            timeGroups.forEach(group => {
                const startInput = group.querySelector('.start-time');
                const endInput = group.querySelector('.end-time');
                const formattedInput = group.querySelector('.formatted-time');
                
                if (startInput && endInput && formattedInput) {
                    const startTime = startInput.value;
                    const endTime = endInput.value;
                    
                    if (startTime && endTime) {
                        const formattedStart = formatTime12Hour(startTime);
                        const formattedEnd = formatTime12Hour(endTime);
                        const formattedSlot = `${formattedStart}-${formattedEnd}`;
                        formattedInput.value = formattedSlot;
                    }
                }
            });
        });
    }
}

// Available personnel data from server
const availablePersonnel = @json($availableMidwives);
const assignedDoctors = @json($assignedDoctors);

// Debug personnel data
console.log('Available personnel (midwives):', availablePersonnel);
console.log('Assigned doctors:', assignedDoctors);



// Initialize calendar
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initial groupedItems:', groupedItems);
    console.log('Current month:', currentMonth);
    console.log('Server today:', serverToday);
    console.log('Calendar body element:', document.getElementById('calendarBody'));
    generateCalendar();
    updateMonthDisplay();
    updateStats();
    
    // Set default week dates for schedule form
    setDefaultWeekDates();
    
    // Initialize schedule form functionality
    initializeScheduleForm();
});
</script>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2"></i>Add New Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rhu.schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="barangay_id" id="calendarAddBarangay" required>
                                <option value="">Select Location</option>
                                @foreach($barangayOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Personnel</label>
                            <input type="search" class="form-control" id="personnelSearch" placeholder="Search by staff name" list="personnelOptions" autocomplete="off" required>
                            <datalist id="personnelOptions"></datalist>
                            <input type="hidden" name="personnel_id" id="personnelId">
                            <input type="hidden" name="personnel_name" id="personnelName">
                            <input type="hidden" name="type" id="personnelType">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" id="personnelDesignation" placeholder="Select personnel" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Week Period</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="week_start" id="weekStart" required>
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" name="week_end" id="weekEnd" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Weekly Schedule</label>
                        <div class="schedule-form">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <div class="schedule-day-form mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-3">{{ ucfirst($day) }}</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="addTimeSlot('{{ $day }}')">
                                            <i class="bi bi-plus"></i> Add Time
                                        </button>
                                    </div>
                                    <div class="time-slots-container" id="timeSlots_{{ $day }}">
                                        <div class="time-input-group mb-2">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control start-time" data-day="{{ $day }}" placeholder="Start Time">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="time" class="form-control end-time" data-day="{{ $day }}" placeholder="End Time">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimeSlot(this)">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="schedule[{{ $day }}][]" class="formatted-time" value="">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 