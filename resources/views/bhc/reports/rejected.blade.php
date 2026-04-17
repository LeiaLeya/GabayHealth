@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Rejected Reports</h2>
            <p class="text-muted mb-0">View all rejected health reports with rejection reasons</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.verify') }}" class="btn btn-warning">
                <i class="bi bi-patch-check me-2"></i>Verify Reports
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Go to Reports
            </a>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['total_rejected'] }}</h4>
                            <small class="text-muted">Total Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-day fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['rejected_today'] }}</h4>
                            <small class="text-muted">Rejected Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['rejected_this_month'] }}</h4>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejected Reports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-x me-2"></i>Rejected Health Reports
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($rejectedReports) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Barangay</th>
                                        <th>Symptoms</th>
                                        <th>Affected Person</th>
                                        <th>Start Date</th>
                                        <th>Reported Date</th>
                                        <th>Rejected Date</th>
                                        <th>Rejected By</th>
                                        <th>Rejection Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedReports as $report)
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
                                                        @php
                                                            $symptomColors = [
                                                                'fever' => 'warning',
                                                                'dengue' => 'danger',
                                                                'diarrhea' => 'purple',
                                                                'rash' => 'info',
                                                                'cough' => 'secondary',
                                                                'headache' => 'dark'
                                                            ];
                                                            $color = $symptomColors[strtolower($symptom)] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }} me-1">{{ ucfirst($symptom) }}</span>
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
                                                @if(isset($report['createdAt']))
                                                    {{ \Carbon\Carbon::parse($report['createdAt'])->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($report['rejected_at']))
                                                    <span class="text-danger fw-semibold">
                                                        {{ \Carbon\Carbon::parse($report['rejected_at'])->format('M d, Y H:i') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $report['rejected_by'] ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $rejectionReason = $report['rejection_reason'] ?? null;
                                                    if (is_array($rejectionReason)) {
                                                        $rejectionReason = json_encode($rejectionReason);
                                                    }
                                                    $rejectionReasonStr = is_string($rejectionReason) ? $rejectionReason : '';
                                                @endphp
                                                @if(!empty($rejectionReasonStr))
                                                    <span class="badge bg-danger-subtle text-danger" 
                                                          data-bs-toggle="tooltip" 
                                                          data-bs-placement="top" 
                                                          title="{{ $rejectionReasonStr }}">
                                                        {{ Str::limit($rejectionReasonStr, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No reason provided</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="viewReportDetails('{{ $report['id'] }}')"
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
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
                            <h5 class="mt-3 text-muted">No Rejected Reports</h5>
                            <p class="text-muted">There are no rejected reports at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: none;
    border-radius: 1rem 1rem 0 0 !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.badge {
    font-size: 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(220, 53, 69, 0.05);
}

.collapse tr {
    border-top: none;
}

.collapse td {
    padding: 0 !important;
}

[data-bs-toggle="collapse"] .bi-chevron-down {
    transition: transform 0.3s ease;
}

[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
}

.alert {
    border-radius: 0.75rem;
    border: none;
}

.bg-danger-subtle {
    background-color: #f8d7da !important;
}
</style>

<script>
const reportsData = (() => {
    try {
        const arr = @json($rejectedReports);
        const map = {};
        (arr || []).forEach(r => { if (r && r.id) map[r.id] = r; });
        return map;
    } catch (e) {
        return {};
    }
})();

function viewReportDetails(reportId) {
    const r = reportsData[reportId];
    if (!r) return;
    
    const formatDate = (d) => {
        if (!d) return 'Not specified';
        try { return new Date(d).toLocaleString(); } catch { return d; }
    };
    
    const formatOnlyDate = (d) => {
        if (!d) return 'Not specified';
        try { return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' }); } catch { return d; }
    };

    const symptoms = Array.isArray(r.symptoms) ? r.symptoms : [];
    const symptomsHtml = symptoms.length
        ? symptoms.map(s => {
            const colors = { fever:'warning', dengue:'danger', diarrhea:'purple', rash:'info', cough:'secondary', headache:'dark' };
            const color = colors[String(s).toLowerCase()] || 'secondary';
            return `<span class="badge bg-${color} me-1">${String(s).charAt(0).toUpperCase()+String(s).slice(1)}</span>`;
          }).join('')
        : '<span class="text-muted">No symptoms listed</span>';

    const detailsHtml = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded border bg-light">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-person text-primary me-2"></i><strong>Affected Person</strong></div>
                    <div>${(r.affectedPerson ? String(r.affectedPerson).charAt(0).toUpperCase()+String(r.affectedPerson).slice(1) : '—')}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-virus text-danger me-2"></i><strong>Symptoms</strong></div>
                    <div>${symptomsHtml}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-clock-history text-primary me-2"></i><strong>Reported At</strong></div>
                    <div>${formatDate(r.createdAt)}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border bg-danger-subtle">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-x-circle text-danger me-2"></i><strong>Rejected At</strong></div>
                    <div>${formatDate(r.rejected_at)}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-person-x text-danger me-2"></i><strong>Rejected By</strong></div>
                    <div>${r.rejected_by || 'Unknown'}</div>
                </div>
            </div>
            <div class="col-12">
                <div class="p-3 rounded border bg-danger-subtle">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-exclamation-triangle text-danger me-2"></i><strong>Rejection Reason</strong></div>
                    <div class="text-danger fw-semibold">${r.rejection_reason || '<span class="text-muted">No reason provided</span>'}</div>
                </div>
            </div>
            <div class="col-12">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-card-text text-primary me-2"></i><strong>Additional Info</strong></div>
                    <div>${r.additionalInfo ? String(r.additionalInfo) : '<span class="text-muted">None</span>'}</div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('reportDetailsContent').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('reportDetailsModal'));
    modal.show();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection

