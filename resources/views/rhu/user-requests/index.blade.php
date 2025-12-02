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
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ count($requests) }}</h4>
                            <small>Total Requests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'pending')) }}</h4>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'approved')) }}</h4>
                            <small>Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'declined')) }}</h4>
                            <small>Declined</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul me-2"></i>All Requests
            </h5>
        </div>
        <div class="card-body">
            @if(count($requests) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Email</th>
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
                                                <strong>{{ $request['email'] ?? 'N/A' }}</strong>
                                                @if(isset($request['submittedAt']))
                                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($request['submittedAt'])->format('M d, Y g:i A') }}</small>
                                                @endif
                                            </div>
                                        </div>
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
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                    <h5 class="text-muted">No Requests Found</h5>
                    <p class="text-muted">There are no user sign-up requests to review at this time.</p>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
</style>

<script>
function viewRequest(requestId) {
    // Load request details via AJAX or redirect to show page
    window.location.href = `/user-requests/${requestId}`;
}

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