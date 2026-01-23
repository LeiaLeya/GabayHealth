@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.system-admin.dashboard') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="h2 mb-0">RHU Application Details</h1>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - RHU Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-hospital"></i> Rural Health Unit Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            @if($rhu['logo_url'] ?? false)
                                <img src="{{ $rhu['logo_url'] }}" alt="Logo" class="img-fluid rounded" style="max-width: 200px;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 200px; height: 200px;">
                                    <i class="fas fa-hospital fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</h4>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ ucfirst($rhu['status'] ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Applied:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mt-4 mb-3">Location Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Address:</strong><br>
                                {{ $rhu['fullAddress'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Region:</strong> {{ $rhu['region'] ?? 'N/A' }}<br>
                                <strong>Province:</strong> {{ $rhu['province'] ?? 'N/A' }}<br>
                                <strong>City:</strong> {{ $rhu['city'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    @if(($rhu['latitude'] ?? false) && ($rhu['longitude'] ?? false))
                        <hr>
                        <p>
                            <strong>Coordinates:</strong> 
                            {{ $rhu['latitude'] }}, {{ $rhu['longitude'] }}
                            <a href="https://maps.google.com/?q={{ $rhu['latitude'] }},{{ $rhu['longitude'] }}" target="_blank" class="ms-2">
                                <i class="fas fa-external-link-alt"></i> View on Map
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            @if($rhu['status'] === 'pending')
                <div class="card border-success mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle"></i> Approve Application
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            This will generate credentials and send them to the RHU email address.
                        </p>
                        <button type="button" class="btn btn-success btn-block w-100" id="approveBtn">
                            <i class="fas fa-check"></i> Generate & Send Credentials
                        </button>
                    </div>
                </div>

                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-times-circle"></i> Reject Application
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="rejectReason">Reason for rejection:</label>
                            <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter rejection reason..."></textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-block w-100" id="rejectBtn">
                            <i class="fas fa-trash"></i> Reject
                        </button>
                    </div>
                </div>
            @elseif($rhu['status'] === 'credentials_sent')
                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check"></i> Credentials Sent
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Credentials were sent on {{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') }}
                        </p>
                        <p class="text-muted mb-3">
                            <strong>Username:</strong> {{ $rhu['username'] ?? 'N/A' }}
                        </p>
                        <button type="button" class="btn btn-info w-100" id="resendBtn">
                            <i class="fas fa-redo"></i> Resend Credentials
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rhuId = '{{ $rhu['id'] }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Approve button
    const approveBtn = document.getElementById('approveBtn');
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            if (confirm('Are you sure? This will generate credentials and send them to the RHU email.')) {
                fetch(`/admin/system-admin/${rhuId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✓ Credentials Generated!\n\nUsername: ${data.username}\nEmail: ${data.email}\n\nWaiting for RHU to change password.`);
                        location.reload();
                    } else {
                        alert(`Error: ${data.error || 'Failed to approve'}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    }

    // Reject button
    const rejectBtn = document.getElementById('rejectBtn');
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
                alert('Please enter a reason for rejection');
                return;
            }

            if (confirm('Are you sure you want to reject this application?')) {
                fetch(`/admin/system-admin/${rhuId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ reason }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ Application rejected');
                        window.location.href = '{{ route("admin.system-admin.dashboard") }}';
                    } else {
                        alert(`Error: ${data.error || 'Failed to reject'}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    }

    // Resend button
    const resendBtn = document.getElementById('resendBtn');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (confirm('Resend credentials to this RHU?')) {
                fetch(`/admin/system-admin/${rhuId}/resend-credentials`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ Credentials resent to RHU email');
                    } else {
                        alert(`Error: ${data.error || 'Failed to resend'}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    }
});
</script>
@endsection
