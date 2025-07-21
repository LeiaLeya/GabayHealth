@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card-header d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h3>Health Workers & Doctors</h3>
                    {{-- <a href="{{ route('BHUs.create') }}" class="btn btn-primary">Add New Doctor</a> --}}
                </div>

                <div class="row">
                    @forelse($doctors as $doctor)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-person-fill" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">
                                                {{ $doctor['name'] ?? 'No name provided' }}
                                            </h5>
                                            <small class="text-muted">Health Worker</small>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        {{-- <i class="bi bi-envelope text-muted me-2"></i> --}}
                                        <span> Email: {{ $doctor['email'] ?? 'No email provided' }}</span>
                                    </div>

                                    <div class="mb-2">
                                        {{-- <i class="bi bi-phone text-muted me-2"></i> --}}
                                        <span> Mobile number: {{ $doctor['mobileNumber'] ?? 'No mobile number' }}</span>
                                    </div>

                                    <div class="mb-3">
                                        {{-- <i class="bi bi-geo-alt text-muted me-2"></i> --}}
                                        <span >
                                            Barangay ID: {{ $doctor['assignedBarangayId'] ?? 'Not assigned' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                                    <h4 class="text-muted mt-3">No doctors found</h4>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
