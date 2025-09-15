@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h3 class="mb-0 fw-bold">Report Details</h3>
                    <a href="{{ route('rhu.reports') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>
                        Back</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if (!empty($report))
                            <div class="mb-4">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Barangay:</strong>
                                        {{ $report['barangayName'] ?? ($report['barangayId'] ?? 'N/A') }}
                                    </li>
                                    <li class="mb-2"><strong>Affected Person:</strong>
                                        {{ ucfirst($report['affectedPerson'] ?? 'N/A') }}</li>
                                    <li class="mb-2"><strong>Date Reported:</strong>
                                        @if (!empty($report['createdAt']))
                                            {{ \Carbon\Carbon::parse($report['createdAt'])->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-4">
                                <h5 class="mb-3">Symptoms</h5>
                                @if (!empty($report['symptoms']) && is_array($report['symptoms']))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($report['symptoms'] as $symptom)
                                            <span class="badge bg-primary">{{ $symptom }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">None listed</div>
                                @endif
                            </div>
                            @if (!empty($report['additionalInfo']))
                                <div class="mb-4">
                                    <h5 class="mb-3">Additional Info</h5>
                                    <p class="mb-0">{{ $report['additionalInfo'] }}</p>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">Report not found.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
