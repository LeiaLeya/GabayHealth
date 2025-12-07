@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Release History</h2>
            <p class="text-muted mb-0">{{ $parentData['name'] }} - Complete release tracking</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.show', $parentData['id']) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Medicine
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="text-primary">
                        <i class="bi bi-box-seam fs-2"></i>
                    </div>
                    <h4 class="fw-bold mt-2 mb-1">{{ $totalReleased }}</h4>
                    <small class="text-muted">Total {{ ucfirst($parentData['unit_type'] ?? 'Units') }} Released</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="text-success">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <h4 class="fw-bold mt-2 mb-1">{{ $totalRecipients }}</h4>
                    <small class="text-muted">Unique Recipients</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="text-info">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                    <h4 class="fw-bold mt-2 mb-1">{{ $releaseCount }}</h4>
                    <small class="text-muted">Total Releases</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="text-warning">
                        <i class="bi bi-graph-up fs-2"></i>
                    </div>
                    <h4 class="fw-bold mt-2 mb-1">{{ $parentData['quantity'] ?? 0 }}</h4>
                    <small class="text-muted">Current Stock</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Release History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul me-2"></i>Medicine Release History
            </h5>
        </div>
        <div class="card-body p-0">
            @if(count($allReleases) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Date & Time</th>
                                <th width="15%">Recipient</th>
                                <th width="10%">Quantity</th>
                                <th width="12%">Lot Number</th>
                                <th width="12%">Batch Expiry</th>
                                <th width="20%">Reason</th>
                                <th width="16%">Released By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allReleases as $release)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $release['release_date'] ? \Carbon\Carbon::parse($release['release_date'])->format('M d, Y') : 'N/A' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $release['released_at'] ? \Carbon\Carbon::parse($release['released_at'])->format('h:i A') : '' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $release['resident_name'] }}</div>
                                                @if($release['resident_id'])
                                                    <small class="text-muted">ID: {{ substr($release['resident_id'], 0, 8) }}...</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">
                                            {{ $release['quantity_released'] }} {{ ucfirst($parentData['unit_type'] ?? 'units') }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="bg-light text-dark p-1 rounded">{{ $release['lot_number'] ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        @php
                                            $expiryDate = $release['batch_expiration'];
                                            $isExpiringSoon = $expiryDate && \Carbon\Carbon::parse($expiryDate)->diffInDays(now()) <= 30;
                                            $isExpired = $expiryDate && \Carbon\Carbon::parse($expiryDate)->isPast();
                                        @endphp
                                        @if($expiryDate)
                                            <span class="badge {{ $isExpired ? 'bg-danger' : ($isExpiringSoon ? 'bg-warning' : 'bg-secondary') }}">
                                                {{ \Carbon\Carbon::parse($expiryDate)->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $release['reason'] ?: 'Not specified' }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $release['released_by'] }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No Release History</h5>
                    <p class="text-muted">No medicine has been released for this item yet.</p>
                    <a href="{{ route('inventory.show', $parentData['id']) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Start Releasing Medicine
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.2s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
    padding: 12px;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

code {
    font-size: 0.875em;
}

.badge {
    font-weight: 500;
}
</style>
@endsection