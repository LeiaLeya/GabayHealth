@extends('layouts.app')

@section('content')
@php
    $barangayOptions = collect($barangaysWithinRhu ?? [])->keyBy('id');
@endphp
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
                            $statusClass = match($status) {
                                'Upcoming' => 'primary',
                                'Ongoing' => 'warning',
                                'Done' => 'success',
                                'Cancelled' => 'secondary',
                                default => 'secondary',
                            };
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
                        @php
                            $allowedBarangayNames = $event['allowed_barangay_names'] ?? [];
                            if (empty($allowedBarangayNames) && !empty($event['allowed_barangays'] ?? [])) {
                                $allowedBarangayNames = collect($event['allowed_barangays'])
                                    ->filter(fn($id) => $barangayOptions->has($id))
                                    ->map(fn($id) => $barangayOptions[$id]['name'])
                                    ->values()
                                    ->all();
                            }
                        @endphp
                        @if(!empty($allowedBarangayNames))
                            <div class="mb-2 d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt text-info"></i>
                                <span class="text-dark small">
                                    Allowed Barangays: {{ implode(', ', $allowedBarangayNames) }}
                                </span>
                            </div>
                        @endif
                        @if(isset($event['in_charge']) && $event['in_charge'])
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge text-primary"></i>
                                <span class="text-dark small">In Charge: {{ $event['in_charge'] }}</span>
                            </div>
                        @endif
                        @if(($event['status'] ?? '') === 'Cancelled' && !empty($event['cancellation_reason'] ?? ''))
                            <div class="mb-2 d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-octagon text-danger"></i>
                                <span class="text-danger small">Reason: {{ $event['cancellation_reason'] }}</span>
                            </div>
                        @endif
                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('rhu.events.show', $event['id']) }}" class="btn btn-outline-secondary btn-sm">View Details</a>
                            @if(($event['status'] ?? '') !== 'Cancelled' && ($event['status'] ?? '') !== 'Done')
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelEventModal{{ $event['id'] }}">Cancel</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Event Modal -->
            <div class="modal fade" id="editEventModal{{ $event['id'] }}" tabindex="-1" aria-labelledby="editEventModalLabel{{ $event['id'] }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="POST" action="{{ route('rhu.events.update', $event['id']) }}" class="modal-content" enctype="multipart/form-data">
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
                                    <input type="date" name="date" class="form-control" value="{{ $event['date'] ?? '' }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
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
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0">Location <span class="text-danger">*</span></label>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="toggleEditLocationMode" onclick="toggleLocationInput('edit')">
                                        <small class="text-primary">Enter manually</small>
                                    </button>
                                </div>
                                <div id="editLocationGeocoderContainer"></div>
                                <input type="text" name="location" id="edit_location" class="form-control" value="{{ $event['location'] ?? '' }}" style="display: none;">
                                <input type="hidden" name="latitude" id="edit_latitude" value="{{ $event['latitude'] ?? '' }}">
                                <input type="hidden" name="longitude" id="edit_longitude" value="{{ $event['longitude'] ?? '' }}">
                                <div id="editMapPreview" class="mt-2" style="display: none; height: 200px; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6;"></div>
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
                                    <input class="form-check-input" type="checkbox" name="isOpenToAll" id="isOpenToAll{{ $event['id'] }}" value="1" data-allowed-target="allowedBarangays{{ $event['id'] }}" {{ ($event['isOpenToAll'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="isOpenToAll{{ $event['id'] }}">
                                        <i class="bi bi-globe me-1"></i>Open to All Barangays
                                    </label>
                                    <small class="form-text text-muted d-block">Check this if the event is open to residents from other barangays</small>
                                </div>
                            </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Allow Other Barangays Within RHU</label>
                            @if(!empty($barangaysWithinRhu))
                                <div class="dropdown w-100 multi-select-dropdown" data-multi-select="allowedBarangays{{ $event['id'] }}">
                                    <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="multi-select-label" id="allowedBarangays{{ $event['id'] }}Label" data-placeholder="Select barangays">
                                            Select barangays
                                        </span>
                                        <i class="bi bi-chevron-down small"></i>
                                    </button>
                                    <div class="dropdown-menu w-100 p-3 shadow-sm">
                                        @foreach($barangaysWithinRhu as $barangay)
                                            @php
                                                $isSelected = in_array($barangay['id'], $event['allowed_barangays'] ?? []);
                                            @endphp
                                            <div class="form-check">
                                                <input class="form-check-input allowed-barangay-option" type="checkbox" name="allowed_barangays[]" value="{{ $barangay['id'] }}" id="allowedBarangays{{ $event['id'] }}Option{{ $loop->index }}" data-label-target="allowedBarangays{{ $event['id'] }}Label" data-option-name="{{ $barangay['name'] }}" {{ $isSelected ? 'checked' : '' }}>
                                                <label class="form-check-label" for="allowedBarangays{{ $event['id'] }}Option{{ $loop->index }}">{{ $barangay['name'] }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="form-text text-muted d-block">Click to open and use the checkboxes to select barangays.</small>
                            @else
                                <div class="text-muted small">No other barangays available within your RHU.</div>
                            @endif
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cancel Event Modal -->
            <div class="modal fade" id="cancelEventModal{{ $event['id'] }}" tabindex="-1" aria-labelledby="cancelEventLabel{{ $event['id'] }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('rhu.events.cancel', $event['id']) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelEventLabel{{ $event['id'] }}"><i class="bi bi-exclamation-octagon me-2"></i>Cancel Event</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">Please provide a reason for cancelling <strong>{{ $event['title'] ?? 'this event' }}</strong>.</p>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="3" required maxlength="500" placeholder="Enter reason..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Confirm Cancel</button>
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
        <form method="POST" action="{{ route('rhu.events.store') }}" class="modal-content" enctype="multipart/form-data">
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
                        <input type="date" name="date" class="form-control" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
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
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">Location</label>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="toggleLocationMode" onclick="toggleLocationInput('add')">
                            <small class="text-primary">Enter manually</small>
                        </button>
                    </div>
                    <div id="locationGeocoderContainer"></div>
                    <input type="text" name="location" id="location" class="form-control" style="display: none;">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <small class="text-muted">Search with Mapbox or enter address manually</small>
                    <div id="mapPreview" class="mt-2" style="display: none; height: 200px; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6;"></div>
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
                        <input class="form-check-input" type="checkbox" name="isOpenToAll" id="isOpenToAll" value="1" data-allowed-target="allowedBarangaysCreate">
                        <label class="form-check-label fw-semibold" for="isOpenToAll">
                            <i class="bi bi-globe me-1"></i>Open to All Barangays
                        </label>
                        <small class="form-text text-muted d-block">Check this if the event is open to residents from other barangays</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Allow Other Barangays Within RHU</label>
                    @if(!empty($barangaysWithinRhu))
                        <div class="dropdown w-100 multi-select-dropdown" data-multi-select="allowedBarangaysCreate">
                            <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="multi-select-label" id="allowedBarangaysCreateLabel" data-placeholder="Select barangays">Select barangays</span>
                                <i class="bi bi-chevron-down small"></i>
                            </button>
                            <div class="dropdown-menu w-100 p-3 shadow-sm">
                                @foreach($barangaysWithinRhu as $barangay)
                                    <div class="form-check">
                                        <input class="form-check-input allowed-barangay-option" type="checkbox" name="allowed_barangays[]" value="{{ $barangay['id'] }}" id="allowedBarangaysCreateOption{{ $loop->index }}" data-label-target="allowedBarangaysCreateLabel" data-option-name="{{ $barangay['name'] }}">
                                        <label class="form-check-label" for="allowedBarangaysCreateOption{{ $loop->index }}">{{ $barangay['name'] }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <small class="form-text text-muted d-block">Click to open and use the checkboxes to select barangays.</small>
                    @else
                        <div class="text-muted small">No other barangays available within your RHU.</div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save Event
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
.multi-select-dropdown .dropdown-menu {
    max-height: 220px;
    overflow-y: auto;
}
.multi-select-dropdown .form-check {
    margin-bottom: 0.35rem;
}
.multi-select-dropdown .form-check:last-child {
    margin-bottom: 0;
}
.multi-select-dropdown.dropdown-disabled button {
    pointer-events: none;
    opacity: 0.65;
}
.multi-select-dropdown.dropdown-disabled .multi-select-label {
    color: #6c757d;
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
    
    function updateAllowedBarangayLabel(dropdown) {
        if (!dropdown) return;
        const label = dropdown.querySelector('.multi-select-label');
        const checkboxes = dropdown.querySelectorAll('.allowed-barangay-option');
        if (!label || !checkboxes.length) return;

        const placeholder = label.getAttribute('data-placeholder') || 'Select options';
        const selectedNames = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.getAttribute('data-option-name'));

        label.textContent = selectedNames.length ? selectedNames.join(', ') : placeholder;
    }

    function toggleAllowedBarangays(checkbox) {
        const targetKey = checkbox.getAttribute('data-allowed-target');
        if (!targetKey) return;

        const dropdown = document.querySelector(`.multi-select-dropdown[data-multi-select="${targetKey}"]`);
        if (!dropdown) return;

        const checkboxes = dropdown.querySelectorAll('.allowed-barangay-option');
        const triggerButton = dropdown.querySelector('button');

        if (checkbox.checked) {
            checkboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
            });
            if (triggerButton) {
                triggerButton.classList.add('disabled');
                triggerButton.setAttribute('disabled', 'disabled');
            }
            dropdown.classList.add('dropdown-disabled');
        } else {
            checkboxes.forEach(cb => cb.disabled = false);
            if (triggerButton) {
                triggerButton.classList.remove('disabled');
                triggerButton.removeAttribute('disabled');
            }
            dropdown.classList.remove('dropdown-disabled');
        }

        updateAllowedBarangayLabel(dropdown);
    }

    const multiSelectDropdowns = document.querySelectorAll('.multi-select-dropdown');
    multiSelectDropdowns.forEach(dropdown => {
        updateAllowedBarangayLabel(dropdown);
        dropdown.querySelectorAll('.allowed-barangay-option').forEach(cb => {
            cb.addEventListener('change', () => updateAllowedBarangayLabel(dropdown));
        });
    });

    const openToAllCheckboxes = document.querySelectorAll('input[name="isOpenToAll"][data-allowed-target]');
    openToAllCheckboxes.forEach(checkbox => {
        toggleAllowedBarangays(checkbox);
        checkbox.addEventListener('change', () => toggleAllowedBarangays(checkbox));
    });

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

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">
<style>
    .mapboxgl-ctrl-geocoder {
        width: 100%;
        max-width: 100%;
        border-radius: 4px;
    }
    .mapboxgl-ctrl-geocoder input {
        padding: 8px 12px;
    }
    .mapboxgl-ctrl-geocoder .mapboxgl-ctrl-geocoder--icon-search {
        display: none !important;
    }
    .mapboxgl-ctrl-geocoder .mapboxgl-ctrl-geocoder--pin-right {
        right: 10px;
    }
    #mapPreview, #editMapPreview {
        margin-top: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
<script>
    const mapboxToken = @json(config('services.mapbox.access_token'));
    
    console.log('Mapbox Token:', mapboxToken ? 'Found' : 'Not found');
    
    if (mapboxToken && mapboxToken !== '' && mapboxToken !== null) {
        mapboxgl.accessToken = mapboxToken;
        
        let addGeocoder = null;
        let addMap = null;
        let editGeocoder = null;
        let editMap = null;
        
        // Toggle between Mapbox search and manual input
        window.toggleLocationInput = function(mode) {
            const isAdd = mode === 'add';
            const geocoderContainer = isAdd ? document.getElementById('locationGeocoderContainer') : document.getElementById('editLocationGeocoderContainer');
            const locationInput = isAdd ? document.getElementById('location') : document.getElementById('edit_location');
            const toggleBtn = isAdd ? document.getElementById('toggleLocationMode') : document.getElementById('toggleEditLocationMode');
            
            if (!geocoderContainer || !locationInput || !toggleBtn) return;
            
            if (geocoderContainer.style.display === 'none' || !geocoderContainer.innerHTML.trim()) {
                // Switch to Mapbox search
                geocoderContainer.style.display = 'block';
                locationInput.style.display = 'none';
                const small = toggleBtn.querySelector('small');
                if (small) small.textContent = 'Enter manually';
                
                // Reinitialize geocoder if needed
                if (isAdd && !addGeocoder) {
                    initializeAddGeocoder();
                } else if (!isAdd && !editGeocoder) {
                    const modal = locationInput.closest('.modal');
                    if (modal) initializeEditGeocoder(modal);
                }
            } else {
                // Switch to manual input
                geocoderContainer.style.display = 'none';
                locationInput.style.display = 'block';
                const small = toggleBtn.querySelector('small');
                if (small) small.textContent = 'Use Mapbox search';
                
                // Clear coordinates when switching to manual
                if (isAdd) {
                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                    const mapPreview = document.getElementById('mapPreview');
                    if (mapPreview) mapPreview.style.display = 'none';
                } else {
                    document.getElementById('edit_latitude').value = '';
                    document.getElementById('edit_longitude').value = '';
                    const editMapPreview = document.getElementById('editMapPreview');
                    if (editMapPreview) editMapPreview.style.display = 'none';
                }
            }
        };
        
        function initializeAddGeocoder() {
            const geocoderContainer = document.getElementById('locationGeocoderContainer');
            const locationInput = document.getElementById('location');
            
            if (!geocoderContainer || !locationInput) return;
            
            // Clear container if it has content
            geocoderContainer.innerHTML = '';
            geocoderContainer.style.width = '100%';
            geocoderContainer.style.display = 'block';
            locationInput.style.display = 'none';
            
            addGeocoder = new MapboxGeocoder({
                accessToken: mapboxToken,
                mapboxgl: mapboxgl,
                placeholder: 'Search for a location...',
                countries: 'ph',
                proximity: [123.8854, 10.3157],
                types: 'address,poi,place,locality,neighborhood',
                marker: false,
                minLength: 2,
                limit: 10
            });
            
            addGeocoder.addTo(geocoderContainer);
            
            const mapPreview = document.getElementById('mapPreview');
            
            addGeocoder.on('result', function(e) {
                const result = e.result;
                const coordinates = result.geometry.coordinates;
                const address = result.place_name;
                
                locationInput.value = address;
                document.getElementById('latitude').value = coordinates[1];
                document.getElementById('longitude').value = coordinates[0];
                
                if (!addMap) {
                    addMap = new mapboxgl.Map({
                        container: 'mapPreview',
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: coordinates,
                        zoom: 15
                    });
                    
                    new mapboxgl.Marker()
                        .setLngLat(coordinates)
                        .addTo(addMap);
                    
                    mapPreview.style.display = 'block';
                } else {
                    addMap.setCenter(coordinates);
                    // Remove existing markers
                    const markers = addMap._markers || [];
                    markers.forEach(m => m.remove());
                    new mapboxgl.Marker()
                        .setLngLat(coordinates)
                        .addTo(addMap);
                }
            });
            
            addGeocoder.on('clear', function() {
                locationInput.value = '';
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                if (mapPreview && addMap) {
                    mapPreview.style.display = 'none';
                }
            });
        }
        
        // Initialize geocoder for Add Event form when modal is shown
        const addEventModal = document.getElementById('addEventModal');
        if (addEventModal) {
            addEventModal.addEventListener('shown.bs.modal', function() {
                initializeAddGeocoder();
            });
            
            // Clean up when modal is hidden
            addEventModal.addEventListener('hidden.bs.modal', function() {
                if (addGeocoder) {
                    addGeocoder = null;
                }
                // Reset to Mapbox search mode
                const geocoderContainer = document.getElementById('locationGeocoderContainer');
                const locationInput = document.getElementById('location');
                const toggleBtn = document.getElementById('toggleLocationMode');
                if (geocoderContainer) {
                    geocoderContainer.innerHTML = '';
                    geocoderContainer.style.display = 'block';
                }
                if (locationInput) locationInput.style.display = 'none';
                if (toggleBtn) {
                    const small = toggleBtn.querySelector('small');
                    if (small) small.textContent = 'Enter manually';
                }
            });
        }
        
        function initializeEditGeocoder(modal) {
            const editGeocoderContainer = modal.querySelector('#editLocationGeocoderContainer');
            const editLocationInput = modal.querySelector('#edit_location');
            
            if (!editGeocoderContainer || !editLocationInput || editGeocoder) return;
            
            editGeocoderContainer.style.width = '100%';
            
            editGeocoder = new MapboxGeocoder({
                accessToken: mapboxToken,
                mapboxgl: mapboxgl,
                placeholder: 'Search for a location...',
                countries: 'ph',
                proximity: [123.8854, 10.3157],
                types: 'address,poi,place,locality,neighborhood',
                marker: false,
                minLength: 2,
                limit: 10
            });
            
            editGeocoder.addTo(editGeocoderContainer);
            
            // Set initial value if editing
            const existingLocation = editLocationInput.value;
            if (existingLocation) {
                editGeocoder.setInput(existingLocation);
            }
            
            // Check if coordinates exist - if not, show manual input by default
            const existingLat = modal.querySelector('#edit_latitude')?.value;
            const existingLng = modal.querySelector('#edit_longitude')?.value;
            if (!existingLat || !existingLng) {
                editGeocoderContainer.style.display = 'none';
                editLocationInput.style.display = 'block';
                const toggleBtn = modal.querySelector('#toggleEditLocationMode');
                if (toggleBtn) toggleBtn.querySelector('small').textContent = 'Use Mapbox search';
            } else {
                editLocationInput.style.display = 'none';
            }
            
            const editMapPreview = modal.querySelector('#editMapPreview');
            
            // If editing and coordinates exist, show map
            if (existingLat && existingLng) {
                editMap = new mapboxgl.Map({
                    container: 'editMapPreview',
                    style: 'mapbox://styles/mapbox/streets-v11',
                    center: [parseFloat(existingLng), parseFloat(existingLat)],
                    zoom: 15
                });
                
                new mapboxgl.Marker()
                    .setLngLat([parseFloat(existingLng), parseFloat(existingLat)])
                    .addTo(editMap);
                
                editMapPreview.style.display = 'block';
            }
            
            editGeocoder.on('result', function(e) {
                const result = e.result;
                const coordinates = result.geometry.coordinates;
                const address = result.place_name;
                
                editLocationInput.value = address;
                modal.querySelector('#edit_latitude').value = coordinates[1];
                modal.querySelector('#edit_longitude').value = coordinates[0];
                
                if (!editMap) {
                    editMap = new mapboxgl.Map({
                        container: 'editMapPreview',
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: coordinates,
                        zoom: 15
                    });
                    
                    new mapboxgl.Marker()
                        .setLngLat(coordinates)
                        .addTo(editMap);
                    
                    editMapPreview.style.display = 'block';
                } else {
                    editMap.setCenter(coordinates);
                    const markers = editMap._markers || [];
                    markers.forEach(m => m.remove());
                    new mapboxgl.Marker()
                        .setLngLat(coordinates)
                        .addTo(editMap);
                }
            });
            
            editGeocoder.on('clear', function() {
                editLocationInput.value = '';
                modal.querySelector('#edit_latitude').value = '';
                modal.querySelector('#edit_longitude').value = '';
                if (editMapPreview && editMap) {
                    editMapPreview.style.display = 'none';
                }
            });
        }
        
        // Initialize geocoder for Edit Event form when modal is shown
        document.addEventListener('shown.bs.modal', function(e) {
            const modal = e.target;
            const editLocationInput = modal.querySelector('#edit_location');
            
            if (editLocationInput && !editGeocoder) {
                initializeEditGeocoder(modal);
            }
        });
        
        // Clean up when edit modal is hidden
        document.addEventListener('hidden.bs.modal', function(e) {
            if (e.target.querySelector('#edit_location')) {
                editGeocoder = null;
                editMap = null;
            }
        });
    } else {
        console.warn('Mapbox token not found or invalid');
    }
</script>
@endpush
@endsection
