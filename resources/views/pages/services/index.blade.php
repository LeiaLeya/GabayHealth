@extends('layouts.app')

@push('styles')
<style>
    .service-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
        height: 100%;
    }
    
    .service-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
    
    .service-card.suspended {
        opacity: 0.7;
        border-color: #dc3545;
    }
    
    .service-card.suspended .service-card-header {
        background: #6c757d;
    }
    
    .service-card-header {
        background:rgb(14, 66, 122);
        color: white;
        padding: 20px;
        position: relative;
    }
    
    .service-card-body {
        padding: 20px;
    }
    
    .service-category {
        background: rgb(14, 66, 122);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .predefined-service-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin: 8px 0;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #333;
    }
    
    .predefined-service-item:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #333;
        transform: translateX(5px);
    }
    
    .predefined-service-item.selected {
        background: #007bff;
        color: white;
        border-color: #0056b3;
    }
    
    .predefined-service-item.selected-disabled {
        background: #e9ecef;
        border-color: #ced4da;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .predefined-service-item.selected-disabled:hover {
        background: #e9ecef;
        border-color: #ced4da;
        color: #6c757d;
        transform: none;
    }
    
    .custom-service-form {
        background: #fff;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 25px;
        margin-top: 25px;
    }
    
    .custom-service-form:hover {
        border-color: #007bff;
    }
    
    .schedule-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }
    
    .day-schedule {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        margin: 8px 0;
    }
    
    .time-slot {
        background: #e3f2fd;
        border: 1px solid #2196f3;
        border-radius: 4px;
        padding: 6px 12px;
        margin: 4px;
        display: inline-block;
        font-size: 12px;
        color: #1976d2;
    }
    

    
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        margin-top: 15px;
    }
    
    .action-btn {
        padding: 8px 12px;
        border-radius: 6px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 12px;
        color: white;
        cursor: pointer;
        min-width: 32px;
    }
    
    .edit-btn {
        background-color: #0d6efd;
    }
    
    .edit-btn:hover {
        background-color: #0b5ed7;
        transform: scale(1.05);
        color: white;
        text-decoration: none;
    }
    
    .delete-btn {
        background-color: #dc3545;
    }
    
    .delete-btn:hover {
        background-color: #bb2d3b;
        transform: scale(1.05);
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin: 0 8px;
    }
    
    .toggle-input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-label {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #343a40;
        transition: .3s;
        border-radius: 24px;
        margin: 0;
    }
    
    .toggle-label:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    
    .toggle-input:checked + .toggle-label {
        background-color: #007bff;
    }
    
    .toggle-input:checked + .toggle-label:before {
        transform: translateX(20px);
    }
    
    .toggle-label:hover {
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Simple confirmation popup styles */
    .simple-confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .simple-confirm-popup {
        background: white;
        border-radius: 8px;
        padding: 24px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }
    
    .simple-confirm-icon {
        font-size: 48px;
        color: #ffc107;
        margin-bottom: 16px;
    }
    
    .simple-confirm-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .simple-confirm-message {
        font-size: 14px;
        color: #666;
        margin-bottom: 24px;
        line-height: 1.5;
    }
    
    .simple-confirm-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 16px;
    }

    #deactivationReason {
        min-height: 100px;
        font-size: 14px;
        resize: vertical;
    }
    
    .simple-confirm-btn {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .simple-confirm-btn.cancel {
        background: #f8f9fa;
        color: #666;
        border: 1px solid #dee2e6;
    }
    
    .simple-confirm-btn.cancel:hover {
        background: #e9ecef;
    }
    
    .simple-confirm-btn.confirm {
        background: #dc3545;
        color: white;
    }
    
    .simple-confirm-btn.confirm:hover {
        background: #c82333;
    }
    
    .badge-suspended {
        background-color: #ffc107;
        color: #212529;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
    }
    
    .schedule-badge {
        background: #e8f5e8;
        color: #2e7d32;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        margin: 2px;
        display: inline-block;
    }
    
    .time-input-group {
        display: flex;
        gap: 10px;
        align-items: center;
        margin: 5px 0;
    }
    
    .add-time-btn {
        background: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        cursor: pointer;
    }
    
    .remove-time-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Services Management</h2>
                    <p class="text-muted">Manage your health center services and schedules</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Predefined Services Section -->
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-check me-2"></i>
                                Available Services
                                <span class="badge bg-secondary ms-2">{{ count($currentServices) }} selected</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Select from predefined services or add custom ones:</p>
                            <div class="alert alert-info alert-sm mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Services with a green checkmark are already added and cannot be selected again.</small>
                            </div>
                            
                            @foreach($predefinedServices as $category => $services)
                                <div class="service-category">
                                    <h6 class="mb-2">{{ $category }}</h6>
                                    @foreach($services as $key => $service)
                                        @php
                                            $isSelected = collect($currentServices)->contains('name', $key);
                                        @endphp
                                        <div class="predefined-service-item {{ $isSelected ? 'selected-disabled' : '' }}" 
                                             onclick="{{ $isSelected ? 'return false;' : "addPredefinedService('$key', '$service', '$category')" }}">
                                            <span>{{ $service }}</span>
                                            @if($isSelected)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-plus-circle"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <!-- Custom Service Form -->
                            <div class="custom-service-form">
                                <h6 class="mb-3">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Add Custom Service
                                </h6>
                                <form action="{{ route('services.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="custom_name" class="form-label">Service Name</label>
                                            <input type="text" class="form-control" id="custom_name" name="name" 
                                                   placeholder="Enter service name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="custom_category" class="form-label">Category</label>
                                            <select class="form-select" id="custom_category" name="category" required>
                                                <option value="">Select category</option>
                                                @foreach(array_keys($predefinedServices) as $category)
                                                    <option value="{{ $category }}">{{ $category }}</option>
                                                @endforeach
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="custom_description" class="form-label">Description (Optional)</label>
                                        <textarea class="form-control" id="custom_description" name="description" 
                                                  rows="3" placeholder="Describe the service..."></textarea>
                                    </div>
                                    
                                    <!-- Schedule Section -->
                                    <div class="schedule-section">
                                        <h6 class="mb-3">
                                            <i class="bi bi-calendar3 me-2"></i>
                                            Service Schedule
                                        </h6>
                                        <div class="row">
                                            @php
                                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            @endphp
                                            @foreach($days as $day)
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label text-capitalize">{{ ucfirst($day) }}</label>
                                                    <div class="day-schedule">
                                                        <div class="time-slots" id="time-slots-{{ $day }}">
                                                            <div class="time-input-group">
                                                                <input type="text" class="form-control form-control-sm" 
                                                                       name="schedule[{{ $day }}][]" 
                                                                       placeholder="e.g., 9AM-11AM">
                                                                <button type="button" class="add-time-btn" 
                                                                        onclick="addTimeSlot('{{ $day }}')">
                                                                    <i class="bi bi-plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="is_custom" value="true">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus me-2"></i>Add Custom Service
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Services Section -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>
                                Current Services
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($currentServices) > 0)
                                <div class="service-grid">
                                    @foreach($currentServices as $service)
                                        <div class="service-card {{ !($service['is_active'] ?? true) ? 'suspended' : '' }}">
                                            <div class="service-card-header">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            {{ $service['display_name'] ?? $service['name'] }}
                                                            @if(!($service['is_active'] ?? true))
                                                                <span class="badge badge-suspended ms-2">
                                                                    <i class="bi bi-pause-circle me-1"></i>Suspended
                                                                </span>
                                                            @endif
                                                        </h6>
                                                        @if(!($service['is_active'] ?? true) && !empty($service['deactivation_reason'] ?? ''))
                                                            <div class="text-warning small mb-2">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Reason: {{ $service['deactivation_reason'] }}
                                                            </div>
                                                        @endif
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <span class="badge bg-light text-dark">{{ $service['category'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="toggle-switch me-2" title="{{ ($service['is_active'] ?? true) ? 'Disable Service' : 'Enable Service' }}">
                                                            <input type="checkbox" 
                                                                   id="toggle-{{ $service['id'] }}" 
                                                                   class="toggle-input"
                                                                   {{ ($service['is_active'] ?? true) ? 'checked' : '' }}
                                                                   onchange="handleToggleChange('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}', this.checked)">
                                                            <label for="toggle-{{ $service['id'] }}" class="toggle-label">
                                                                <span class="toggle-inner"></span>
                                                                <span class="toggle-switch-slider"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="service-card-body">
                                                @if($service['description'])
                                                    <p class="text-muted mb-3">{{ $service['description'] }}</p>
                                                @endif
                                                
                                                @if(isset($service['schedule']) && !empty($service['schedule']))
                                                    <div class="mb-3">
                                                        <h6 class="text-dark mb-2">
                                                            <i class="bi bi-clock me-2"></i>Schedule
                                                        </h6>
                                                        @foreach($service['schedule'] as $day => $times)
                                                            @if(!empty($times))
                                                                <div class="mb-2">
                                                                    <strong class="text-capitalize">{{ ucfirst($day) }}:</strong>
                                                                    @foreach($times as $time)
                                                                        <span class="schedule-badge">{{ $time }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="mb-3">
                                                        <span class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>No schedule set
                                                        </span>
                                                    </div>
                                                @endif
                                                
                                                <div class="action-buttons">
                                                    <button type="button" 
                                                            class="action-btn edit-btn" 
                                                            onclick="editService('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}', '{{ $service['category'] }}', '{{ $service['description'] ?? '' }}', {{ json_encode($service['schedule'] ?? []) }})"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="action-btn delete-btn" 
                                                            onclick="deleteService('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}')"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bi bi-clipboard-x"></i>
                                    <h5>No Services Added Yet</h5>
                                    <p>Start by selecting predefined services or adding custom ones.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editServiceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-select" id="edit_category" name="category" required>
                                @foreach(array_keys($predefinedServices) as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <!-- Edit Schedule Section -->
                    <div class="schedule-section">
                        <h6 class="mb-3">
                            <i class="bi bi-calendar3 me-2"></i>
                            Service Schedule
                        </h6>
                        <div class="row">
                            @php
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            @endphp
                            @foreach($days as $day)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-capitalize">{{ ucfirst($day) }}</label>
                                    <div class="day-schedule">
                                        <div class="time-slots" id="edit-time-slots-{{ $day }}">
                                            <div class="time-input-group">
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="schedule[{{ $day }}][]" 
                                                       placeholder="e.g., 9AM-11AM">
                                                <button type="button" class="add-time-btn" 
                                                        onclick="addEditTimeSlot('{{ $day }}')">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="serviceName"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteServiceForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Service</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Simple Confirmation Popup -->
<div id="simpleConfirmOverlay" class="simple-confirm-overlay">
    <div class="simple-confirm-popup">
        <div class="simple-confirm-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="simple-confirm-title" id="confirmTitle">Disable Service?</div>
        <div class="simple-confirm-message" id="confirmMessage">
            Are you sure you want to disable this service? It will no longer be available to users.
        </div>
        <div style="margin-top:10px; text-align:left;">
            <label for="deactivationReason" style="display:block; font-weight:600; margin-bottom:6px;">Reason for disabling</label>
            <textarea id="deactivationReason" class="form-control" rows="2" placeholder="Enter reason (shown to users)"></textarea>
        </div>
        <div class="simple-confirm-buttons">
            <button class="simple-confirm-btn cancel" onclick="hideConfirmPopup()">Cancel</button>
            <button class="simple-confirm-btn confirm" id="confirmButton" onclick="confirmToggle()">Disable</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add predefined service
    function addPredefinedService(serviceKey, serviceName, category) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("services.store") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = serviceKey;
        
        const displayNameInput = document.createElement('input');
        displayNameInput.type = 'hidden';
        displayNameInput.name = 'display_name';
        displayNameInput.value = serviceName;
        
        const categoryInput = document.createElement('input');
        categoryInput.type = 'hidden';
        categoryInput.name = 'category';
        categoryInput.value = category;
        
        const isCustomInput = document.createElement('input');
        isCustomInput.type = 'hidden';
        isCustomInput.name = 'is_custom';
        isCustomInput.value = 'false';
        
        form.appendChild(csrfToken);
        form.appendChild(nameInput);
        form.appendChild(displayNameInput);
        form.appendChild(categoryInput);
        form.appendChild(isCustomInput);
        
        document.body.appendChild(form);
        form.submit();
    }
    
    // Add time slot for custom service form
    function addTimeSlot(day) {
        const container = document.getElementById(`time-slots-${day}`);
        const timeGroup = document.createElement('div');
        timeGroup.className = 'time-input-group';
        timeGroup.innerHTML = `
            <input type="text" class="form-control form-control-sm" 
                   name="schedule[${day}][]" 
                   placeholder="e.g., 9AM-11AM">
            <button type="button" class="remove-time-btn" 
                    onclick="removeTimeSlot(this)">
                <i class="bi bi-dash"></i>
            </button>
        `;
        container.appendChild(timeGroup);
    }
    
    // Add time slot for edit modal
    function addEditTimeSlot(day) {
        const container = document.getElementById(`edit-time-slots-${day}`);
        const timeGroup = document.createElement('div');
        timeGroup.className = 'time-input-group';
        timeGroup.innerHTML = `
            <input type="text" class="form-control form-control-sm" 
                   name="schedule[${day}][]" 
                   placeholder="e.g., 9AM-11AM">
            <button type="button" class="add-time-btn" 
                    onclick="addEditTimeSlot('${day}')">
                <i class="bi bi-plus"></i>
            </button>
        `;
        container.appendChild(timeGroup);
    }
    
    // Remove time slot
    function removeTimeSlot(button) {
        button.parentElement.remove();
    }
    
    // Edit service
    function editService(id, name, category, description, schedule) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_description').value = description;
        document.getElementById('editServiceForm').action = `/services/${id}`;
        
        // Clear existing time slots
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(day => {
            const container = document.getElementById(`edit-time-slots-${day}`);
            container.innerHTML = `
                <div class="time-input-group">
                    <input type="text" class="form-control form-control-sm" 
                           name="schedule[${day}][]" 
                           placeholder="e.g., 9AM-11AM">
                    <button type="button" class="add-time-btn" 
                            onclick="addEditTimeSlot('${day}')">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            `;
        });
        
        // Populate schedule data
        if (schedule && typeof schedule === 'object') {
            days.forEach(day => {
                const container = document.getElementById(`edit-time-slots-${day}`);
                container.innerHTML = ''; // Clear the container first
                
                if (schedule[day] && Array.isArray(schedule[day]) && schedule[day].length > 0) {
                    // If this day has existing schedule data, populate it
                    schedule[day].forEach((time, index) => {
                        const timeGroup = document.createElement('div');
                        timeGroup.className = 'time-input-group';
                        timeGroup.innerHTML = `
                            <input type="text" class="form-control form-control-sm" 
                                   name="schedule[${day}][]" 
                                   value="${time}"
                                   placeholder="e.g., 9AM-11AM">
                            <button type="button" class="${index === 0 ? 'add-time-btn' : 'remove-time-btn'}" 
                                    onclick="${index === 0 ? `addEditTimeSlot('${day}')` : 'removeTimeSlot(this)'}">
                                <i class="bi bi-${index === 0 ? 'plus' : 'dash'}"></i>
                            </button>
                        `;
                        container.appendChild(timeGroup);
                    });
                } else {
                    // If this day has no existing data, add one empty input with add button
                    const timeGroup = document.createElement('div');
                    timeGroup.className = 'time-input-group';
                    timeGroup.innerHTML = `
                        <input type="text" class="form-control form-control-sm" 
                               name="schedule[${day}][]" 
                               placeholder="e.g., 9AM-11AM">
                        <button type="button" class="add-time-btn" 
                                onclick="addEditTimeSlot('${day}')">
                            <i class="bi bi-plus"></i>
                        </button>
                    `;
                    container.appendChild(timeGroup);
                }
            });
        }
        
        const editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
        editModal.show();
    }
    
    // Variables for confirmation popup
    let pendingToggle = null;

    // Handle toggle change
    function handleToggleChange(id, name, isChecked) {
        if (!isChecked) {
            // If turning off (disabling), show confirmation
            const toggle = document.getElementById('toggle-' + id);
            toggle.checked = true; // Reset toggle to on while confirming
            
            pendingToggle = { id, name, isChecked: false };
            showConfirmPopup(name, false);
        } else {
            // If turning on (enabling), no confirmation needed
            toggleServiceStatus(id, name, true);
        }
    }

    // Show confirmation popup
    function showConfirmPopup(serviceName, isEnabling) {
        const overlay = document.getElementById('simpleConfirmOverlay');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmButton');
        
        title.textContent = 'Disable Service?';
        message.innerHTML = `Are you sure you want to disable "<strong>${serviceName}</strong>"?<br>It will no longer be available to users.`;
        confirmBtn.textContent = 'Disable';
        confirmBtn.className = 'simple-confirm-btn confirm';
        
        overlay.style.display = 'flex';
    }

    // Hide confirmation popup
    function hideConfirmPopup() {
        const overlay = document.getElementById('simpleConfirmOverlay');
        overlay.style.display = 'none';
        pendingToggle = null;
    }

    // Confirm toggle action
    function confirmToggle() {
        if (pendingToggle) {
            const { id, name, isChecked } = pendingToggle;
            const reason = document.getElementById('deactivationReason').value.trim();
            toggleServiceStatus(id, name, isChecked, reason);
        }
        hideConfirmPopup();
    }

    // Toggle service status
    function toggleServiceStatus(id, name, newStatus, reason = '') {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/services/${id}/toggle-status`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';

        if (newStatus === false) {
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'deactivation_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);
        }
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Close popup when clicking overlay
    document.getElementById('simpleConfirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            hideConfirmPopup();
        }
    });

    // Delete service
    function deleteService(id, name) {
        document.getElementById('serviceName').textContent = name;
        document.getElementById('deleteServiceForm').action = `/services/${id}`;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteServiceModal'));
        deleteModal.show();
    }
</script>
@endpush 