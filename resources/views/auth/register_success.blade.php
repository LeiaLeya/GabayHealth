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

    .success-page {
        min-height: 100vh;
        background: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1.5rem;
    }

    .success-brand {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 2.5rem;
    }

    .success-brand img {
        width: 48px;
        height: 48px;
    }

    .success-brand-name {
        font-family: 'Lora', Georgia, serif;
        font-size: 1.6rem;
        color: #1e40af;
        letter-spacing: -0.2px;
    }

    .success-brand-name .word-gabay { font-weight: 700; font-style: normal; }
    .success-brand-name .word-health { font-weight: 400; font-style: italic; }

    .success-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 1.25rem;
        padding: 3rem 2.5rem;
        max-width: 480px;
        width: 100%;
        text-align: center;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    .success-icon-wrap {
        width: 80px;
        height: 80px;
        background: #eff6ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        animation: pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes pop {
        from { transform: scale(0.5); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }

    .success-icon-wrap i {
        font-size: 2.25rem;
        color: #1e40af;
    }

    .success-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.75rem;
    }

    .success-message {
        font-size: 0.9375rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .success-steps {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }

    .success-steps p {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .success-step {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.625rem;
    }

    .success-step:last-child { margin-bottom: 0; }

    .step-dot {
        width: 22px;
        height: 22px;
        min-width: 22px;
        background: #dbeafe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: #1e40af;
        margin-top: 1px;
    }

    .step-text {
        font-size: 0.875rem;
        color: #4b5563;
        line-height: 1.5;
    }

    .btn-login {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        background: #1e40af;
        color: #fff;
        border: none;
        border-radius: 0.625rem;
        font-size: 0.9375rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: background 0.2s;
        cursor: pointer;
        margin-bottom: 0.75rem;
    }

    .btn-login:hover {
        background: #1e3a8a;
        color: #fff;
        text-decoration: none;
    }

    .btn-back {
        display: block;
        font-size: 0.875rem;
        color: #9ca3af;
        text-align: center;
        text-decoration: none;
        transition: color 0.2s;
    }

    .btn-back:hover {
        color: #1e40af;
        text-decoration: none;
    }

    @media (max-width: 520px) {
        .success-card { padding: 2rem 1.25rem; }
    }
</style>

<div class="success-page">

    <div class="success-brand">
        <img src="{{ asset('images/GabayHealthLogoLight.png') }}" alt="GabayHealth">
        <span class="success-brand-name">
            <span class="word-gabay">Gabay</span><span class="word-health">Health</span>
        </span>
    </div>

    <div class="success-card">

        <div class="success-icon-wrap">
            <i class="bi bi-check2-circle"></i>
        </div>

        <h1 class="success-title">Registration Submitted!</h1>

        @if(session('success_type') === 'rhu')
            <p class="success-message">
                Your Rural Health Unit registration has been received.
                A confirmation email has been sent — please check your inbox.
            </p>
            <div class="success-steps">
                <p>What happens next</p>
                <div class="success-step">
                    <div class="step-dot">1</div>
                    <span class="step-text">Our admin team will review your registration details.</span>
                </div>
                <div class="success-step">
                    <div class="step-dot">2</div>
                    <span class="step-text">Once approved, you'll receive your login credentials via email.</span>
                </div>
                <div class="success-step">
                    <div class="step-dot">3</div>
                    <span class="step-text">Log in and start managing your health unit on GabayHealth.</span>
                </div>
            </div>
        @else
            <p class="success-message">
                Your Barangay Health Center registration has been submitted
                and is now awaiting approval from your assigned Rural Health Unit.
            </p>
            <div class="success-steps">
                <p>What happens next</p>
                <div class="success-step">
                    <div class="step-dot">1</div>
                    <span class="step-text">Your assigned RHU will review and approve your registration.</span>
                </div>
                <div class="success-step">
                    <div class="step-dot">2</div>
                    <span class="step-text">Once approved, you can log in using your username and password.</span>
                </div>
                <div class="success-step">
                    <div class="step-dot">3</div>
                    <span class="step-text">Start managing patient records and barangay health services.</span>
                </div>
            </div>
        @endif

        <a href="{{ route('login') }}" class="btn-login">Go to Login</a>
        <a href="{{ route('register.landing') }}" class="btn-back">Back to registration</a>

    </div>

</div>

@endsection
