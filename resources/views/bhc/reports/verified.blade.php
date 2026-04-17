@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Verified Reports</h2>
            <p class="text-muted mb-0">View all verified health reports</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bhc.reports.verify') }}" class="btn btn-outline-dark">Verify Reports</a>
            <a href="{{ route('bhc.reports.rejected') }}" class="btn btn-outline-dark">Rejected Reports</a>
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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Verified Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Verified Health Reports</h5>
                </div>
                <div class="card-body">
                    @if(count($verifiedReports) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Barangay</th>
                                        <th>Symptoms</th>
                                        <th>Affected Person</th>
                                        <th>Start Date</th>
                                        <th>Verified Date</th>
                                        <th>Verified By</th>
                                        <th>Additional Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($verifiedReports as $report)
                                        @php
                                            $collapseId = 'collapse_' . $report['id'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">
                                                    {{ $barangayNames[$report['barangayId'] ?? ''] ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($report['symptoms']) && is_array($report['symptoms']))
                                                    @foreach($report['symptoms'] as $symptom)
                                                        <span class="badge bg-dark text-white me-1">{{ ucfirst($symptom) }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No symptoms listed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ ucfirst($report['affectedPerson'] ?? 'Unknown') }}</span>
                                            </td>
                                            <td>
                                                @if(isset($report['startDate']))
                                                    {{ \Carbon\Carbon::parse($report['startDate'])->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($report['verified_at']))
                                                    {{ \Carbon\Carbon::parse($report['verified_at'])->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-muted">Not available</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $report['verified_by'] ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $additionalInfo = $report['additionalInfo'] ?? null;
                                                    if (is_array($additionalInfo)) {
                                                        $additionalInfo = json_encode($additionalInfo);
                                                    }
                                                    $displayInfo = $additionalInfo ?: 'No additional info';
                                                @endphp
                                                <span class="text-muted">{{ Str::limit($displayInfo, 50) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Verified Reports</h5>
                            <p class="text-muted">There are no verified reports at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #dee2e6;
}

.card-header {
    background-color: #198754;
    color: white;
    border-bottom: 1px solid #198754;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #212529;
    background-color: #f8f9fa;
    border-bottom: 2px solid #212529;
}

[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
}
</style>
@endsection

