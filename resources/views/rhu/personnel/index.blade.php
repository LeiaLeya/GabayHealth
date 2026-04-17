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
    <div class="d-flex flex-wrap" style="gap: 1rem;">
        @forelse($personnel as $person)
            <div>
                <div class="card border position-relative personnel-card h-100">
                    <!-- Status Badge -->
                    <div class="position-absolute top-0 end-0 m-2">
                        @php
                            $status = $person['status'] ?? 'Active';
                            $statusClass = $status === 'Active' ? 'success' : ($status === 'Inactive' ? 'secondary' : 'warning');
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
                    </div>

                    <!-- Personnel Image and Info -->
                    <div class="card-body d-flex flex-column text-center pt-5">
                        <!-- Profile Image -->
                        <div class="mb-3 d-flex justify-content-center">
                            @if(isset($person['image_url']))
                                <img src="{{ $person['image_url'] }}" alt="Personnel Photo" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:1px solid #000;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:120px;height:120px;border:1px solid #000;">
                                    <i class="bi bi-person display-4 text-dark"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Name -->
                        <h5 class="fw-bold mb-1 text-dark">{{ $person['name'] ?? 'Unknown' }}</h5>
                        
                        <!-- Position -->
                        <div class="text-dark fw-semibold mb-2">{{ $person['position'] ?? 'No Position' }}</div>

                        <!-- Address -->
                        @if(isset($person['address']) && $person['address'])
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-geo-alt text-muted flex-shrink-0"></i>
                                    <span class="text-dark small text-start">{{ Str::limit($person['address'], 100) }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#editPersonnelModal{{ $person['id'] }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletePersonnel('{{ $person['id'] }}', {{ json_encode($person['name'] ?? 'Unknown') }})" title="Delete Personnel">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Edit Personnel Modal -->
                <div class="modal fade" id="editPersonnelModal{{ $person['id'] }}" tabindex="-1" aria-labelledby="editPersonnelModalLabel{{ $person['id'] }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" action="{{ route('rhu.personnel.update', $person['id']) }}" class="modal-content" enctype="multipart/form-data">
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
                                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" placeholder="Enter address" value="{{ $person['address'] ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Profile Photo</label>
                                    <input type="file" name="image" class="form-control personnel-image-input" accept="image/*">
                                    <div class="image-preview-container mt-2" style="display:none;"></div>
                                    <small class="text-muted">Leave empty to keep current photo. Click to select, then crop.</small>
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
        <form method="POST" action="{{ route('rhu.personnel.store') }}" class="modal-content" enctype="multipart/form-data">
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
                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" placeholder="Enter address" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Profile Photo</label>
                    <input type="file" name="image" class="form-control personnel-image-input" accept="image/*">
                    <div class="image-preview-container mt-2" style="display:none;"></div>
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

@include('partials.personnel_image_crop')

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePersonnelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Personnel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="personnelName"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deletePersonnelForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Personnel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.personnel-card {
    width: 320px;
    min-height: 360px;
    transition: box-shadow 0.2s;
}

.personnel-card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}
</style>

<script>
function deletePersonnel(personnelId, personnelName) {
    document.getElementById('personnelName').textContent = personnelName;
    document.getElementById('deletePersonnelForm').action = '{{ url("rhu/personnel") }}/' + personnelId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePersonnelModal'));
    deleteModal.show();
}

function fillPersonnelData() {
    const select = document.getElementById('personnelSelect');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        // Auto-fill the form fields
        document.getElementById('nameInput').value = selectedOption.dataset.name || '';
        
        // Capitalize first letter of position, with special handling for BHW
        const position = selectedOption.dataset.position || '';
        let capitalizedPosition;
        if (position.toLowerCase() === 'bhw') {
            capitalizedPosition = 'Barangay Health Worker';
        } else {
            capitalizedPosition = position.charAt(0).toUpperCase() + position.slice(1).toLowerCase();
        }
        document.getElementById('positionInput').value = capitalizedPosition;
        
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