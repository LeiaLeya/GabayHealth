@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Flash Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert" id="successToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert" id="errorToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    @php
        $status = $rhu['status'] ?? 'pending';
        $badgeClass = match($status) {
            'pending'          => 'brgy-status pending',
            'pending_setup'    => 'brgy-status setup',
            'credentials_sent' => 'brgy-status approved',
            'active'           => 'brgy-status active',
            'rejected'         => 'brgy-status rejected',
            'archived'         => 'brgy-status archived',
            default            => 'brgy-status pending',
        };
        $badgeLabel = match($status) {
            'pending'          => 'Pending',
            'pending_setup'    => 'Processing',
            'credentials_sent' => 'Credentials Sent',
            'active'           => 'Active',
            'rejected'         => 'Rejected',
            'archived'         => 'Archived',
            default            => ucfirst($status),
        };
    @endphp

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'RHU Details' }}</h2>
            <p class="text-muted mb-0 small">Rural Health Unit · Application Details</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($status === 'pending')
            <button type="button" class="btn btn-dark rounded-pill px-4" id="approveBtn" style="font-size:.85rem;">
                <i class="bi bi-check-lg me-1"></i>Approve Application
            </button>
            @elseif($status === 'credentials_sent')
            <button type="button" class="btn-resend-hdr" id="resendBtn">
                <i class="bi bi-arrow-repeat me-1"></i>Resend Credentials
            </button>
            @endif
            @if($status === 'archived')
            <form method="POST" action="{{ route('admin.system-admin.restore', $rhu['id']) }}">
                @csrf
                <button type="submit" class="btn-restore">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore RHU
                </button>
            </form>
            @elseif(!in_array($status, ['pending', 'pending_setup']))
            <button class="btn-archive" onclick="openArchiveConfirm('{{ $rhu['id'] }}','{{ addslashes($rhu['rhuName'] ?? $rhu['name'] ?? '') }}','{{ route('admin.system-admin.archive', $rhu['id']) }}')">
                <i class="bi bi-archive me-1"></i>Archive
            </button>
            @endif
            <a href="{{ route('admin.system-admin.all-rhus') }}" class="btn-back">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12">

            {{-- Identity Card --}}
            <div class="info-card mb-4">
                <div class="info-card-header">
                    <div class="info-badge">Rural Health Unit</div>
                    <div class="info-card-title">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                </div>
                <div class="info-card-body">
                    <div class="d-flex align-items-center gap-4 mb-4">
                        <div class="brgy-logo-zone">
                            @if($rhu['logo_url'] ?? false)
                                <img src="{{ $rhu['logo_url'] }}" alt="{{ $rhu['rhuName'] ?? '' }}"
                                     class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
                            @else
                                <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1rem;color:#37352f;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                            <span class="{{ $badgeClass }} mt-2 d-inline-block">{{ $badgeLabel }}</span>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $rhu['email'] ?? '—' }}</div>
                            </div>
                        </div>
                        @if($rhu['username'] ?? false)
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-person-badge-fill"></i></div>
                            <div>
                                <div class="info-label">Username</div>
                                <div class="info-value"><code style="background:#f1f1ef;padding:2px 6px;border-radius:4px;color:#c026d3;font-size:.82rem;">{{ $rhu['username'] }}</code></div>
                            </div>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-calendar-check-fill"></i></div>
                            <div>
                                <div class="info-label">Applied</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        @if($status === 'credentials_sent' && isset($rhu['credentials_sent_at']))
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-envelope-check-fill"></i></div>
                            <div>
                                <div class="info-label">Credentials Sent</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($status === 'archived' && isset($rhu['archived_at']))
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-archive-fill"></i></div>
                            <div>
                                <div class="info-label">Archived On</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($rhu['archived_at'])->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Location Card --}}
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-badge">Location</div>
                    <div class="info-card-title">Address Information</div>
                </div>
                <div class="info-card-body">
                    <div class="info-grid">
                        @if($rhu['fullAddress'] ?? false)
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <div class="info-label">Full Address</div>
                                <div class="info-value">{{ $rhu['fullAddress'] }}</div>
                            </div>
                        </div>
                        @endif
                        @if(isset($rhu['displayLocation']) && $rhu['displayLocation'])
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-pin-map-fill"></i></div>
                            <div>
                                <div class="info-label">Location</div>
                                <div class="info-value">{{ $rhu['displayLocation'] }}</div>
                            </div>
                        </div>
                        @elseif(isset($rhu['region']) || isset($rhu['province']) || isset($rhu['city']))
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-map-fill"></i></div>
                            <div>
                                <div class="info-label">Region / Province / City</div>
                                <div class="info-value">{{ $rhu['region'] ?? 'N/A' }} / {{ $rhu['province'] ?? 'N/A' }} / {{ $rhu['city'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @endif
                        @if(($rhu['latitude'] ?? false) && ($rhu['longitude'] ?? false))
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-compass-fill"></i></div>
                            <div>
                                <div class="info-label">Coordinates</div>
                                <div class="info-value d-flex align-items-center gap-2">
                                    {{ $rhu['latitude'] }}, {{ $rhu['longitude'] }}
                                    <a href="https://maps.google.com/?q={{ $rhu['latitude'] }},{{ $rhu['longitude'] }}" target="_blank"
                                       style="font-size:.75rem;padding:2px 8px;border-radius:4px;border:1px solid #e9e9e7;color:#1657c1;text-decoration:none;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Map
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.btn-back { background:transparent; border:1px solid #e9e9e7; color:#787774; font-size:.82rem; font-weight:500; padding:6px 16px; border-radius:6px; text-decoration:none; transition:all .1s; }
.btn-back:hover { background:#f1f1ef; color:#37352f; }
.btn-archive { background:transparent; border:1px solid #fde68a; color:#d97706; font-size:.82rem; font-weight:500; padding:6px 14px; border-radius:6px; cursor:pointer; transition:all .1s; }
.btn-archive:hover { background:#fef9c3; color:#b45309; border-color:#f59e0b; }
.btn-restore { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; font-size:.82rem; font-weight:500; padding:6px 14px; border-radius:6px; cursor:pointer; transition:all .1s; }
.btn-restore:hover { background:#bbf7d0; color:#14532d; }
.btn-resend-hdr { background:transparent; border:1px solid #bfdbfe; color:#1657c1; font-size:.82rem; font-weight:500; padding:6px 14px; border-radius:6px; cursor:pointer; transition:all .1s; }
.btn-resend-hdr:hover { background:#dbeafe; color:#1245a8; }

.info-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; }
.info-card-header { background:#1657c1; padding:16px 20px 12px; }
.info-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.info-card-title { font-size:.95rem; font-weight:600; color:#fff; }
.info-card-body { padding:20px; }

.brgy-logo-zone { width:64px; height:64px; border-radius:8px; background:#f1f1ef; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid #e9e9e7; }
.brgy-logo-img { width:56px; height:56px; object-fit:contain; }

.brgy-status { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.brgy-status.active   { background:#dcfce7; color:#166534; }
.brgy-status.setup    { background:#fef9c3; color:#92400e; }
.brgy-status.approved { background:#dbeafe; color:#1e40af; }
.brgy-status.pending  { background:#fef3c7; color:#92400e; }
.brgy-status.rejected { background:#fee2e2; color:#991b1b; }
.brgy-status.archived { background:#f1f1ef; color:#787774; }

.info-grid { display:flex; flex-direction:column; gap:0; }
.info-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f1f1ef; }
.info-row:last-child { border-bottom:none; }
.info-icon { width:30px; height:30px; border-radius:6px; background:#f1f1ef; display:flex; align-items:center; justify-content:center; color:#787774; font-size:.85rem; flex-shrink:0; margin-top:1px; }
.info-label { font-size:.65rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#9b9b9b; margin-bottom:2px; }
.info-value { font-size:.85rem; font-weight:500; color:#37352f; }
</style>

{{-- JS Toasts --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
    <div id="jsSuccessToast" class="toast align-items-center text-white border-0" role="alert" style="background:#1657c1;border-radius:10px;min-width:300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span id="jsSuccessMsg">Done.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="jsErrorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" style="background:#dc2626;border-radius:10px;min-width:300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="jsErrorMsg">Something went wrong.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:12px;border:1px solid #e9e9e7;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-check-circle-fill" style="font-size:1.3rem;color:#166534;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#37352f;">Approve Application?</div>
                        <div class="small text-muted">Credentials will be generated and sent to the RHU's email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmApproveBtn" class="btn-modal-confirm" style="background:#166534;border-color:#166534;">
                        <i class="bi bi-check me-1"></i>Yes, Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Resend Modal --}}
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:12px;border:1px solid #e9e9e7;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.3rem;color:#1657c1;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#37352f;">Resend Credentials?</div>
                        <div class="small text-muted">The login credentials will be re-sent to the RHU's registered email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmResendBtn" class="btn-modal-confirm">
                        <i class="bi bi-arrow-repeat me-1"></i>Yes, Resend
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-modal-cancel { background:transparent; border:1px solid #e9e9e7; color:#787774; font-size:.82rem; font-weight:500; padding:7px 16px; border-radius:6px; cursor:pointer; }
.btn-modal-cancel:hover { background:#f1f1ef; color:#37352f; }
.btn-modal-confirm { background:#1657c1; border:1px solid #1657c1; color:#fff; font-size:.82rem; font-weight:500; padding:7px 16px; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; }
.btn-modal-confirm:hover { background:#1245a8; }
.btn-modal-confirm:disabled { opacity:.55; cursor:not-allowed; }
</style>

@include('partials.archive-confirm-modal', ['modalId' => 'rhuArchiveModal', 'entityLabel' => 'RHU'])

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast[role="alert"]').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 5000));

    const rhuId = '{{ $rhu['id'] }}';
    const csrf  = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(type, message) {
        const toastEl = document.getElementById(type === 'success' ? 'jsSuccessToast' : 'jsErrorToast');
        const msgEl   = document.getElementById(type === 'success' ? 'jsSuccessMsg'  : 'jsErrorMsg');
        msgEl.textContent = message;
        new bootstrap.Toast(toastEl, { delay: 5000 }).show();
    }

    // Approve
    const approveBtn   = document.getElementById('approveBtn');
    const approveModalEl = document.getElementById('approveModal');
    const approveModal = approveModalEl ? new bootstrap.Modal(approveModalEl) : null;

    if (approveBtn && approveModal) {
        approveBtn.addEventListener('click', () => approveModal.show());
        document.getElementById('confirmApproveBtn').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

            fetch(`/admin/system-admin/${rhuId}/approve`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                approveModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check me-1"></i>Yes, Approve';
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
                btn.innerHTML = '<i class="bi bi-check me-1"></i>Yes, Approve';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }

    // Resend
    const resendBtn   = document.getElementById('resendBtn');
    const resendModalEl = document.getElementById('resendModal');
    const resendModal = resendModalEl ? new bootstrap.Modal(resendModalEl) : null;

    if (resendBtn && resendModal) {
        resendBtn.addEventListener('click', () => resendModal.show());
        document.getElementById('confirmResendBtn').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';

            fetch(`/admin/system-admin/${rhuId}/resend-credentials`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                resendModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Yes, Resend';
                if (data.success) {
                    showToast('success', 'Credentials resent to RHU email.');
                } else {
                    showToast('error', data.error || 'Failed to resend.');
                }
            })
            .catch(() => {
                resendModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Yes, Resend';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }
});
</script>
@endpush
@endsection
