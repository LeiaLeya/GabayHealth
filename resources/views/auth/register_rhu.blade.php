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
    .registration-form .form-control, .registration-form .form-select {
        min-height: 40px;
        font-size: 14px;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        min-width: 0;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;
    }
    .registration-form .form-control:focus, .registration-form .form-select:focus {
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
        line-height: 1.2;
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
    .form-group small {
        display: block;
        color: #9ca3af;
        font-size: 0.85rem;
        margin-top: 0.25rem;
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
    .login-link a:hover {
        text-decoration: underline;
    }
    .oauth-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: #fff;
        border: 1px solid #d1d5db;
        color: #1f2937;
        border-radius: 0.5rem;
        padding: 0.625rem;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
        margin-bottom: 1rem;
    }
    .oauth-btn:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .divider span {
        padding: 0 0.75rem;
        color: #9ca3af;
        font-size: 0.85rem;
        font-weight: 500;
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
    <!-- Left Panel: Branding -->
    <div class="left-panel">
        <div class="left-content">
            <h1>GabayHealth</h1>
            <p>Register your Rural Health Unit to manage inventory and health records efficiently.</p>
            <div id="logoUploadArea" style="width: 120px; height: 120px; border: 2px dashed #cbd5e1; border-radius: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; background: rgba(37, 99, 235, 0.1); margin: 0 auto; transition: all 0.3s ease;">
                <img id="logoPreview" src='{{ asset('images/gabayhealth_logo.png') }}' alt='RHU Logo' style='width: 70px; height: 70px; object-fit: contain;'>
                <input type="file" id="logoUpload" name="logo" accept="image/*" style="display: none;" onchange="previewLogo(event)">
            </div>
            <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 1rem;">Upload your RHU logo</p>
        </div>
    </div>

    <!-- Right Panel: Registration Form -->
    <div class="right-panel">
        <div class="form-container">
            <h2>Sign up for GabayHealth</h2>
            <p class="form-subtitle">Get started with GabayHealth today.</p>
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3" style="font-size:0.95rem;">{{ session('success') }}</div>
            @endif
            <form class="registration-form" method="POST" action="{{ route('register.rhu.submit') }}">
                @csrf
                
                <button type="button" class="oauth-btn" onclick="handleGoogleSignIn()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </button>

                <div class="divider">
                    <span>or</span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                    <small>Must be at least 8 characters with a number and lowercase letter</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label for="rhuName">Rural Health Unit</label>
                    <input type="text" id="rhuName" name="rhuName" class="form-control" placeholder="Rural Health Unit" required>
                </div>

                <div class="form-group">
                    <label for="fullAddress">Address</label>
                    <input type="text" id="fullAddress" name="fullAddress" class="form-control" placeholder="Address" required>
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

                <div style="margin-top: 1.5rem; margin-bottom: 1rem;">
                    <div class="form-check" style="font-size: 0.9rem;">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" style="color:#2563eb; text-decoration:none;">Terms</a> and <a href="#" style="color:#2563eb; text-decoration:none;">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Create account</button>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function handleGoogleSignIn() {
    alert('Google Sign-In integration coming soon!');
    // TODO: Implement Firebase Google authentication
    // window.location.href = '/auth/google';
}

function previewLogo(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const logoUploadArea = document.getElementById('logoUploadArea');
    const logoUpload = document.getElementById('logoUpload');
    
    if (logoUploadArea) {
        logoUploadArea.addEventListener('click', function() {
            logoUpload.click();
        });
        
        logoUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            logoUploadArea.style.background = 'rgba(37, 99, 235, 0.2)';
            logoUploadArea.style.borderColor = '#fff';
        });
        
        logoUploadArea.addEventListener('dragleave', function() {
            logoUploadArea.style.background = 'rgba(37, 99, 235, 0.1)';
            logoUploadArea.style.borderColor = '#cbd5e1';
        });
        
        logoUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                logoUpload.files = files;
                previewLogo({target: {files: files}});
            }
            logoUploadArea.style.background = 'rgba(37, 99, 235, 0.1)';
        });
    }

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

    regionSelect && regionSelect.addEventListener('change', function () {
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

    provinceSelect && provinceSelect.addEventListener('change', function () {
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
