@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-0">Distribution History</h2>
            <p class="text-muted mb-0">{{ $parentData['name'] }} - Lot No: {{ $batchData['lot_number'] ?? 'N/A' }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('inventory.show', $parentData['id']) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to {{ $parentData['name'] }}
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Batch Summary Card -->
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle me-2"></i>Batch Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info mb-1">{{ $batchData['quantity'] }}</h3>
                        <p class="text-muted mb-0">Current Stock</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success mb-1">{{ count($distributions) }}</h3>
                        <p class="text-muted mb-0">Total Distributions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning mb-1">
                            {{ collect($distributions)->sum('quantity_distributed') }}
                        </h3>
                        <p class="text-muted mb-0">Total Distributed</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary mb-1">
                            {{ \Carbon\Carbon::parse($batchData['expiration_date'])->format('M d, Y') }}
                        </h3>
                        <p class="text-muted mb-0">Expiration Date</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(count($distributions) > 0)
        <!-- Distribution History Table Card -->
        <div class="card shadow-sm border border-info-subtle" style="border-width:2px;">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Distribution History
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 inventory-table">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">Date</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Resident Name</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Quantity</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Reason</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Distributed By</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Recorded At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distributions as $distribution)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">
                                            {{ \Carbon\Carbon::parse($distribution['distribution_date'])->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-dark">{{ $distribution['resident_name'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-success">{{ $distribution['quantity_distributed'] }} {{ ucfirst($parentData['unit_type']) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-muted" style="max-width: 200px;">
                                            {{ Str::limit($distribution['reason'] ?? 'No reason provided', 50) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-muted">{{ $distribution['distributed_by'] ?? 'Health Worker' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($distribution['distributed_at'])->format('M d, Y g:i A') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-clock-history display-4 d-block mb-3"></i>
                <h5>No distribution history found</h5>
                <p class="mb-0">This batch hasn't been distributed yet.</p>
            </div>
        </div>
    @endif
</div>

<style>
.card {
    border-radius: 0.75rem;
    overflow: hidden;
}

.table th, .table td {
    vertical-align: middle;
    background: #fff;
    font-size: 1rem;
}

.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}

.table tr {
    border-radius: 0.5rem;
}

.table tbody tr {
    border-top: none;
    border-bottom: 1px solid #f1f1f1;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Add visible outline inside the table */
.inventory-table th, .inventory-table td {
    border-left: none !important;
    border-right: none !important;
    background: #fff;
}

.inventory-table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #17a2b8 !important;
}

.inventory-table tr {
    border-radius: 0.5rem;
}

.inventory-table tbody tr {
    border-top: none;
    border-bottom: 1.5px solid #b6e3e8 !important;
}
</style>
@endsection 