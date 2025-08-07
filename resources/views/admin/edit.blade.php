@extends('layouts.app')

@section('content')
    <div class="container pt-5 mt-3">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="">{{ $ruralHealthUnit['name'] ?? 'No Name' }}</h3>
            <a href="{{ route('RHUs.approvals') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
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
                <li><strong>Contact Number:</strong> {{ $ruralHealthUnit['contactNumber'] ?? 'N/A' }}</li>
                <li><strong>Address:</strong> {{ $ruralHealthUnit['fullAddress'] ?? 'N/A' }}</li>
                <li><strong>ZIP Code:</strong> {{ $ruralHealthUnit['zipCode'] ?? 'N/A' }}</li>
            </ul>
        </div>

        <div class="mb-4">
            <ul class="list-unstyled">
                <li><strong>RHU Head Name:</strong> {{ $ruralHealthUnit['headName'] ?? 'N/A' }}</li>
                <li><strong>License Number:</strong> {{ $ruralHealthUnit['licenseNumber'] ?? 'N/A' }}</li>
                <li><strong>Operating Hours:</strong> {{ $ruralHealthUnit['operatingHours'] ?? 'N/A' }}</li>
                <li><strong>Description:</strong> {{ $ruralHealthUnit['description'] ?? 'N/A' }}</li>
            </ul>

            @if (($ruralHealthUnit['status'] ?? '') === 'pending')
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Review Required</strong><br>
                    <small>Please verify all information before approving this Rural Health Unit application.</small>
                </div>
            @endif
        </div>

        <div class="mt-4">
            @if (($ruralHealthUnit['status'] ?? '') !== 'approved')
                <div class="d-flex gap-3">
                    <form method="POST" action="{{ route('RHUs.update', $ruralHealthUnit['id']) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success">
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('RHUs.update', $ruralHealthUnit['id']) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to reject this RHU application?')">
                            Reject
                        </button>
                    </form>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>This RHU is already approved.
                </div>
                <a href="{{ route('RHUs.show', $ruralHealthUnit['id']) }}" class="btn btn-primary">
                    View Details
                </a>
            @endif
        </div>
    </div>
@endsection
