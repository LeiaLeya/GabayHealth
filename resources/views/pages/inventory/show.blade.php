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
                                        <small class="text-muted">Batch: {{ $batch['batch_number'] }}</small>
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
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Batch
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
                                    <label for="batch_number_{{ $batch['id'] }}" class="form-label fw-semibold">Batch Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="batch_number_{{ $batch['id'] }}" name="batch_number" value="{{ $batch['batch_number'] }}" required readonly>
                                    <small class="text-muted">Auto-generated batch numbers cannot be edited</small>
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
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Batch
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
});

// Resident Search Functionality
function initializeResidentSearch() {
    const searchInput = document.getElementById('resident_search');
    const dropdown = document.getElementById('resident_dropdown');
    const hiddenNameInput = document.getElementById('resident_name');
    const hiddenIdInput = document.getElementById('resident_id');
    let searchTimeout;
    
    // Search for residents as user types
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideDropdown();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchResidents(query);
        }, 300);
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
        
        // Add option to create new resident
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
    // For now, just set the name directly
    // In the future, this could open a modal for additional details
    document.getElementById('resident_search').value = name;
    document.getElementById('resident_name').value = name;
    document.getElementById('resident_id').value = ''; // Empty ID indicates new resident
    hideDropdown();
}

function showDropdown() {
    document.getElementById('resident_dropdown').style.display = 'block';
}

function hideDropdown() {
    document.getElementById('resident_dropdown').style.display = 'none';
}
</script>
@endsection 