@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h3 class="mb-0">Report Details</h3>
                    <a href="{{ route('rhu.reports') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>
                        Back</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if (!empty($report))
                            <div class="mb-3">
                                <strong>Barangay:</strong> {{ $report['barangayName'] ?? ($report['barangayId'] ?? 'N/A') }}
                            </div>
                            <div class="mb-3">
                                <strong>Affected Person:</strong> {{ ucfirst($report['affectedPerson'] ?? 'N/A') }}
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                @php $status = strtolower($report['status'] ?? ''); @endphp
                                <span
                                    class="badge {{ $status === 'to be reviewed' ? 'bg-warning text-dark' : ($status === 'reviewed' ? 'bg-success' : 'bg-secondary') }}">{{ ucfirst($report['status'] ?? 'N/A') }}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Start Date:</strong> {{ $report['startDate'] ?? 'N/A' }}
                            </div>
                            <div class="mb-3">
                                <strong>Created At:</strong> {{ $report['createdAt'] ?? 'N/A' }}
                            </div>
                            <div class="mb-3">
                                <strong>Symptoms / Cases:</strong>
                                @if (!empty($report['symptoms']) && is_array($report['symptoms']))
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach ($report['symptoms'] as $symptom)
                                            <span class="badge bg-primary">{{ $symptom }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">None listed</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Additional Info:</strong>
                                <p class="mb-0">{{ $report['additionalInfo'] ?? 'N/A' }}</p>
                            </div>
                        @else
                            <div class="alert alert-warning">Report not found.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
