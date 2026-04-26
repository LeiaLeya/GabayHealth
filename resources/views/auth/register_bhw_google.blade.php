@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
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
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 40px;
    }
    
    .registration-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .registration-header img {
        width: 50px;
        height: 50px;
        margin-bottom: 15px;
    }
    
    .registration-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }
    
    .registration-header p {
        color: #6b7280;
        font-size: 15px;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
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
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .form-control:read-only {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }
    
    .form-group small {
        display: block;
        color: #9ca3af;
        font-size: 12px;
        margin-top: 4px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .form-row .form-group {
        margin-bottom: 0;
    }
    
    .change-account-link {
        display: inline-block;
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 16px;
    }
    
    .change-account-link:hover {
        text-decoration: underline;
    }
    
    .submit-btn {
        width: 100%;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 20px;
    }
    
    .submit-btn:hover {
        background: #1d4ed8;
    }
    
    .submit-btn:active {
        transform: scale(0.98);
    }
    
    .login-link {
        text-align: center;
        margin-top: 16px;
        font-size: 14px;
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
    
    .alert {
        padding: 12px 14px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 16px;
    }
    
    .alert-danger {
        background: #fee2e2;
        color: #7f1d1d;
        border: 1px solid #fecaca;
    }
    
    .alert-danger ul {
        margin: 0;
        padding-left: 1.5rem;
    }
    
    .alert-danger li {
        font-size: 13px;
    }

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
    
    .logo-upload-section:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    
    .logo-upload-section.dragging {
        border-color: #2563eb;
        background: #dbeafe;
    }
    
    #logoUploadArea {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    #logoUploadArea svg {
        color: #9ca3af;
    }
    
    #logoUploadArea p {
        color: #6b7280;
        font-size: 13px;
        margin: 0;
    }
    
    #logoUploadArea p:first-child {
        font-weight: 600;
        color: #374151;
    }
    
    #logoPreview {
        display: none;
        text-align: center;
    }
    
    #logoPreviewImg {
        width: 100px;
        height: 100px;
        object-fit: contain;
        margin-bottom: 12px;
    }
    
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
    
    .logo-change-btn:hover {
        background: #e5e7eb;
    }

    /* Mapbox Geocoder Styles */
    .location-search-container {
        position: relative;
        z-index: 1000;
    }

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
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: -1px;
    }

    .suggestions-list.show {
        display: block;
    }

    .suggestion-item {
        padding: 12px;
        font-size: 13px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover {
        background-color: #f3f4f6;
        color: #2563eb;
    }

    .suggestion-item .suggestion-title {
        font-weight: 600;
        color: #1f2937;
    }

    .suggestion-item .suggestion-subtitle {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }

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

    .manual-entry-option:hover {
        background-color: #eff6ff;
    }

    .manual-mode-indicator {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
        font-style: italic;
    }

    .manual-mode-indicator.active {
        color: #2563eb;
    }

    .location-coordinates {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 8px;
        padding: 8px 0;
    }

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
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    @media (max-width: 600px) {
        .registration-wrapper {
            padding: 24px;
        }
        
        .registration-header h1 {
            font-size: 24px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="app">
    <div class="registration-wrapper">
        <!-- Header -->
        <div class="registration-header">
            <img src="{{ asset('images/GabayHealthLight.png') }}" alt="GabayHealth Logo">
            <h1>Complete Your Profile</h1>
            <p>Just a few more details to get started</p>
        </div>

        <!-- Alerts -->
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- User Info Display -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ session('google_email') }}" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
        </div>

        <a href="{{ route('google.redirect.bhw') }}" class="change-account-link">Use a different Google account</a>

        <!-- Form -->
        <form method="POST" action="{{ route('register.bhw.google.submit') }}" enctype="multipart/form-data">
            @csrf

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

            <input type="file" id="logoUpload" name="logo" accept="image/*" style="display: none;">

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required value="{{ old('username') }}">
                @error('username') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Password Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                    @error('password') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                </div>
            </div>

            <!-- Health Center Name -->
            <div class="form-group">
                <label for="healthCenterName">Health Center Name</label>
                <input type="text" id="healthCenterName" name="healthCenterName" class="form-control" placeholder="Enter health center name" required value="{{ old('healthCenterName') }}">
                @error('healthCenterName') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Address -->
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
                    <div id="manualModeIndicator" class="manual-mode-indicator" style="display: none;">
                        Manual entry mode — Type your full address below
                    </div>
                </div>
                @error('fullAddress') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Region, Province Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="region">Region</label>
                    <select id="region" name="region" class="form-select" required>
                        <option value="">Select region</option>
                    </select>
                    @error('region') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="province">Province</label>
                    <select id="province" name="province" class="form-select" required>
                        <option value="">Select province</option>
                    </select>
                    @error('province') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- City and Barangay Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City/Municipality</label>
                    <select id="city" name="city" class="form-select" required>
                        <option value="">Select city</option>
                    </select>
                    @error('city') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" class="form-select" required>
                        <option value="">Select barangay</option>
                    </select>
                    @error('barangay') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Postal Code and Parent RHU Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="postalCode">Postal Code</label>
                    <input type="text" id="postalCode" name="postalCode" class="form-control" placeholder="Enter postal code" required value="{{ old('postalCode') }}">
                    @error('postalCode') <small style="color: #dc2626;">{{ $message }}</small> @enderror
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
                        <small style="color: #dc2626;">No approved RHUs found. Please contact administrator or wait for RHU approval.</small>
                    @endif
                    @error('rhuId') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Complete Registration</button>

            <!-- Login Link -->
            <div class="login-link">
                <a href="{{ route('register.bhw') }}">← Back to regular registration</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

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
        resetSelect(provinceSelect, 'Select province');
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
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
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.code}">${city.name}</option>`;
                });
            });
    });

    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, 'Select barangay');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(barangay => {
                    barangaySelect.innerHTML += `<option value="${barangay.code}">${barangay.name}</option>`;
                });
            });
    });

    // Logo Upload Handlers
    const logoUploadSection = document.getElementById('logoUploadSection');
    const logoUploadArea = document.getElementById('logoUploadArea');
    const logoUpload = document.getElementById('logoUpload');
    const logoPreview = document.getElementById('logoPreview');
    const logoPreviewImg = document.getElementById('logoPreviewImg');
    
    // Click to upload
    logoUploadSection.addEventListener('click', () => logoUpload.click());

    // File selected
    logoUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreviewImg.src = e.target.result;
                logoUploadArea.style.display = 'none';
                logoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Change logo button
    document.querySelector('.logo-change-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        logoUpload.value = '';
        logoUploadArea.style.display = 'flex';
        logoPreview.style.display = 'none';
        logoUpload.click();
    });
    
    // Drag and drop
    logoUploadSection.addEventListener('dragover', (e) => {
        e.preventDefault();
        logoUploadSection.classList.add('dragging');
    });
    
    logoUploadSection.addEventListener('dragleave', () => {
        logoUploadSection.classList.remove('dragging');
    });
    
    logoUploadSection.addEventListener('drop', (e) => {
        e.preventDefault();
        logoUploadSection.classList.remove('dragging');
        logoUpload.files = e.dataTransfer.files;
        const event = new Event('change', { bubbles: true });
        logoUpload.dispatchEvent(event);
    });
});
</script>
<!-- Mapbox Geocoding -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxAccessToken = @json(env('MAPBOX_ACCESS_TOKEN'));

    const searchInput         = document.getElementById('addressSearch');
    const suggestionsList     = document.getElementById('suggestionsList');
    const fullAddressInput    = document.getElementById('fullAddress');
    const latitudeInput       = document.getElementById('latitude');
    const longitudeInput      = document.getElementById('longitude');
    const coordsDisplay       = document.getElementById('coordsDisplay');
    const manualModeInput     = document.getElementById('manualMode');
    const manualModeIndicator = document.getElementById('manualModeIndicator');

    if (!mapboxAccessToken) {
        if (searchInput && fullAddressInput) {
            searchInput.addEventListener('input', function () {
                fullAddressInput.value = this.value.trim();
            });
            searchInput.addEventListener('blur', function () {
                if (this.value.trim()) fullAddressInput.value = this.value.trim();
            });
        }
        return;
    }

    if (searchInput.value && !fullAddressInput.value) {
        fullAddressInput.value = searchInput.value;
    }

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
        if (query.length < 2) {
            suggestionsList.innerHTML = '';
            suggestionsList.classList.remove('show');
            if (query.length === 0) fullAddressInput.value = '';
            return;
        }
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchMapbox(query), 300);
    });

    searchInput.addEventListener('blur', function () {
        if (this.value.trim()) fullAddressInput.value = this.value.trim();
    });

    searchInput.addEventListener('change', function () {
        if (this.value.trim()) fullAddressInput.value = this.value.trim();
    });

    function searchMapbox(query) {
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?` +
            `access_token=${mapboxAccessToken}&country=PH&proximity=120.7,15.5&limit=5`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error(`Mapbox error: ${res.status}`);
                return res.json();
            })
            .then(data => displaySuggestions(data.features || []))
            .catch(() => displaySuggestions([]));
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
        if (e.target !== searchInput && !suggestionsList.contains(e.target)) {
            suggestionsList.classList.remove('show');
        }
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        const searchValue = searchInput.value.trim();
        if (searchValue) fullAddressInput.value = searchValue;
        if (!fullAddressInput.value.trim()) {
            e.preventDefault();
            searchInput.focus();
            return false;
        }
    });
});
</script>
@endsection
