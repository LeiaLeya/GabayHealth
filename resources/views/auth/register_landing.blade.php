@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,700;1,400&display=swap');

    body, html {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
    }

    .main-content {
        padding: 0 !important;
    }

    .register-page {
        min-height: 100vh;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1.5rem;
    }

    .register-brand {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.75rem;
    }

    .register-brand img {
        width: 48px;
        height: 48px;
    }

    .register-brand-name {
        font-family: 'Lora', Georgia, serif;
        font-size: 1.6rem;
        color: #1e40af;
        letter-spacing: -0.2px;
    }

    .register-brand-name .word-gabay {
        font-weight: 700;
        font-style: normal;
    }

    .register-brand-name .word-health {
        font-weight: 400;
        font-style: italic;
    }

    .register-heading {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.375rem;
        text-align: center;
    }

    .register-subheading {
        font-size: 0.95rem;
        color: #6b7280;
        margin-bottom: 2.5rem;
        text-align: center;
    }

    .register-cards {
        display: flex;
        gap: 1.25rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .role-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 1rem;
        padding: 2rem 1.75rem;
        width: 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
        cursor: pointer;
    }

    .role-card:hover {
        border-color: #1e40af;
        box-shadow: 0 8px 24px rgba(30, 64, 175, 0.12);
        transform: translateY(-3px);
        text-decoration: none;
    }

    .role-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.6rem;
        color: #1e40af;
        transition: background 0.2s;
    }

    .role-card:hover .role-card-icon {
        background: #dbeafe;
    }

    .role-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .role-card-desc {
        font-size: 0.8125rem;
        color: #6b7280;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .role-card-btn {
        width: 100%;
        padding: 0.65rem 1rem;
        background: #1e40af;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        transition: background 0.2s;
        pointer-events: none;
    }

    .role-card:hover .role-card-btn {
        background: #1e3a8a;
    }

    .register-login {
        font-size: 0.875rem;
        color: #9ca3af;
        text-align: center;
        padding-top: 1.5rem;
        border-top: 1px solid #f3f4f6;
        width: 100%;
        max-width: 520px;
    }

    .register-login a {
        color: #1e40af;
        font-weight: 600;
        text-decoration: none;
    }

    .register-login a:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }

    @media (max-width: 560px) {
        .role-card { width: 100%; max-width: 320px; }
        .register-cards { flex-direction: column; align-items: center; }
    }
</style>

<div class="register-page">

    <div class="register-brand">
        <img src="{{ asset('images/GabayHealthLogoLight.png') }}" alt="GabayHealth">
        <span class="register-brand-name">
            <span class="word-gabay">Gabay</span><span class="word-health">Health</span>
        </span>
    </div>

    <h1 class="register-heading">Create an account</h1>
    <p class="register-subheading">What are you registering as?</p>

    <div class="register-cards">

        <a href="{{ route('register.bhw') }}" class="role-card">
            <div class="role-card-icon">
                <i class="bi bi-house-heart-fill"></i>
            </div>
            <div class="role-card-title">Barangay Health Center</div>
            <div class="role-card-desc">Manage patient records and health services at the barangay level.</div>
            <div class="role-card-btn">Register as BHC</div>
        </a>

        <a href="{{ route('register.rhu') }}" class="role-card">
            <div class="role-card-icon">
                <i class="bi bi-hospital-fill"></i>
            </div>
            <div class="role-card-title">Rural Health Unit</div>
            <div class="role-card-desc">Oversee multiple barangay health centers and coordinate regional care.</div>
            <div class="role-card-btn">Register as RHU</div>
        </a>

    </div>

    <div class="register-login">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>

</div>

@endsection
