@extends('layouts.publicApp')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="mt-5 pt-5">
                    <div class="text-center mb-5 mt-5">
                        <h3>Join GabayHealth</h3>
                    </div>

                    <div class="row g-3">
                        {{-- <div class="col-12">
                            <div class="card h-100 text-center">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <h5 class="card-title">Admin</h5>
                                    <p class="card-text text-muted">Manage the entire system</p>
                                    <a href="{{ route('register.admin') }}" class="btn btn-primary">Register as Admin</a>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-12 col-md-6">
                            <div class="card h-100 text-center">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <h5 class="card-title">Rural Health Unit</h5>
                                    <p class="card-text text-muted">Primary healthcare facility serving rural communities
                                        with basic medical services and health programs</p>
                                    <a href="{{ route('rhu.register') }}" class="btn btn-primary">Register as RHU</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card h-100 text-center">
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <h5 class="card-title">Barangay Health Unit</h5>
                                    <p class="card-text text-muted">Community-level health facility providing basic
                                        healthcare services and health education at the barangay level</p>
                                    <a href="#" class="btn btn-primary">Register as BHU</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account? <a href="{{ route('login') }}">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
