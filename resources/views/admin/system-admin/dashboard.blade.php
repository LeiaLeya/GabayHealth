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
        <div class="card-header">
            <h5 class="mb-0">Pending RHU Applications</h5>
        </div>
        <div class="card-body">
            @if(empty($pendingRhus))
                <p class="text-muted">No pending applications.</p>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>RHU Name</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRhus as $rhu)
                            <tr>
                                <td><strong>{{ $rhu['rhuName'] ?? $rhu['name'] ?? 'N/A' }}</strong></td>
                                <td>{{ $rhu['email'] ?? 'N/A' }}</td>
                                <td>{{ $rhu['city'] ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.system-admin.view-application', $rhu['id']) }}" class="btn btn-sm btn-primary">View</a>
                                    <button type="button" class="btn btn-sm btn-success approve-btn" data-rhu-id="{{ $rhu['id'] }}">Approve</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            const rhuId = this.getAttribute('data-rhu-id');
            const row = this.closest('tr');
            const rhuName = row.querySelector('strong').textContent;

            if (confirm(`Approve "${rhuName}"?`)) {
                fetch(`/admin/system-admin/${rhuId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Approved! Username: ${data.username}`);
                        location.reload();
                    } else {
                        alert(`Error: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error approving RHU');
                });
            }
        });
    });
});
</script>
@endsection
