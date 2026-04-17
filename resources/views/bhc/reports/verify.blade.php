@extends('layouts.app')

@section('content')
<style>
    .collapse-detail {
        transition: height .25s ease, opacity .2s ease;
    }
    .details-row > td {
        padding: 0;
        border-top: 0;
    }
</style>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Verify Health Reports</h2>
            <p class="text-muted mb-0">Review and approve resident health reports</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bhc.reports.verified') }}" class="btn btn-outline-dark">Verified Reports</a>
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

    @php
        $processedToday = ($stats['verified_today'] ?? 0) + ($stats['rejected_today'] ?? 0);
    @endphp

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $stats['pending'] }}</h4>
                            <small class="text-muted">Pending Reports</small>
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
                            <i class="bi bi-activity fs-2 text-dark"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 text-dark">{{ $processedToday }}</h4>
                            <small class="text-muted">
                                Processed Today
                                ({{ $stats['verified_today'] }} approved · {{ $stats['rejected_today'] }} rejected)
                            </small>
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
                            <h4 class="mb-0 text-dark">{{ $stats['total_this_month'] }}</h4>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check me-2"></i>Mobile App Reports Pending Verification
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($pendingReports) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Barangay</th>
                                        <th>Symptoms</th>
                                        <th>Affected Person</th>
                                        <th>Start Date</th>
                                        <th>Additional Info</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingReports as $report)
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
                                                @php
                                                    $additionalInfo = $report['additionalInfo'] ?? null;
                                                    if (is_array($additionalInfo)) {
                                                        $additionalInfo = json_encode($additionalInfo);
                                                    }
                                                    $displayInfo = $additionalInfo ?: 'No additional info';

                                                    $reportedAt = null;
                                                    if (!empty($report['createdAt'])) {
                                                        $reportedAt = \Carbon\Carbon::parse($report['createdAt'])
                                                            ->format('M d, Y H:i');
                                                    }
                                                @endphp
                                                <span class="text-muted">
                                                    {{ Str::limit($displayInfo, 50) }}
                                                    @if($reportedAt)
                                                        <br><small class="text-muted">Reported: {{ $reportedAt }}</small>
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-dark" 
                                                            onclick="approveReport('{{ $report['id'] }}')"
                                                            title="Verify Report">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-dark" 
                                                            onclick="rejectReport('{{ $report['id'] }}')"
                                                            title="Reject Report">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick="viewReportDetails('{{ $report['id'] }}')"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
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
                            <i class="bi bi-check-circle text-dark" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-dark">No Pending Reports</h5>
                            <p class="text-muted">All reports have been verified!</p>
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

<!-- Approve/Reject Forms -->
<form id="approveForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="verified_by" id="verified_by_input">
</form>

<form id="rejectForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="rejection_reason" id="rejection_reason_input">
</form>

<!-- Confirm Verify Modal -->
<div class="modal fade" id="confirmApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Verify Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to verify this report? It will be added to the heatmap.</p>
                <div class="mb-3">
                    <label for="verified_by_select" class="form-label fw-semibold">Verified By <span class="text-danger">*</span></label>
                    <select class="form-select" id="verified_by_select" name="verified_by" required>
                        <option value="">Select health worker...</option>
                        @foreach($staffAccounts as $staff)
                            <option value="{{ $staff['name'] }}">
                                {{ $staff['name'] }} 
                                @php
                                    $roleDisplay = match($staff['role']) {
                                        'bhw' => 'Barangay Health Worker',
                                        default => ucfirst($staff['role'])
                                    };
                                @endphp
                                ({{ $roleDisplay }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select the health worker who verified this report</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="confirmApproveBtn">Verify Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Report Modal with Reason -->
<div class="modal fade" id="rejectReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Reject Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Please provide a reason for rejecting this report. This will help the submitter understand why the report was rejected.</p>
                <form id="rejectReasonForm">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-semibold">Rejection Reason <span class="text-dark">*</span></label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="Enter the reason for rejection (e.g., Incomplete information, Duplicate report, Invalid data, etc.)"
                                  required
                                  maxlength="500"></textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="confirmRejectBtn">Reject Report</button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #dee2e6;
    transition: box-shadow 0.2s ease-in-out;
}

.card:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.card-header {
    background-color: #212529;
    color: white;
    border-bottom: 1px solid #212529;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #212529;
    background-color: #f8f9fa;
    border-bottom: 2px solid #212529;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
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
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}
</style>

<script>

const reportsData = (() => {
    try {
        const arr = @json($pendingReports);
        const map = {};
        (arr || []).forEach(r => { if (r && r.id) map[r.id] = r; });
        return map;
    } catch (e) {
        return {};
    }
})();
let currentRejectReportId = null;

function approveReport(reportId) {
    const form = document.getElementById('approveForm');
    form.action = `/bhc/reports/${reportId}/approve`;
    const btn = document.getElementById('confirmApproveBtn');
    const verifiedBySelect = document.getElementById('verified_by_select');
    const verifiedByInput = document.getElementById('verified_by_input');
    
    // Reset the select
    verifiedBySelect.value = '';
    
    btn.onclick = function() {
        const selectedVerifier = verifiedBySelect.value;
        if (!selectedVerifier) {
            alert('Please select a health worker who verified this report.');
            return;
        }
        verifiedByInput.value = selectedVerifier;
        form.submit();
    };
    new bootstrap.Modal(document.getElementById('confirmApproveModal')).show();
}

function rejectReport(reportId) {
    currentRejectReportId = reportId;
    // Reset the form
    document.getElementById('rejection_reason').value = '';
    // Show the reject modal
    new bootstrap.Modal(document.getElementById('rejectReportModal')).show();
}

// Handle confirm reject button
document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const reason = document.getElementById('rejection_reason').value.trim();
    
    if (!reason) {
        alert('Please provide a rejection reason.');
        return;
    }
    
    if (reason.length > 500) {
        alert('Rejection reason must be 500 characters or less.');
        return;
    }
    
    const form = document.getElementById('rejectForm');
    form.action = `/reports/${currentRejectReportId}/reject`;
    document.getElementById('rejection_reason_input').value = reason;
    
    // Close the modal and submit
    const modal = bootstrap.Modal.getInstance(document.getElementById('rejectReportModal'));
    modal.hide();
    form.submit();
});

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
            return `<span class="badge bg-dark text-white me-1">${String(s).charAt(0).toUpperCase()+String(s).slice(1)}</span>`;
          }).join('')
        : '<span class="text-muted">No symptoms listed</span>';

    const detailsHtml = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3 rounded border bg-light">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-person text-dark me-2"></i><strong>Affected Person</strong></div>
                    <div>${(r.affectedPerson ? String(r.affectedPerson).charAt(0).toUpperCase()+String(r.affectedPerson).slice(1) : '—')}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-virus text-dark me-2"></i><strong>Symptoms</strong></div>
                    <div>${symptomsHtml}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-clock-history text-dark me-2"></i><strong>Reported At</strong></div>
                    <div>${formatDate(r.createdAt)}</div>
                </div>
            </div>
            <div class="col-12">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-card-text text-dark me-2"></i><strong>Additional Info</strong></div>
                    <div>${r.additionalInfo ? String(r.additionalInfo) : '<span class="text-muted">None</span>'}</div>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center gap-2">
            <button class="btn btn-outline-dark px-4" onclick="rejectReport('${reportId}'); bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal')).hide();"><i class="bi bi-x-circle me-1"></i>Reject</button>
            <button class="btn btn-dark px-4" onclick="approveReport('${reportId}'); bootstrap.Modal.getInstance(document.getElementById('reportDetailsModal')).hide();"><i class="bi bi-check-circle me-1"></i>Verify</button>
        </div>
    `;

    document.getElementById('reportDetailsContent').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('reportDetailsModal'));
    modal.show();
}
</script>
@endsection 