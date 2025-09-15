@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-1">Events</h2>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
            <i class="bi bi-plus-circle"></i> Add Event
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($events as $event)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm position-relative event-card">
                    <!-- Edit Icon and Status Badge -->
                    <div class="position-absolute top-0 end-0 m-2 d-flex align-items-center gap-2">
                        @php
                            $status = $event['status'] ?? 'Upcoming';
                            $statusClass = $status === 'Done' ? 'success' : ($status === 'Upcoming' ? 'primary' : 'secondary');
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                        <button class="btn btn-light btn-sm p-1" title="Edit Event" data-bs-toggle="modal" data-bs-target="#editEventModal{{ $event['id'] }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            @if(isset($event['image_url']))
                                <img src="{{ $event['image_url'] }}" alt="Event Image" class="rounded me-3" style="width:60px;height:60px;object-fit:cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" style="width:60px;height:60px;">
                                    <i class="bi bi-calendar-event display-6 text-secondary"></i>
                                </div>
                            @endif
                            <div style="padding-right: 100px;">
                                <h5 class="fw-bold mb-1">{{ $event['title'] ?? 'Untitled' }}</h5>
                                <div class="text-muted small">{{ $event['description'] ?? 'No description' }}</div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <span class="text-dark small">{{ $event['location'] ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-clock text-primary"></i>
                            <span class="text-dark small">
                                {{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('F d, Y') : 'N/A' }},
                                @if(isset($event['start_time']) && isset($event['end_time']))
                                    {{ \Carbon\Carbon::parse($event['start_time'])->format('h:iA') }} - {{ \Carbon\Carbon::parse($event['end_time'])->format('h:iA') }}
                                @else
                                    {{ $event['time'] ?? 'N/A' }}
                                @endif
                            </span>
                        </div>
                        @if(isset($event['targetAttendees']) && $event['targetAttendees'])
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-people text-success"></i>
                                <span class="text-dark small">{{ $event['targetAttendees'] }}</span>
                            </div>
                        @endif
                        @if(isset($event['isOpenToAll']) && $event['isOpenToAll'])
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-globe text-info"></i>
                                <span class="text-dark small">Open to All Barangays</span>
                            </div>
                        @endif
                        @if(isset($event['in_charge']) && $event['in_charge'])
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge text-primary"></i>
                                <span class="text-dark small">In Charge: {{ $event['in_charge'] }}</span>
                            </div>
                        @endif
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('events.show', $event['id']) }}" class="btn btn-outline-secondary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Event Modal -->
            <div class="modal fade" id="editEventModal{{ $event['id'] }}" tabindex="-1" aria-labelledby="editEventModalLabel{{ $event['id'] }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="POST" action="{{ route('events.update', $event['id']) }}" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editEventModalLabel{{ $event['id'] }}"><i class="bi bi-pencil-square me-2"></i>Edit Event</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ $event['title'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ $event['date'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" class="form-control" value="{{ $event['start_time'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" class="form-control" value="{{ $event['end_time'] ?? '' }}" required>
                                    <div class="invalid-feedback">
                                        End time must be after start time.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                    <input type="text" name="location" class="form-control" value="{{ $event['location'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Upcoming" {{ ($event['status'] ?? '') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="Done" {{ ($event['status'] ?? '') == 'Done' ? 'selected' : '' }}>Done</option>
                                        <option value="Cancelled" {{ ($event['status'] ?? '') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $event['description'] ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Event Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Target Attendees</label>
                                    <select name="targetAttendees" class="form-select">
                                        <option value="">Select target audience...</option>
                                        <option value="All Residents" {{ ($event['targetAttendees'] ?? '') == 'All Residents' ? 'selected' : '' }}>All Residents</option>
                                        <option value="Seniors Only" {{ ($event['targetAttendees'] ?? '') == 'Seniors Only' ? 'selected' : '' }}>Seniors Only</option>
                                        <option value="Children Only" {{ ($event['targetAttendees'] ?? '') == 'Children Only' ? 'selected' : '' }}>Children Only</option>
                                        <option value="Pregnant Women" {{ ($event['targetAttendees'] ?? '') == 'Pregnant Women' ? 'selected' : '' }}>Pregnant Women</option>
                                        <option value="Adults Only" {{ ($event['targetAttendees'] ?? '') == 'Adults Only' ? 'selected' : '' }}>Adults Only</option>
                                        <option value="Health Workers" {{ ($event['targetAttendees'] ?? '') == 'Health Workers' ? 'selected' : '' }}>Health Workers</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">In Charge / Head of Event</label>
                                    <input type="text" name="in_charge" class="form-control" value="{{ $event['in_charge'] ?? '' }}" placeholder="Enter name of person in charge">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="isOpenToAll" id="isOpenToAll{{ $event['id'] }}" value="1" {{ ($event['isOpenToAll'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isOpenToAll{{ $event['id'] }}">
                                        <i class="bi bi-globe me-1"></i>Open to All Barangays
                                    </label>
                                    <small class="form-text text-muted d-block">Check this if the event is open to residents from other barangays</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                    <h5>No events found.</h5>
                    <p class="mb-0">Start by adding your first event using the button above.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('events.store') }}" class="modal-content" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel"><i class="bi bi-calendar-plus me-2"></i>Add New Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                        <div class="invalid-feedback" id="end_time_error">
                            End time must be after start time.
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Upcoming">Upcoming</option>
                            <option value="Done">Done</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Event Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Target Attendees</label>
                        <select name="targetAttendees" class="form-select">
                            <option value="">Select target audience...</option>
                            <option value="All Residents">All Residents</option>
                            <option value="Seniors Only">Seniors Only</option>
                            <option value="Children Only">Children Only</option>
                            <option value="Pregnant Women">Pregnant Women</option>
                            <option value="Adults Only">Adults Only</option>
                            <option value="Health Workers">Health Workers</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">In Charge / Head of Event</label>
                        <input type="text" name="in_charge" class="form-control" placeholder="Enter name of person in charge">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="isOpenToAll" id="isOpenToAll" value="1">
                        <label class="form-check-label fw-semibold" for="isOpenToAll">
                            <i class="bi bi-globe me-1"></i>Open to All Barangays
                        </label>
                        <small class="form-text text-muted d-block">Check this if the event is open to residents from other barangays</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.event-card {
    border-radius: 1rem;
    transition: box-shadow 0.2s;
}
.event-card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08);
    border-color: #0d6efd22;
}
</style>

<script>
// Time validation for add event form
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    if (startTimeInput && endTimeInput) {
        function validateTime() {
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;
            
            if (startTime && endTime) {
                if (startTime >= endTime) {
                    endTimeInput.setCustomValidity('End time must be after start time.');
                    endTimeInput.classList.add('is-invalid');
                } else {
                    endTimeInput.setCustomValidity('');
                    endTimeInput.classList.remove('is-invalid');
                }
            }
        }
        
        startTimeInput.addEventListener('change', validateTime);
        endTimeInput.addEventListener('change', validateTime);
        endTimeInput.addEventListener('input', validateTime);
    }
    
    // Time validation for edit event forms
    const editForms = document.querySelectorAll('form[action*="/events/"]');
    editForms.forEach(form => {
        const startTimeField = form.querySelector('input[name="start_time"]');
        const endTimeField = form.querySelector('input[name="end_time"]');
        
        if (startTimeField && endTimeField) {
            function validateEditTime() {
                const startTime = startTimeField.value;
                const endTime = endTimeField.value;
                
                if (startTime && endTime) {
                    if (startTime >= endTime) {
                        endTimeField.setCustomValidity('End time must be after start time.');
                        endTimeField.classList.add('is-invalid');
                    } else {
                        endTimeField.setCustomValidity('');
                        endTimeField.classList.remove('is-invalid');
                    }
                }
            }
            
            startTimeField.addEventListener('change', validateEditTime);
            endTimeField.addEventListener('change', validateEditTime);
            endTimeField.addEventListener('input', validateEditTime);
        }
    });
});
</script>
@endsection
