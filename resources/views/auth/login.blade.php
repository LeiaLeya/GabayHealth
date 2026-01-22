@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    body, html {
        background: #f9f9f9 !important;
        min-height: 100vh;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .login-card {
        background: #2563eb;
        border-radius: 2rem;
        box-shadow: 0 8px 32px rgba(37,99,235,0.15);
        color: #fff;
        max-width: 420px;
        width: 100%;
        padding: 2.5rem 2.2rem 2rem 2.2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .login-card .form-control {
        min-height: 44px;
        font-size: 15px;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }
    .login-card .btn-primary {
        background: #fff;
        color: #2563eb;
        font-weight: 600;
        border: none;
        font-size: 1.08rem;
        border-radius: 0.75rem;
        margin-top: 0.5rem;
    }
    .login-card .btn-primary:hover {
        background: #e0e7ff;
        color: #1d4ed8;
    }
    .login-card .register-link {
        color: #fff;
        font-weight: 500;
        margin-top: 1.5rem;
        font-size: 1.01rem;
    }
    .login-card .register-link a {
        color: #e0e7ff;
        font-weight: 600;
        text-decoration: none;
        margin-left: 4px;
    }
    .login-card .register-link a:hover {
        text-decoration: underline;
    }
    .oauth-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        background: #fff;
        color: #1f2937;
        border: none;
        border-radius: 0.75rem;
        padding: 0.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        margin-bottom: 1rem;
        text-decoration: none;
    }
    .oauth-btn:hover {
        background: #f3f4f6;
        transform: translateY(-2px);
    }
    .oauth-btn:active {
        transform: translateY(0);
    }
    .divider {
        display: flex;
        align-items: center;
        width: 100%;
        margin: 1.5rem 0;
        gap: 0.75rem;
    }
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(255, 255, 255, 0.3);
    }
    .divider span {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>
<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #f9f9f9;">
    <div class="login-card">
        <img src="{{ asset('images/gabayhealth_logo.png') }}" alt="GabayHealth Logo" style="width: 60px; height: 60px; margin-bottom: 10px;">
        <h2 class="fw-bold mb-2 text-center" style="color: #fff;">Login to GabayHealth</h2>
        <div class="mb-3" style="color: #e0e7ff; text-align: center; font-size: 1.08rem;">Enter your credentials to access your account.</div>
        
        @if($errors->has('login'))
            <div class="alert alert-danger w-100" style="font-size:0.97rem;">{{ $errors->first('login') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger w-100" style="font-size:0.97rem;">{{ session('error') }}</div>
        @endif

        <!-- Google Sign-In Button -->
        <a href="{{ route('google.login.redirect') }}" class="oauth-btn" style="max-width: 320px; margin-bottom: 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continue with Google
        </a>

        <div class="divider" style="max-width: 320px;">
            <span>or</span>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="w-100" style="max-width: 320px;">
            @csrf
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus value="{{ old('username') }}">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="register-link text-center">
            Don't have an account?
            <a href="{{ route('register.landing') }}">Register here</a>
        </div>
    </div>
</div>
@endsection