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
            <a href="{{ route('bhc.reports.verify') }}" class="btn btn-outline-dark">Pending Reports</a>
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

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bhc.reports.verified.export') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Verified From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Verified To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Symptom</label>
                        <input type="text" class="form-control" name="symptom" placeholder="e.g. fever" value="{{ request('symptom') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Verified By</label>
                        <input type="text" class="form-control" name="verified_by" placeholder="Staff name" value="{{ request('verified_by') }}">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-download me-1"></i>CSV
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Export the current verified report dataset as CSV. Filters are optional.</small>
            </form>
        </div>
    </div>

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
                                        <th>Actions</th>
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
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="openResolveModal('{{ route('bhc.reports.resolve', $report['id']) }}')">
                                                    <i class="bi bi-check2-circle me-1"></i>Resolved
                                                </button>
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

<form id="resolveForm" method="POST" style="display: none;">
    @csrf
</form>

<div class="modal fade" id="confirmResolveModal" tabindex="-1" aria-labelledby="confirmResolveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="confirmResolveModalLabel">Mark Report as Resolved</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Mark this verified report as resolved? It will no longer appear in current active analytics.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmResolveBtn">
                    <i class="bi bi-check2-circle me-1"></i>Yes, Mark as Resolved
                </button>
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

<script>
let resolveActionUrl = '';

function openResolveModal(actionUrl) {
    resolveActionUrl = actionUrl;
    new bootstrap.Modal(document.getElementById('confirmResolveModal')).show();
}

document.getElementById('confirmResolveBtn')?.addEventListener('click', function () {
    if (!resolveActionUrl) return;
    const form = document.getElementById('resolveForm');
    form.action = resolveActionUrl;
    form.submit();
});
</script>
@endsection

