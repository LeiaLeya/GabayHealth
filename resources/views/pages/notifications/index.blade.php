@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">Create Notification</h2>
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

    <div class="row">
        <!-- Create Notification Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="notificationForm" action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- 1. Notification Type -->
                        <div class="mb-4">
                            <label for="notification_type" class="form-label fw-semibold">Notification Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="notification_type" name="notification_type" required>
                                <option value="">Select notification type...</option>
                                <option value="health_alert">🚨 Health Alert</option>
                                <option value="announcement">📢 Announcement</option>
                                <option value="reminder">📝 Reminder</option>
                                <option value="vaccination_update">💉 Vaccination Update</option>
                                <option value="clinic_schedule_update">🏥 Clinic Schedule Update</option>
                            </select>
                        </div>

                        <!-- 2. Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" required maxlength="255">
                        </div>

                        <!-- 3. Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required maxlength="2000"></textarea>
                            <small class="text-muted">Maximum 2000 characters</small>
                        </div>

                        <!-- 4. Target Audience -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-3">Target Audience <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_all" value="all_residents" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_all">
                                            <strong>All residents</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_purok" value="specific_purok" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_purok">
                                            <strong>Specific purok</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_age_group" value="specific_age_group" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_age_group">
                                            <strong>Specific age group</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_pregnant" value="pregnant_women" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_pregnant">
                                            <strong>Pregnant women</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_senior" value="senior_citizens" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_senior">
                                            <strong>Senior citizens</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" name="target_audience" id="target_children" value="children_0_12" required>
                                        <label class="form-check-label flex-grow-1 mb-0" for="target_children">
                                            <strong>Children (0-12)</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conditional Fields -->
                        <div id="purokField" class="mb-4" style="display: none;">
                            <label for="target_purok" class="form-label fw-semibold">Purok Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="target_purok" name="target_purok" placeholder="e.g., Purok Sunflower">
                        </div>

                        <div id="ageGroupField" class="mb-4" style="display: none;">
                            <label for="target_age_group" class="form-label fw-semibold">Age Group <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="target_age_group" name="target_age_group" placeholder="e.g., 18-30, 31-50, etc.">
                        </div>

                        <!-- 5. Optional Image -->
                        <div class="mb-4">
                            <label for="image" class="form-label fw-semibold">Attach Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">For posters, schedules, infographics (Max 5MB)</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                            </div>
                        </div>

                        <!-- Send Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="sendBtn" disabled>
                                Send Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification History -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Notification History</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if(count($notifications) > 0)
                        @foreach($notifications as $notification)
                            @php
                                $notificationType = $notification['notification_type'] ?? 'announcement';
                                $typeLabels = [
                                    'health_alert' => '🚨 Health Alert',
                                    'announcement' => '📢 Announcement',
                                    'reminder' => '📝 Reminder',
                                    'vaccination_update' => '💉 Vaccination Update',
                                    'clinic_schedule_update' => '🏥 Clinic Schedule Update',
                                ];
                                $status = $notification['status'] ?? 'sent';
                                $createdAt = $notification['createdAt'] ?? '';
                                $formattedDate = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y h:i A') : 'Unknown';
                            @endphp
                            <div class="border-bottom pb-3 mb-3 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 fw-bold">{{ $notification['title'] ?? 'Untitled' }}</h6>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" type="button" id="dropdownMenuButton{{ $notification['id'] }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $notification['id'] }}">
                                            <li>
                                                <form action="{{ route('notifications.destroy', $notification['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-muted small mb-2">{{ $typeLabels[$notificationType] ?? 'Notification' }}</p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $formattedDate }}
                                    </small>
                                    <span class="badge bg-{{ $status === 'sent' ? 'success' : 'warning' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>
                                @if($status === 'scheduled')
                                    <div class="mb-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editNotification('{{ $notification['id'] }}')">Edit</button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No notifications sent yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Health Alert Confirmation Modal -->
<div class="modal fade" id="healthAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Confirm Health Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold">Send Health Alert to all residents of <strong>{{ session('user.name', 'Barangay') }}</strong>?</p>
                <p class="text-muted small">This notification will be sent to all registered residents. Please ensure the information is accurate and important.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmSendBtn">Send</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('notificationForm');
    const sendBtn = document.getElementById('sendBtn');
    const notificationType = document.getElementById('notification_type');
    const targetAudience = document.querySelectorAll('input[name="target_audience"]');
    const purokField = document.getElementById('purokField');
    const ageGroupField = document.getElementById('ageGroupField');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    // Show/hide conditional fields based on target audience
    targetAudience.forEach(radio => {
        radio.addEventListener('change', function() {
            purokField.style.display = this.value === 'specific_purok' ? 'block' : 'none';
            ageGroupField.style.display = this.value === 'specific_age_group' ? 'block' : 'none';
            
            if (this.value === 'specific_purok') {
                document.getElementById('target_purok').required = true;
                document.getElementById('target_age_group').required = false;
            } else if (this.value === 'specific_age_group') {
                document.getElementById('target_age_group').required = true;
                document.getElementById('target_purok').required = false;
            } else {
                document.getElementById('target_purok').required = false;
                document.getElementById('target_age_group').required = false;
            }
            validateForm();
        });
    });

    // Image preview
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Validate form and enable/disable send button
    function validateForm() {
        const type = notificationType.value;
        const title = document.getElementById('title').value.trim();
        const message = document.getElementById('message').value.trim();
        const audience = document.querySelector('input[name="target_audience"]:checked');
        
        let isValid = type && title && message && audience;
        
        if (audience && audience.value === 'specific_purok') {
            isValid = isValid && document.getElementById('target_purok').value.trim();
        }
        if (audience && audience.value === 'specific_age_group') {
            isValid = isValid && document.getElementById('target_age_group').value.trim();
        }
        
        sendBtn.disabled = !isValid;
    }

    // Add event listeners for validation
    notificationType.addEventListener('change', validateForm);
    document.getElementById('title').addEventListener('input', validateForm);
    document.getElementById('message').addEventListener('input', validateForm);
    targetAudience.forEach(radio => radio.addEventListener('change', validateForm));
    document.getElementById('target_purok').addEventListener('input', validateForm);
    document.getElementById('target_age_group').addEventListener('input', validateForm);

    // Handle form submission with Health Alert confirmation
    form.addEventListener('submit', function(e) {
        const type = notificationType.value;
        
        if (type === 'health_alert') {
            e.preventDefault();
            const healthAlertModal = new bootstrap.Modal(document.getElementById('healthAlertModal'));
            healthAlertModal.show();
            
            document.getElementById('confirmSendBtn').onclick = function() {
                healthAlertModal.hide();
                form.submit();
            };
        }
    });
});

function editNotification(id) {
    // TODO: Implement edit functionality for scheduled notifications
    alert('Edit functionality coming soon!');
}
</script>

<style>
.card {
    border-radius: 12px;
    border: 1px solid #e0e0e0;
}

.form-select-lg, .form-control-lg {
    font-size: 1rem;
    padding: 0.75rem 1rem;
}

.target-audience-option {
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: #fff;
}

.target-audience-option:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.target-audience-option .form-check-input:checked ~ .form-check-label,
.target-audience-option:has(.form-check-input:checked) {
    background-color: #e7f1ff;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.target-audience-option .form-check-input {
    cursor: pointer;
    flex-shrink: 0;
    margin: 0;
}

.target-audience-option .form-check-label {
    cursor: pointer;
    margin: 0;
}

.target-audience-option .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endsection
