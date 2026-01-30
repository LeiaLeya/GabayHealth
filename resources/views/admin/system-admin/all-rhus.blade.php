@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.system-admin.dashboard') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="h2 mb-0">All Rural Health Units</h1>
            <p class="text-muted mb-0">Manage all RHU applications and accounts</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#all">
                All ({{ count($rhus) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#pending">
                Pending ({{ count(array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'pending')) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#approved">
                Credentials Sent ({{ count(array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'credentials_sent')) }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#rejected">
                Rejected ({{ count(array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'rejected')) }})
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- All RHUs Tab -->
        <div id="all" class="tab-pane fade show active">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($rhu['logo_url'] ?? false)
                                                <img src="{{ $rhu['logo_url'] }}" alt="Logo" class="rounded me-2" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <div class="rounded me-2 bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-hospital text-muted"></i>
                                                </div>
                                            @endif
                                            <strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($rhu['status'] ?? 'pending') {
                                                'pending' => 'bg-warning',
                                                'credentials_sent' => 'bg-success',
                                                'active' => 'bg-info',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ match($rhu['status'] ?? 'pending') {
                                                'pending' => 'Pending',
                                                'credentials_sent' => 'Credentials Sent',
                                                'active' => 'Active',
                                                'rejected' => 'Rejected',
                                                default => ucfirst($rhu['status'] ?? 'unknown')
                                            } }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($rhu['displayLocation']))
                                            <small>{{ $rhu['displayLocation'] }}</small>
                                        @else
                                            <small>{{ $rhu['city'] ?? 'N/A' }}{{ isset($rhu['province']) ? ', ' . $rhu['province'] : '' }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if(($rhu['status'] ?? '') === 'credentials_sent')
                                            <button type="button" class="btn btn-sm btn-outline-info resend-btn" data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No RHUs found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Tab -->
        <div id="pending" class="tab-pane fade">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>RHU Name</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $pendingRhus = array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'pending');
                            @endphp
                            @forelse($pendingRhus as $rhu)
                                <tr>
                                    <td>
                                        <strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a>
                                    </td>
                                    <td>
                                        <small>{{ $rhu['city'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($rhu['created_at'])->format('M d, Y') ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No pending applications
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Approved Tab -->
        <div id="approved" class="tab-pane fade">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>RHU Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Approved</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $approvedRhus = array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'credentials_sent');
                            @endphp
                            @forelse($approvedRhus as $rhu)
                                <tr>
                                    <td>
                                        <strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $rhu['username'] ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y') ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info resend-btn" data-rhu-id="{{ $rhu['id'] }}">
                                            <i class="fas fa-redo"></i> Resend
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No approved applications
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rejected Tab -->
        <div id="rejected" class="tab-pane fade">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>RHU Name</th>
                                <th>Email</th>
                                <th>Rejection Reason</th>
                                <th>Rejected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rejectedRhus = array_filter($rhus, fn($r) => ($r['status'] ?? '') === 'rejected');
                            @endphp
                            @forelse($rejectedRhus as $rhu)
                                <tr>
                                    <td>
                                        <strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a>
                                    </td>
                                    <td>
                                        <small>{{ $rhu['rejection_reason'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($rhu['rejected_at'])->format('M d, Y') ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No rejected applications
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Resend credentials button
    document.querySelectorAll('.resend-btn').forEach(button => {
        button.addEventListener('click', function() {
            const rhuId = this.getAttribute('data-rhu-id');
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
                        alert('✓ Credentials resent');
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
    });
});
</script>
@endsection
