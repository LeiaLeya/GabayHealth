{{-- filepath: resources/views/RuralHealthUnit/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Barangay Health Units under {{ $ruralHealthUnit['name'] ?? '' }}</h2>
        <div class="row">
            @forelse($bhus as $bhu)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <img src="https://via.placeholder.com/400x200?text=BHU+Image" class="card-img-top" alt="BHU Image">
                        <div class="card-body">
                            <h5 class="card-title">{{ $bhu['healthCenterName'] ?? 'No Health Center Name' }}</h5>
                            <p class="card-text mb-1"><strong>Address:</strong> {{ $bhu['fullAddress'] ?? 'N/A' }}</p>
                            <p class="card-text mb-1"><strong>Contact:</strong> {{ $bhu['contactInfo'] ?? 'N/A' }}</p>
                            <p class="card-text"><strong>Head:</strong> {{ $bhu['head'] ?? 'N/A' }}</p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="#" class="btn btn-primary w-100">View Details</a>
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
