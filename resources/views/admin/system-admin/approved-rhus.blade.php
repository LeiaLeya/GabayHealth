@extends('layouts.app')

@section('content')
<style>
    :root {
        --nb: #f7f8fc; --ns: #ffffff; --nbr: #e3e8f0;
        --nt: #1e293b; --nm: #64748b; --nbl: #1657c1;
        --nblt: #eff4ff; --nbmd: #dbeafe; --nbdk: #1e40af;
    }
    .n-page-title { font-size:1.35rem; font-weight:700; color:var(--nt); letter-spacing:-0.3px; }
    .n-page-sub   { font-size:0.825rem; color:var(--nm); margin-top:2px; }
    .n-back-link  { font-size:0.8rem; color:var(--nm); text-decoration:none; display:inline-flex;
                     align-items:center; gap:5px; margin-bottom:8px; transition:color 0.15s; }
    .n-back-link:hover { color:var(--nbl); }
    .n-table-wrap { border:1px solid var(--nbr); border-radius:10px; overflow:hidden; }
    .n-table { width:100%; border-collapse:collapse; }
    .n-table thead th { font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em;
                         color:#fff; background:var(--nbl); padding:11px 16px;
                         border-bottom:2px solid #1e6fd9; white-space:nowrap; }
    .n-table thead th:first-child { padding-left:20px; }
    .n-table thead th:last-child  { padding-right:20px; }
    .n-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background 0.12s; }
    .n-table tbody tr:last-child { border-bottom:none; }
    .n-table tbody tr:hover { background:var(--nblt); }
    .n-table td { padding:12px 16px; font-size:0.85rem; color:var(--nt); vertical-align:middle; }
    .n-table td:first-child { padding-left:20px; }
    .n-table td:last-child  { padding-right:20px; }
    .n-avatar   { width:34px; height:34px; border-radius:8px; background:var(--nbmd);
                   display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .n-avatar i { color:var(--nbl); font-size:1rem; }
    .n-badge-green { background:#d1fae5; color:#065f46; font-size:0.7rem; font-weight:600;
                      padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
    .n-btn-sm { font-size:0.775rem; padding:5px 12px; border-radius:6px; font-weight:500;
                 border:1px solid var(--nbr); background:var(--ns); color:var(--nbl); cursor:pointer;
                 transition:background 0.15s,border-color 0.15s; text-decoration:none;
                 display:inline-flex; align-items:center; gap:4px; }
    .n-btn-sm:hover { background:var(--nbmd); border-color:var(--nbl); color:var(--nbl); }
    .n-btn-info-sm  { border-color:#bae6fd; color:#0369a1; }
    .n-btn-info-sm:hover { background:#e0f2fe; border-color:#0369a1; color:#0369a1; }
    .n-empty { padding:48px 20px; text-align:center; color:var(--nm); }
    .n-empty i { font-size:2rem; display:block; margin-bottom:10px; color:#cbd5e1; }
    .n-empty p { font-size:0.875rem; margin:0; }
</style>

<div class="container-fluid" style="max-width:1100px;">

    <!-- Page Header -->
    <div class="mb-4">
        <a href="{{ route('admin.system-admin.dashboard') }}" class="n-back-link">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
        <h1 class="n-page-title mb-0">Approved RHUs — Credentials Sent</h1>
        <p class="n-page-sub">RHUs waiting to activate their accounts</p>
    </div>

    <div class="n-table-wrap">
        <div class="table-responsive">
            <table class="n-table">
                <thead>
                    <tr>
                        <th>RHU Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Credentials Sent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRhus as $rhu)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($rhu['logo_url'] ?? false)
                                        <img src="{{ $rhu['logo_url'] }}" alt="Logo"
                                             class="n-avatar" style="object-fit:cover;">
                                    @else
                                        <div class="n-avatar"><i class="bi bi-hospital"></i></div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold" style="color:var(--nt);">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                                        <div style="font-size:0.775rem;color:var(--nm);">
                                            {{ $rhu['city'] ?? 'N/A' }}{{ isset($rhu['province']) && $rhu['province'] ? ', ' . $rhu['province'] : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="background:#f1f5f9;color:var(--nt);padding:3px 8px;border-radius:5px;font-size:0.8rem;">{{ $rhu['username'] ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none" style="color:var(--nm);font-size:0.82rem;">{{ $rhu['email'] }}</a>
                            </td>
                            <td style="color:var(--nm);font-size:0.82rem;white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') }}
                            </td>
                            <td>
                                <span class="n-badge-green">
                                    <i class="bi bi-clock"></i> Awaiting Activation
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}"
                                       class="n-btn-sm" title="View details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <button type="button" class="n-btn-sm n-btn-info-sm resend-btn"
                                            data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="n-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>No RHUs with credentials sent</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:#1657c1;border-radius:10px;min-width:280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>Credentials resent successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:#dc2626;border-radius:10px;min-width:280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="errorMsg">Failed to resend credentials.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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
                    <button type="button" class="n-btn-sm" data-bs-dismiss="modal"
                            style="padding:7px 16px;font-size:0.825rem;">Cancel</button>
                    <button type="button" id="confirmResendBtn"
                            style="font-size:0.825rem;padding:7px 16px;border-radius:7px;border:1px solid #1657c1;
                                   background:#1657c1;color:#fff;font-weight:500;cursor:pointer;
                                   display:inline-flex;align-items:center;gap:5px;">
                        <i class="bi bi-arrow-repeat"></i> Yes, Resend
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pendingId = null;

    const resendModal = new bootstrap.Modal(document.getElementById('resendModal'));

    document.querySelectorAll('.resend-btn').forEach(button => {
        button.addEventListener('click', function () {
            pendingId = this.getAttribute('data-rhu-id');
            resendModal.show();
        });
    });

    document.getElementById('confirmResendBtn').addEventListener('click', function () {
        if (!pendingId) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';

        fetch(`/admin/system-admin/${pendingId}/resend-credentials`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Yes, Resend';
            if (data.success) {
                new bootstrap.Toast(document.getElementById('successToast'), { delay: 4000 }).show();
            } else {
                document.getElementById('errorMsg').textContent = data.error || 'Failed to resend credentials.';
                new bootstrap.Toast(document.getElementById('errorToast'), { delay: 4000 }).show();
            }
        })
        .catch(() => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Yes, Resend';
            document.getElementById('errorMsg').textContent = 'Request failed. Check your connection.';
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 4000 }).show();
        });
    });
});
</script>
@endsection
