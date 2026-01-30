@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="py-4">
        <h1>System Administrator Dashboard</h1>
        <p class="text-muted">Manage RHU applications</p>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>{{ $stats['approved'] }}</h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>{{ $stats['active'] }}</h3>
                    <p class="mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>{{ $stats['rejected'] }}</h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0">⏳ Pending RHU Applications</h5>
        </div>
        <div class="card-body">
            @if(empty($pendingRhus))
                <p class="text-muted">No pending applications.</p>
            @else
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>RHU Name</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRhus as $rhu)
                            <tr id="rhu-row-{{ $rhu['id'] }}">
                                <td><strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong></td>
                                <td><small>{{ $rhu['email'] ?? 'N/A' }}</small></td>
                                <td>{{ $rhu['displayLocation'] ?? $rhu['city'] ?? 'N/A' }}</td>
                                <td><small>{{ $rhu['phone'] ?? 'N/A' }}</small></td>
                                <td>
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success approve-btn" data-rhu-id="{{ $rhu['id'] }}">
                                        <i class="bi bi-check-circle"></i> Approve & Send Email
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Toast notification for feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">✓ Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="successMessage"></div>
    </div>
    <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto">✕ Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="errorMessage"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const rhuId = this.getAttribute('data-rhu-id');
            const row = document.getElementById(`rhu-row-${rhuId}`);
            const rhuName = row.querySelector('strong').textContent;
            const btn = this;

            if (confirm(`Approve "${rhuName}" and send account setup email?`)) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

                fetch(`/admin/system-admin/${rhuId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const successMsg = document.getElementById('successMessage');
                        successMsg.innerHTML = `
                            <strong>${rhuName}</strong> has been approved!<br>
                            <strong>Username:</strong> ${data.username}<br>
                            <strong>Email:</strong> ${data.email}<br>
                            <small>Setup email sent. RHU will receive password setup link.</small>
                        `;
                        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
                        successToast.show();
                        
                        // Remove row after 2 seconds
                        setTimeout(() => {
                            row.style.opacity = '0.5';
                            row.style.textDecoration = 'line-through';
                            btn.disabled = true;
                            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Approved';
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-secondary');
                        }, 500);
                    } else {
                        // Show error toast
                        const errorMsg = document.getElementById('errorMessage');
                        errorMsg.innerHTML = `<strong>Error:</strong> ${data.error || 'Failed to approve RHU'}`;
                        const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                        errorToast.show();
                        
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-circle"></i> Approve & Send Email';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMsg = document.getElementById('errorMessage');
                    errorMsg.innerHTML = '<strong>Error:</strong> Failed to approve RHU. Check console for details.';
                    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
                    errorToast.show();
                    
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Approve & Send Email';
                });
            }
        });
    });
});
</script>
@endsection
