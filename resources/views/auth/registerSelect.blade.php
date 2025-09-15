@extends('layouts.publicApp')

@section('content')
    <div class="container h-100">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-10 col-lg-8 py-5">
                <div class="text-center mb-5">
                    <img src="{{ asset('images/GabayHealthLight.png') }}" alt="GabayHealth Logo" class="img-fluid mb-4"
                        style="max-width: 200px;">
                    <h2 class="fw-bold text-primary">Join GabayHealth</h2>
                    <p class="text-muted lead">Select your healthcare facility type to get started</p>
                </div>

                <div class="row g-4 row-eq-height">
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
                        <div class="card h-100 shadow-lg border-0 hover-card">
                            <div class="card-body p-4 text-center">
                                {{-- <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-4 icon-circle">
                                    <img src="{{ asset('images/RHU.png') }}" alt="RHU Icon" class="facility-icon">
                                </div> --}}
                                <h4 class="card-title mb-3 fw-bold">Rural Health Unit</h4>
                                <p class="card-text text-muted mb-4">
                                    Primary healthcare facility serving rural communities with basic medical services and
                                    health programs
                                </p>
                                <a href="{{ route('rhu.register') }}" class="btn btn-primary btn-lg w-100 hover-btn">
                                    <i class="fas fa-hospital me-2"></i>Register as RHU
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card h-100 shadow-lg border-0 hover-card">
                            <div class="card-body p-4 text-center">
                                {{-- <div class="rounded-circle bg-primary bg-opacity-10 mx-auto mb-4 icon-circle">
                                    <img src="{{ asset('images/BHU.png') }}" alt="BHU Icon" class="facility-icon">
                                </div> --}}
                                <h4 class="card-title mb-3 fw-bold">Barangay Health Unit</h4>
                                <p class="card-text text-muted mb-4">
                                    Community-level health facility providing basic healthcare services and health education
                                    at the barangay level
                                </p>
                                <a href="#" class="btn btn-primary btn-lg w-100 hover-btn">
                                    <i class="fas fa-clinic-medical me-2"></i>Register as BHU
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted mb-0">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">
                            Sign in here
                            <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .row-eq-height {
                display: flex;
                flex-wrap: wrap;
            }

            .row-eq-height > [class*='col-'] {
                display: flex;
                flex-direction: column;
            }

            .row-eq-height .card {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .row-eq-height .card-body {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .row-eq-height .card-text {
                flex-grow: 1;
            }

            .hover-card {
                transition: all 0.3s ease;
                cursor: pointer;
                background: white;
            }

            .hover-card:hover {
                transform: translateY(-10px);
            }

            .icon-circle {
                width: 100px;
                height: 100px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.5rem;
            }

            .facility-icon {
                width: 60px;
                height: 60px;
                object-fit: contain;
            }

            .hover-btn {
                transition: all 0.3s ease;
                margin-top: auto;
            }

            .hover-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .card-body {
                padding: 2.5rem !important;
            }

            @media (max-width: 768px) {
                .icon-circle {
                    width: 80px;
                    height: 80px;
                }

                .facility-icon {
                    width: 50px;
                    height: 50px;
                }

                .row-eq-height > [class*='col-'] {
                    margin-bottom: 1.5rem;
                }
            }
        </style>
    @endpush
@endsection
