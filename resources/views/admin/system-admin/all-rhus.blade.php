@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Flash Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">All Rural Health Units</h2>
            <p class="text-muted mb-0 small">Manage all RHU applications and accounts</p>
        </div>
    </div>

    @php
        $activeRhus        = collect($rhus)->where('status', '!=', 'archived')->values();
        $archivedRhus      = collect($rhus)->where('status', 'archived')->values();
        $pendingRhus       = $activeRhus->filter(fn($r) => in_array($r['status'] ?? '', ['pending', 'pending_setup']))->values();
        $credentialRhus    = $activeRhus->filter(fn($r) => ($r['status'] ?? '') === 'credentials_sent')->values();
        $pendingCount      = $pendingRhus->count();
        $credentialCount   = $credentialRhus->count();
        $archivedCount     = $archivedRhus->count();
    @endphp

    {{-- Stats Row --}}
    <div class="brgy-stats mb-4">
        <div class="brgy-stat">
            <div class="brgy-stat-val">{{ $activeRhus->count() }}</div>
            <div class="brgy-stat-lbl">Total Active</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#92400e;">{{ $pendingCount }}</div>
            <div class="brgy-stat-lbl">Pending</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#166534;">{{ $credentialCount }}</div>
            <div class="brgy-stat-lbl">Credentials Sent</div>
        </div>
        @if($archivedCount > 0)
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#787774;">{{ $archivedCount }}</div>
            <div class="brgy-stat-lbl">Archived</div>
        </div>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="brgy-tabs mb-0">
        <button class="brgy-tab active" id="tabAll" onclick="switchRhuTab('all')">
            <i class="bi bi-hospital me-1"></i>All
            <span class="brgy-tab-count">{{ $activeRhus->count() }}</span>
        </button>
        <button class="brgy-tab" id="tabPending" onclick="switchRhuTab('pending')">
            <i class="bi bi-hourglass-split me-1"></i>Pending
            <span class="brgy-tab-count">{{ $pendingCount }}</span>
        </button>
        <button class="brgy-tab" id="tabCredentials" onclick="switchRhuTab('credentials')">
            <i class="bi bi-envelope-check me-1"></i>Credentials Sent
            <span class="brgy-tab-count">{{ $credentialCount }}</span>
        </button>
        @if($archivedCount > 0)
        <button class="brgy-tab" id="tabArchived" onclick="switchRhuTab('archived')">
            <i class="bi bi-archive me-1"></i>Archived
            <span class="brgy-tab-count">{{ $archivedCount }}</span>
        </button>
        @endif
    </div>

    {{-- All Tab --}}
    <div id="paneAll" class="brgy-card" style="border-top-left-radius:0;">
        <div class="brgy-card-header">
            <div class="brgy-badge">RHUs</div>
            <div class="brgy-card-title">All Rural Health Units</div>
        </div>
        @if($activeRhus->isEmpty())
        <div class="brgy-empty" style="border:none;border-radius:0;">
            <i class="bi bi-hospital"></i>
            <div class="brgy-empty-title">No RHUs Found</div>
            <div class="brgy-empty-text">No rural health units have been registered yet.</div>
        </div>
        @else
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Logo</th>
                            <th>RHU Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Applied</th>
                            <th style="width:100px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeRhus as $rhu)
                        @php
                            $s = $rhu['status'] ?? 'pending';
                            $statusMap = [
                                'pending'          => ['label'=>'Pending',          'cls'=>'pending'],
                                'pending_setup'    => ['label'=>'Processing',       'cls'=>'setup'],
                                'credentials_sent' => ['label'=>'Credentials Sent', 'cls'=>'approved'],
                                'active'           => ['label'=>'Active',           'cls'=>'active'],
                                'rejected'         => ['label'=>'Rejected',         'cls'=>'rejected'],
                            ];
                            $st = $statusMap[$s] ?? ['label'=>ucfirst($s),'cls'=>'pending'];
                        @endphp
                        <tr>
                            <td>
                                <div class="brgy-logo-wrap">
                                    @if($rhu['logo_url'] ?? false)
                                        <img src="{{ $rhu['logo_url'] }}" alt="{{ $rhu['rhuName'] ?? '' }}" class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
                                    @else
                                        <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:#37352f;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                            </td>
                            <td class="small text-muted">{{ $rhu['email'] }}</td>
                            <td><span class="brgy-status {{ $st['cls'] }}">{{ $st['label'] }}</span></td>
                            <td class="small text-muted">{{ $rhu['displayLocation'] ?? (($rhu['city'] ?? '') . (isset($rhu['province']) ? ', '.$rhu['province'] : '')) }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($s === 'credentials_sent')
                                    <button type="button" class="tbl-btn resend resend-btn" data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Pending Tab --}}
    <div id="panePending" class="brgy-card d-none" style="border-top-left-radius:0;border-top-right-radius:0;">
        <div class="brgy-card-header" style="background:#d97706;">
            <div class="brgy-badge">Pending</div>
            <div class="brgy-card-title">Pending Applications</div>
        </div>
        @if($pendingRhus->isEmpty())
        <div class="brgy-empty" style="border:none;border-radius:0;">
            <i class="bi bi-inbox"></i>
            <div class="brgy-empty-title">No Pending Applications</div>
            <div class="brgy-empty-text">All applications have been processed.</div>
        </div>
        @else
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead style="background:#d97706;">
                        <tr>
                            <th>RHU Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Applied</th>
                            <th style="width:80px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRhus as $rhu)
                        @php $ps = $rhu['status'] ?? 'pending'; @endphp
                        <tr>
                            <td class="fw-semibold" style="color:#37352f;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</td>
                            <td class="small text-muted">{{ $rhu['email'] }}</td>
                            <td><span class="brgy-status {{ $ps === 'pending_setup' ? 'setup' : 'pending' }}">{{ $ps === 'pending_setup' ? 'Processing' : 'Pending' }}</span></td>
                            <td class="small text-muted">{{ $rhu['displayLocation'] ?? ($rhu['city'] ?? 'N/A') }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Credentials Sent Tab --}}
    <div id="paneCredentials" class="brgy-card d-none" style="border-top-left-radius:0;border-top-right-radius:0;">
        <div class="brgy-card-header" style="background:#166534;">
            <div class="brgy-badge">Sent</div>
            <div class="brgy-card-title">Credentials Sent</div>
        </div>
        @if($credentialRhus->isEmpty())
        <div class="brgy-empty" style="border:none;border-radius:0;">
            <i class="bi bi-envelope"></i>
            <div class="brgy-empty-title">No Credentials Sent Yet</div>
            <div class="brgy-empty-text">No RHUs have had credentials sent.</div>
        </div>
        @else
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead style="background:#166534;">
                        <tr>
                            <th>RHU Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Credentials Sent</th>
                            <th style="width:110px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credentialRhus as $rhu)
                        <tr>
                            <td class="fw-semibold" style="color:#37352f;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</td>
                            <td><code style="background:#f1f1ef;padding:2px 6px;border-radius:4px;color:#c026d3;font-size:.82rem;">{{ $rhu['username'] ?? 'N/A' }}</code></td>
                            <td class="small text-muted">{{ $rhu['email'] }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="tbl-btn resend resend-btn" data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Archived Tab --}}
    @if($archivedCount > 0)
    <div id="paneArchived" class="brgy-card d-none" style="border-top-left-radius:0;border-top-right-radius:0;">
        <div class="brgy-card-header" style="background:#787774;">
            <div class="brgy-badge">Archived</div>
            <div class="brgy-card-title">Archived RHUs</div>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead style="background:#787774;">
                        <tr>
                            <th style="width:50px;">Logo</th>
                            <th>RHU Name</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Archived On</th>
                            <th style="width:100px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedRhus as $rhu)
                        <tr>
                            <td>
                                <div class="brgy-logo-wrap" style="opacity:.6;">
                                    @if($rhu['logo_url'] ?? false)
                                        <img src="{{ $rhu['logo_url'] }}" alt="{{ $rhu['rhuName'] ?? '' }}" class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
                                    @else
                                        <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:#787774;">{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</div>
                            </td>
                            <td class="small text-muted">{{ $rhu['email'] }}</td>
                            <td class="small text-muted">{{ $rhu['displayLocation'] ?? ($rhu['city'] ?? 'N/A') }}</td>
                            <td class="small text-muted">{{ isset($rhu['archived_at']) ? \Carbon\Carbon::parse($rhu['archived_at'])->format('M d, Y') : '—' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.system-admin.restore', $rhu['id']) }}">
                                        @csrf
                                        <button type="submit" class="tbl-btn restore" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
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
    @endif

</div>

<style>
.brgy-stats { display:flex; gap:16px; flex-wrap:wrap; }
.brgy-stat { background:#fff; border:1px solid #e9e9e7; border-radius:8px; padding:14px 20px; min-width:120px; }
.brgy-stat-val { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.brgy-stat-lbl { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px; color:#9b9b9b; margin-top:4px; }

.brgy-tabs { display:flex; gap:0; }
.brgy-tab { background:#f8fafc; border:1px solid #e9e9e7; border-bottom:none; border-radius:8px 8px 0 0; padding:9px 20px; font-size:.82rem; font-weight:500; color:#787774; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:6px; margin-right:4px; }
.brgy-tab.active { background:#fff; color:#37352f; font-weight:600; border-color:#e9e9e7; position:relative; z-index:1; }
.brgy-tab-count { background:#f1f1ef; color:#787774; font-size:.65rem; font-weight:700; border-radius:10px; padding:1px 6px; }

.brgy-card { background:#fff; border:1px solid #e9e9e7; border-radius:0 8px 8px 8px; overflow:hidden; }
.brgy-card-header { background:#1657c1; padding:16px 20px 12px; }
.brgy-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.brgy-card-title { font-size:.95rem; font-weight:600; color:#fff; }

.brgy-table { width:100%; border-collapse:collapse; }
.brgy-table thead tr { background:#1657c1; }
.brgy-table th { padding:10px 14px; font-size:.68rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#fff; white-space:nowrap; }
.brgy-table td { padding:12px 14px; font-size:.82rem; border-bottom:1px solid #f1f1ef; vertical-align:middle; }
.brgy-table tbody tr:last-child td { border-bottom:none; }
.brgy-table tbody tr:hover { background:#fafaf9; }

.brgy-logo-wrap { width:40px; height:40px; border-radius:6px; background:#f1f1ef; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.brgy-logo-img { width:36px; height:36px; object-fit:contain; }

.brgy-status { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.brgy-status.active   { background:#dcfce7; color:#166534; }
.brgy-status.setup    { background:#fef9c3; color:#92400e; }
.brgy-status.approved { background:#dbeafe; color:#1e40af; }
.brgy-status.pending  { background:#fef3c7; color:#92400e; }
.brgy-status.rejected { background:#fee2e2; color:#991b1b; }

.brgy-empty { text-align:center; padding:60px 20px; background:#fff; border:1px solid #e9e9e7; border-radius:8px; }
.brgy-empty i { font-size:2.5rem; color:#c4c4c2; display:block; margin-bottom:12px; }
.brgy-empty-title { font-size:.95rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.brgy-empty-text { font-size:.82rem; color:#9b9b9b; }

.tbl-btn { width:28px; height:28px; border-radius:5px; display:flex; align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; text-decoration:none; transition:background .1s; border:none; }
.tbl-btn.view    { background:#f1f1ef; color:#787774; }
.tbl-btn.view:hover { background:#dbeafe; color:#1e40af; }
.tbl-btn.resend  { background:#f1f1ef; color:#787774; }
.tbl-btn.resend:hover { background:#dbeafe; color:#1e40af; }
.tbl-btn.restore { background:#f1f1ef; color:#787774; }
.tbl-btn.restore:hover { background:#dcfce7; color:#166534; }
</style>

{{-- Resend Toast --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
    <div id="resendSuccessToast" class="toast align-items-center text-white border-0" role="alert" style="background:#1657c1;border-radius:10px;min-width:280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>Credentials resent successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="resendErrorToast" class="toast align-items-center text-white border-0 mt-2" role="alert" style="background:#dc2626;border-radius:10px;min-width:280px;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span id="resendErrorMsg">Failed to resend credentials.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

{{-- Resend Confirm Modal --}}
<div class="modal fade" id="resendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0" style="border-radius:12px;border:1px solid #e9e9e7;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.4rem;color:#1657c1;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#37352f;">Resend Credentials?</div>
                        <div class="small text-muted">The login credentials will be re-sent to the RHU's registered email.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="btn-back-modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmResendBtn" class="btn-confirm-modal">
                        <i class="bi bi-arrow-repeat me-1"></i> Yes, Resend
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-back-modal { background:transparent; border:1px solid #e9e9e7; color:#787774; font-size:.82rem; font-weight:500; padding:7px 16px; border-radius:6px; cursor:pointer; }
.btn-back-modal:hover { background:#f1f1ef; color:#37352f; }
.btn-confirm-modal { background:#1657c1; border:1px solid #1657c1; color:#fff; font-size:.82rem; font-weight:500; padding:7px 16px; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; }
.btn-confirm-modal:hover { background:#1245a8; }
.btn-confirm-modal:disabled { opacity:.55; cursor:not-allowed; }
</style>

@push('scripts')
<script>
function switchRhuTab(tab) {
    ['All','Pending','Credentials','Archived'].forEach(t => {
        const pane = document.getElementById('pane' + t);
        const btn  = document.getElementById('tab' + t);
        if (pane) pane.classList.toggle('d-none', tab !== t.toLowerCase());
        if (btn)  btn.classList.toggle('active', tab === t.toLowerCase());
    });
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast[role="alert"]').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pendingResendId = null;
    const resendModal = new bootstrap.Modal(document.getElementById('resendModal'));

    document.querySelectorAll('.resend-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            pendingResendId = this.getAttribute('data-rhu-id');
            resendModal.show();
        });
    });

    document.getElementById('confirmResendBtn').addEventListener('click', function() {
        if (!pendingResendId) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        fetch(`/admin/system-admin/${pendingResendId}/resend-credentials`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
        })
        .then(r => r.json())
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
@endpush
@endsection
