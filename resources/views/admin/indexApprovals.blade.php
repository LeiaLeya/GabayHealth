@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h3 class="fw-bold">Rural Health Unit Applications</h3>
                    </div>

                    {{-- <div class="card"> --}}
                    <div class="card-body">
                        <div class="row">
                            @forelse($ruralHealthUnits as $unit)
                                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div
                                            class="bg-primary text-white d-flex align-items-center justify-content-center rounded-top">
                                            <img src="{{ asset('images/RHU.png') }}" class="card-img-top" alt="RHU Logo">

                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title mb-2">{{ $unit['name'] ?? 'Unnamed RHU' }}</h6>
                                            @if (!empty($unit['cityName']) || !empty($unit['provinceName']))
                                                <div class="text-muted small mb-3">
                                                    {{ trim(($unit['cityName'] ?? '') . ', ' . ($unit['provinceName'] ?? ''), ' ,') }}
                                                </div>
                                            @endif
                                            <div class="text-muted small mb-3">
                                                
                                            </div>
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <a href="{{ route('RHUs.edit', $unit['id']) }}"
                                                    class="btn btn-primary btn-sm">Review</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center mb-0">
                                        No pending applications.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        {{-- {{ $ruralHealthUnits->links() }} --}}
                    </div>
                    {{-- </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection
