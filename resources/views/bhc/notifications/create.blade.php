@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="fw-bold text-dark mb-0">Send notification</h2>
    </div>
    <p class="text-muted mb-3">Compose a message to residents. This does not affect your inbox.</p>

    @include('notifications._subnav', ['prefix' => 'bhc', 'active' => 'create'])

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
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="notificationForm" action="{{ route('notifications.store') }}" method="POST">
                        @include('notifications._compose_fields')
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle me-1"></i>Tip</h6>
                    <p class="small text-muted mb-3">Incoming alerts (such as registration requests) appear under <strong>Inbox</strong>, not here.</p>
                    <a href="{{ route('bhc.notifications.sent') }}" class="btn btn-outline-primary btn-sm w-100">View sent history</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="healthAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Confirm Health Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold">Send Health Alert to all residents of <strong>{{ session('user.name', 'Barangay') }}</strong>?</p>
                <p class="text-muted small">Please ensure the information is accurate and important.</p>
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

    targetAudience.forEach(radio => {
        radio.addEventListener('change', function() {
            purokField.style.display = this.value === 'specific_purok' ? 'block' : 'none';
            ageGroupField.style.display = this.value === 'specific_age_group' ? 'block' : 'none';
            if (this.value === 'specific_purok') {
                document.getElementById('target_purok').required = true;
                document.getElementById('target_age_group_input').required = false;
            } else if (this.value === 'specific_age_group') {
                document.getElementById('target_age_group_input').required = true;
                document.getElementById('target_purok').required = false;
            } else {
                document.getElementById('target_purok').required = false;
                document.getElementById('target_age_group_input').required = false;
            }
            validateForm();
        });
    });

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
            isValid = isValid && document.getElementById('target_age_group_input').value.trim();
        }
        sendBtn.disabled = !isValid;
    }

    notificationType.addEventListener('change', validateForm);
    document.getElementById('title').addEventListener('input', validateForm);
    document.getElementById('message').addEventListener('input', validateForm);
    targetAudience.forEach(radio => radio.addEventListener('change', validateForm));
    document.getElementById('target_purok').addEventListener('input', validateForm);
    document.getElementById('target_age_group_input').addEventListener('input', validateForm);

    const checked = document.querySelector('input[name="target_audience"]:checked');
    if (checked) {
        checked.dispatchEvent(new Event('change'));
    }
    validateForm();

    form.addEventListener('submit', function(e) {
        if (notificationType.value === 'health_alert') {
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
</script>

<style>
.target-audience-option { cursor: pointer; transition: all 0.2s ease; background-color: #fff; }
.target-audience-option:hover { background-color: #f8f9fa; border-color: #0d6efd !important; }
.target-audience-option:has(.form-check-input:checked) {
    background-color: #e7f1ff; border-color: #0d6efd !important;
}
.target-audience-option .form-check-input { cursor: pointer; flex-shrink: 0; margin: 0; }
.target-audience-option .form-check-label { cursor: pointer; margin: 0; }
</style>
@endsection
