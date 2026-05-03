@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">User Requests</h2>
        </div>
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

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $totalRequests ?? count($requests) }}</h4>
                            <small class="text-muted">Total Requests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $pendingCount ?? count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'pending')) }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $approvedCount ?? count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'approved')) }}</h4>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $declinedCount ?? count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'declined')) }}</h4>
                            <small class="text-muted">Declined</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="card">
        <div class="card-header d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
            <h5 class="card-title mb-0">All Requests</h5>
            <form method="get" action="{{ request()->url() }}" class="user-requests-search-form flex-grow-1" style="max-width: 32rem;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="search"
                           name="q"
                           value="{{ $search ?? '' }}"
                           class="form-control border-start-0"
                           placeholder="Search email, name, barangay, status…"
                           aria-label="Search user requests"
                           autocomplete="off">
                    @if(!empty($search ?? ''))
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary" title="Clear search">Clear</a>
                    @endif
                    <button type="submit" class="btn btn-primary px-3">
                        <span class="d-none d-sm-inline">Search</span>
                        <i class="bi bi-search d-sm-none"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body pt-lg-3">
            @if(count($requests) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Email</th>
                                <th>Date created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="bi bi-person text-muted"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $request['email'] ?? ($request['username'] ?? 'N/A') }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $createdRaw = $request['submittedAt'] ?? $request['createdAt'] ?? $request['created_at'] ?? $request['approved_at'] ?? null;
                                        @endphp
                                        @if($createdRaw)
                                            {{ \Carbon\Carbon::parse($createdRaw)->format('M d, Y g:i A') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $request['status'] ?? 'pending';
                                            $statusClass = $status === 'approved' ? 'success' : ($status === 'declined' ? 'danger' : 'warning');
                                            $statusIcon = $status === 'approved' ? 'check-circle' : ($status === 'declined' ? 'x-circle' : 'clock');
                                        @endphp
                                        <span class="btn btn-{{ $statusClass }} btn-sm" style="font-weight: normal; cursor: default;">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    onclick="viewRequest('{{ $request['id'] }}')" 
                                                    title="View Details">
                                                <i class="bi bi-eye me-1"></i>View
                                            </button>
                                            @if(($request['status'] ?? 'pending') === 'pending')
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="approveRequest('{{ $request['id'] }}', '{{ $request['email'] ?? 'this request' }}')" 
                                                        title="Approve Request">
                                                    <i class="bi bi-check me-1"></i>Approve
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="declineRequest('{{ $request['id'] }}', '{{ $request['email'] ?? 'this request' }}')" 
                                                        title="Decline Request">
                                                    <i class="bi bi-x me-1"></i>Decline
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($requests, 'links'))
                    <div class="mt-3 d-flex justify-content-center user-requests-pagination">
                        {{ $requests->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    @if(!empty($search ?? ''))
                        <i class="bi bi-search display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No matching requests</h5>
                        <p class="text-muted mb-0">Nothing matches <strong>{{ $search }}</strong>. Try another term or <a href="{{ request()->url() }}">clear the search</a>.</p>
                    @else
                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No Requests Found</h5>
                        <p class="text-muted">There are no user sign-up requests to review at this time.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge me-2"></i>Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestModalBody">
                <!-- Request details will be loaded here -->
            </div>
            <div class="modal-footer d-flex flex-wrap align-items-center gap-2">
                <div id="viewModalPendingActions" class="d-none me-auto">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="viewModalApproveBtn">
                            <i class="bi bi-check me-1"></i>Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="viewModalDeclineBtn">
                            <i class="bi bi-x me-1"></i>Decline
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary ms-auto" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="bi bi-check-circle me-2"></i>Approve Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve <strong id="approveEmail"></strong>?</p>
                <p class="text-muted small">This will allow the user to access the health center system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="approveForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check me-2"></i>Approve Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Decline Confirmation Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-x-circle me-2"></i>Decline Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to decline <strong id="declineEmail"></strong>?</p>
                <p class="text-muted small">This will reject the user's access request.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="declineForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x me-2"></i>Decline Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.user-requests-pagination p.small.text-muted {
    display: none;
}
</style>

<script>
let viewModalCurrent = { id: '', email: '' };

function escapeReqHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatReqDate(s) {
    if (!s) return 'N/A';
    const d = new Date(s);
    if (!isNaN(d.getTime())) {
        return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
    }
    return escapeReqHtml(String(s));
}

function pickCreatedRaw(d) {
    return d.submittedAt || d.createdAt || d.created_at || d.approved_at || null;
}

function buildRequestDetailHtml(d) {
    const status = d.status || 'pending';
    const statusClass = status === 'approved' ? 'success' : (status === 'declined' ? 'danger' : 'warning');
    const statusIcon = status === 'approved' ? 'check-circle' : (status === 'declined' ? 'x-circle' : 'clock');
    const email = d.email || d.username || 'N/A';
    let extraDates = '';
    if (d.approvedAt) {
        extraDates += `<div class="mb-2"><span class="text-muted small d-block">Approved at</span>${escapeReqHtml(formatReqDate(d.approvedAt))}</div>`;
    }
    if (d.declinedAt) {
        extraDates += `<div class="mb-2"><span class="text-muted small d-block">Declined at</span>${escapeReqHtml(formatReqDate(d.declinedAt))}</div>`;
    }
    return `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold text-muted small">Email</label>
                <p class="mb-0">${escapeReqHtml(String(email))}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold text-muted small">User ID</label>
                <p class="mb-0">${escapeReqHtml(String(d.userId ?? d.uid ?? d.id ?? 'N/A'))}</p>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label fw-semibold text-muted small">Full address</label>
                <p class="mb-0">${escapeReqHtml(String(d.fullAddress ?? d.address ?? d.location ?? 'N/A'))}</p>
            </div>
        </div>
        <hr class="my-3">
        <div class="mb-2">
            <label class="form-label fw-semibold text-muted small">Status</label>
            <div><span class="badge bg-${statusClass}"><i class="bi bi-${statusIcon} me-1"></i>${escapeReqHtml(String(status).charAt(0).toUpperCase() + String(status).slice(1))}</span></div>
        </div>
        <div class="mb-2">
            <span class="text-muted small d-block">Date created</span>
            ${escapeReqHtml(formatReqDate(pickCreatedRaw(d)))}
        </div>
        ${extraDates}`;
}

function viewRequest(requestId) {
    const body = document.getElementById('requestModalBody');
    const pendingWrap = document.getElementById('viewModalPendingActions');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    pendingWrap.classList.add('d-none');

    const modalEl = document.getElementById('viewRequestModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    fetch(`/user-requests/${requestId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(function (r) {
            if (!r.ok) throw new Error('load failed');
            return r.json();
        })
        .then(function (data) {
            viewModalCurrent = {
                id: data.id || requestId,
                email: data.email || data.username || 'this request'
            };
            body.innerHTML = buildRequestDetailHtml(data);
            if ((data.status || 'pending') === 'pending') {
                pendingWrap.classList.remove('d-none');
            } else {
                pendingWrap.classList.add('d-none');
            }
        })
        .catch(function () {
            body.innerHTML = '<p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Could not load request details.</p>';
            pendingWrap.classList.add('d-none');
        });
}

document.getElementById('viewModalApproveBtn').addEventListener('click', function () {
    const inst = bootstrap.Modal.getInstance(document.getElementById('viewRequestModal'));
    if (inst) inst.hide();
    approveRequest(viewModalCurrent.id, viewModalCurrent.email);
});

document.getElementById('viewModalDeclineBtn').addEventListener('click', function () {
    const inst = bootstrap.Modal.getInstance(document.getElementById('viewRequestModal'));
    if (inst) inst.hide();
    declineRequest(viewModalCurrent.id, viewModalCurrent.email);
});

function approveRequest(requestId, email) {
    document.getElementById('approveEmail').textContent = email;
    document.getElementById('approveForm').action = `/user-requests/${requestId}/approve`;
    
    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
    approveModal.show();
}

function declineRequest(requestId, email) {
    document.getElementById('declineEmail').textContent = email;
    document.getElementById('declineForm').action = `/user-requests/${requestId}/decline`;
    
    const declineModal = new bootstrap.Modal(document.getElementById('declineModal'));
    declineModal.show();
}
</script>
@endsection 