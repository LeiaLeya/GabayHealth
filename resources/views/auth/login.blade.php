@extends('layouts.publicApp')

@section('content')
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-lg-8">
                <div class="row bg-white shadow-lg rounded-3 overflow-hidden">
                    <div class="col-lg-5 d-none d-lg-block p-5 bg-primary bg-gradient" style="position: relative;">
                        <div class="h-100 d-flex flex-column justify-content-center align-items-center text-center">
                            <img src="{{ asset('images/GabayHealthDark.png') }}" alt="GabayHealth Logo" class="img-fluid mb-4"
                                style="max-width: 300px;">
                            <h2 class="text-white mb-3">Welcome to GabayHealth</h2>
                            <p class="text-white-50">Your trusted healthcare companion</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="card-body p-4">
                                <h3 class="text-center mb-4 fw-bold">Welcome Back!</h3>

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-floating mb-3">
                                        <input type="text" name="loginField" value="{{ old('loginField') }}"
                                            class="form-control @error('loginField') is-invalid @enderror" id="loginField"
                                            placeholder="Username or email">
                                        <label for="loginField">
                                            <i class="fas fa-user text-muted me-2"></i>Username or Email
                                        </label>
                                        @error('loginField')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            placeholder="Password">
                                        <label for="password">
                                            <i class="fas fa-lock text-muted me-2"></i>Password
                                        </label>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label text-muted" for="remember">
                                    Remember me
                                </label>
                            </div> --}}

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                        </button>
                                    </div>
                                </form>

                                <hr class="my-4">

                                <div class="text-center">
                                    <p class="text-muted mb-0">Don't have an account?</p>
                                    <a href="{{ route('register.select') }}"
                                        class="btn btn-link fw-bold text-decoration-none">
                                        Create new account
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('styles')
            <style>
                .btn-primary {
                    transition: all 0.3s ease;
                }

                .btn-primary:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }

                .form-floating>label {
                    padding-left: 1.75rem;
                }

                .form-floating>.form-control {
                    padding-left: 1.75rem;
                }

                .bg-primary {
                    position: relative;
                }

                .bg-primary::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: linear-gradient(135deg, rgba(0, 0, 0, 0.1) 0%, rgba(255, 255, 255, 0.1) 100%);
                    pointer-events: none;
                }

                @media (min-width: 992px) {
                    .row.bg-white {
                        min-height: 600px;
                    }
                }
            </style>
        @endpush
    @endsection
