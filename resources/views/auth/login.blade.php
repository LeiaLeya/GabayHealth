@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,700;1,400&display=swap');

    body, html {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
    }

    .main-content {
        padding: 0 !important;
    }

    .login-container {
        display: flex;
        min-height: 100vh;
    }

    /* ── Left: Form ── */
    .login-form-panel {
        flex: 0 0 55%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3rem 2rem;
        background: #fff;
        box-sizing: border-box;
    }

    .login-form-inner {
        width: 100%;
        max-width: 400px;
    }

    .login-brand {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 2.5rem;
    }

    .login-brand img {
        width: 48px;
        height: 48px;
    }

    .login-brand-name {
        font-family: 'Lora', Georgia, serif;
        font-size: 1.6rem;
        color: #1e40af;
        letter-spacing: -0.2px;
    }

    .login-brand-name .word-gabay {
        font-weight: 700;
        font-style: normal;
    }

    .login-brand-name .word-health {
        font-weight: 400;
        font-style: italic;
    }

    .login-title {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.375rem;
        line-height: 1.2;
    }

    .login-subtitle {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 2rem;
        line-height: 1.5;
    }

    .login-form-group {
        margin-bottom: 1.125rem;
    }

    .login-form-group label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.375rem;
    }

    .login-form-group input {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        background: #f9fafb;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        box-sizing: border-box;
        color: #111827;
    }

    .login-form-group input:focus {
        outline: none;
        border-color: #1e40af;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .login-form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        font-size: 0.8125rem;
    }

    .login-remember {
        display: flex;
        align-items: center;
        gap: 0.425rem;
        color: #6b7280;
    }

    .login-remember input {
        width: 0.9rem;
        height: 0.9rem;
        accent-color: #1e40af;
        cursor: pointer;
    }

    .login-remember label {
        cursor: pointer;
        margin: 0;
    }

    .login-forgot {
        color: #1e40af;
        text-decoration: none;
        font-weight: 500;
    }

    .login-forgot:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }

    .login-btn {
        width: 100%;
        padding: 0.8rem 1rem;
        background: #1e40af;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        margin-bottom: 1rem;
    }

    .login-btn:hover { background: #1e3a8a; }
    .login-btn:active { background: #172554; transform: scale(0.99); }

    .login-divider {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .login-divider::before,
    .login-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f3f4f6;
    }

    .login-divider span { margin: 0 0.75rem; font-weight: 600; }

    .login-btn-google {
        width: 100%;
        padding: 0.7rem 1rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        transition: background 0.2s, border-color 0.2s;
        text-decoration: none;
        margin-bottom: 1.75rem;
    }

    .login-btn-google:hover {
        background: #f9fafb;
        border-color: #bfdbfe;
        color: #374151;
        text-decoration: none;
    }

    .login-signup {
        text-align: center;
        font-size: 0.875rem;
        color: #9ca3af;
        padding-top: 1rem;
        border-top: 1px solid #f3f4f6;
    }

    .login-signup a {
        color: #1e40af;
        text-decoration: none;
        font-weight: 600;
    }

    .login-signup a:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }

    .alert {
        margin-bottom: 1.125rem;
        padding: 0.7rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
    }

    .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    /* ── Right: Photo ── */
    .login-photo-panel {
        flex: 1;
        background-image: url('{{ asset('images/Login_StockPhoto.jpg') }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
    }

    .login-photo-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(10, 25, 70, 0.55) 0%, rgba(0, 0, 0, 0.45) 100%);
    }

    .login-photo-caption {
        position: absolute;
        bottom: 2.5rem;
        left: 2.5rem;
        right: 2.5rem;
        z-index: 2;
        color: #fff;
    }

    .login-photo-caption-quote {
        font-size: 1.1rem;
        font-weight: 400;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.9);
        font-style: italic;
        margin-bottom: 0.75rem;
    }

    .login-photo-caption-sub {
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.55);
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    @media (max-width: 768px) {
        .login-container { flex-direction: column-reverse; }

        .login-form-panel {
            flex: 1;
            padding: 2rem 1.25rem;
        }

        .login-photo-panel {
            flex: 0 0 200px;
            min-height: 200px;
        }

        .login-photo-caption { display: none; }

        .login-title { font-size: 1.75rem; }
    }
</style>

<div class="login-container">

    <!-- Left: Form -->
    <div class="login-form-panel">
        <div class="login-form-inner">

            <div class="login-brand">
                <img src="{{ asset('images/GabayHealthLogoLight.png') }}" alt="GabayHealth">
                <span class="login-brand-name"><span class="word-gabay">Gabay</span><span class="word-health">Health</span></span>
            </div>

            <h1 class="login-title">Welcome back</h1>
            <p class="login-subtitle">Sign in to your account to continue.</p>

            @if($errors->has('login'))
                <div class="alert alert-danger">{{ $errors->first('login') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <a href="{{ route('google.login.redirect') }}" class="login-btn-google">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>

            <div class="login-divider"><span>or</span></div>

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="login-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus value="{{ old('username') }}">
                </div>

                <div class="login-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="login-form-row">
                    <div class="login-remember">
                        <input type="checkbox" id="remember" name="remember" value="on">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="login-forgot">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">Log in</button>
            </form>

            <div class="login-signup">
                Don't have an account? <a href="{{ route('register.landing') }}">Sign up</a>
            </div>

        </div>
    </div>

    <!-- Right: Photo -->
    <div class="login-photo-panel">
        <div class="login-photo-caption">
            <div class="login-photo-caption-quote">
                "Empowering rural health units with technology for better healthcare delivery."
            </div>
            <div class="login-photo-caption-sub">Community Health Management System</div>
        </div>
    </div>

</div>

@endsection
