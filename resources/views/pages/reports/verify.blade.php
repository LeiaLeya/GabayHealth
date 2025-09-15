@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Verify Health Reports</h2>
            <p class="text-muted mb-0">Review and approve resident health reports</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Go to Reports
        </a>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                            <small>Pending Reports</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ $stats['verified_today'] }}</h4>
                            <small>Approved Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-x-circle fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ $stats['rejected_today'] }}</h4>
                            <small>Rejected Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">{{ $stats['total_this_month'] }}</h4>
                            <small>This Month</small>
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
                                        <th>Reported Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingReports as $report)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">
                                                    @php
                                                        $barangayName = '';
                                                        try {
                                                            $barangayDoc = app('App\Services\FirebaseService')->getFirestore()
                                                                ->collection("barangay")
                                                                ->document($report['barangayId'] ?? '')
                                                                ->snapshot();
                                                            if ($barangayDoc->exists()) {
                                                                $data = $barangayDoc->data();
                                                                $barangayName = $data['healthCenterName'] ?? $data['barangay'] ?? 'Unknown';
                                                            }
                                                        } catch (\Exception $e) {
                                                            $barangayName = 'Unknown';
                                                        }
                                                    @endphp
                                                    {{ $barangayName }}
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
                                                <span class="text-muted">{{ Str::limit($report['additionalInfo'] ?? 'No additional info', 50) }}</span>
                                            </td>
                                            <td>
                                                @if(isset($report['createdAt']))
                                                    {{ \Carbon\Carbon::parse($report['createdAt'])->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="approveReport('{{ $report['id'] }}')"
                                                            title="Approve Report">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="rejectReport('{{ $report['id'] }}')"
                                                            title="Reject Report">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info" 
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
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Pending Reports</h5>
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
</form>

<form id="rejectForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Confirm Action Modal -->
<div class="modal fade" id="confirmActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmActionTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmActionMessage">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
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
    background-color: rgba(102, 126, 234, 0.05);
}

.alert {
    border-radius: 0.75rem;
    border: none;
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
function approveReport(reportId) {
    const form = document.getElementById('approveForm');
    form.action = `/reports/${reportId}/approve`;
    const title = document.getElementById('confirmActionTitle');
    const message = document.getElementById('confirmActionMessage');
    const btn = document.getElementById('confirmActionBtn');
    title.textContent = 'Approve Report';
    message.textContent = 'Are you sure you want to approve this report? It will be added to the heatmap.';
    btn.textContent = 'Approve Report';
    btn.className = 'btn btn-success';
    btn.onclick = function() {
        form.submit();
    };
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function rejectReport(reportId) {
    const form = document.getElementById('rejectForm');
    form.action = `/reports/${reportId}/reject`;
    const title = document.getElementById('confirmActionTitle');
    const message = document.getElementById('confirmActionMessage');
    const btn = document.getElementById('confirmActionBtn');
    title.textContent = 'Reject Report';
    message.textContent = 'Are you sure you want to reject this report? This action cannot be undone.';
    btn.textContent = 'Reject Report';
    btn.className = 'btn btn-danger';
    btn.onclick = function() {
        form.submit();
    };
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

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
            <div class="col-12">
                <div class="p-3 rounded border">
                    <div class="d-flex align-items-center mb-2"><i class="bi bi-card-text text-primary me-2"></i><strong>Additional Info</strong></div>
                    <div>${r.additionalInfo ? String(r.additionalInfo) : '<span class="text-muted">None</span>'}</div>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center gap-2">
            <button class="btn btn-danger px-4" onclick="rejectReport('${reportId}')"><i class="bi bi-x-circle me-1"></i>Reject</button>
            <button class="btn btn-success px-4" onclick="approveReport('${reportId}')"><i class="bi bi-check-circle me-1"></i>Approve</button>
        </div>
    `;

    document.getElementById('reportDetailsContent').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('reportDetailsModal'));
    modal.show();
}
</script>
@endsection 