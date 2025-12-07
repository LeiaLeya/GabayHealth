@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Personnel Management</h2>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
            <i class="bi bi-plus-circle"></i> Add Personnel
        </button>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Personnel Cards -->
    <div class="row g-4">
        @forelse($personnel as $person)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm position-relative personnel-card">
                    <!-- Status Badge and Edit Button -->
                    <div class="position-absolute top-0 end-0 m-2 d-flex align-items-center gap-2">
                        @php
                            $status = $person['status'] ?? 'Active';
                            $statusClass = $status === 'Active' ? 'success' : ($status === 'Inactive' ? 'secondary' : 'warning');
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                        <button class="btn btn-light btn-sm p-1" title="Edit Personnel" data-bs-toggle="modal" data-bs-target="#editPersonnelModal{{ $person['id'] }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>

                    <!-- Personnel Image and Info -->
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            @if(isset($person['image_url']))
                                <img src="{{ $person['image_url'] }}" alt="Personnel Photo" class="rounded-circle me-3" style="width:80px;height:80px;object-fit:cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width:80px;height:80px;">
                                    <i class="bi bi-person display-6 text-secondary"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">{{ $person['name'] ?? 'Unknown' }}</h5>
                                <div class="text-primary fw-semibold">{{ $person['position'] ?? 'No Position' }}</div>
                            </div>
                        </div>

                        <!-- Address -->
                        @if(isset($person['address']) && $person['address'])
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-geo-alt text-muted"></i>
                                    <span class="text-dark small">{{ Str::limit($person['address'], 100) }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#viewPersonnelModal{{ $person['id'] }}">
                                <i class="bi bi-eye me-1"></i>View Details
                            </button>
                            <form action="{{ route('personnel.destroy', $person['id']) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this personnel?')" title="Delete Personnel">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Personnel Modal -->
                <div class="modal fade" id="editPersonnelModal{{ $person['id'] }}" tabindex="-1" aria-labelledby="editPersonnelModalLabel{{ $person['id'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" action="{{ route('personnel.update', $person['id']) }}" class="modal-content" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editPersonnelModalLabel{{ $person['id'] }}">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Personnel
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $person['name'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                                        <input type="text" name="position" class="form-control" value="{{ $person['position'] ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Enter address">{{ $person['address'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Profile Photo</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current photo</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Update Personnel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- View Personnel Details Modal -->
                <div class="modal fade" id="viewPersonnelModal{{ $person['id'] }}" tabindex="-1" aria-labelledby="viewPersonnelModalLabel{{ $person['id'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewPersonnelModalLabel{{ $person['id'] }}">
                                    <i class="bi bi-person-circle me-2"></i>Personnel Details
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <!-- Profile Photo Column -->
                                    <div class="col-md-4 text-center mb-4">
                                        @if(isset($person['image_url']))
                                            <img src="{{ $person['image_url'] }}" alt="Profile Photo" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:120px;height:120px;">
                                                <i class="bi bi-person display-6 text-secondary"></i>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-center mb-2">
                                            @php
                                                $status = $person['status'] ?? 'Active';
                                                $statusClass = $status === 'Active' ? 'success' : ($status === 'Inactive' ? 'secondary' : 'warning');
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Details Column -->
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-semibold text-muted small">Full Name</label>
                                                <p class="fw-bold mb-0">{{ $person['name'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-semibold text-muted small">Position</label>
                                                <p class="text-primary fw-bold mb-0">{{ $person['position'] ?? 'N/A' }}</p>
                                            </div>
                                            @if(isset($person['address']) && $person['address'])
                                                <div class="col-12 mb-3">
                                                    <label class="form-label fw-semibold text-muted small">Address</label>
                                                    <p class="mb-0">{{ $person['address'] }}</p>
                                                </div>
                                            @endif
                                            <div class="col-12 mb-3">
                                                <label class="form-label fw-semibold text-muted small">Personnel Type</label>
                                                <p class="mb-0">
                                                    <i class="bi bi-person-check text-primary me-1"></i>
                                                    Personnel Member
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>Close
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editPersonnelModal{{ $person['id'] }}">
                                    <i class="bi bi-pencil me-1"></i>Edit Personnel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-people display-4 d-block mb-3"></i>
                    <h5>No personnel found.</h5>
                    <p class="mb-0">Start by adding your first personnel using the button above.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Personnel Modal -->
<div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-labelledby="addPersonnelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('personnel.store') }}" class="modal-content" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addPersonnelModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Add New Personnel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select from Account Management</label>
                        <select class="form-select" id="personnelSelect" onchange="fillPersonnelData()">
                            <option value="">Choose personnel from account management</option>
                            @foreach($availablePersonnel as $person)
                                <option value="{{ $person['id'] }}" 
                                        data-name="{{ $person['name'] ?? $person['full_name'] ?? '' }}"
                                        data-position="{{ $person['role'] ?? '' }}"
                                        data-address="{{ $person['address'] ?? '' }}">
                                    {{ $person['name'] ?? $person['full_name'] ?? 'Unknown' }} ({{ ucfirst($person['role'] ?? 'Unknown') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select to auto-fill personnel information</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Position <span class="text-danger">*</span></label>
                        <input type="text" name="position" id="positionInput" class="form-control" required placeholder="Enter position">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="nameInput" class="form-control" required placeholder="Enter full name">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Enter address"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Profile Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Save Personnel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.personnel-card {
    border-radius: 1rem;
    transition: box-shadow 0.2s;
    border: 1px solid #e9ecef;
}

.personnel-card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08);
    border-color: #0d6efd22;
}

.card-body {
    padding: 1.5rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    border-radius: 0.5rem !important;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    color: #495057;
    margin-bottom: 0.5rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>

<script>
function fillPersonnelData() {
    const select = document.getElementById('personnelSelect');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        // Auto-fill the form fields
        document.getElementById('nameInput').value = selectedOption.dataset.name || '';
        document.getElementById('positionInput').value = selectedOption.dataset.position || '';
        
        // You can also auto-fill address if needed
        // document.getElementById('addressInput').value = selectedOption.dataset.address || '';
    } else {
        // Clear the fields if no option is selected
        document.getElementById('nameInput').value = '';
        document.getElementById('positionInput').value = '';
    }
}
</script>
@endsection 