@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                {{ $barangayHealthUnit['healthCenterName'] ?? ($barangayHealthUnit['barangay'] ?? 'No Name') }}</h2>
            <a href="{{ route('rhu.approvals') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>Back
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="mb-4">
            <ul class="list-unstyled">
                <li><strong>Address:</strong> {{ $barangayHealthUnit['fullAddress'] ?? 'N/A' }}</li>
                <li><strong>City:</strong> {{ $barangayHealthUnit['city'] ?? 'N/A' }}</li>
                <li><strong>Barangay:</strong> {{ $barangayHealthUnit['barangay'] ?? 'N/A' }}</li>
                <li><strong>Province:</strong> {{ $barangayHealthUnit['province'] ?? 'N/A' }}</li>
                <li><strong>Region:</strong> {{ $barangayHealthUnit['region'] ?? 'N/A' }}</li>
                <li><strong>Postal Code:</strong> {{ $barangayHealthUnit['postalCode'] ?? 'N/A' }}</li>
                <li><strong>RHU ID:</strong> {{ $barangayHealthUnit['rhuId'] ?? 'N/A' }}</li>
                <li><strong>Username:</strong> {{ $barangayHealthUnit['username'] ?? 'N/A' }}</li>
                <li><strong>Created:</strong> {{ $barangayHealthUnit['created_at'] ?? 'N/A' }}</li>
            </ul>
        </div>

        @if (($barangayHealthUnit['status'] ?? '') === 'pending')
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Review Required</strong><br>
                <small>Please verify all information before approving this Barangay Health Unit application.</small>
            </div>
        @endif

        <div class="mt-4">
            @if (($barangayHealthUnit['status'] ?? '') !== 'approved')
                <div class="d-flex gap-3">
                    <form method="POST" action="{{ route('BHUs.update', $barangayHealthUnit['id']) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success">
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('BHUs.update', $barangayHealthUnit['id']) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to reject this BHU application?')">
                            Reject
                        </button>
                    </form>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>This BHU is already approved.
                </div>
                <a href="{{ route('BHUs.show', $barangayHealthUnit['id']) }}" class="btn btn-primary">
                    View Details
                </a>
            @endif
        </div>
    </div>
@endsection
