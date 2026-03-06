@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Registered Barangays</h1>
            <p class="text-muted mb-0">Manage barangay health centers under {{ session('user.name', 'this RHU') }}</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Barangays List -->
    @if(empty($barangays))
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No barangays found</strong>
            <p class="mb-0 mt-2">No registered barangays under this RHU yet.</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Logo</th>
                            <th>Health Center Name</th>
                            <th>Email</th>
                            <th style="width: 100px;">Status</th>
                            <th>Location</th>
                            <th>Applied</th>
                            <th style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangays as $barangay)
                            <tr>
                                <td class="align-middle">
                                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; overflow: hidden;">
                                        @if($barangay['logo_url'])
                                            <img src="{{ $barangay['logo_url'] }}" 
                                                 alt="{{ $barangay['healthCenterName'] }}" 
                                                 style="max-height: 50px; max-width: 50px; object-fit: contain;"
                                                 onerror="this.src='{{ asset('images/seal.png') }}'; this.classList.add('fallback-logo');"
                                                 loading="lazy">
                                        @else
                                            <img src="{{ asset('images/seal.png') }}" 
                                                 alt="Seal" 
                                                 class="fallback-logo"
                                                 style="max-height: 50px; max-width: 50px; object-fit: contain;">
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle fw-bold">
                                    {{ $barangay['healthCenterName'] }}
                                </td>
                                <td class="align-middle">
                                    {{ $barangay['email'] }}
                                </td>
                                <td class="align-middle">
                                    @if($barangay['status'] === 'active')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </span>
                                    @elseif($barangay['status'] === 'pending_setup')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending Setup
                                        </span>
                                    @elseif($barangay['status'] === 'approved')
                                        <span class="badge bg-info">
                                            <i class="bi bi-check2 me-1"></i> Approved
                                        </span>
                                    @elseif($barangay['status'] === 'pending')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($barangay['status']) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <small class="text-muted">{{ $barangay['location'] }}</small>
                                </td>
                                <td class="align-middle">
                                    <small>{{ $barangay['appliedDate'] }}</small>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('rhu.barangays.show', $barangay['id']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<style>
    .table {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        color: #495057;
    }

    .table tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .fallback-logo {
        opacity: 0.5;
    }

    .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .card {
        border-radius: 0.75rem;
        overflow: hidden;
    }
</style>
@endsection
