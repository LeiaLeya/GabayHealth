@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h3 class="mb-0">Reports</h3>
            @if (isset($summary))
                <span class="text-muted">Showing {{ $summary['total'] }} result(s)</span>
            @endif
        </div>

        {{-- Summary cards --}}
        @if (isset($summary))
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total</div>
                            <div class="fs-4 fw-bold">{{ $summary['total'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">To be reviewed</div>
                            <div class="fs-4 fw-bold text-warning">{{ $summary['toBeReviewed'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Reviewed</div>
                            <div class="fs-4 fw-bold text-success">{{ $summary['reviewed'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Other</div>
                            <div class="fs-4 fw-bold text-secondary">{{ $summary['other'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Trend + Top symptoms --}}
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong>Reports (last 30 days)</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="reportsTrendChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="card h-100">
                    <div class="card-header"><strong>Top Symptoms / Cases</strong></div>
                    <div class="card-body">
                        @if (!empty($symptomCounts))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach (array_slice($symptomCounts, 0, 12) as $s => $cnt)
                                    <span class="badge bg-primary">{{ $s }} <span
                                            class="bg-light text-dark ms-1 px-1 rounded">{{ $cnt }}</span></span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">No symptoms recorded.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (count($reports ?? []) === 0)
                    <div class="text-center py-5">
                        <h5 class="text-muted">No Reports Found</h5>
                        <p class="text-muted mb-0">Reports submitted by BHUs under your RHU will appear here.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Barangay</th>
                                    <th>Affected Person</th>
                                    <th>Symptoms / Cases</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reports as $report)
                                    <tr>
                                        <td style="white-space:nowrap;">{{ $report['createdAt'] ?? 'N/A' }}</td>
                                        <td>{{ $report['barangayName'] ?? ($report['barangayId'] ?? 'N/A') }}</td>
                                        <td>{{ ucfirst($report['affectedPerson'] ?? 'N/A') }}</td>
                                        <td>
                                            @if (!empty($report['symptoms']) && is_array($report['symptoms']))
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($report['symptoms'] as $symptom)
                                                        <span class="badge bg-secondary">{{ $symptom }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $status = strtolower($report['status'] ?? ''); @endphp
                                            <span
                                                class="badge {{ $status === 'to be reviewed' ? 'bg-warning text-dark' : ($status === 'reviewed' ? 'bg-success' : 'bg-secondary') }}">
                                                {{ ucfirst($report['status'] ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('rhu.reports.view', $report['id']) }}"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const ctx = document.getElementById('reportsTrendChart');
            if (!ctx) return;
            const labels = @json($trend['labels'] ?? []);
            const data = @json($trend['data'] ?? []);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Reports',
                        data,
                        fill: true,
                        tension: 0.25,
                        borderColor: '#0b6ffd',
                        backgroundColor: 'rgba(11,111,253,0.12)',
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                maxTicksLimit: 7
                            }
                        },
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            });
        })();
    </script>
@endsection
