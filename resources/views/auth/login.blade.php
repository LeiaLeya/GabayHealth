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
                                <div class="d-flex align-items-center my-4">
                                    <hr class="flex-grow-1">
                                    <span class="mx-3 text-muted fw-500">OR</span>
                                    <hr class="flex-grow-1">
                                </div>
                                <div class="text-center my-4">
                                    <a href="{{ route('auth.google') }}" class="btn btn-google">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px; display: inline-block; vertical-align: middle;">
                                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                        </svg>
                                        Login with Google
                                    </a>
                                </div>
                                

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
