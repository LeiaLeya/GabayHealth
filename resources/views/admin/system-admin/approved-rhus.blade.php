@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.system-admin.dashboard') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="h2 mb-0">Approved RHUs - Credentials Sent</h1>
            <p class="text-muted mb-0">RHUs waiting to activate their accounts</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>RHU Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Approved</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRhus as $rhu)
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
                                    <div>
                                        <strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $rhu['city'] ?? 'N/A' }}, {{ $rhu['province'] ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="bg-light p-2 rounded">{{ $rhu['username'] ?? 'N/A' }}</code>
                            </td>
                            <td>
                                <a href="mailto:{{ $rhu['email'] }}">{{ $rhu['email'] }}</a>
                            </td>
                            <td>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($rhu['credentials_sent_at'])->format('M d, Y h:i A') ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Awaiting Activation
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-outline-primary" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-info resend-btn" data-rhu-id="{{ $rhu['id'] }}" title="Resend credentials">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No RHUs with credentials sent
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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
                        alert('✓ Credentials resent successfully');
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
