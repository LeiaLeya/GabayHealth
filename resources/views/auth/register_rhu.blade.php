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
    
    .oauth-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #d1d5db;
        color: #1f2937;
        border-radius: 6px;
        padding: 10px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 16px;
    }
    
    .oauth-btn:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    
    .divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 0;
        font-size: 12px;
        color: #9ca3af;
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
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
    
    .terms-check {
        font-size: 12px;
        color: #6b7280;
        margin: 16px 0;
        line-height: 1.5;
    }
    
    .terms-check a {
        color: #2563eb;
        text-decoration: none;
    }
    
    .terms-check a:hover {
        text-decoration: underline;
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
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #7f1d1d;
        border: 1px solid #fecaca;
    }
    
    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-check-input {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #2563eb;
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
        padding: 12px 12px;
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
        padding: 12px 12px;
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
            <h1>Sign up for GabayHealth</h1>
            <p>Join GabayHealth and manage your health unit</p>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('register.rhu.submit') }}" enctype="multipart/form-data">
            @csrf

            <!-- Logo Upload -->
            <div class="logo-upload-section" id="logoUploadSection">
                <div id="logoUploadArea">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <p>Upload your RHU logo</p>
                    <p>PNG or JPG, up to 5MB</p>
                </div>
                <div id="logoPreview">
                    <img id="logoPreviewImg" src="" alt="Logo Preview">
                    <button type="button" class="logo-change-btn">Change Logo</button>
                </div>
            </div>

            <input type="file" id="logoUpload" name="logo" accept="image/*" style="display: none;">

            <!-- OAuth Button -->
            <button type="button" class="oauth-btn" onclick="window.location.href='{{ route('google.redirect') }}'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Sign up with Google
            </button>

            <div class="divider">or</div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required value="{{ old('email') }}">
                <small style="color: #9ca3af;">You will receive your credentials here after admin approval</small>
                @error('email') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- RHU Name -->
            <div class="form-group">
                <label for="rhuName">Rural Health Unit</label>
                <input type="text" id="rhuName" name="rhuName" class="form-control" placeholder="Rural" required value="{{ old('rhuName') }}">
                @error('rhuName') <small style="color: #dc2626;">{{ $message }}</small> @enderror
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
                    <div class="location-coordinates">
                        <span id="coordsDisplay"></span>
                    </div>
                    <div id="manualModeIndicator" class="manual-mode-indicator" style="display: none;">
                        Manual entry mode - Type your full address below
                    </div>
                </div>
                @error('fullAddress') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Region, Province, City Row -->
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

            <div class="form-group">
                <label for="city">City/Municipality</label>
                <select id="city" name="city" class="form-select" required>
                    <option value="">Select city</option>
                </select>
                @error('city') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Terms Checkbox -->
            <div class="terms-check">
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Create Account</button>

            <!-- Login Link -->
            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
    document.querySelector('.logo-change-btn').addEventListener('click', (e) => {
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

    // Location dropdowns
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
        resetSelect(provinceSelect, 'Select province');
        resetSelect(citySelect, 'Select city');
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

<!-- Mapbox Geocoding API -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxAccessToken = @json(config('mapbox.access_token'));
    
    if (!mapboxAccessToken) {
        console.error('Mapbox token not configured');
    const mapboxAccessToken = @json(env('MAPBOX_ACCESS_TOKEN'));
    
    console.log('Mapbox token loaded:', mapboxAccessToken ? 'Yes (length: ' + mapboxAccessToken.length + ')' : 'No');
    
    if (!mapboxAccessToken || mapboxAccessToken === null) {
        console.error('Mapbox token not configured. Please set MAPBOX_ACCESS_TOKEN in your .env file');
        // If Mapbox is not configured, enable manual mode by default
        const searchInput = document.getElementById('addressSearch');
        const fullAddressInput = document.getElementById('fullAddress');
        const manualModeInput = document.getElementById('manualMode');
        const manualModeIndicator = document.getElementById('manualModeIndicator');
        
        if (searchInput && fullAddressInput) {
            searchInput.addEventListener('input', function() {
                fullAddressInput.value = this.value.trim();
            });
            searchInput.addEventListener('blur', function() {
                if (this.value.trim()) {
                    fullAddressInput.value = this.value.trim();
                }
            });
        }
        return;
    }

    const searchInput = document.getElementById('addressSearch');
    const suggestionsList = document.getElementById('suggestionsList');
    const fullAddressInput = document.getElementById('fullAddress');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordsDisplay = document.getElementById('coordsDisplay');
    const manualModeInput = document.getElementById('manualMode');
    const manualModeIndicator = document.getElementById('manualModeIndicator');

    if (!searchInput || !fullAddressInput || !suggestionsList) {
        console.error('Required form elements not found', {
            searchInput: !!searchInput,
            fullAddressInput: !!fullAddressInput,
            suggestionsList: !!suggestionsList
        });
        return;
    }

    console.log('Mapbox geocoding initialized successfully');

    // Initialize fullAddress from searchInput if it has old value
    if (searchInput.value && !fullAddressInput.value) {
        fullAddressInput.value = searchInput.value;
    }

    let searchTimeout;
    let isManualMode = false;

    // Enable manual address entry mode
    function enableManualMode() {
        isManualMode = true;
        manualModeInput.value = '1';
        manualModeIndicator.style.display = 'block';
        manualModeIndicator.classList.add('active');
        searchInput.placeholder = 'Enter your full address manually...';
        suggestionsList.innerHTML = '';
        suggestionsList.classList.remove('show');
        searchInput.focus();
        
        // Clear coordinates since we don't have them in manual mode
        latitudeInput.value = '';
        longitudeInput.value = '';
        coordsDisplay.textContent = '';
    }

    // Search as user types
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Always update fullAddress when user types (as fallback)
        // This ensures the field is populated even if user doesn't select a suggestion
        if (query.length > 0) {
            fullAddressInput.value = query;
        }
        
        // In manual mode, we're done - just update the value
        if (isManualMode) {
            return;
        }
        
        if (query.length < 2) {
            suggestionsList.innerHTML = '';
            suggestionsList.classList.remove('show');
            // Don't clear fullAddress if user has typed something
            if (query.length === 0) {
                fullAddressInput.value = '';
            }
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchMapbox(query);
        }, 300);
    });

    // Ensure address is saved when user leaves the field
    searchInput.addEventListener('blur', function() {
        const query = this.value.trim();
        if (query.length > 0) {
            fullAddressInput.value = query;
            console.log('Address saved on blur:', query);
        }
    });

    // Also sync on change event
    searchInput.addEventListener('change', function() {
        const query = this.value.trim();
        if (query.length > 0) {
            fullAddressInput.value = query;
            console.log('Address saved on change:', query);
        }
    });

    function searchMapbox(query) {
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?` +
            `access_token=${mapboxAccessToken}&` +
            `country=PH&` +
            `proximity=121.7740,12.8797&` +
            `limit=8`;

        console.log('Searching Mapbox for:', query);
        
        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`Mapbox API error: ${res.status} ${res.statusText}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Mapbox results:', data.features?.length || 0, 'suggestions found');
                displaySuggestions(data.features || []);
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                // Show manual entry option on error
                displaySuggestions([]);
            });
    }

    function displaySuggestions(features) {
        suggestionsList.innerHTML = '';

        if (!features || features.length === 0) {
            suggestionsList.innerHTML = '<div style="padding: 12px; color: #9ca3af;">No results found</div>';
            // Show "Enter full address manually" option when no results
            const manualOption = document.createElement('div');
            manualOption.className = 'manual-entry-option';
            manualOption.textContent = 'Enter full address manually';
            manualOption.addEventListener('click', (e) => {
                e.preventDefault();
                enableManualMode();
            });
            suggestionsList.appendChild(manualOption);
            suggestionsList.classList.add('show');
            console.log('No suggestions found, showing manual entry option');
            return;
        }

        features.forEach((feature) => {
        console.log('Displaying', features.length, 'suggestions');
        
        features.forEach((feature, index) => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            
            const title = feature.place_name.split(',')[0];
            const subtitle = feature.place_name.split(',').slice(1).join(',').trim();

            item.innerHTML = `
                <div class="suggestion-title">${title}</div>
                <div class="suggestion-subtitle">${subtitle}</div>
            `;

            item.addEventListener('click', () => {
                console.log('Selected address:', feature.place_name);
                selectAddress(feature);
            });

            suggestionsList.appendChild(item);
        });

        suggestionsList.classList.add('show');
        console.log('Suggestions list shown, element:', suggestionsList);
    }

    function selectAddress(feature) {
        const [longitude, latitude] = feature.geometry.coordinates;

        searchInput.value = feature.place_name;
        fullAddressInput.value = feature.place_name;
        latitudeInput.value = latitude.toFixed(6);
        longitudeInput.value = longitude.toFixed(6);
        coordsDisplay.textContent = `📍 ${latitude.toFixed(4)}, ${longitude.toFixed(4)}`;

        suggestionsList.innerHTML = '';
        suggestionsList.classList.remove('show');
        isManualMode = false;
        manualModeInput.value = '0';
        manualModeIndicator.style.display = 'none';
        searchInput.placeholder = 'Search for an address...';
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && !suggestionsList.contains(e.target)) {
            suggestionsList.classList.remove('show');
        }
    });

    // Form validation - ensure address is set before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        // Always sync searchInput to fullAddress before submit
        const searchValue = searchInput.value.trim();
        if (searchValue.length > 0) {
            fullAddressInput.value = searchValue;
        }
        
        const addressValue = fullAddressInput.value.trim();
        
        if (!addressValue) {
            e.preventDefault();
            alert('Please enter or select an address');
            searchInput.focus();
            return false;
        }
        
        // Log for debugging
        console.log('Submitting form with address:', addressValue);
    });
});
</script>
@endsection