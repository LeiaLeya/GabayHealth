@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4 mt-5">
            Barangay Health Units 
            @if(isset($rhuName))
                under {{ $rhuName }}
            @endif
        </h3>
        <div class="card-body">
            <div class="row">
                @forelse($bhus as $bhu)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="BHU Logo">
                            <div class="card-body">
                                <h6 class="card-title">{{ $bhu['barangay_name'] ?? 'No name' }}</h6>
                                <p class="card-text" style="font-size: 0.9rem">
                                    {{ $bhu['address'] ?? 'No address provided.' }}
                                </p>
                                <p class="card-text text-muted" style="font-size: 0.8rem;">
                                    Status: {{ $bhu['status'] ?? 'Unknown' }}
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                {{-- Add more actions if needed --}}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">No Barangay Health Units found for this RHU.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection