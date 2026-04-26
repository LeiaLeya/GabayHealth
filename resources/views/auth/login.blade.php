@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    body, html {
        background: #fff !important;
        min-height: 100vh;
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
    }
    
    .nucleus-login-container {
        display: flex;
        min-height: 100vh;
        background: #fff;
        margin: 0;
        padding: 0;
    }
    
    .nucleus-sidebar {
        flex: 0 0 35%;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.7) 100%);
        background-size: cover;
        background-position: left center;
        background-image: url('{{ asset('images/Login_StockPhoto.jpg') }}');
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2.5rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    
    .nucleus-sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.5) 100%);
    }
    
    .nucleus-sidebar-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: space-between;
    }
    
    .nucleus-sidebar-top {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .nucleus-sidebar-top img {
        width: 50px;
        height: 50px;
    }
    
    .nucleus-sidebar-top-text {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    .nucleus-sidebar-testimonial {
        display: flex;
        flex-direction: column;
    }
    
    .nucleus-sidebar-quote {
        font-size: 1.75rem;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 2rem;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    
    .nucleus-sidebar-author {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .nucleus-sidebar-title {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 400;
    }
    
    .nucleus-form-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3rem 2rem;
        background: #fff;
    }
    
    .nucleus-form-wrapper {
        width: 100%;
        max-width: 400px;
    }
    
    .nucleus-logo {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e40af;
        margin-bottom: 2rem;
        letter-spacing: -0.5px;
    }
    
    .nucleus-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }
    
    .nucleus-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 2rem;
        line-height: 1.5;
    }
    
    .nucleus-form-group {
        margin-bottom: 1.5rem;
    }
    
    .nucleus-form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .nucleus-form-group input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }
    
    .nucleus-form-group input:focus {
        outline: none;
        border-color: #1e40af;
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }
    
    .nucleus-form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }
    
    .nucleus-forgot-link {
        color: #1e40af;
        text-decoration: none;
        font-weight: 500;
    }
    
    .nucleus-forgot-link:hover {
        color: #1e3a8a;
    }
    
    .nucleus-remember-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .nucleus-remember-check input {
        width: 1.2rem;
        height: 1.2rem;
        cursor: pointer;
        accent-color: #1e40af;
    }
    
    .nucleus-remember-check label {
        cursor: pointer;
        color: #374151;
        margin: 0;
    }
    
    .nucleus-btn-login {
        width: 100%;
        padding: 0.75rem 1rem;
        background: #1e40af;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-bottom: 1.5rem;
    }
    
    .nucleus-btn-login:hover {
        background: #1e3a8a;
    }
    
    .nucleus-btn-login:active {
        background: #172554;
    }
    
    .nucleus-divider {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .nucleus-divider::before,
    .nucleus-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    
    .nucleus-divider span {
        margin: 0 1rem;
        font-weight: 500;
    }
    
    .nucleus-btn-google {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #1f2937;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: background-color 0.2s, border-color 0.2s;
        text-decoration: none;
        margin-bottom: 1.5rem;
    }
    
    .nucleus-btn-google:hover {
        background: #f9fafb;
        border-color: #bfdbfe;
    }
    
    .nucleus-signup {
        text-align: center;
        font-size: 0.95rem;
        color: #6b7280;
    }
    
    .nucleus-signup a {
        color: #1e40af;
        text-decoration: none;
        font-weight: 600;
    }
    
    .nucleus-signup a:hover {
        color: #1e3a8a;
    }
    
    .alert {
        margin-bottom: 1.5rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    @media (max-width: 768px) {
        .nucleus-login-container {
            flex-direction: column;
        }
        
        .nucleus-sidebar {
            flex: 0 0 250px;
            min-height: 250px;
            padding: 2rem;
        }
        
        .nucleus-form-container {
            padding: 2rem 1rem;
        }
        
        .nucleus-sidebar-quote {
            font-size: 1.5rem;
        }
        
        .nucleus-title {
            font-size: 1.75rem;
        }
    }
</style>

<div class="nucleus-login-container">
    <!-- Left Sidebar -->
    <div class="nucleus-sidebar" style="background-image: url('{{ asset('images/Login_StockPhoto.jpg') }}');">
        <div class="nucleus-sidebar-content">
            <div class="nucleus-sidebar-top">
                <img src="{{ asset('images/GabayHealthLogoLight.png') }}" alt="GabayHealth Logo">
                <div class="nucleus-sidebar-top-text">GabayHealth</div>
            </div>
            <div class="nucleus-sidebar-testimonial">
                <div class="nucleus-sidebar-quote">
                    "Empowering rural health units with technology for better healthcare delivery."
                </div>
                <div class="nucleus-sidebar-author">GabayHealth</div>
                <div class="nucleus-sidebar-title">Community Health Management System</div>
            </div>
        </div>
    </div>
    
    <!-- Right Form Container -->
    <div class="nucleus-form-container">
        <div class="nucleus-form-wrapper">
            <div class="nucleus-logo">GabayHealth</div>
            <h1 class="nucleus-title">Welcome back</h1>
            <p class="nucleus-subtitle">Sign in to your account to manage your health services.</p>
            
            @if($errors->has('login'))
                <div class="alert alert-danger">{{ $errors->first('login') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <!-- Google Sign-In Button -->
            <a href="{{ route('google.login.redirect') }}" class="nucleus-btn-google">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </a>
            
            <div class="nucleus-divider">
                <span>OR</span>
            </div>
            
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="nucleus-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus value="{{ old('username') }}">
                </div>
                
                <div class="nucleus-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <div class="nucleus-form-actions">
                    <div class="nucleus-remember-check">
                        <input type="checkbox" id="remember" name="remember" value="on">
                        <label for="remember">Remember sign in details</label>
                    </div>
                </div>
                
                <a href="#" class="nucleus-forgot-link" style="display: block; margin-bottom: 1.5rem; text-align: right;">Forgot password?</a>
                
                <button type="submit" class="nucleus-btn-login">Log in</button>
            </form>
            
            <div class="nucleus-signup">
                Don't have an account? <a href="{{ route('register.landing') }}">Sign up</a>
            </div>
        </div>
    </div>
</div>

@endsection