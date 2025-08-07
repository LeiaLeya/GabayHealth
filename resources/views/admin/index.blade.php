@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h3 class="">Rural Health Units</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($ruralHealthUnits as $unit)
                            <div class="col-md-3 mb-4">
                                <div style="position: relative" class="card h-100">
                                    <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="RHU Logo">
                                    {{-- <img src="{{ asset('images/seal.png') }}" class="card-img-top" style="position: absolute; top: 50px; left: 50px; width: 150px; height:150px; "> --}}
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $unit['name'] ?? '' }}</h5>
                                        {{-- <p class="card-text" style="font-size: 0.9rem">
                                    {{ $unit['description'] ?? 'No description provided.' }}</p> --}}
                                    </div>
                                    <div class="card-footer bg-transparent border-0">
                                        <a href="{{ route('RHUs.show', $unit['id']) }}"
                                            class="btn btn-primary btn-sm mb-2">See BHUs</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">No Rural Health Units found.</div>
                            </div>
                        @endforelse

                    </div>
                    {{-- {{ $ruralHealthUnits->links() }} --}}
                </div>
            </div>
        </div>
    </div>
@endsection
