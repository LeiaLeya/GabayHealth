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
</style>
<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #f9f9f9;">
    <div class="login-card">
        <img src="{{ asset('images/gabayhealth_logo.png') }}" alt="GabayHealth Logo" style="width: 60px; height: 60px; margin-bottom: 10px;">
        <h2 class="fw-bold mb-2 text-center" style="color: #fff;">Login to GabayHealth</h2>
        <div class="mb-3" style="color: #e0e7ff; text-align: center; font-size: 1.08rem;">Enter your credentials to access your account.</div>
        @if($errors->has('login'))
            <div class="alert alert-danger w-100" style="font-size:0.97rem;">{{ $errors->first('login') }}</div>
        @endif
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