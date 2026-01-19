@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Schedules</h2>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle me-2"></i>Add Schedule
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif



    <!-- Schedules Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="scheduleTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="midwife-tab" data-bs-toggle="tab" data-bs-target="#midwife" type="button" role="tab">
                        <i class="bi bi-heart-pulse me-2"></i>Midwife Schedules
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="doctor-tab" data-bs-toggle="tab" data-bs-target="#doctor" type="button" role="tab">
                        <i class="bi bi-person-vcard me-2"></i>Doctor Schedules
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="scheduleTabsContent">
                <!-- Midwife Schedules Tab -->
                <div class="tab-pane fade show active" id="midwife" role="tabpanel">
                    @if(count($midwifeSchedules) > 0)
                        <div class="row">
                            @foreach($midwifeSchedules as $schedule)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 schedule-card">
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-heart-pulse me-2"></i>{{ $schedule['personnel_name'] }}
                                                </h6>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-light edit-schedule-btn" 
                                                            data-schedule-id="{{ $schedule['id'] }}" 
                                                            data-personnel-name="{{ $schedule['personnel_name'] }}" 
                                                            data-schedule="{{ json_encode($schedule['schedule']) }}" 
                                                            data-week-start="{{ $schedule['week_start'] ?? '' }}" 
                                                            data-week-end="{{ $schedule['week_end'] ?? '' }}" 
                                                            title="Edit Schedule">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-light delete-schedule-btn" 
                                                            data-schedule-id="{{ $schedule['id'] }}" 
                                                            data-personnel-name="{{ $schedule['personnel_name'] }}" 
                                                            title="Delete Schedule">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($schedule['week_start']) && isset($schedule['week_end']))
                                                <div class="alert alert-info mb-3">
                                                    <i class="bi bi-calendar-week me-2"></i>
                                                    <strong>Week Period:</strong> 
                                                    {{ \Carbon\Carbon::parse($schedule['week_start'])->format('M d') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule['week_end'])->format('M d, Y') }}
                                                </div>
                                            @endif
                                            <div class="schedule-grid">
                                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                    @if(isset($schedule['schedule'][$day]) && !empty($schedule['schedule'][$day]))
                                                        <div class="schedule-day">
                                                            <div class="day-label">{{ ucfirst($day) }}</div>
                                                            <div class="time-slots">
                                                                @if(is_array($schedule['schedule'][$day]))
                                                                    @foreach($schedule['schedule'][$day] as $timeSlot)
                                                                        @if(is_string($timeSlot))
                                                                            <span class="time-badge">{{ $timeSlot }}</span>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">No Midwife Schedules</h5>
                            <p class="text-muted">No midwife schedules have been created yet.</p>
                        </div>
                    @endif
                </div>

                <!-- Doctor Schedules Tab -->
                <div class="tab-pane fade" id="doctor" role="tabpanel">
                    @if(count($doctorSchedules) > 0)
                        <div class="row">
                            @foreach($doctorSchedules as $schedule)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 schedule-card">
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-person-vcard me-2"></i>{{ $schedule['personnel_name'] }}
                                                </h6>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-light edit-schedule-btn" 
                                                            data-schedule-id="{{ $schedule['id'] }}" 
                                                            data-personnel-name="{{ $schedule['personnel_name'] }}" 
                                                            data-schedule="{{ json_encode($schedule['schedule']) }}" 
                                                            data-week-start="{{ $schedule['week_start'] ?? '' }}" 
                                                            data-week-end="{{ $schedule['week_end'] ?? '' }}" 
                                                            title="Edit Schedule">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-light delete-schedule-btn" 
                                                            data-schedule-id="{{ $schedule['id'] }}" 
                                                            data-personnel-name="{{ $schedule['personnel_name'] }}" 
                                                            title="Delete Schedule">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($schedule['week_start']) && isset($schedule['week_end']))
                                                <div class="alert alert-info mb-3">
                                                    <i class="bi bi-calendar-week me-2"></i>
                                                    <strong>Week Period:</strong> 
                                                    {{ \Carbon\Carbon::parse($schedule['week_start'])->format('M d') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule['week_end'])->format('M d, Y') }}
                                                </div>
                                            @endif
                                            <div class="schedule-grid">
                                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                                    @if(isset($schedule['schedule'][$day]) && !empty($schedule['schedule'][$day]))
                                                        <div class="schedule-day">
                                                            <div class="day-label">{{ ucfirst($day) }}</div>
                                                            <div class="time-slots">
                                                                @if(is_array($schedule['schedule'][$day]))
                                                                    @foreach($schedule['schedule'][$day] as $timeSlot)
                                                                        @if(is_string($timeSlot))
                                                                            <span class="time-badge">{{ $timeSlot }}</span>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">No Doctor Schedules</h5>
                            <p class="text-muted">No doctor schedules have been created yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
                            <select class="form-select" name="barangay_id" id="barangaySelect" required>
                                <option value="">Select Location</option>
                                @foreach($barangayOptions as $option)
                                    <option value="{{ $option['id'] }}" @if($option['id'] == $selectedBarangayId) selected @endif>
                                        {{ $option['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule Type</label>
                            <select class="form-select" name="type" id="scheduleType" required>
                                <option value="">Select Type</option>
                                <option value="midwife">Midwife</option>
                                <option value="doctor">Doctor</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Personnel</label>
                            <select class="form-select" name="personnel_id" id="personnelSelect" required>
                                <option value="">Select Personnel</option>
                            </select>
                            <input type="hidden" name="personnel_name" id="personnelName">
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

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="barangay_id" id="editBarangaySelect" required>
                                <option value="">Select Location</option>
                                @foreach($barangayOptions as $option)
                                    <option value="{{ $option['id'] }}" @if($option['id'] == $selectedBarangayId) selected @endif>
                                        {{ $option['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Personnel</label>
                            <input type="text" class="form-control" id="editPersonnelName" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Week Period</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="week_start" id="editWeekStart" required>
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" name="week_end" id="editWeekEnd" required>
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
                                                onclick="addEditTimeSlot('{{ $day }}')">
                                            <i class="bi bi-plus"></i> Add Time
                                        </button>
                                    </div>
                                    <div class="time-slots-container" id="editTimeSlots_{{ $day }}">
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
                        <i class="bi bi-save me-2"></i>Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the schedule for <strong id="deleteScheduleName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteScheduleForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete Schedule
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.schedule-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.schedule-grid {
    display: grid;
    gap: 8px;
}

.schedule-day {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 8px;
}

.day-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 4px;
}

.time-slots {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.time-badge {
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.time-input-group {
    display: flex;
    gap: 8px;
    align-items: center;
}

.time-input-group .form-control {
    flex: 1;
}

.schedule-day-form {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    background: #f8f9fa;
}
</style>

<script>
// Time formatting function for 12-hour format
function formatTime12Hour(timeString) {
    if (!timeString) return '';
    
    try {
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
        return timeString;
    }
}

// Convert 12-hour format to 24-hour format for input fields
function convert12To24Hour(time12Hour) {
    if (!time12Hour) return '';
    
    try {
        // Remove any extra spaces and convert to uppercase
        const cleanTime = time12Hour.trim().toUpperCase();
        
        // Match pattern like "9:30 AM" or "1:45 PM"
        let match = cleanTime.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/);
        
        if (!match) {
            // Try pattern without minutes like "9AM" or "1PM"
            match = cleanTime.match(/(\d{1,2})\s*(AM|PM)/);
            if (match) {
                let hour = parseInt(match[1]);
                const period = match[2];
                
                // Convert to 24-hour format
                if (period === 'PM' && hour !== 12) {
                    hour += 12;
                } else if (period === 'AM' && hour === 12) {
                    hour = 0;
                }
                
                return `${hour.toString().padStart(2, '0')}:00`;
            }
            return time12Hour;
        }
        
        let hour = parseInt(match[1]);
        const minute = parseInt(match[2]);
        const period = match[3];
        
        // Convert to 24-hour format
        if (period === 'PM' && hour !== 12) {
            hour += 12;
        } else if (period === 'AM' && hour === 12) {
            hour = 0;
        }
        
        return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
    } catch (e) {
        console.error('Error converting time:', time12Hour, e);
        return time12Hour;
    }
}

// Available personnel data
const availablePersonnel = @json($availableMidwives);
const assignedDoctors = @json($assignedDoctors);

// Handle schedule type change
document.getElementById('scheduleType').addEventListener('change', function() {
    const personnelSelect = document.getElementById('personnelSelect');
    const type = this.value;
    
    personnelSelect.innerHTML = '<option value="">Select Personnel</option>';
    
    if (type === 'midwife') {
        availablePersonnel.forEach(personnel => {
            const option = document.createElement('option');
            option.value = personnel.id;
            option.textContent = personnel.name || personnel.full_name || 'Unknown';
            option.dataset.name = personnel.name || personnel.full_name || 'Unknown';
            personnelSelect.appendChild(option);
        });
    } else if (type === 'doctor') {
        assignedDoctors.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name || doctor.full_name || 'Unknown';
            option.dataset.name = doctor.name || doctor.full_name || 'Unknown';
            personnelSelect.appendChild(option);
        });
    }
});

// Handle personnel selection
document.getElementById('personnelSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('personnelName').value = selectedOption.dataset.name || '';
});

// Handle form submission to ensure all time slots are formatted
document.querySelector('#addScheduleModal form').addEventListener('submit', function(e) {
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

// Handle edit form submission to ensure all time slots are formatted
document.querySelector('#editScheduleModal form').addEventListener('submit', function(e) {
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

// Function to set default week dates
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

// Add event listeners for edit and delete buttons
document.addEventListener('DOMContentLoaded', function() {
    // Set default week dates
    setDefaultWeekDates();
    // Edit button event listeners
    document.querySelectorAll('.edit-schedule-btn').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-schedule-id');
            const personnelName = this.getAttribute('data-personnel-name');
            const scheduleData = this.getAttribute('data-schedule');
            const weekStart = this.getAttribute('data-week-start');
            const weekEnd = this.getAttribute('data-week-end');
            
            try {
                const schedule = JSON.parse(scheduleData);
                editSchedule(scheduleId, personnelName, schedule, weekStart, weekEnd);
                
                // Show the edit modal
                const editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
                editModal.show();
            } catch (error) {
                console.error('Error parsing schedule data:', error);
                alert('Error loading schedule data');
            }
        });
    });
    
    // Delete button event listeners
    document.querySelectorAll('.delete-schedule-btn').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-schedule-id');
            const personnelName = this.getAttribute('data-personnel-name');
            deleteSchedule(scheduleId, personnelName);
        });
    });
    
    // Fix modal backdrop issues
    const editModal = document.getElementById('editScheduleModal');
    const addModal = document.getElementById('addScheduleModal');
    
    // Handle edit modal hidden event
    editModal.addEventListener('hidden.bs.modal', function() {
        // Remove any remaining backdrop
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        
        // Reset body padding
        document.body.style.paddingRight = '';
        
        // Enable scrolling
        document.body.style.overflow = '';
    });
    
    // Handle add modal hidden event
    addModal.addEventListener('hidden.bs.modal', function() {
        // Remove any remaining backdrop
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        
        // Reset body padding
        document.body.style.paddingRight = '';
        
        // Enable scrolling
        document.body.style.overflow = '';
    });
    
    // Handle modal close button clicks
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        });
    });
    
    // Handle escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cleanupModals();
        }
    });
    
    // Handle clicks outside modal to close
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            cleanupModals();
        }
    });
    
    // Emergency cleanup button (for debugging)
    if (document.querySelector('.btn-close')) {
        document.querySelector('.btn-close').addEventListener('click', function() {
            setTimeout(cleanupModals, 100);
        });
    }
});

// Add time slot functions
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

function addEditTimeSlot(day) {
    const container = document.getElementById(`editTimeSlots_${day}`);
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

// Update formatted time when start/end times change
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

// Force cleanup stuck modals
function cleanupModals() {
    // Remove all modal backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    
    // Reset body styles
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';
    
    // Hide all modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
}

// Edit schedule function
function editSchedule(scheduleId, personnelName, schedule, weekStart, weekEnd) {
    console.log('Editing schedule:', scheduleId, personnelName, schedule);
    
    document.getElementById('editPersonnelName').value = personnelName;
    document.getElementById('editScheduleForm').action = `/rhu/schedules/${scheduleId}`;
    
    // Set week dates if provided
    if (weekStart && weekEnd) {
        document.getElementById('editWeekStart').value = weekStart;
        document.getElementById('editWeekEnd').value = weekEnd;
    }
    
    // Clear existing time slots
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        const container = document.getElementById(`editTimeSlots_${day}`);
        container.innerHTML = '';
        
        if (schedule[day] && Array.isArray(schedule[day]) && schedule[day].length > 0) {
            console.log(`Processing ${day}:`, schedule[day]);
            // Handle simple string format (e.g., "9AM-12PM")
            schedule[day].forEach(timeSlot => {
                console.log(`Processing timeSlot: ${timeSlot}`);
                // Parse the time slot to extract start and end times
                // Updated regex to handle formats like "9:00 AM-12:00 PM" or "9AM-12PM"
                const timeMatch = timeSlot.match(/(\d{1,2}:\d{2}\s*[AP]M)-(\d{1,2}:\d{2}\s*[AP]M)/);
                let startTime = '';
                let endTime = '';
                
                if (timeMatch) {
                    console.log('Time match found:', timeMatch[1], timeMatch[2]);
                    // Convert 12-hour format back to 24-hour for input fields
                    startTime = convert12To24Hour(timeMatch[1].trim());
                    endTime = convert12To24Hour(timeMatch[2].trim());
                } else {
                    // Try alternative format without minutes
                    const altMatch = timeSlot.match(/(\d{1,2}[AP]M)-(\d{1,2}[AP]M)/);
                    if (altMatch) {
                        console.log('Alt match found:', altMatch[1], altMatch[2]);
                        startTime = convert12To24Hour(altMatch[1].trim());
                        endTime = convert12To24Hour(altMatch[2].trim());
                    } else {
                        console.log('No match found for timeSlot:', timeSlot);
                    }
                }
                
                const timeGroup = document.createElement('div');
                timeGroup.className = 'time-input-group mb-2';
                timeGroup.innerHTML = `
                    <div class="row">
                        <div class="col-md-5">
                            <input type="time" class="form-control start-time" data-day="${day}" value="${startTime}" placeholder="Start Time" onchange="updateFormattedTime(this)">
                        </div>
                        <div class="col-md-5">
                            <input type="time" class="form-control end-time" data-day="${day}" value="${endTime}" placeholder="End Time" onchange="updateFormattedTime(this)">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimeSlot(this)">
                                <i class="bi bi-dash"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="schedule[${day}][]" class="formatted-time" value="${timeSlot}">
                `;
                container.appendChild(timeGroup);
            });
        } else {
            // Add one empty slot
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
    });
    
    const editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
    editModal.show();
}

// Delete schedule function
function deleteSchedule(scheduleId, personnelName) {
    if (confirm(`Are you sure you want to delete the schedule for ${personnelName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/rhu/schedules/${scheduleId}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        const barangayInput = document.createElement('input');
        barangayInput.type = 'hidden';
        barangayInput.name = 'barangay_id';
        barangayInput.value = document.getElementById('barangaySelect').value;
        form.appendChild(barangayInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection 