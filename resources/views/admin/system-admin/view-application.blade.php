@extends('layouts.app')

@section('content')
<style>
    :root {
        --nb: #f7f8fc; --ns: #ffffff; --nbr: #e3e8f0;
        --nt: #1e293b; --nm: #64748b; --nbl: #1657c1;
        --nblt: #eff4ff; --nbmd: #dbeafe; --nbdk: #1e40af;
    }
    .n-page-title { font-size:1.35rem; font-weight:700; color:var(--nt); letter-spacing:-0.3px; }
    .n-back-link  { font-size:0.8rem; color:var(--nm); text-decoration:none; display:inline-flex;
                     align-items:center; gap:5px; margin-bottom:8px; transition:color 0.15s; }
    .n-back-link:hover { color:var(--nbl); }
    .n-card  { background:var(--ns); border:1px solid var(--nbr); border-radius:10px; overflow:hidden; }
    .n-card-header { padding:14px 20px; border-bottom:1px solid var(--nbr); display:flex; align-items:center; gap:8px; }
    .n-card-header-title { font-size:0.78rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:var(--nm); }
    .n-card-header i { color:var(--nbl); }
    .n-card-body  { padding:24px; }
    .n-prop       { margin-bottom:16px; }
    .n-prop-label { font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em;
                     color:var(--nm); margin-bottom:4px; }
    .n-prop-value { font-size:0.875rem; color:var(--nt); }
    .n-prop-box   { background:#fafbfd; border:1px solid var(--nbr); border-radius:8px; padding:12px 14px; }
    .n-divider    { border:none; border-top:1px solid var(--nbr); margin:20px 0; }
    .n-badge        { font-size:0.7rem; font-weight:600; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; }
    .n-badge-amber  { background:#fef3c7; color:#92400e; }
    .n-badge-green  { background:#d1fae5; color:#065f46; }
    .n-badge-blue   { background:var(--nbmd); color:var(--nbdk); }
    .n-badge-red    { background:#fee2e2; color:#991b1b; }
    .n-badge-gray   { background:#f1f5f9; color:#475569; }
    .n-btn-primary  { font-size:0.825rem; padding:9px 20px; border-radius:7px; border:1px solid var(--nbl);
                       background:var(--nbl); color:#fff; font-weight:500; cursor:pointer;
                       transition:background 0.15s; width:100%; display:flex; align-items:center; justify-content:center; gap:6px; }
    .n-btn-primary:hover { background:var(--nbdk); border-color:var(--nbdk); }
    .n-btn-primary:disabled { opacity:0.55; cursor:not-allowed; }
    .n-btn-outline  { font-size:0.825rem; padding:9px 20px; border-radius:7px; border:1px solid var(--nbr);
                       background:var(--ns); color:var(--nbl); font-weight:500; cursor:pointer;
                       transition:background 0.15s; width:100%; display:flex; align-items:center; justify-content:center; gap:6px; }
    .n-btn-outline:hover { background:var(--nblt); border-color:var(--nbl); }
    .n-action-section { font-size:0.8rem; color:var(--nm); margin-bottom:16px; line-height:1.5; }
</style>

<div class="container-fluid" style="max-width:1100px;">

    <!-- Page Header -->
    <div class="mb-4">
        <a href="{{ route('admin.system-admin.dashboard') }}" class="n-back-link">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
        <h1 class="n-page-title mb-0">RHU Application Details</h1>
    </div>

    <div class="row g-4">

        <!-- Left: RHU Info -->
        <div class="col-lg-8">
            <div class="n-card">
                <div class="n-card-header">
                    <i class="bi bi-hospital-fill"></i>
                    <span class="n-card-header-title">Rural Health Unit Information</span>
                </div>
                <div class="n-card-body">

                    <!-- Logo + Name -->
                    <div class="d-flex gap-4 mb-4">
                        @if($rhu['logo_url'] ?? false)
                            <img src="{{ $rhu['logo_url'] }}" alt="RHU Logo"
                                 style="width:96px;height:96px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid #e3e8f0;">
                        @else
                            <div style="width:96px;height:96px;border-radius:10px;background:var(--nbmd);
                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-hospital" style="font-size:2.4rem;color:var(--nbl);"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 pt-1">
                            @php
                                $status = $rhu['status'] ?? 'pending';
                                $badgeClass = match($status) {
                                    'pending'          => 'n-badge n-badge-amber',
                                    'credentials_sent' => 'n-badge n-badge-green',
                                    'active'           => 'n-badge n-badge-blue',
                                    'rejected'         => 'n-badge n-badge-red',
                                    default            => 'n-badge n-badge-gray',
                                };
                                $badgeLabel = match($status) {
                                    'pending'          => 'Pending',
                                    'credentials_sent' => 'Credentials Sent',
                                    'active'           => 'Active',
                                    'rejected'         => 'Rejected',
                                    default            => ucfirst($status),
                                };
                            @endphp
                            <span class="{{ $badgeClass }} mb-2">{{ $badgeLabel }}</span>
                            <h5 class="fw-bold mb-2" style="color:var(--nt);">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</h5>
                            <div class="n-prop-label mb-1">Contact</div>
                            <div style="font-size:0.82rem; color:var(--nm);">
                                <i class="bi bi-envelope me-1"></i>
                                <a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none" style="color:var(--nm);">{{ $rhu['email'] }}</a>
                            </div>
                            <div style="font-size:0.82rem; color:var(--nm); margin-top:4px;">
                                <i class="bi bi-calendar me-1"></i>
                                Applied {{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>

                    <hr class="n-divider">

                    <!-- Location -->
                    <div class="n-prop-label mb-3">
                        <i class="bi bi-geo-alt me-1" style="color:var(--nbl);"></i> Location Details
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="n-prop-box">
                                <div class="n-prop-label">Full Address</div>
                                <div class="n-prop-value">{{ $rhu['fullAddress'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="n-prop-box">
                                @if(isset($rhu['displayLocation']))
                                    <div class="n-prop-label">Location</div>
                                    <div class="n-prop-value">{{ $rhu['displayLocation'] }}</div>
                                @else
                                    <div class="n-prop-label">Region / Province / City</div>
                                    <div class="n-prop-value">
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
                            <i class="bi bi-pin-map" style="color:var(--nbl);"></i>
                            <span style="font-size:0.8rem;color:var(--nm);">{{ $rhu['latitude'] }}, {{ $rhu['longitude'] }}</span>
                            <a href="https://maps.google.com/?q={{ $rhu['latitude'] }},{{ $rhu['longitude'] }}"
                               target="_blank"
                               style="font-size:0.775rem;padding:4px 10px;border-radius:6px;border:1px solid var(--nbr);
                                      background:var(--ns);color:var(--nbl);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                <i class="bi bi-box-arrow-up-right"></i> Map
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="col-lg-4">
            @if($status === 'pending')
                <div class="n-card">
                    <div class="n-card-header">
                        <i class="bi bi-check-circle-fill" style="color:#059669 !important;"></i>
                        <span class="n-card-header-title">Approve Application</span>
                    </div>
                    <div class="n-card-body">
                        <p class="n-action-section">
                            Approving will generate login credentials and send a setup email to the RHU's registered address.
                        </p>
                        <button type="button" class="n-btn-primary" id="approveBtn"
                                style="background:#059669;border-color:#059669;">
                            <i class="bi bi-check"></i> Generate &amp; Send Credentials
                        </button>
                    </div>
                </div>

            @elseif($status === 'credentials_sent')
                <div class="n-card">
                    <div class="n-card-header">
                        <i class="bi bi-envelope-check-fill"></i>
                        <span class="n-card-header-title">Credentials Sent</span>
                    </div>
                    <div class="n-card-body">
                        <div class="n-prop-box mb-3">
                            <div class="n-prop-label">Sent on</div>
                            <div class="n-prop-value">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="n-prop-box mb-4">
                            <div class="n-prop-label">Username</div>
                            <code style="font-size:0.85rem;color:var(--nt);">{{ $rhu['username'] ?? 'N/A' }}</code>
                        </div>
                        <button type="button" class="n-btn-outline" id="resendBtn">
                            <i class="bi bi-arrow-repeat"></i> Resend Credentials
                        </button>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Toasts -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:#1657c1;border-radius:10px;min-width:300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span id="successMsg">Done.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:#dc2626;border-radius:10px;min-width:300px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="errorMsg">Something went wrong.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:12px;border:1px solid #e3e8f0;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#d1fae5;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-check-circle-fill" style="font-size:1.3rem;color:#059669;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#1e293b;">Approve Application?</div>
                        <div class="small" style="color:#64748b;">Credentials will be generated and sent to the RHU's email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="n-btn-outline" data-bs-dismiss="modal"
                            style="width:auto;padding:7px 16px;font-size:0.825rem;">Cancel</button>
                    <button type="button" id="confirmApproveBtn"
                            style="font-size:0.825rem;padding:7px 16px;border-radius:7px;border:1px solid #059669;
                                   background:#059669;color:#fff;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                        <i class="bi bi-check"></i> Yes, Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resend Modal -->
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:12px;border:1px solid #e3e8f0;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#eff4ff;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.3rem;color:#1657c1;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#1e293b;">Resend Credentials?</div>
                        <div class="small" style="color:#64748b;">The login credentials will be re-sent to the RHU's registered email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="n-btn-outline" data-bs-dismiss="modal"
                            style="width:auto;padding:7px 16px;font-size:0.825rem;">Cancel</button>
                    <button type="button" id="confirmResendBtn"
                            style="font-size:0.825rem;padding:7px 16px;border-radius:7px;border:1px solid #1657c1;
                                   background:#1657c1;color:#fff;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                        <i class="bi bi-arrow-repeat"></i> Yes, Resend
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rhuId = '{{ $rhu['id'] }}';
    const csrf  = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(type, message) {
        const el = type === 'success' ? 'successToast' : 'errorToast';
        const msg = type === 'success' ? 'successMsg' : 'errorMsg';
        document.getElementById(msg).textContent = message;
        new bootstrap.Toast(document.getElementById(el), { delay: 5000 }).show();
    }

    // Approve
    const approveBtn   = document.getElementById('approveBtn');
    const approveModal = document.getElementById('approveModal') ? new bootstrap.Modal(document.getElementById('approveModal')) : null;

    if (approveBtn && approveModal) {
        approveBtn.addEventListener('click', () => approveModal.show());
        document.getElementById('confirmApproveBtn').addEventListener('click', function () {
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
                btn.innerHTML = '<i class="bi bi-check"></i> Yes, Approve';
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
                btn.innerHTML = '<i class="bi bi-check"></i> Yes, Approve';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }

    // Resend
    const resendBtn   = document.getElementById('resendBtn');
    const resendModal = document.getElementById('resendModal') ? new bootstrap.Modal(document.getElementById('resendModal')) : null;

    if (resendBtn && resendModal) {
        resendBtn.addEventListener('click', () => resendModal.show());
        document.getElementById('confirmResendBtn').addEventListener('click', function () {
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
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Yes, Resend';
                if (data.success) {
                    showToast('success', 'Credentials resent to RHU email.');
                } else {
                    showToast('error', data.error || 'Failed to resend.');
                }
            })
            .catch(() => {
                resendModal.hide();
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Yes, Resend';
                showToast('error', 'Request failed. Check your connection.');
            });
        });
    }
});
</script>
@endsection
