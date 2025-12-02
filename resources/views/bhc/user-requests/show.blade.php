@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Request Details</h2>
            <p class="text-muted mb-0">View detailed information about this sign-up request</p>
        </div>
        <div>
            <a href="{{ route('user-requests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Requests
            </a>
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Request Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>Request Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Email Address</label>
                            <p class="mb-0">{{ $request['email'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">User ID</label>
                            <p class="mb-0">{{ $request['userId'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Health Center Name</label>
                            <p class="mb-0">{{ $request['healthCenterName'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Barangay</label>
                            <p class="mb-0">{{ $request['barangay'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Contact Number</label>
                            <p class="mb-0">{{ $request['contact_number'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">City Code</label>
                            <p class="mb-0">{{ $request['city'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Full Address</label>
                        <p class="mb-0">{{ $request['fullAddress'] ?? $request['address'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Status Information
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $status = $request['status'] ?? 'pending';
                        $statusClass = $status === 'approved' ? 'success' : ($status === 'declined' ? 'danger' : 'warning');
                        $statusIcon = $status === 'approved' ? 'check-circle' : ($status === 'declined' ? 'x-circle' : 'clock');
                    @endphp
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Current Status</label>
                        <div>
                            <span class="badge bg-{{ $statusClass }} fs-6">
                                <i class="bi bi-{{ $statusIcon }} me-2"></i>
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">Submitted At</label>
                        <p class="mb-0">
                            @if(isset($request['submittedAt']))
                                {{ \Carbon\Carbon::parse($request['submittedAt'])->format('F d, Y g:i A') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    
                    @if(isset($request['approvedAt']))
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Approved At</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($request['approvedAt'])->format('F d, Y g:i A') }}</p>
                        </div>
                    @endif
                    
                    @if(isset($request['declinedAt']))
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted">Declined At</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($request['declinedAt'])->format('F d, Y g:i A') }}</p>
                        </div>
                    @endif
                    
                    @if($status === 'pending')
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="approveRequest('{{ $request['id'] }}', '{{ $request['email'] ?? 'this request' }}')">
                                <i class="bi bi-check me-2"></i>Approve Request
                            </button>
                            <button type="button" class="btn btn-danger" onclick="declineRequest('{{ $request['id'] }}', '{{ $request['email'] ?? 'this request' }}')">
                                <i class="bi bi-x me-2"></i>Decline Request
                            </button>
                        </div>
                    @endif
                </div>
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

<script>
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