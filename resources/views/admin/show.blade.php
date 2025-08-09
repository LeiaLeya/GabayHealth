@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-4">{{ $ruralHealthUnit['name'] ?? '' }}</h3>
            <a href="{{ route('RHUs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>Back
            </a>
        </div>
        <div class="row">
            @forelse($bhus as $bhu)
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm h-100">
                        <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="RHU Logo">
                        <div class="card-body">
                            <h6 class="card-title">{{ $bhu['healthCenterName'] ?? 'No Health Center Name' }}</h6>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.BHUs.show', ['rhu' => $ruralHealthUnit['id'], 'bhu' => $bhu['id']]) }}"
                                class="btn btn-primary btn-sm mb-2">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">No Barangay Health Units found.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
