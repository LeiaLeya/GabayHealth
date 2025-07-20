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
                            <div>
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
                                {{ $event['time'] ?? 'N/A' }}
                            </span>
                        </div>
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
                                    <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                    <input type="time" name="time" class="form-control" value="{{ $event['time'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                    <input type="text" name="location" class="form-control" value="{{ $event['location'] ?? '' }}" required>
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Upcoming" {{ ($event['status'] ?? '') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    <option value="Done" {{ ($event['status'] ?? '') == 'Done' ? 'selected' : '' }}>Done</option>
                                    <option value="Cancelled" {{ ($event['status'] ?? '') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
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
                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" required>
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
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="Upcoming">Upcoming</option>
                        <option value="Done">Done</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
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
@endsection
