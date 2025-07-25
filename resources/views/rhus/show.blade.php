@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Barangay Health Unit Details</h3>
                    <a href="{{ route('BHUs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-hospital me-2"></i>
                            {{ $barangayHealthUnit['healthCenterName'] ?? ($barangayHealthUnit['barangay'] ?? 'Health Center') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Health Center Name:</label>
                                    <p class="mb-0">{{ $barangayHealthUnit['healthCenterName'] ?? 'N/A' }}</p>
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
                                    <p class="mb-0">{{ $barangayHealthUnit['region'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Contact Information:</label>
                                    <p class="mb-0">{{ $barangayHealthUnit['contactInfo'] ?? 'N/A' }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Address:</label>
                                    <p class="mb-0">{{ $barangayHealthUnit['fullAddress'] ?? 'N/A' }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <span
                                        class="badge {{ $barangayHealthUnit['status'] === 'approved' ? 'bg-success' : ($barangayHealthUnit['status'] === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($barangayHealthUnit['status'] ?? 'Unknown') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if (isset($barangayHealthUnit['description']))
                            <div class="mt-3">
                                <label class="form-label fw-bold">Description:</label>
                                <p class="mb-0">{{ $barangayHealthUnit['description'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
