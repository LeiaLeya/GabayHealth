@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="mb-4">
        <a href="{{ route('admin.system-admin.dashboard') }}"
           class="d-inline-flex align-items-center gap-1 text-muted small text-decoration-none mb-2"
           style="transition: color 0.2s;"
           onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color=''">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <h4 class="fw-bold mb-1" style="color: #1a1a2e;">RHU Application Details</h4>
    </div>

    <div class="row g-4">

        <!-- Left Column: RHU Info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header border-0 py-3 px-4"
                     style="background: linear-gradient(135deg, #1657c1, #2563eb);">
                    <div class="d-flex align-items-center gap-2 text-white">
                        <i class="bi bi-hospital-fill"></i>
                        <h6 class="mb-0 fw-semibold">Rural Health Unit Information</h6>
                    </div>
                </div>
                <div class="card-body p-4">

                    <!-- Logo + Basic Info -->
                    <div class="d-flex gap-4 mb-4">
                        @if($rhu['logo_url'] ?? false)
                            <img src="{{ $rhu['logo_url'] }}" alt="RHU Logo"
                                 class="rounded flex-shrink-0"
                                 style="width: 110px; height: 110px; object-fit: cover;">
                        @else
                            <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 110px; height: 110px; background: #dbeafe;">
                                <i class="bi bi-hospital" style="font-size: 2.8rem; color: #2563eb;"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2" style="color: #1a1a2e;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</h5>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @php
                                    $status = $rhu['status'] ?? 'pending';
                                    $badgeStyle = match($status) {
                                        'pending'          => 'background:#fef3c7; color:#92400e;',
                                        'credentials_sent' => 'background:#d1fae5; color:#065f46;',
                                        'active'           => 'background:#dbeafe; color:#1e40af;',
                                        'rejected'         => 'background:#fee2e2; color:#991b1b;',
                                        default            => 'background:#f3f4f6; color:#374151;',
                                    };
                                    $badgeLabel = match($status) {
                                        'pending'          => 'Pending',
                                        'credentials_sent' => 'Credentials Sent',
                                        'active'           => 'Active',
                                        'rejected'         => 'Rejected',
                                        default            => ucfirst($status),
                                    };
                                @endphp
                                <span class="badge fw-medium px-2 py-1"
                                      style="{{ $badgeStyle }} border-radius: 6px; font-size: 0.8rem;">
                                    {{ $badgeLabel }}
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <span class="small text-muted">
                                    <i class="bi bi-envelope me-1"></i>
                                    <a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none text-muted">{{ $rhu['email'] }}</a>
                                </span>
                                <span class="small text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    Applied {{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr style="border-color: #f1f5f9;">

                    <!-- Location Details -->
                    <h6 class="fw-semibold mb-3" style="color: #1a1a2e;">
                        <i class="bi bi-geo-alt me-1 text-primary"></i> Location Details
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: #f8fafc;">
                                <div class="text-muted small fw-semibold mb-1">Full Address</div>
                                <div class="small">{{ $rhu['fullAddress'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded" style="background: #f8fafc;">
                                @if(isset($rhu['displayLocation']))
                                    <div class="text-muted small fw-semibold mb-1">Location</div>
                                    <div class="small">{{ $rhu['displayLocation'] }}</div>
                                @else
                                    <div class="text-muted small fw-semibold mb-1">Region / Province / City</div>
                                    <div class="small">
                                        {{ $rhu['region'] ?? 'N/A' }}<br>
                                        {{ $rhu['province'] ?? 'N/A' }}<br>
                                        {{ $rhu['city'] ?? 'N/A' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(($rhu['latitude'] ?? false) && ($rhu['longitude'] ?? false))
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <i class="bi bi-pin-map text-primary"></i>
                            <span class="small text-muted">{{ $rhu['latitude'] }}, {{ $rhu['longitude'] }}</span>
                            <a href="https://maps.google.com/?q={{ $rhu['latitude'] }},{{ $rhu['longitude'] }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-secondary"
                               style="border-radius: 6px; font-size: 0.75rem;">
                                <i class="bi bi-box-arrow-up-right me-1"></i> View on Map
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Actions -->
        <div class="col-lg-4">
            @if($status === 'pending')
                <!-- Approve Card -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 py-3 px-4"
                         style="background: linear-gradient(135deg, #059669, #10b981);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-check-circle-fill"></i>
                            <h6 class="mb-0 fw-semibold">Approve Application</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">
                            Approving will generate login credentials and send them to the RHU's registered email address.
                        </p>
                        <button type="button" class="btn btn-success w-100" id="approveBtn" style="border-radius: 8px;">
                            <i class="bi bi-check me-1"></i> Generate & Send Credentials
                        </button>
                    </div>
                </div>

                <!-- Reject Card -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 py-3 px-4"
                         style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-x-circle-fill"></i>
                            <h6 class="mb-0 fw-semibold">Reject Application</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label small fw-semibold text-muted">Reason for rejection</label>
                            <textarea id="rejectReason" class="form-control" rows="3"
                                      placeholder="Enter rejection reason..."
                                      style="border-radius: 8px; font-size: 0.875rem; resize: none;"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger w-100" id="rejectBtn" style="border-radius: 8px;">
                            <i class="bi bi-x me-1"></i> Reject Application
                        </button>
                    </div>
                </div>

            @elseif($status === 'credentials_sent')
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 py-3 px-4"
                         style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-envelope-check-fill"></i>
                            <h6 class="mb-0 fw-semibold">Credentials Sent</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 rounded mb-3" style="background:#f8fafc;">
                            <div class="text-muted small fw-semibold mb-1">Sent on</div>
                            <div class="small">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="p-3 rounded mb-3" style="background:#f8fafc;">
                            <div class="text-muted small fw-semibold mb-1">Username</div>
                            <code class="small" style="color:#1a1a2e;">{{ $rhu['username'] ?? 'N/A' }}</code>
                        </div>
                        <button type="button" class="btn btn-primary w-100" id="resendBtn" style="border-radius: 8px;">
                            <i class="bi bi-arrow-repeat me-1"></i> Resend Credentials
                        </button>
                    </div>
                </div>

            @elseif($status === 'rejected')
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 py-3 px-4"
                         style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                        <div class="d-flex align-items-center gap-2 text-white">
                            <i class="bi bi-x-circle-fill"></i>
                            <h6 class="mb-0 fw-semibold">Application Rejected</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 rounded mb-3" style="background:#fff5f5;">
                            <div class="text-muted small fw-semibold mb-1">Rejection Reason</div>
                            <div class="small">{{ $rhu['rejection_reason'] ?? 'No reason provided.' }}</div>
                        </div>
                        @if($rhu['rejected_at'] ?? false)
                            <div class="text-muted small">
                                <i class="bi bi-calendar me-1"></i>
                                Rejected on {{ \Carbon\Carbon::parse($rhu['rejected_at'])->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #059669; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span id="successMsg">Done.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #dc2626; border-radius: 10px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="errorMsg">Something went wrong.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mt-2"
                     style="width: 56px; height: 56px; background: #d1fae5;">
                    <i class="bi bi-check-circle-fill" style="font-size: 1.8rem; color: #059669;"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <h5 class="fw-bold mb-1">Approve Application?</h5>
                <p class="text-muted small mb-0">Credentials will be generated and sent to the RHU's email address.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="confirmApproveBtn" style="border-radius: 8px;">
                    <i class="bi bi-check me-1"></i> Yes, Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Confirmation Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mt-2"
                     style="width: 56px; height: 56px; background: #fee2e2;">
                    <i class="bi bi-x-circle-fill" style="font-size: 1.8rem; color: #dc2626;"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <h5 class="fw-bold mb-1">Reject Application?</h5>
                <p class="text-muted small mb-0">This action cannot be undone. The applicant will not be able to re-apply with the same account.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmRejectBtn" style="border-radius: 8px;">
                    <i class="bi bi-x me-1"></i> Yes, Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resend Confirmation Modal -->
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mt-2"
                     style="width: 56px; height: 56px; background: #dbeafe;">
                    <i class="bi bi-arrow-repeat" style="font-size: 1.8rem; color: #2563eb;"></i>
                </div>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <h5 class="fw-bold mb-1">Resend Credentials?</h5>
                <p class="text-muted small mb-0">The login credentials will be re-sent to the RHU's registered email address.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmResendBtn" style="border-radius: 8px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Yes, Resend
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rhuId   = '{{ $rhu['id'] }}';
    const csrf    = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(type, message) {
        if (type === 'success') {
            document.getElementById('successMsg').textContent = message;
            new bootstrap.Toast(document.getElementById('successToast'), { delay: 5000 }).show();
        } else {
            document.getElementById('errorMsg').textContent = message;
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 5000 }).show();
        }
    }

    // ── Approve ──
    const approveBtn = document.getElementById('approveBtn');
    const approveModal = document.getElementById('approveModal')
        ? new bootstrap.Modal(document.getElementById('approveModal')) : null;

    if (approveBtn && approveModal) {
        approveBtn.addEventListener('click', () => approveModal.show());

        document.getElementById('confirmApproveBtn').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            fetch(`/admin/system-admin/${rhuId}/approve`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                approveModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check me-1"></i> Yes, Approve';

                if (data.success) {
                    showToast('success', `Approved! Username: ${data.username}. Setup email sent to ${data.email}.`);
                    setTimeout(() => location.reload(), 2500);
                } else {
                    showToast('error', data.error || 'Failed to approve.');
                }
            })
            .catch(() => {
                approveModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check me-1"></i> Yes, Approve';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }

    // ── Reject ──
    const rejectBtn = document.getElementById('rejectBtn');
    const rejectModal = document.getElementById('rejectModal')
        ? new bootstrap.Modal(document.getElementById('rejectModal')) : null;

    if (rejectBtn && rejectModal) {
        rejectBtn.addEventListener('click', function () {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
                document.getElementById('rejectReason').classList.add('is-invalid');
                document.getElementById('rejectReason').focus();
                return;
            }
            document.getElementById('rejectReason').classList.remove('is-invalid');
            rejectModal.show();
        });

        document.getElementById('confirmRejectBtn').addEventListener('click', function () {
            const reason = document.getElementById('rejectReason').value.trim();
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Rejecting...';

            fetch(`/admin/system-admin/${rhuId}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason }),
            })
            .then(r => r.json())
            .then(data => {
                rejectModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x me-1"></i> Yes, Reject';

                if (data.success) {
                    showToast('success', 'Application rejected.');
                    setTimeout(() => window.location.href = '{{ route("admin.system-admin.dashboard") }}', 1800);
                } else {
                    showToast('error', data.error || 'Failed to reject.');
                }
            })
            .catch(() => {
                rejectModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x me-1"></i> Yes, Reject';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }

    // ── Resend ──
    const resendBtn = document.getElementById('resendBtn');
    const resendModal = document.getElementById('resendModal')
        ? new bootstrap.Modal(document.getElementById('resendModal')) : null;

    if (resendBtn && resendModal) {
        resendBtn.addEventListener('click', () => resendModal.show());

        document.getElementById('confirmResendBtn').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            fetch(`/admin/system-admin/${rhuId}/resend-credentials`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                resendModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';

                if (data.success) {
                    showToast('success', 'Credentials resent to RHU email.');
                } else {
                    showToast('error', data.error || 'Failed to resend.');
                }
            })
            .catch(() => {
                resendModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }
});
</script>
@endsection
