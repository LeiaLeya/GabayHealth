@extends('layouts.app')

@section('content')
<style>
    :root {
        --notion-bg:      #f7f8fc;
        --notion-surface: #ffffff;
        --notion-border:  #e3e8f0;
        --notion-text:    #1e293b;
        --notion-muted:   #64748b;
        --notion-blue:    #1657c1;
        --notion-blue-lt: #eff4ff;
        --notion-blue-md: #dbeafe;
        --notion-blue-dk: #1e40af;
    }
    .n-page-title { font-size: 1.45rem; font-weight: 700; color: var(--notion-text); letter-spacing: -0.3px; }
    .n-page-sub   { font-size: 0.825rem; color: var(--notion-muted); margin-top: 2px; }
    .n-back-link  { font-size: 0.8rem; color: var(--notion-muted); text-decoration: none; display: inline-flex;
                     align-items: center; gap: 5px; margin-bottom: 8px; transition: color 0.15s; }
    .n-back-link:hover { color: var(--notion-blue); }

    .rhu-tabs { border-bottom: 2px solid var(--notion-border); }
    .rhu-tabs .nav-link { font-size: 0.825rem; font-weight: 500; color: var(--notion-muted);
                           background: transparent; border: none; padding: 8px 14px; border-radius: 6px 6px 0 0; }
    .rhu-tabs .nav-link.active { color: var(--notion-blue) !important;
                                  border-bottom: 2px solid var(--notion-blue) !important; background: transparent; }
    .rhu-tabs .nav-link:hover:not(.active) { color: var(--notion-text); background: var(--notion-blue-lt); }

    .n-table-wrap { border: 1px solid var(--notion-border); border-radius: 10px; overflow: hidden; }
    .n-table { width: 100%; border-collapse: collapse; }
    .n-table thead th { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
                         color: #fff; background: var(--notion-blue); padding: 11px 16px;
                         border-bottom: 2px solid #1e6fd9; white-space: nowrap; }
    .n-table thead th:first-child { padding-left: 20px; }
    .n-table thead th:last-child  { padding-right: 20px; }
    .n-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.12s; }
    .n-table tbody tr:last-child { border-bottom: none; }
    .n-table tbody tr:hover { background: var(--notion-blue-lt); }
    .n-table td { padding: 12px 16px; font-size: 0.85rem; color: var(--notion-text); vertical-align: middle; }
    .n-table td:first-child { padding-left: 20px; }
    .n-table td:last-child  { padding-right: 20px; }

    .n-avatar   { width: 34px; height: 34px; border-radius: 8px; background: var(--notion-blue-md);
                   display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .n-avatar i { color: var(--notion-blue); font-size: 1rem; }

    .n-badge        { font-size: 0.7rem; font-weight: 600; padding: 3px 9px; border-radius: 20px; display: inline-flex; align-items: center; }
    .n-badge-amber  { background: #fef3c7; color: #92400e; }
    .n-badge-green  { background: #d1fae5; color: #065f46; }
    .n-badge-blue   { background: var(--notion-blue-md); color: var(--notion-blue-dk); }
    .n-badge-indigo { background: #e0e7ff; color: #3730a3; }
    .n-badge-red    { background: #fee2e2; color: #991b1b; }
    .n-badge-gray   { background: #f1f5f9; color: #475569; }

    .n-btn-sm { font-size: 0.775rem; padding: 5px 12px; border-radius: 6px; font-weight: 500;
                 border: 1px solid var(--notion-border); background: var(--notion-surface);
                 color: var(--notion-blue); cursor: pointer; transition: background 0.15s, border-color 0.15s;
                 text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .n-btn-sm:hover { background: var(--notion-blue-md); border-color: var(--notion-blue); color: var(--notion-blue); }
    .n-btn-info-sm  { border-color: #bae6fd; color: #0369a1; }
    .n-btn-info-sm:hover { background: #e0f2fe; border-color: #0369a1; color: #0369a1; }

    .n-empty { padding: 48px 20px; text-align: center; color: var(--notion-muted); }
    .n-empty i { font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1; }
    .n-empty p { font-size: 0.875rem; margin: 0; }
</style>

<div class="container-fluid" style="max-width: 1100px;">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('admin.system-admin.dashboard') }}" class="n-back-link">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <h1 class="n-page-title mb-0">All Rural Health Units</h1>
            <p class="n-page-sub">Manage all RHU applications and accounts</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    @php
        $pendingCount = count(array_filter($rhus, fn($r) => in_array($r['status'] ?? '', ['pending', 'pending_setup'])));
        $credentialsSentCount = count(array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'credentials_sent'));
    @endphp
    <ul class="nav rhu-tabs mb-4 gap-1" role="tablist">
        <li class="nav-item">
            <a class="nav-link active fw-medium px-3 py-2" data-bs-toggle="tab" href="#all"
               style="border-radius: 8px 8px 0 0; font-size: 0.875rem; color: #1a1a2e; border: none;">
                All <span class="badge bg-secondary ms-1">{{ count($rhus) }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-medium px-3 py-2" data-bs-toggle="tab" href="#pending"
               style="border-radius: 8px 8px 0 0; font-size: 0.875rem; border: none;">
                Pending
                <span class="badge ms-1" style="background:#f59e0b;">{{ $pendingCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-medium px-3 py-2" data-bs-toggle="tab" href="#approved"
               style="border-radius: 8px 8px 0 0; font-size: 0.875rem; border: none;">
                Credentials Sent
                <span class="badge ms-1" style="background:#10b981;">{{ $credentialsSentCount }}</span>
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">

        <!-- All RHUs -->
        <div id="all" class="tab-pane fade show active">
            <div class="n-table-wrap">
                <div class="table-responsive">
                    <table class="n-table">
                        <thead>
                            <tr>
                                <th>RHU Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rhus as $rhu)
                                @php
                                    $status = $rhu['status'] ?? 'pending';
                                    $badgeClass = match($status) {
                                        'pending'          => 'n-badge n-badge-amber',
                                        'pending_setup'    => 'n-badge n-badge-indigo',
                                        'credentials_sent' => 'n-badge n-badge-green',
                                        'active'           => 'n-badge n-badge-blue',
                                        'rejected'         => 'n-badge n-badge-red',
                                        default            => 'n-badge n-badge-gray',
                                    };
                                    $badgeLabel = match($status) {
                                        'pending'          => 'Pending',
                                        'pending_setup'    => 'Processing',
                                        'credentials_sent' => 'Credentials Sent',
                                        'active'           => 'Active',
                                        'rejected'         => 'Rejected',
                                        default            => ucfirst($status),
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($rhu['logo_url'] ?? false)
                                                <img src="{{ $rhu['logo_url'] }}" alt="Logo"
                                                     class="n-avatar flex-shrink-0"
                                                     style="width:34px;height:34px;border-radius:8px;object-fit:cover;">
                                            @else
                                                <div class="n-avatar flex-shrink-0">
                                                    <i class="bi bi-hospital"></i>
                                                </div>
                                            @endif
                                            <span class="fw-semibold" style="color:#1e293b;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none" style="color:#64748b; font-size:0.82rem;">
                                            {{ $rhu['email'] }}
                                        </a>
                                    </td>
                                    <td><span class="{{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                                    <td style="color:#64748b; font-size:0.82rem;">
                                        {{ $rhu['displayLocation'] ?? ($rhu['city'] ?? 'N/A') . (isset($rhu['province']) ? ', ' . $rhu['province'] : '') }}
                                    </td>
                                    <td style="color:#64748b; font-size:0.82rem; white-space:nowrap;">
                                        {{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}"
                                               class="n-btn-sm">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            @if($status === 'credentials_sent')
                                                <button type="button" class="n-btn-sm n-btn-info-sm resend-btn"
                                                        data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="n-empty"><i class="bi bi-inbox"></i><p>No RHUs found</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div id="pending" class="tab-pane fade">
            <div class="n-table-wrap">
                <div class="table-responsive">
                    <table class="n-table">
                        <thead>
                            <tr>
                                <th>RHU Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $pendingRhus = array_filter($rhus, fn($r) => in_array($r['status'] ?? '', ['pending', 'pending_setup'])); @endphp
                            @forelse($pendingRhus as $rhu)
                                @php
                                    $pStatus = $rhu['status'] ?? 'pending';
                                    $pClass  = $pStatus === 'pending_setup' ? 'n-badge n-badge-indigo' : 'n-badge n-badge-amber';
                                    $pLabel  = $pStatus === 'pending_setup' ? 'Processing' : 'Pending';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="n-avatar flex-shrink-0"><i class="bi bi-hospital"></i></div>
                                            <span class="fw-semibold" style="color:#1e293b;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td><a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none" style="color:#64748b; font-size:0.82rem;">{{ $rhu['email'] }}</a></td>
                                    <td><span class="{{ $pClass }}">{{ $pLabel }}</span></td>
                                    <td style="color:#64748b; font-size:0.82rem;">{{ $rhu['displayLocation'] ?? $rhu['city'] ?? 'N/A' }}</td>
                                    <td style="color:#64748b; font-size:0.82rem; white-space:nowrap;">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="n-btn-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="n-empty"><i class="bi bi-inbox"></i><p>No pending applications</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Credentials Sent -->
        <div id="approved" class="tab-pane fade">
            <div class="n-table-wrap">
                <div class="table-responsive">
                    <table class="n-table">
                        <thead>
                            <tr>
                                <th>RHU Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Credentials Sent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $approvedRhus = array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'credentials_sent'); @endphp
                            @forelse($approvedRhus as $rhu)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="n-avatar flex-shrink-0"><i class="bi bi-hospital"></i></div>
                                            <span class="fw-semibold" style="color:#1e293b;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <code style="background:#f1f5f9; color:#1e293b; padding:3px 8px; border-radius:5px; font-size:0.8rem;">{{ $rhu['username'] ?? 'N/A' }}</code>
                                    </td>
                                    <td><a href="mailto:{{ $rhu['email'] }}" class="text-decoration-none" style="color:#64748b; font-size:0.82rem;">{{ $rhu['email'] }}</a></td>
                                    <td style="color:#64748b; font-size:0.82rem; white-space:nowrap;">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="n-btn-sm">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button type="button" class="n-btn-sm n-btn-info-sm resend-btn" data-rhu-id="{{ $rhu['id'] }}">
                                                <i class="bi bi-arrow-repeat"></i> Resend
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="n-empty"><i class="bi bi-inbox"></i><p>No credentials sent yet</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.tab-content -->
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
    <div id="resendSuccessToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #1657c1; border-radius: 10px; min-width: 280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>Credentials resent successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="resendErrorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true"
         style="background: #dc2626; border-radius: 10px; min-width: 280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="resendErrorMsg">Failed to resend credentials.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Resend Confirmation Modal -->
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0" style="border-radius: 12px; border: 1px solid #e3e8f0;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#eff4ff;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.4rem;color:#1657c1;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#1e293b;">Resend Credentials?</div>
                        <div class="small" style="color:#64748b;">The login credentials will be re-sent to the RHU's registered email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="n-btn-sm" data-bs-dismiss="modal" style="padding:7px 16px; font-size:0.825rem;">Cancel</button>
                    <button type="button" id="confirmResendBtn"
                            style="font-size:0.825rem;padding:7px 16px;border-radius:6px;border:1px solid #1657c1;
                                   background:#1657c1;color:#fff;font-weight:500;cursor:pointer;">
                        <i class="bi bi-arrow-repeat me-1"></i> Yes, Resend
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let pendingResendId = null;

    const resendModal = new bootstrap.Modal(document.getElementById('resendModal'));

    document.querySelectorAll('.resend-btn').forEach(button => {
        button.addEventListener('click', function () {
            pendingResendId = this.getAttribute('data-rhu-id');
            resendModal.show();
        });
    });

    document.getElementById('confirmResendBtn').addEventListener('click', function () {
        if (!pendingResendId) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        fetch(`/admin/system-admin/${pendingResendId}/resend-credentials`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';

            if (data.success) {
                new bootstrap.Toast(document.getElementById('resendSuccessToast'), { delay: 4000 }).show();
            } else {
                document.getElementById('resendErrorMsg').textContent = data.error || 'Failed to resend credentials.';
                new bootstrap.Toast(document.getElementById('resendErrorToast'), { delay: 4000 }).show();
            }
        })
        .catch(() => {
            resendModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Yes, Resend';
            document.getElementById('resendErrorMsg').textContent = 'Request failed. Check your connection.';
            new bootstrap.Toast(document.getElementById('resendErrorToast'), { delay: 4000 }).show();
        });
    });
});
</script>
@endsection
