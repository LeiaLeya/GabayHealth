@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <h3 class="mb-0">
                {{ $barangayHealthUnit['healthCenterName'] ?? ($barangayHealthUnit['name'] ?? ($barangayHealthUnit['barangay'] ?? 'Barangay Health Unit')) }}
            </h3>
            <a href="{{ route('RHUs.show', $rhuId) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to BHUs
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Health Center Name:</label>
                                    <p class="mb-0">
                                        {{ $barangayHealthUnit['healthCenterName'] ?? ($barangayHealthUnit['name'] ?? 'N/A') }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Barangay:</label>
                                    <p class="mb-0">{{ $barangayName ?: $barangayHealthUnit['barangay'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">City/Municipality:</label>
                                    <p class="mb-0">{{ $cityName ?: $barangayHealthUnit['city'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Region:</label>
                                    <p class="mb-0">{{ $regionName ?: $barangayHealthUnit['region'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Province:</label>
                                    <p class="mb-0">{{ $provinceName ?: $barangayHealthUnit['province'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email:</label>
                                    <p class="mb-0">{{ $barangayHealthUnit['email'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Contact Number:</label>
                                    <p class="mb-0">
                                        {{ $barangayHealthUnit['contact_number'] ?? ($barangayHealthUnit['contactInfo'] ?? 'N/A') }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Address:</label>
                                    <p class="mb-0">
                                        {{ $barangayHealthUnit['fullAddress'] ?? ($barangayHealthUnit['address'] ?? 'N/A') }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Postal Code:</label>
                                    <p class="mb-0">
                                        {{ $barangayHealthUnit['postalCode'] ?? ($barangayHealthUnit['zipCode'] ?? 'N/A') }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    @php $st = strtolower($barangayHealthUnit['status'] ?? ''); @endphp
                                    <span
                                        class="badge {{ $st === 'approved' ? 'bg-success' : ($st === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ ucfirst($barangayHealthUnit['status'] ?? 'Unknown') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Open Days:</strong>
                                    {{ $barangayHealthUnit['open_days'] ?? 'N/A' }}</div>
                                <div class="mb-2"><strong>Open Time:</strong>
                                    {{ $barangayHealthUnit['open_time'] ?? 'N/A' }}</div>
                                <div class="mb-2"><strong>Close Time:</strong>
                                    {{ $barangayHealthUnit['close_time'] ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Created:</strong>
                                    {{ $barangayHealthUnit['created_at'] ?? ($barangayHealthUnit['createdAt'] ?? 'N/A') }}
                                </div>
                                <div class="mb-2"><strong>Updated:</strong>
                                    {{ $barangayHealthUnit['updated_at'] ?? ($barangayHealthUnit['updatedAt'] ?? 'N/A') }}
                                </div>
                                <div class="mb-2"><strong>RHU ID:</strong>
                                    <code>{{ $barangayHealthUnit['rhuId'] ?? 'N/A' }}</code>
                                </div>
                            </div>
                        </div>

                        {{-- @if (!empty($barangayHealthUnit['description']))
                            <div class="mt-3">
                                <label class="form-label fw-bold">Description:</label>
                                <p class="mb-0">{{ $barangayHealthUnit['description'] }}</p>
                            </div>
                        @endif --}}
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong>Health Workers</strong>
                    </div>
                    <div class="card-body">
                        @if (empty($healthWorkers))
                            <div class="text-muted">No health workers assigned.</div>
                        @else
                            <div class="row">
                                @foreach ($healthWorkers as $worker)
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="fw-bold">{{ $worker['name'] ?? 'Unnamed' }}</div>
                                            <div>Email: {{ $worker['email'] ?? 'N/A' }}</div>
                                            <div>Mobile: {{ $worker['mobileNumber'] ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
