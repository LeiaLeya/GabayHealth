@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    .register-card {
        background: #2563eb; /* Tailwind blue-600, matches your sidebar */
        color: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px rgba(37,99,235,0.15);
    }
    .register-card .btn {
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 0.5rem;
    }
    .register-card .btn-primary {
        background: #fff;
        color: #2563eb;
        border: none;
    }
    .register-card .btn-primary:hover {
        background: #e0e7ff;
        color: #1d4ed8;
    }
    .register-card .btn-blue-alt {
        background: #60a5fa; /* Tailwind blue-400 */
        color: #fff;
        border: none;
    }
    .register-card .btn-blue-alt:hover {
        background: #1d4ed8; /* Tailwind blue-700 */
        color: #fff;
    }
</style>
<div class="d-flex align-items-center justify-content-center flex-column" style="min-height: 100vh; background: #f9f9f9;">
    <div class="register-card card shadow p-5 mb-3" style="min-width: 350px; max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <img src="{{ asset('images/gabayhealth_logo.png') }}" alt="GabayHealth Logo" style="width: 60px; height: 60px;">
            <h2 class="fw-bold mt-3 mb-2" style="color: #fff;">Register to GabayHealth</h2>
            <div class="mb-2" style="font-size: 1.1rem; color: #e0e7ff;">What are you registering as?</div>
        </div>
        <div class="d-flex flex-column gap-3">
            <a href="{{ route('register.bhw') }}" class="btn btn-lg btn-primary">Barangay Health Center</a>
            <a href="{{ route('register.rhu') }}" class="btn btn-lg btn-blue-alt">Rural Health Unit</a>
        </div>
        <div class="text-center mt-4" style="margin-top:2rem !important;">
            <span style="color:#fff; font-weight:500;">Already have an account?</span>
            <a href="{{ route('login') }}" style="color:#e0e7ff; font-weight:600; text-decoration:none;">Login here</a>
        </div>
    </div>
</div>
@endsection 