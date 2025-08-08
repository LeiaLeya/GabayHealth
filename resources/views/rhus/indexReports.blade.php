@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <h3 class="mb-0">Reports</h3>
        </div>
        <div class="card">
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (count($reports) === 0)
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
                                                        <span class="badge bg-primary">{{ $symptom }}</span>
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
