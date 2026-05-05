@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Flash Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert" id="successToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert" id="errorToast">
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
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Account Management</h2>
            <p class="text-muted mb-0 small">Manage health center profile and staff accounts</p>
        </div>
    </div>

    {{-- Health Center Profile Card --}}
    <div class="acct-card mb-4">
        <div class="acct-card-header">
            <div>
                <div class="acct-badge">Health Center</div>
                <div class="acct-card-title">{{ $healthCenter['healthCenterName'] ?? 'Health Center Profile' }}</div>
            </div>
            <a href="{{ route('rhu.accounts.profile.edit') }}" class="btn-acct-action">
                <i class="bi bi-pencil me-1"></i>Edit Profile
            </a>
        </div>
        <div class="acct-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-row"><div class="info-icon"><i class="bi bi-telephone-fill"></i></div><div><div class="info-label">Contact Number</div><div class="info-value">{{ $healthCenter['contact_number'] ?? $healthCenter['contactInfo'] ?? '—' }}</div></div></div>
                    <div class="info-row"><div class="info-icon"><i class="bi bi-envelope-fill"></i></div><div><div class="info-label">Email Address</div><div class="info-value">{{ $healthCenter['email'] ?? '—' }}</div></div></div>
                    <div class="info-row"><div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div><div><div class="info-label">Address</div><div class="info-value">{{ $healthCenter['address'] ?? $healthCenter['fullAddress'] ?? '—' }}</div></div></div>
                </div>
                <div class="col-md-6">
                    <div class="info-row"><div class="info-icon"><i class="bi bi-calendar-week-fill"></i></div><div><div class="info-label">Open Days</div><div class="info-value">
                        @if(isset($healthCenter['open_days']))
                            @php $days = is_array($healthCenter['open_days']) ? $healthCenter['open_days'] : explode(',', $healthCenter['open_days']); @endphp
                            <div class="d-flex flex-wrap gap-1 mt-1">@foreach($days as $d)<span class="day-pill">{{ trim($d) }}</span>@endforeach</div>
                        @else —
                        @endif
                    </div></div></div>
                    <div class="info-row"><div class="info-icon"><i class="bi bi-clock-fill"></i></div><div><div class="info-label">Operating Hours</div><div class="info-value">
                        @if(isset($healthCenter['open_time']) && isset($healthCenter['close_time']))
                            {{ $healthCenter['open_time'] }} – {{ $healthCenter['close_time'] }}
                        @else — @endif
                    </div></div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Accounts --}}
    <div class="acct-card">
        <div class="acct-card-header">
            <div>
                <div class="acct-badge">Staff</div>
                <div class="acct-card-title">Staff Accounts</div>
            </div>
            <a href="{{ route('rhu.accounts.staff.create') }}" class="btn-acct-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Staff
            </a>
        </div>
        <div class="acct-card-body p-0">
            @if(count($staffAccounts) > 0)
            <div class="table-responsive">
                <table class="acct-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="width:80px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffAccounts as $staff)
                        <tr>
                            <td class="fw-semibold" style="color:#37352f;">{{ $staff['name'] }}</td>
                            <td class="text-muted small">{{ $staff['email'] }}</td>
                            <td>
                                @php
                                    $roleColors = ['doctor'=>'blue','midwife'=>'green','nurse'=>'teal','bhw'=>'gray'];
                                    $roleColor = $roleColors[$staff['role']] ?? 'gray';
                                    $roleDisplay = $staff['role'] === 'bhw' ? 'Barangay Health Worker' : ucfirst($staff['role']);
                                @endphp
                                <span class="role-pill {{ $roleColor }}">{{ $roleDisplay }}</span>
                            </td>
                            <td class="small">{{ $staff['contact_number'] }}</td>
                            <td class="small text-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $staff['address'] ?? '' }}">{{ $staff['address'] ?? '—' }}</td>
                            <td>
                                <span class="status-pill {{ $staff['status'] === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($staff['status']) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($staff['created_at'])->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('rhu.accounts.staff.edit', $staff['id']) }}" class="tbl-btn edit" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button class="tbl-btn delete" onclick="openDeleteConfirm('{{ $staff['id'] }}','{{ addslashes($staff['name']) }}','/rhu/accounts/staff/{{ $staff['id'] }}','Delete Staff')" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="acct-empty">
                <i class="bi bi-people"></i>
                <div class="acct-empty-title">No Staff Accounts</div>
                <div class="acct-empty-text">Add your first staff member to get started.</div>
                <a href="{{ route('rhu.accounts.staff.create') }}" class="btn btn-dark rounded-pill px-4 mt-3 btn-sm">Add Staff Member</a>
            </div>
            @endif
        </div>
    </div>
</div>

@include('partials.delete-confirm-modal', ['modalId' => 'acctDeleteModal'])

<style>
.acct-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; }
.acct-card-header { display:flex; align-items:flex-start; justify-content:space-between; padding:16px 20px 12px; background:#1657c1; }
.acct-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.acct-card-title { font-size:.95rem; font-weight:600; color:#fff; }
.btn-acct-action { background:rgba(255,255,255,.12); border:none; color:rgba(255,255,255,.9); font-size:.8rem; font-weight:500; padding:6px 14px; border-radius:6px; text-decoration:none; transition:background .1s; }
.btn-acct-action:hover { background:rgba(255,255,255,.22); color:#fff; }
.btn-acct-primary { background:#fff; border:none; color:#37352f; font-size:.8rem; font-weight:600; padding:6px 14px; border-radius:6px; text-decoration:none; transition:opacity .1s; }
.btn-acct-primary:hover { opacity:.85; color:#37352f; }
.acct-card-body { padding:16px 20px; }
.info-row { display:flex; align-items:flex-start; gap:10px; padding:8px 0; border-bottom:1px solid #f1f1ef; }
.info-row:last-child { border-bottom:none; }
.info-icon { width:28px; height:28px; border-radius:5px; background:#f1f1ef; display:flex; align-items:center; justify-content:center; color:#787774; font-size:.85rem; flex-shrink:0; margin-top:1px; }
.info-label { font-size:.65rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#9b9b9b; margin-bottom:2px; }
.info-value { font-size:.85rem; font-weight:500; color:#37352f; }
.day-pill { background:#f1f1ef; color:#787774; font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.acct-table { width:100%; border-collapse:collapse; }
.acct-table thead tr { background:#1657c1; border-bottom:1px solid #1657c1; color:#fff; }
.acct-table th { padding:10px 14px; font-size:.68rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#fff; white-space:nowrap; }
.acct-table td { padding:11px 14px; font-size:.82rem; border-bottom:1px solid #f1f1ef; vertical-align:middle; }
.acct-table tbody tr:last-child td { border-bottom:none; }
.acct-table tbody tr:hover { background:#fafaf9; }
.role-pill { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.role-pill.blue { background:#dbeafe; color:#1e40af; }
.role-pill.green { background:#dcfce7; color:#166534; }
.role-pill.teal { background:#ccfbf1; color:#0f766e; }
.role-pill.gray { background:#f1f1ef; color:#787774; }
.status-pill { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.status-pill.active { background:#dcfce7; color:#166534; }
.status-pill.inactive { background:#fee2e2; color:#991b1b; }
.tbl-btn { width:28px; height:28px; border-radius:5px; border:none; display:flex; align-items:center; justify-content:center; font-size:.75rem; cursor:pointer; text-decoration:none; transition:background .1s; }
.tbl-btn.edit { background:#f1f1ef; color:#787774; }
.tbl-btn.edit:hover { background:#dbeafe; color:#1e40af; }
.tbl-btn.delete { background:#f1f1ef; color:#787774; }
.tbl-btn.delete:hover { background:#fee2e2; color:#b91c1c; }
.acct-empty { text-align:center; padding:50px 20px; }
.acct-empty i { font-size:2.2rem; color:#c4c4c2; display:block; margin-bottom:10px; }
.acct-empty-title { font-size:.95rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.acct-empty-text { font-size:.82rem; color:#9b9b9b; }
.modal-header-custom { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.modal-type-badge { display:inline-block; font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; }
.modal-title-custom { font-size:1rem; font-weight:600; color:#fff; margin:0; }
</style>

@push('scripts')
<script>
// delete handled by partials/delete-confirm-modal
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>
@endpush
@endsection
