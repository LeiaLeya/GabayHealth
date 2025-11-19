@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-0">{{ $parentData['name'] }}</h2>
            <p class="text-muted mb-0">Manage batches and track expiration dates</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                <i class="bi bi-plus-circle"></i>
                Add New Batch
            </button>
            <a href="{{ route('inventory.release-history', $parentData['id']) }}" class="btn btn-info d-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i>
                Release History
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Back to Inventory
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(count($batches) > 0)
        <!-- Release Medicine Section -->
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-heart-pulse me-2"></i>Release Medicine
                </h5>
            </div>
            <div class="card-body">
                <div id="residentInlineAlert" class="alert d-none" role="alert"></div>
                <form action="{{ route('inventory.release', $parentData['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="resident_name" class="form-label fw-semibold">Resident Name <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="resident_search" autocomplete="off" placeholder="Search resident name or email..." required>
                                <input type="hidden" id="resident_name" name="resident_name" required>
                                <input type="hidden" id="resident_id" name="resident_id">
                                <div id="resident_dropdown" class="dropdown-menu w-100" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1050; word-wrap: break-word;"></div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="quantity_to_release" class="form-label fw-semibold">Quantity to Release <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity_to_release" name="quantity_to_release" min="1" max="{{ $parentData['quantity'] }}" required placeholder="Enter quantity">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="release_date" class="form-label fw-semibold">Release Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="release_date" name="release_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="released_by" class="form-label fw-semibold">Released By <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="personnel_search" autocomplete="off" placeholder="Search personnel name..." required>
                                <input type="hidden" id="released_by" name="released_by" required>
                                <input type="hidden" id="personnel_id" name="personnel_id">
                                <div id="personnel_dropdown" class="dropdown-menu w-100" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1050; word-wrap: break-word;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reason" class="form-label fw-semibold">Reason for Release</label>
                            <input type="text" class="form-control" id="reason" name="reason" placeholder="e.g., fever, headache, etc.">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-heart-pulse me-1"></i>Release Medicine
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Batches Table Card -->
        <div class="card shadow-sm border border-primary-subtle" style="border-width:2px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Medicine Batches
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" id="sortExpirationBtn" onclick="toggleExpirationSort()">
                        <i class="bi bi-funnel me-1"></i>
                        <span id="sortText">Sort by Expiration</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 inventory-table">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">Item Name</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Type</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Quantity</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Unit Type</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Expiration Date</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Status</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                                @php
                                    $expirationDate = strtotime($batch['expiration_date']);
                                    $today = strtotime('today');
                                    $thirtyDays = strtotime('+30 days');
                                    
                                    if ($expirationDate <= $today) {
                                        $status = 'expired';
                                        $statusText = 'Expired';
                                        $badgeClass = 'danger';
                                    } elseif ($expirationDate <= $thirtyDays) {
                                        $status = 'expiring_soon';
                                        $statusText = 'Expiring Soon';
                                        $badgeClass = 'warning';
                                    } else {
                                        $status = 'good';
                                        $statusText = 'Good';
                                        $badgeClass = 'success';
                                    }
                                    
                                    $daysLeft = ceil(($expirationDate - $today) / (60 * 60 * 24));
                                @endphp
                                <tr class="border-bottom {{ $status === 'expired' ? 'table-danger' : ($status === 'expiring_soon' ? 'table-warning' : '') }}">
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">{{ $parentData['name'] }}</div>
                                        <small class="text-muted">Lot No: {{ $batch['lot_number'] ?? 'N/A' }}</small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-muted">{{ $parentData['type'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-dark">{{ $batch['quantity'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-muted">{{ ucfirst($parentData['unit_type']) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($batch['expiration_date'])->format('M d, Y') }}</span>
                                            @if($status === 'expired')
                                                <span class="badge bg-danger ms-2">Expired</span>
                                            @elseif($status === 'expiring_soon')
                                                <span class="badge bg-warning ms-2">{{ $daysLeft }} days left</span>
                                            @else
                                                <span class="badge bg-success ms-2">{{ $daysLeft }} days left</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-{{ $badgeClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group" role="group" style="gap: 0.4rem;">
                                            <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBatchModal{{ $batch['id'] }}" 
                                                    title="Edit Batch">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('inventory.batches.destroy', [$parentData['id'], $batch['id']]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" 
                                                        onclick="return confirm('Are you sure you want to delete this batch?')" 
                                                        title="Delete Batch">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-box display-4 d-block mb-3"></i>
                <h5>No batches found for {{ $parentData['name'] }}</h5>
                <p class="mb-0">Start by adding your first batch using the button above.</p>
            </div>
        </div>
    @endif
</div>

<!-- Add Resident Modal -->
<div class="modal fade" id="newResidentModal" tabindex="-1" aria-labelledby="newResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newResidentModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Add New Resident
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newResidentForm">
                <div class="modal-body">
                    <div id="newResidentFormAlert" class="alert d-none" role="alert"></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="newResidentFirstName" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="newResidentFirstName" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="newResidentLastName" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="newResidentLastName" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="newResidentEmail" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="newResidentEmail" name="email" placeholder="name@example.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="newResidentPurok" class="form-label fw-semibold">Purok / Street <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="newResidentPurok" name="purok" placeholder="e.g., Purok Sunflower" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="newResidentPassword" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newResidentPassword" name="password" minlength="8" autocomplete="new-password" required>
                            <small class="text-muted">Minimum of 8 characters.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="newResidentPasswordConfirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="newResidentPasswordConfirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Role</label>
                        <input type="text" class="form-control" value="User" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="newResidentSubmitBtn">
                        <span class="submit-label">Create Resident Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Batch Modal -->
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-labelledby="addBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBatchModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Batch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('inventory.batches.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parent_medicine_id" class="form-label fw-semibold">Select Medicine <span class="text-danger">*</span></label>
                            <select class="form-control" id="parent_medicine_id" name="parent_medicine_id" required>
                                <option value="">Select a medicine...</option>
                                @foreach($allMedicines as $medicine)
                                    <option value="{{ $medicine['id'] }}" 
                                            {{ $medicine['id'] === $parentData['id'] ? 'selected' : '' }}>
                                        {{ $medicine['name'] }} ({{ ucfirst($medicine['unit_type']) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lot_number" class="form-label fw-semibold">Lot Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lot_number" name="lot_number" required placeholder="Enter lot number">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required placeholder="Enter quantity">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expiration_date" class="form-label fw-semibold">Expiration Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expiration_date" name="expiration_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter batch notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Save Batch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Batch Modals (Outside Table Structure) -->
@if(count($batches) > 0)
    @foreach($batches as $batch)
        <div class="modal fade" id="editBatchModal{{ $batch['id'] }}" tabindex="-1" aria-labelledby="editBatchModalLabel{{ $batch['id'] }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editBatchModalLabel{{ $batch['id'] }}">
                            <i class="bi bi-pencil me-2"></i>Edit Batch
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.batches.update', [$parentData['id'], $batch['id']]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lot_number_{{ $batch['id'] }}" class="form-label fw-semibold">Lot Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lot_number_{{ $batch['id'] }}" name="lot_number" value="{{ $batch['lot_number'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_{{ $batch['id'] }}" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="quantity_{{ $batch['id'] }}" name="quantity" min="0" value="{{ $batch['quantity'] }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiration_date_{{ $batch['id'] }}" class="form-label fw-semibold">Expiration Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="expiration_date_{{ $batch['id'] }}" name="expiration_date" value="{{ $batch['expiration_date'] }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes_{{ $batch['id'] }}" class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control" id="notes_{{ $batch['id'] }}" name="notes" rows="3" placeholder="Enter batch notes...">{{ $batch['notes'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Update Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<style>
.table th, .table td {
    vertical-align: middle;
    background: #fff;
    font-size: 1rem;
}
.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}
.table tr {
    border-radius: 0.5rem;
}
.table tbody tr {
    border-top: none;
    border-bottom: 1px solid #f1f1f1;
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

.card {
    border-radius: 0.75rem;
    overflow: hidden;
    border: 2px solid #1657c1 !important;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Add visible outline inside the table */
.inventory-table th, .inventory-table td {
    border-left: none !important;
    border-right: none !important;
    background: #fff;
}
.inventory-table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #1657c1 !important;
}
.inventory-table tr {
    border-radius: 0.5rem;
}
.inventory-table tbody tr {
    border-top: none;
    border-bottom: 1.5px solid #b6c6e3 !important;
}

/* Expiration status styling */
.table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

/* Filter button styling */
.dropdown-toggle {
    position: relative;
    transition: all 0.2s ease;
}

.dropdown-toggle:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dropdown-toggle:active {
    transform: translateY(0);
}

/* Simple modal fix - no more jittering */
.modal {
    z-index: 1055;
}

.modal-backdrop {
    z-index: 1050;
}
</style>

<script>
let currentSortDirection = '{{ $sortDirection ?? "asc" }}';
const residentStoreUrl = '{{ route('inventory.residents.store') }}';
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
}
let newResidentModalInstance = null;

function toggleExpirationSort() {
    // Toggle sort direction
    currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    
    // Update button text and icon
    const sortText = document.getElementById('sortText');
    const sortIcon = document.querySelector('#sortExpirationBtn i');
    
    if (currentSortDirection === 'desc') {
        sortText.textContent = 'Latest Expiry';
        sortIcon.className = 'bi bi-funnel me-1';
    } else {
        sortText.textContent = 'Earliest Expiry';
        sortIcon.className = 'bi bi-funnel me-1';
    }
    
    // Get the current URL and construct the sort URL
    const currentUrl = window.location.pathname;
    let baseUrl;
    
    if (currentUrl.includes('/sort')) {
        // If we're already on a sort page, remove the /sort part
        baseUrl = currentUrl.replace('/sort', '');
    } else {
        // If we're on the regular show page, use the current URL
        baseUrl = currentUrl;
    }
    
    const sortUrl = baseUrl + '/sort?direction=' + currentSortDirection;
    
    // Redirect to sorted view
    window.location.href = sortUrl;
}

// Initialize button state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sortText = document.getElementById('sortText');
    const sortIcon = document.querySelector('#sortExpirationBtn i');
    
    if (currentSortDirection === 'desc') {
        sortText.textContent = 'Latest Expiry';
        sortIcon.className = 'bi bi-funnel me-1';
    } else {
        sortText.textContent = 'Earliest Expiry';
        sortIcon.className = 'bi bi-funnel me-1';
    }
    
    // Initialize resident search functionality
    initializeResidentSearch();
    // Initialize personnel search functionality
    initializePersonnelSearch();
    // Setup modal for registering new residents
    setupNewResidentModal();
});

function setupNewResidentModal() {
    const modalElement = document.getElementById('newResidentModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        newResidentModalInstance = new bootstrap.Modal(modalElement);
        modalElement.addEventListener('hidden.bs.modal', () => {
            resetNewResidentForm();
        });
    }

    const residentForm = document.getElementById('newResidentForm');
    if (residentForm) {
        residentForm.addEventListener('submit', submitNewResidentForm);
    }
}

function openNewResidentModal(initialName = '') {
    resetNewResidentForm();

    if (initialName) {
        const parts = initialName.trim().split(/\s+/);
        const firstNameField = document.getElementById('newResidentFirstName');
        const lastNameField = document.getElementById('newResidentLastName');
        if (firstNameField) {
            firstNameField.value = parts.shift() || '';
        }
        if (lastNameField) {
            lastNameField.value = parts.join(' ');
        }
    }

    const emailField = document.getElementById('newResidentEmail');
    if (emailField && !emailField.value) {
        emailField.focus();
    }

    if (newResidentModalInstance) {
        newResidentModalInstance.show();
    }
}

function resetNewResidentForm() {
    const form = document.getElementById('newResidentForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
    clearNewResidentFormAlert();
}

function clearNewResidentFormAlert() {
    const alertEl = document.getElementById('newResidentFormAlert');
    if (!alertEl) return;
    alertEl.classList.add('d-none');
    alertEl.classList.remove('alert-success', 'alert-danger');
    alertEl.innerHTML = '';
}

function showNewResidentFormMessage(type, message) {
    const alertEl = document.getElementById('newResidentFormAlert');
    if (!alertEl) return;
    alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
    alertEl.classList.add(`alert-${type}`);
    alertEl.innerHTML = message;
}

function displayNewResidentErrors(errors) {
    const messages = [];
    Object.keys(errors || {}).forEach(field => {
        const fieldErrors = errors[field];
        const input = document.querySelector(`#newResidentForm [name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
        }
        (fieldErrors || []).forEach(message => messages.push(message));
    });

    if (messages.length) {
        const list = `<ul class="mb-0 ps-3">${messages.map(msg => `<li>${msg}</li>`).join('')}</ul>`;
        showNewResidentFormMessage('danger', list);
    }
}

function submitNewResidentForm(event) {
    event.preventDefault();

    const form = event.target;
    clearNewResidentFormAlert();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const submitBtn = document.getElementById('newResidentSubmitBtn');
    const originalLabel = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...';
    }

    const payload = {
        first_name: form.first_name ? form.first_name.value.trim() : '',
        last_name: form.last_name ? form.last_name.value.trim() : '',
        email: form.email ? form.email.value.trim() : '',
        purok: form.purok ? form.purok.value.trim() : '',
        password: form.password ? form.password.value : '',
        password_confirmation: form.password_confirmation ? form.password_confirmation.value : '',
    };

    fetch(residentStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                displayNewResidentErrors(data.errors);
            } else {
                showNewResidentFormMessage('danger', data.message || 'Failed to create resident.');
            }
            throw new Error('Request failed');
        }
        return data;
    })
    .then(data => {
        selectResident(data.id, data.name, data.email, data.username);
        showResidentInlineAlert('success', `Resident account created for ${data.name}.`);
        if (newResidentModalInstance) {
            newResidentModalInstance.hide();
        }
    })
    .catch(error => {
        console.error('Error creating resident:', error);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
        }
    });
}

function showResidentInlineAlert(type, message) {
    const alertEl = document.getElementById('residentInlineAlert');
    if (!alertEl) return;
    alertEl.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
    alertEl.classList.add(`alert-${type}`);
    alertEl.innerHTML = message;
}

function hideResidentInlineAlert() {
    const alertEl = document.getElementById('residentInlineAlert');
    if (!alertEl) return;
    alertEl.classList.add('d-none');
    alertEl.classList.remove('alert-success', 'alert-danger', 'alert-info');
    alertEl.innerHTML = '';
}

// Resident Search Functionality
function initializeResidentSearch() {
    const searchInput = document.getElementById('resident_search');
    const dropdown = document.getElementById('resident_dropdown');
    const hiddenNameInput = document.getElementById('resident_name');
    const hiddenIdInput = document.getElementById('resident_id');
    let searchTimeout;
    let hasShownInitialList = false;
    
    // Show all residents when field is focused/clicked
    searchInput.addEventListener('focus', function() {
        if (!hasShownInitialList && this.value.trim() === '') {
            searchResidents('');
            hasShownInitialList = true;
        }
    });
    
    // Search for residents as user types
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        hideResidentInlineAlert();
        
        // Reset flag when field is cleared
        if (query === '') {
            hasShownInitialList = false;
        }
        
        clearTimeout(searchTimeout);
        
        // Show all results if empty, or search if has query
        searchTimeout = setTimeout(() => {
            searchResidents(query);
        }, query.length >= 2 ? 300 : 100);
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            hideDropdown();
        }
    });
    
    // Clear selection when input is manually changed
    searchInput.addEventListener('keydown', function() {
        hiddenNameInput.value = '';
        hiddenIdInput.value = '';
    });
}

function searchResidents(query) {
    const dropdown = document.getElementById('resident_dropdown');
    
    fetch(`{{ route('inventory.residents.search') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(residents => {
            displayResidents(residents, query);
        })
        .catch(error => {
            console.error('Error searching residents:', error);
            dropdown.innerHTML = '<div class="dropdown-item text-danger">Error searching residents</div>';
            showDropdown();
        });
}

function displayResidents(residents, query) {
    const dropdown = document.getElementById('resident_dropdown');
    
    if (residents.length === 0) {
        if (query.length >= 2) {
            dropdown.innerHTML = `
                <div class="dropdown-item" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-person-plus me-3 text-success" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4;">
                            <strong style="color: #333;">No residents found</strong><br>
                            <small class="text-muted">Click to add "${query}" as new resident</small>
                        </div>
                    </div>
                </div>
            `;
            
            // Add click handler for new resident
            dropdown.querySelector('.dropdown-item').addEventListener('click', function() {
                addNewResident(query);
            });
        } else {
            dropdown.innerHTML = `
                <div class="dropdown-item" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle me-3 text-info" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4;">
                            <strong style="color: #333;">No residents found</strong><br>
                            <small class="text-muted">Start typing to search for residents</small>
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        let html = '';
        
        residents.forEach(resident => {
            html += `
                <div class="dropdown-item" data-resident-id="${resident.id}" data-resident-name="${resident.name}" data-resident-email="${resident.email}" data-resident-username="${resident.username}" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-person me-3 text-primary" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4; flex: 1;">
                            <strong style="color: #333;">${resident.name}</strong><br>
                            <small class="text-muted">${resident.email || '@' + resident.username || 'No contact info'}</small>
                            ${resident.location ? `<br><small class="text-muted">${resident.location}</small>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Add option to create new resident (only if query is provided)
        if (query.length >= 2) {
            html += `
                <div class="dropdown-divider"></div>
                <div class="dropdown-item" data-new-resident="${query}" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-person-plus me-3 text-success" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4; flex: 1;">
                            <strong style="color: #333;">Add "${query}" as new resident</strong><br>
                            <small class="text-muted">Register this person for the first time</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        dropdown.innerHTML = html;
        
        // Add click handlers
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.dataset.newResident) {
                    addNewResident(this.dataset.newResident);
                } else {
                    selectResident(this.dataset.residentId, this.dataset.residentName, this.dataset.residentEmail, this.dataset.residentUsername);
                }
            });
        });
    }
    
    showDropdown();
}

function selectResident(id, name, email, username) {
    // Display name with email or username
    const displayText = email ? `${name} (${email})` : (username ? `${name} (@${username})` : name);
    document.getElementById('resident_search').value = displayText;
    document.getElementById('resident_name').value = name;
    document.getElementById('resident_id').value = id;
    hideDropdown();
}

function addNewResident(name) {
    hideDropdown();
    openNewResidentModal(name);
}

function showDropdown() {
    document.getElementById('resident_dropdown').style.display = 'block';
}

function hideDropdown() {
    document.getElementById('resident_dropdown').style.display = 'none';
}

// Personnel Search Functionality
function initializePersonnelSearch() {
    const searchInput = document.getElementById('personnel_search');
    const dropdown = document.getElementById('personnel_dropdown');
    const hiddenNameInput = document.getElementById('released_by');
    const hiddenIdInput = document.getElementById('personnel_id');
    let searchTimeout;
    let hasShownInitialList = false;
    
    if (!searchInput || !dropdown || !hiddenNameInput) {
        return; // Exit if elements don't exist
    }
    
    // Show all personnel when field is focused/clicked
    searchInput.addEventListener('focus', function() {
        if (!hasShownInitialList && this.value.trim() === '') {
            searchPersonnel('');
            hasShownInitialList = true;
        }
    });
    
    // Search for personnel as user types
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Reset flag when field is cleared
        if (query === '') {
            hasShownInitialList = false;
        }
        
        clearTimeout(searchTimeout);
        
        // Show all results if empty, or search if has query
        searchTimeout = setTimeout(() => {
            searchPersonnel(query);
        }, query.length >= 2 ? 300 : 100);
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            hidePersonnelDropdown();
        }
    });
    
    // Clear selection when input is manually changed
    searchInput.addEventListener('keydown', function() {
        hiddenNameInput.value = '';
        hiddenIdInput.value = '';
    });
}

function searchPersonnel(query) {
    const dropdown = document.getElementById('personnel_dropdown');
    
    fetch(`{{ route('inventory.personnel.search') }}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(personnel => {
            displayPersonnel(personnel, query);
        })
        .catch(error => {
            console.error('Error searching personnel:', error);
            dropdown.innerHTML = '<div class="dropdown-item text-danger">Error searching personnel</div>';
            showPersonnelDropdown();
        });
}

function displayPersonnel(personnel, query) {
    const dropdown = document.getElementById('personnel_dropdown');
    
    if (personnel.length === 0) {
        if (query.length >= 2) {
            dropdown.innerHTML = `
                <div class="dropdown-item" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-person-x me-3 text-warning" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4;">
                            <strong style="color: #333;">No personnel found</strong><br>
                            <small class="text-muted">No matching personnel found for "${query}"</small>
                        </div>
                    </div>
                </div>
            `;
        } else {
            dropdown.innerHTML = `
                <div class="dropdown-item" style="white-space: normal; padding: 12px;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle me-3 text-info" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4;">
                            <strong style="color: #333;">No personnel found</strong><br>
                            <small class="text-muted">No personnel available. Start typing to search.</small>
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        let html = '';
        
        personnel.forEach(person => {
            html += `
                <div class="dropdown-item" data-personnel-id="${person.id}" data-personnel-name="${person.name}" style="white-space: normal; padding: 12px; cursor: pointer;">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-person-badge me-3 text-primary" style="margin-top: 2px;"></i>
                        <div style="line-height: 1.4; flex: 1;">
                            <strong style="color: #333;">${person.name}</strong>
                            ${person.position ? `<br><small class="text-muted">${person.position}</small>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        dropdown.innerHTML = html;
        
        // Add click handlers
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                selectPersonnel(this.dataset.personnelId, this.dataset.personnelName);
            });
        });
    }
    
    showPersonnelDropdown();
}

function selectPersonnel(id, name) {
    document.getElementById('personnel_search').value = name;
    document.getElementById('released_by').value = name;
    document.getElementById('personnel_id').value = id;
    hidePersonnelDropdown();
}

function showPersonnelDropdown() {
    const dropdown = document.getElementById('personnel_dropdown');
    if (dropdown) {
        dropdown.style.display = 'block';
    }
}

function hidePersonnelDropdown() {
    const dropdown = document.getElementById('personnel_dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}
</script>
@endsection 