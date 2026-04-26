@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
        height: 100%;
        width: 100%;
        background: #f8fafc;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    #app {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .registration-wrapper {
        width: 100%;
        max-width: 500px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 4px 12px rgba(0,0,0,0.08);
        padding: 40px;
    }

    .registration-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .registration-header img { width: 50px; height: 50px; margin-bottom: 15px; }

    .registration-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .registration-header p { color: #6b7280; font-size: 14px; }

    /* ── Stepper ── */
    .stepper {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        margin-bottom: 32px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        min-width: 70px;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        transition: background 0.3s, color 0.3s;
        flex-shrink: 0;
    }

    .step.active .step-circle  { background: #2563eb; color: #fff; }
    .step.completed .step-circle { background: #2563eb; color: #fff; }

    .step-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 500;
        white-space: nowrap;
        transition: color 0.3s;
    }

    .step.active .step-label    { color: #2563eb; font-weight: 600; }
    .step.completed .step-label { color: #2563eb; }

    .step-connector {
        flex: 1;
        height: 2px;
        background: #e5e7eb;
        margin-top: 15px;
        min-width: 28px;
        transition: background 0.3s;
    }

    .step-connector.completed { background: #2563eb; }

    /* ── Step panels ── */
    .step-panel { display: none; }

    .step-panel.active {
        display: block;
        animation: fadeIn 0.2s ease;
    }

    @@keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .step-heading {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* ── Form elements ── */
    .form-group { margin-bottom: 16px; }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background-color: #f9fafb;
        transition: all 0.2s;
        font-family: inherit;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #2563eb;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }

    .form-group small { display: block; color: #9ca3af; font-size: 12px; margin-top: 4px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-row .form-group { margin-bottom: 0; }

    /* ── Google badge ── */
    .google-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 20px;
    }

    .google-badge svg { flex-shrink: 0; }
    .google-badge-text { flex: 1; }
    .google-badge-text span { display: block; font-size: 12px; color: #9ca3af; }
    .google-badge-text strong { display: block; font-size: 13px; color: #1f2937; font-weight: 600; }
    .google-badge a { font-size: 12px; color: #2563eb; text-decoration: none; font-weight: 600; white-space: nowrap; }
    .google-badge a:hover { text-decoration: underline; }

    /* ── Logo upload ── */
    .logo-upload-section {
        background: #f3f4f6;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 20px;
    }

    .logo-upload-section:hover { border-color: #2563eb; background: #eff6ff; }
    .logo-upload-section.dragging { border-color: #2563eb; background: #dbeafe; }

    #logoUploadArea { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    #logoUploadArea svg { color: #9ca3af; }
    #logoUploadArea p { color: #6b7280; font-size: 13px; margin: 0; }
    #logoUploadArea p:first-of-type { font-weight: 600; color: #374151; }

    #logoPreview { display: none; text-align: center; }

    #logoPreviewImg { width: 100px; height: 100px; object-fit: contain; margin-bottom: 12px; }

    .logo-change-btn {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .logo-change-btn:hover { background: #e5e7eb; }

    /* ── Address / Mapbox ── */
    .location-search-container { position: relative; z-index: 1000; }

    .suggestions-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 6px 6px;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        display: none;
        z-index: 10000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: -1px;
    }

    .suggestions-list.show { display: block; }

    .suggestion-item {
        padding: 12px;
        font-size: 13px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover { background-color: #f3f4f6; color: #2563eb; }
    .suggestion-item .suggestion-title { font-weight: 600; color: #1f2937; }
    .suggestion-item .suggestion-subtitle { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    .manual-entry-option {
        padding: 12px;
        font-size: 13px;
        color: #2563eb;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: background-color 0.15s;
        font-weight: 600;
        text-align: center;
    }

    .manual-entry-option:hover { background-color: #eff6ff; }

    .manual-mode-indicator { font-size: 12px; color: #6b7280; margin-top: 4px; font-style: italic; display: none; }
    .manual-mode-indicator.active { color: #2563eb; }

    .location-coordinates { font-size: 12px; color: #9ca3af; margin-top: 8px; padding: 8px 0; }

    #addressSearch {
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background-color: #f9fafb;
        transition: all 0.2s;
        font-family: inherit;
    }

    #addressSearch:focus {
        outline: none;
        border-color: #2563eb;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }

    /* ── Terms ── */
    .terms-check { font-size: 12px; color: #6b7280; margin: 16px 0; line-height: 1.5; }
    .terms-check a { color: #2563eb; text-decoration: none; }
    .terms-check a:hover { text-decoration: underline; }
    .form-check { display: flex; align-items: center; gap: 8px; }
    .form-check-input { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }

    /* ── Alerts ── */
    .alert { padding: 12px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
    .alert-error { background: #fee2e2; color: #7f1d1d; border: 1px solid #fecaca; }
    .alert-error ul { margin: 0; padding-left: 1.5rem; }
    .alert-error li { font-size: 13px; }

    /* ── Step navigation ── */
    .step-nav { display: flex; gap: 10px; margin-top: 24px; }

    .nav-back-btn {
        flex: 1;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .nav-back-btn:hover { background: #e5e7eb; }

    .nav-next-btn, .nav-submit-btn {
        flex: 2;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .nav-next-btn:hover, .nav-submit-btn:hover { background: #1d4ed8; }
    .nav-next-btn:active, .nav-submit-btn:active { transform: scale(0.98); }

    .login-link { text-align: center; margin-top: 16px; font-size: 14px; color: #6b7280; }
    .login-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
    .login-link a:hover { text-decoration: underline; }

    @media (max-width: 600px) {
        .registration-wrapper { padding: 24px; }
        .registration-header h1 { font-size: 20px; }
        .form-row { grid-template-columns: 1fr; }
        .step-label { font-size: 10px; }
    }
</style>

<div id="app">
    <div class="registration-wrapper">

        <!-- Header -->
        <div class="registration-header">
            <img src="{{ asset('images/GabayHealthLogoLight.png') }}" alt="GabayHealth Logo">
            <h1>Sign up for GabayHealth</h1>
            <p>Complete your BHC registration</p>
        </div>

        <!-- Stepper -->
        <div class="stepper">
            <div class="step active" data-step="1">
                <div class="step-circle"><span>1</span></div>
                <div class="step-label">Your Center</div>
            </div>
            <div class="step-connector" id="connector-1"></div>
            <div class="step" data-step="2">
                <div class="step-circle"><span>2</span></div>
                <div class="step-label">Location</div>
            </div>
            <div class="step-connector" id="connector-2"></div>
            <div class="step" data-step="3">
                <div class="step-circle"><span>3</span></div>
                <div class="step-label">Details</div>
            </div>
        </div>

        <!-- Alerts -->
        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('register.bhw.google.submit') }}" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Your Center -->
            <div class="step-panel active" id="step-1">
                <p class="step-heading">Tell us about your health center</p>

                <!-- Google badge -->
                <div class="google-badge">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <div class="google-badge-text">
                        <span>Signed in with Google</span>
                        <strong>{{ session('google_email') }}</strong>
                    </div>
                    <a href="{{ route('google.redirect.bhw') }}">Change</a>
                </div>

                <!-- Logo Upload -->
                <div class="logo-upload-section" id="logoUploadSection">
                    <div id="logoUploadArea">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <p>Upload your Health Center logo</p>
                        <p>PNG or JPG, up to 5MB</p>
                    </div>
                    <div id="logoPreview">
                        <img id="logoPreviewImg" src="" alt="Logo Preview">
                        <button type="button" class="logo-change-btn">Change Logo</button>
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required value="{{ old('username') }}">
                    @error('username') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                </div>

                <!-- Health Center Name -->
                <div class="form-group">
                    <label for="healthCenterName">Health Center Name</label>
                    <input type="text" id="healthCenterName" name="healthCenterName" class="form-control" placeholder="Enter health center name" required value="{{ old('healthCenterName') }}">
                    @error('healthCenterName') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                </div>

                <!-- Password Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                        @error('password') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                    </div>
                </div>
            </div>

            <!-- Step 2: Location -->
            <div class="step-panel" id="step-2">
                <p class="step-heading">Where is your health center located?</p>

                <div class="form-group">
                    <label for="addressSearch">Address</label>
                    <div class="location-search-container">
                        <input type="text" id="addressSearch" class="form-control" placeholder="Search for an address..." autocomplete="off" value="{{ old('fullAddress') }}">
                        <div id="suggestionsList" class="suggestions-list"></div>
                        <input type="hidden" id="fullAddress" name="fullAddress" value="{{ old('fullAddress') }}">
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                        <input type="hidden" id="manualMode" name="manualMode" value="0">
                        <div class="location-coordinates"><span id="coordsDisplay"></span></div>
                        <div id="manualModeIndicator" class="manual-mode-indicator">
                            Manual entry mode — Type your full address below
                        </div>
                    </div>
                    @error('fullAddress') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" class="form-select">
                            <option value="">Select region</option>
                        </select>
                        @error('region') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select id="province" name="province" class="form-select">
                            <option value="">Select province</option>
                        </select>
                        @error('province') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City / Municipality</label>
                        <select id="city" name="city" class="form-select">
                            <option value="">Select city</option>
                        </select>
                        @error('city') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label for="barangay">Barangay</label>
                        <select id="barangay" name="barangay" class="form-select">
                            <option value="">Select barangay</option>
                        </select>
                        @error('barangay') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            <!-- Step 3: Details -->
            <div class="step-panel" id="step-3">
                <p class="step-heading">Final details</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postalCode">Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode" class="form-control" placeholder="Enter postal code" required value="{{ old('postalCode') }}">
                        @error('postalCode') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label for="rhuId">Parent RHU</label>
                        <select id="rhuId" name="rhuId" class="form-select" required>
                            <option value="">Select RHU</option>
                            @forelse($rhus as $rhu)
                                <option value="{{ $rhu['id'] }}" {{ old('rhuId') == $rhu['id'] ? 'selected' : '' }}>{{ $rhu['name'] }}</option>
                            @empty
                                <option value="" disabled>No approved RHUs available</option>
                            @endforelse
                        </select>
                        @if(empty($rhus))
                            <small style="color:#dc2626;">No approved RHUs found. Please contact administrator.</small>
                        @endif
                        @error('rhuId') <small style="color:#dc2626;">{{ $message }}</small> @enderror
                    </div>
                </div>

                <!-- Terms -->
                <div class="terms-check">
                    <div class="form-check">
                        <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>
                </div>
            </div>

            <!-- File input at form level -->
            <input type="file" id="logoUpload" name="logo" accept="image/*" style="display:none;">

            <!-- Navigation -->
            <div class="step-nav">
                <button type="button" id="backBtn" class="nav-back-btn" style="display:none;" onclick="goToStep(currentStep - 1)">Back</button>
                <button type="button" id="nextBtn" class="nav-next-btn" onclick="goToStep(currentStep + 1)">Next &rarr;</button>
                <button type="submit" id="submitBtn" class="nav-submit-btn" style="display:none;">Create Account</button>
            </div>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>

    </div>
</div>

<script>
let currentStep = {{ $errors->hasAny(['fullAddress', 'region', 'province', 'city', 'barangay']) ? 2 : ($errors->hasAny(['postalCode', 'rhuId', 'terms']) ? 3 : 1) }};
const totalSteps = 3;

const checkSVG = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

function goToStep(target) {
    if (target < 1 || target > totalSteps) return;
    if (target > currentStep && !validateStep(currentStep)) return;

    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step-' + target).classList.add('active');

    document.querySelectorAll('.step').forEach(s => {
        const n = parseInt(s.dataset.step);
        const circle = s.querySelector('.step-circle');
        s.classList.remove('active', 'completed');
        if (n === target) {
            s.classList.add('active');
            circle.innerHTML = `<span>${n}</span>`;
        } else if (n < target) {
            s.classList.add('completed');
            circle.innerHTML = checkSVG;
        } else {
            circle.innerHTML = `<span>${n}</span>`;
        }
    });

    for (let i = 1; i < totalSteps; i++) {
        const c = document.getElementById('connector-' + i);
        if (c) c.classList.toggle('completed', i < target);
    }

    document.getElementById('backBtn').style.display   = target === 1 ? 'none' : 'block';
    document.getElementById('nextBtn').style.display   = target < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = target === totalSteps ? 'block' : 'none';

    currentStep = target;
    document.querySelector('.registration-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
    if (step === 1) {
        const username         = document.getElementById('username').value.trim();
        const healthCenterName = document.getElementById('healthCenterName').value.trim();
        const password         = document.getElementById('password').value;
        const confirm          = document.getElementById('password_confirmation').value;

        if (!username)         { document.getElementById('username').focus(); document.getElementById('username').style.borderColor = '#dc2626'; return false; }
        document.getElementById('username').style.borderColor = '';

        if (!healthCenterName) { document.getElementById('healthCenterName').focus(); document.getElementById('healthCenterName').style.borderColor = '#dc2626'; return false; }
        document.getElementById('healthCenterName').style.borderColor = '';

        if (!password || password.length < 6) { document.getElementById('password').focus(); document.getElementById('password').style.borderColor = '#dc2626'; return false; }
        document.getElementById('password').style.borderColor = '';

        if (password !== confirm) { document.getElementById('password_confirmation').focus(); document.getElementById('password_confirmation').style.borderColor = '#dc2626'; return false; }
        document.getElementById('password_confirmation').style.borderColor = '';
    }
    if (step === 2) {
        const address  = document.getElementById('fullAddress').value.trim() || document.getElementById('addressSearch').value.trim();
        const region   = document.getElementById('region').value;
        const province = document.getElementById('province').value;
        const city     = document.getElementById('city').value;
        const barangay = document.getElementById('barangay').value;

        if (!address)  { document.getElementById('addressSearch').focus(); document.getElementById('addressSearch').style.borderColor = '#dc2626'; return false; }
        document.getElementById('addressSearch').style.borderColor = '';
        if (!region)   { document.getElementById('region').focus(); return false; }
        if (!province) { document.getElementById('province').focus(); return false; }
        if (!city)     { document.getElementById('city').focus(); return false; }
        if (!barangay) { document.getElementById('barangay').focus(); return false; }
    }
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    if (currentStep > 1) goToStep(currentStep);

    // Logo upload
    const logoUploadSection = document.getElementById('logoUploadSection');
    const logoUploadArea    = document.getElementById('logoUploadArea');
    const logoUpload        = document.getElementById('logoUpload');
    const logoPreview       = document.getElementById('logoPreview');
    const logoPreviewImg    = document.getElementById('logoPreviewImg');

    logoUploadSection.addEventListener('click', () => logoUpload.click());

    logoUpload.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                logoPreviewImg.src = e.target.result;
                logoUploadArea.style.display = 'none';
                logoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.querySelector('.logo-change-btn').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        logoUpload.value = '';
        logoUploadArea.style.display = 'flex';
        logoPreview.style.display = 'none';
        logoUpload.click();
    });

    logoUploadSection.addEventListener('dragover', (e) => { e.preventDefault(); logoUploadSection.classList.add('dragging'); });
    logoUploadSection.addEventListener('dragleave', () => logoUploadSection.classList.remove('dragging'));
    logoUploadSection.addEventListener('drop', (e) => {
        e.preventDefault();
        logoUploadSection.classList.remove('dragging');
        logoUpload.files = e.dataTransfer.files;
        logoUpload.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // Location dropdowns (PSGC)
    const regionSelect   = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect     = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    fetch('https://psgc.gitlab.io/api/regions/')
        .then(res => res.json())
        .then(data => data.forEach(r => regionSelect.innerHTML += `<option value="${r.code}">${r.name}</option>`));

    regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, 'Select province');
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/regions/${this.value}/provinces/`)
            .then(res => res.json())
            .then(data => data.forEach(p => provinceSelect.innerHTML += `<option value="${p.code}">${p.name}</option>`));
    });

    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`)
            .then(res => res.json())
            .then(data => data.forEach(c => citySelect.innerHTML += `<option value="${c.code}">${c.name}</option>`));
    });

    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, 'Select barangay');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`)
            .then(res => res.json())
            .then(data => data.forEach(b => barangaySelect.innerHTML += `<option value="${b.code}">${b.name}</option>`));
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxAccessToken   = @json(env('MAPBOX_ACCESS_TOKEN'));
    const searchInput         = document.getElementById('addressSearch');
    const suggestionsList     = document.getElementById('suggestionsList');
    const fullAddressInput    = document.getElementById('fullAddress');
    const latitudeInput       = document.getElementById('latitude');
    const longitudeInput      = document.getElementById('longitude');
    const coordsDisplay       = document.getElementById('coordsDisplay');
    const manualModeInput     = document.getElementById('manualMode');
    const manualModeIndicator = document.getElementById('manualModeIndicator');

    if (!mapboxAccessToken) {
        searchInput.addEventListener('input', function () { fullAddressInput.value = this.value.trim(); });
        searchInput.addEventListener('blur',  function () { if (this.value.trim()) fullAddressInput.value = this.value.trim(); });
        return;
    }

    if (searchInput.value && !fullAddressInput.value) fullAddressInput.value = searchInput.value;

    let searchTimeout;
    let isManualMode = false;

    function enableManualMode() {
        isManualMode = true;
        manualModeInput.value = '1';
        manualModeIndicator.style.display = 'block';
        manualModeIndicator.classList.add('active');
        searchInput.placeholder = 'Enter your full address manually...';
        suggestionsList.innerHTML = '';
        suggestionsList.classList.remove('show');
        searchInput.focus();
        latitudeInput.value = '';
        longitudeInput.value = '';
        coordsDisplay.textContent = '';
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        if (query.length > 0) fullAddressInput.value = query;
        if (isManualMode) return;
        if (query.length < 2) { suggestionsList.innerHTML = ''; suggestionsList.classList.remove('show'); if (!query) fullAddressInput.value = ''; return; }
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchMapbox(query), 300);
    });

    searchInput.addEventListener('blur',   function () { if (this.value.trim()) fullAddressInput.value = this.value.trim(); });
    searchInput.addEventListener('change', function () { if (this.value.trim()) fullAddressInput.value = this.value.trim(); });

    function searchMapbox(query) {
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${mapboxAccessToken}&country=PH&proximity=120.7,15.5&limit=5`;
        fetch(url).then(r => r.json()).then(d => displaySuggestions(d.features || [])).catch(() => displaySuggestions([]));
    }

    function displaySuggestions(features) {
        suggestionsList.innerHTML = '';
        if (!features.length) {
            const opt = document.createElement('div');
            opt.className = 'manual-entry-option';
            opt.textContent = 'Enter full address manually';
            opt.addEventListener('click', (e) => { e.preventDefault(); enableManualMode(); });
            suggestionsList.appendChild(opt);
            suggestionsList.classList.add('show');
            return;
        }
        features.forEach(feature => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            const title    = feature.place_name.split(',')[0];
            const subtitle = feature.place_name.split(',').slice(1).join(',').trim();
            item.innerHTML = `<div class="suggestion-title">${title}</div><div class="suggestion-subtitle">${subtitle}</div>`;
            item.addEventListener('click', () => selectAddress(feature));
            suggestionsList.appendChild(item);
        });
        suggestionsList.classList.add('show');
    }

    function selectAddress(feature) {
        const [lng, lat] = feature.geometry.coordinates;
        searchInput.value      = feature.place_name;
        fullAddressInput.value = feature.place_name;
        latitudeInput.value    = lat.toFixed(6);
        longitudeInput.value   = lng.toFixed(6);
        coordsDisplay.textContent = `📍 ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        suggestionsList.innerHTML = '';
        suggestionsList.classList.remove('show');
        isManualMode = false;
        manualModeInput.value = '0';
        manualModeIndicator.style.display = 'none';
        searchInput.placeholder = 'Search for an address...';
    }

    document.addEventListener('click', function (e) {
        if (e.target !== searchInput && !suggestionsList.contains(e.target)) suggestionsList.classList.remove('show');
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        if (searchInput.value.trim()) fullAddressInput.value = searchInput.value.trim();
        if (!fullAddressInput.value.trim()) { e.preventDefault(); searchInput.focus(); }
    });
});
</script>
@endsection
