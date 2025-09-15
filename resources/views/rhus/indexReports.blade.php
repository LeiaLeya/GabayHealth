@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <h3 class="mb-0 fw-bold">Reports</h3>
            @if (isset($summary))
                <span class="text-muted">Showing {{ $summary['total'] }} result(s)</span>
            @endif
        </div>

        @if (isset($summary))
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Total</div>
                            <div class="fs-4 fw-bold">{{ $summary['total'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">To be reviewed</div>
                            <div class="fs-4 fw-bold">{{ $summary['toBeReviewed'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small">Reviewed</div>
                            <div class="fs-4 fw-bold">{{ $summary['reviewed'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-body px-2 px-md-4 py-3">
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
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reports as $report)
                                    <tr>
                                        <td style="white-space:nowrap;">
                                            @if (!empty($report['createdAt']))
                                                {{ \Carbon\Carbon::parse($report['createdAt'])->format('M d, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $report['barangayName'] ?? ($report['barangayId'] ?? 'N/A') }}</td>
                                        <td>{{ ucfirst($report['affectedPerson'] ?? 'N/A') }}</td>

                                        <td>
                                            @php $status = strtolower($report['status'] ?? ''); @endphp
                                            @if ($status === 'to be reviewed')
                                                <span class="badge" style="background-color: #000; color: #fff;">
                                                    {{ ucfirst($report['status'] ?? 'N/A') }}
                                                </span>
                                            @elseif ($status === 'reviewed')
                                                <span class="badge" style="background-color: #198754; color: #fff;">
                                                    {{ ucfirst($report['status'] ?? 'N/A') }}
                                                </span>
                                            @else
                                                <span class="badge" style="background-color: #6c757d; color: #fff;">
                                                    {{ ucfirst($report['status'] ?? 'N/A') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('rhu.reports.view', $report['id']) }}"
                                                class="btn btn-sm btn-outline-primary">View Symptoms</a>
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
