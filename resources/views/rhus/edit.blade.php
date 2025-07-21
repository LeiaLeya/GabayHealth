@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Barangay Health Unit</h2>
            <a href="{{ route('rhu.approvals') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Approvals
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="BHU Image" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            {{ $barangayHealthUnit['healthCenterName'] ?? ($barangayHealthUnit['barangay'] ?? 'No Name') }}
                        </h5>
                        
                        <div class="mb-3">
                            <p class="card-text mb-2">
                                <i class="bi bi-geo-alt text-muted me-2"></i>
                                <strong>Address:</strong> {{ $barangayHealthUnit['fullAddress'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-building text-muted me-2"></i>
                                <strong>City:</strong> {{ $barangayHealthUnit['city'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-map text-muted me-2"></i>
                                <strong>Barangay:</strong> {{ $barangayHealthUnit['barangay'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-pin-map text-muted me-2"></i>
                                <strong>Province:</strong> {{ $barangayHealthUnit['province'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-globe text-muted me-2"></i>
                                <strong>Region:</strong> {{ $barangayHealthUnit['region'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-mailbox text-muted me-2"></i>
                                <strong>Postal Code:</strong> {{ $barangayHealthUnit['postalCode'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-hospital text-muted me-2"></i>
                                <strong>RHU ID:</strong> {{ $barangayHealthUnit['rhuId'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-person text-muted me-2"></i>
                                <strong>Username:</strong> {{ $barangayHealthUnit['username'] ?? 'N/A' }}
                            </p>
                            <p class="card-text mb-2">
                                <i class="bi bi-calendar text-muted me-2"></i>
                                <strong>Created:</strong> {{ $barangayHealthUnit['created_at'] ?? 'N/A' }}
                            </p>
                        </div>

                        {{-- Status Badge --}}
                        <div class="mb-3">
                            @if(($barangayHealthUnit['status'] ?? '') === 'approved')
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </span>
                            @elseif(($barangayHealthUnit['status'] ?? '') === 'pending')
                                <span class="badge bg-warning fs-6">
                                    <i class="bi bi-clock me-1"></i>Pending Review
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6">Unknown Status</span>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        @if (($barangayHealthUnit['status'] ?? '') !== 'approved')
                            <div class="d-grid gap-2">
                                <form method="POST" action="{{ route('BHUs.update', $barangayHealthUnit['id']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-2"></i>Approve BHU
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('BHUs.update', $barangayHealthUnit['id']) }}" class="mt-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this BHU application?')">
                                        <i class="bi bi-x-circle me-2"></i>Reject Application
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>This BHU is already approved.
                            </div>
                            <a href="{{ route('BHUs.show', $barangayHealthUnit['id']) }}" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Additional Information Card --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Application Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="border-start border-primary border-3 ps-3">
                                    <h6 class="text-primary">Application Status</h6>
                                    <p class="text-muted mb-0">{{ ucfirst($barangayHealthUnit['status'] ?? 'pending') }}</p>
                                </div>
                            </div>
                            
                            @if(isset($barangayHealthUnit['created_at']))
                                <div class="col-12">
                                    <div class="border-start border-info border-3 ps-3">
                                        <h6 class="text-info">Application Date</h6>
                                        <p class="text-muted mb-0">{{ $barangayHealthUnit['created_at'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($barangayHealthUnit['region']))
                                <div class="col-12">
                                    <div class="border-start border-success border-3 ps-3">
                                        <h6 class="text-success">Region</h6>
                                        <p class="text-muted mb-0">{{ $barangayHealthUnit['region'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if(isset($barangayHealthUnit['rhuId']))
                                <div class="col-12">
                                    <div class="border-start border-warning border-3 ps-3">
                                        <h6 class="text-warning">RHU ID</h6>
                                        <p class="text-muted mb-0">{{ $barangayHealthUnit['rhuId'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Review Notes --}}
                        @if (($barangayHealthUnit['status'] ?? '') === 'pending')
                            <div class="alert alert-warning mt-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Review Required</strong><br>
                                <small>Please verify all information before approving this Barangay Health Unit application.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection