@extends('layouts.app')

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 14px;
        color: white;
        cursor: pointer;
    }
    
    .edit-btn {
        background-color: #0d6efd;
    }
    
    .edit-btn:hover {
        background-color: #0b5ed7;
        transform: scale(1.05);
        color: white;
        text-decoration: none;
    }
    
    .delete-btn {
        background-color: #dc3545;
    }
    
    .delete-btn:hover {
        background-color: #bb2d3b;
        transform: scale(1.05);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.075);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark mb-0">Account Management</h2>
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
        <div class="col-12">
            <!-- Health Center Profile Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Health Center Profile</h4>
                    <a href="{{ route('accounts.profile.edit') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-pencil"></i>
                        Edit Profile
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Health Center Name:</label>
                                <p class="mb-0">{{ $healthCenter['healthCenterName'] ?? 'Not set' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contact Number:</label>
                                <p class="mb-0">{{ $healthCenter['contact_number'] ?? $healthCenter['contactInfo'] ?? 'Not set' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address:</label>
                                <p class="mb-0">{{ $healthCenter['email'] ?? 'Not set' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Address:</label>
                                <p class="mb-0">{{ $healthCenter['address'] ?? $healthCenter['fullAddress'] ?? 'Not set' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Open Days:</label>
                                <div class="d-flex flex-wrap gap-1">
                                    @if(isset($healthCenter['open_days']))
                                        @if(is_array($healthCenter['open_days']))
                                            @foreach($healthCenter['open_days'] as $day)
                                                <span class="badge bg-primary">{{ $day }}</span>
                                            @endforeach
                                        @else
                                            @php
                                                $days = explode(',', $healthCenter['open_days']);
                                            @endphp
                                            @foreach($days as $day)
                                                <span class="badge bg-primary">{{ trim($day) }}</span>
                                            @endforeach
                                        @endif
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Operating Hours:</label>
                                <p class="mb-0">
                                    @if(isset($healthCenter['open_time']) && isset($healthCenter['close_time']))
                                        {{ $healthCenter['open_time'] }} - {{ $healthCenter['close_time'] }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Accounts Management -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Staff Accounts Management</h4>
                    <a href="{{ route('bhc.accounts.staff.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i>
                        Add Staff
                    </a>
                </div>
                <div class="card-body">
                    @if(count($staffAccounts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 15%;">Name</th>
                                        <th style="width: 20%;">Email</th>
                                        <th style="width: 10%;">Role</th>
                                        <th style="width: 15%;">Contact Number</th>
                                        <th style="width: 15%;">Specialization</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 10%;">Created</th>
                                        <th style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffAccounts as $staff)
                                    <tr>
                                        <td>{{ $staff['name'] }}</td>
                                        <td>{{ $staff['email'] }}</td>
                                        <td>
                                            @php
                                                $roleBadge = match($staff['role']) {
                                                    'doctor' => 'primary',
                                                    'midwife' => 'success',
                                                    'nurse' => 'info',
                                                    'bhw' => 'secondary',
                                                    default => 'secondary'
                                                };
                                                $roleDisplay = match($staff['role']) {
                                                    'bhw' => 'Barangay Health Worker',
                                                    default => ucfirst($staff['role'])
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $roleBadge }}">
                                                {{ $roleDisplay }}
                                            </span>
                                        </td>
                                        <td>{{ $staff['contact_number'] }}</td>
                                        <td>{{ $staff['specialization'] ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $staff['status'] === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($staff['status']) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($staff['created_at'])->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <div class="action-buttons">
                                                <a href="{{ route('accounts.staff.edit', $staff['id']) }}" 
                                                   class="action-btn edit-btn" 
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="action-btn delete-btn" 
                                                        onclick="deleteStaff('{{ $staff['id'] }}', '{{ $staff['name'] }}')"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people display-4 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">No Staff Accounts Found</h5>
                            <p class="text-muted mb-0">Start by adding staff members to your health center.</p>
                            <a href="{{ route('bhc.accounts.staff.create') }}" class="btn btn-primary mt-3 d-flex align-items-center gap-2 mx-auto" style="width: fit-content;">
                                <i class="bi bi-plus-circle"></i>
                                Add First Staff Member
                            </a>
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="staffName"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Delete staff function
    function deleteStaff(staffId, staffName) {
        document.getElementById('staffName').textContent = staffName;
        document.getElementById('deleteForm').action = `/accounts/staff/${staffId}`;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>
@endpush 