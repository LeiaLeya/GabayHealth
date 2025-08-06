@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h3 class="mb-3 mt-5">{{ $rhuName }}</h3>
        <div class="card-body">
            <div class="row">

                @forelse($barangayHealthUnits as $unit)
                    <div class="col-md-3 mb-4">
                        <div style="position: relative" class="card h-100">
                            <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="BHU Logo">
                            <div class="card-body">
                                <h6 class="card-title">
                                    {{ $unit['healthCenterName'] ?? ($unit['barangay'] ?? 'No name provided') }}</h6>
                                
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('BHUs.show', $unit['id']) }}" class="btn btn-primary btn-sm mb-2">View
                                    Details</a>
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
    </div>
@endsection
