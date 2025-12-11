@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
        background: #fff !important;
        overflow: hidden !important;
        box-sizing: border-box;
    }
    #app {
        height: 100vh;
    }
    .container-fluid {
        min-height: 100vh;
        width: 100%;
        padding: 0;
        margin: 0;
        overflow: hidden !important;
        display: flex;
    }
    .form-control, .form-select {
        min-height: 40px;
        font-size: 14px;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }
    .left-panel {
        background: linear-gradient(135deg, #1a202c 0%, #0f172a 100%);
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .left-panel::before {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        top: -100px;
        right: -100px;
    }
    .left-panel::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        bottom: -50px;
        left: -50px;
    }
    .left-content {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 400px;
        padding: 40px;
    }
    .left-content h1 {
        color: #fff;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }
    .left-content p {
        color: #cbd5e1;
        font-size: 1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    .right-panel {
        flex: 1;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        overflow-y: auto;
    }
    .form-container {
        width: 100%;
        max-width: 400px;
    }
    .form-container h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    .form-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .user-info {
        background: #f3f4f6;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .user-info p {
        margin: 0.25rem 0;
        color: #374151;
        font-size: 0.9rem;
    }
    .user-info strong {
        color: #1f2937;
    }
    .submit-btn {
        width: 100%;
        background: #1f2937;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        padding: 0.625rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        margin-top: 1rem;
        transition: background 0.2s;
    }
    .submit-btn:hover {
        background: #111827;
    }
    .login-link {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #6b7280;
    }
    .login-link a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .container-fluid {
            flex-direction: column;
        }
        .left-panel {
            min-height: 250px;
        }
        .right-panel {
            min-height: calc(100vh - 250px);
        }
    }
</style>

<div class="container-fluid">
    <!-- Left Panel -->
    <div class="left-panel">
        <div class="left-content">
            <h1>GabayHealth</h1>
            <p>Complete your registration to start managing your Rural Health Unit.</p>
        </div>
    </div>

    <!-- Right Panel: Simplified Form -->
    <div class="right-panel">
        <div class="form-container">
            <h2>Complete Your Profile</h2>
            <p class="form-subtitle">Just a few more details to get started.</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li style="font-size: 0.9rem;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Display Google Email (Read-only) -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ session('google_email') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
            </div>

            <a href="{{ route('google.redirect') }}" style="color: #2563eb; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Use a different Google account</a>

            <form method="POST" action="{{ route('register.rhu.google.submit') }}" style="margin-top: 1.5rem;">
                @csrf

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                    <small style="color: #9ca3af;">Must be at least 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label for="rhuName">Rural Health Unit Name</label>
                    <input type="text" id="rhuName" name="rhuName" class="form-control" placeholder="Your RHU name" required>
                </div>

                <div class="form-group">
                    <label for="region">Region</label>
                    <select id="region" name="region" class="form-select" required>
                        <option value="">Select a region</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="province">Province</label>
                    <select id="province" name="province" class="form-select" required>
                        <option value="">Select a province</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="city">City or Municipality</label>
                    <select id="city" name="city" class="form-select" required>
                        <option value="">Select city or municipality</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Complete Registration</button>

                <div class="login-link">
                    <a href="{{ route('register.rhu') }}">← Back to regular registration</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    fetch('https://psgc.gitlab.io/api/regions/')
        .then(res => res.json())
        .then(data => {
            data.forEach(region => {
                regionSelect.innerHTML += `<option value="${region.code}">${region.name}</option>`;
            });
        });

    regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, 'Select a province');
        resetSelect(citySelect, 'Select city/municipality');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/regions/${this.value}/provinces/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(province => {
                    provinceSelect.innerHTML += `<option value="${province.code}">${province.name}</option>`;
                });
            });
    });

    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, 'Select city/municipality');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.code}">${city.name}</option>`;
                });
            });
    });
});
</script>
@endsection