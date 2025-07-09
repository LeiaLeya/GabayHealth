@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4 mt-5">Rural Health Units</h3>
        <div class="card-body">
            <div class="row">

                @forelse($ruralHealthUnits as $unit)
                
                    <div class="col-md-3 mb-4">
                        <div style="position: relative" class="card h-100">
                            <img src="{{ asset('images/Doctor.png') }}" class="card-img-top" alt="RHU Logo">
                            {{-- <img src="{{ asset('images/seal.png') }}" style="position: absolute; top: 50px; left: 50px; width: 150px; height:150px; "> --}}
                            <div class="card-body">
                                <h6 class="card-title">{{ $unit->name }}</h6>
                                <p class="card-text" style="font-size: 0.9rem">
                                    {{ $unit->tagline ?? 'No tagline provided.' }}</p>
                                {{-- <p class="card-text"><small class="text-muted">{{ $unit->city }}</small></p> --}}
                                {{-- <span
                                    class="badge bg-{{ $unit->status == 'approved' ? 'success' : ($unit->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($unit->status) }}
                                </span> --}}
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="{{ route('RHUs.show', $unit->id) }}" class="btn btn-primary btn-sm mb-2">View
                                    Details</a>
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">No Rural Health Units found.</div>
                    </div>
                @endforelse

            </div>
            {{ $ruralHealthUnits->links() }}
        </div>
    </div>
@endsection
