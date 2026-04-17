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
    
    .user-info {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 16px;
    }
    
    .user-info p {
        margin: 4px 0;
        color: #374151;
        font-size: 13px;
    }
    
    .user-info strong {
        color: #1f2937;
        font-weight: 600;
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
        z-index: 10;
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
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        <a href="{{ route('google.redirect') }}" class="change-account-link">Use a different Google account</a>

        <!-- Form -->
        <form method="POST" action="{{ route('register.rhu.google.submit') }}" enctype="multipart/form-data">
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

            <!-- RHU Name -->
            <div class="form-group">
                <label for="rhuName">Rural Health Unit</label>
                <input type="text" id="rhuName" name="rhuName" class="form-control" placeholder="Rural Health Unit" required value="{{ old('rhuName') }}">
                @error('rhuName') <small style="color: #dc2626;">{{ $message }}</small> @enderror
            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="addressSearch">Address</label>
                <div class="location-search-container">
                    <input type="text" id="addressSearch" class="form-control" placeholder="Search for an address..." autocomplete="off">
                    <div id="suggestionsList" class="suggestions-list"></div>
                    <input type="hidden" id="fullAddress" name="fullAddress" value="{{ old('fullAddress') }}">
                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                    <div class="location-coordinates">
                        <span id="coordsDisplay"></span>
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

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Complete Registration</button>

            <!-- Login Link -->
            <div class="login-link">
                <a href="{{ route('register.rhu') }}">← Back to regular registration</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxAccessToken = @json(config('mapbox.access_token'));
    
    if (!mapboxAccessToken) {
        console.error('Mapbox token not configured');
        loadLocationDropdowns();
        return;
    }

    const searchInput = document.getElementById('addressSearch');
    const suggestionsList = document.getElementById('suggestionsList');
    const fullAddressInput = document.getElementById('fullAddress');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const coordsDisplay = document.getElementById('coordsDisplay');

    let searchTimeout;

    // Search as user types
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsList.innerHTML = '';
            suggestionsList.classList.remove('show');
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchMapbox(query), 300);
    });

    function searchMapbox(query) {
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?` +
            `country=PH&` +
            `limit=8&` +
            `access_token=${mapboxAccessToken}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    suggestionsList.innerHTML = data.features.map((feature, index) => `
                        <div class="suggestion-item" data-lat="${feature.center[1]}" data-lng="${feature.center[0]}" data-place="${feature.place_name}">
                            <div class="suggestion-title">${feature.text}</div>
                            <div class="suggestion-subtitle">${feature.place_name}</div>
                        </div>
                    `).join('');
                    suggestionsList.classList.add('show');

                    document.querySelectorAll('.suggestion-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const lat = parseFloat(this.dataset.lat);
                            const lng = parseFloat(this.dataset.lng);
                            const place = this.dataset.place;

                            searchInput.value = place;
                            fullAddressInput.value = place;
                            latitudeInput.value = lat.toFixed(6);
                            longitudeInput.value = lng.toFixed(6);
                            coordsDisplay.textContent = `📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

                            suggestionsList.classList.remove('show');
                        });
                    });
                }
            })
            .catch(err => console.error('Mapbox search error:', err));
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.classList.remove('show');
        }
    });

    loadLocationDropdowns();
});

function loadLocationDropdowns() {
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
    const logoChangeBtn = document.querySelector('.logo-change-btn');
    if (logoChangeBtn) {
        logoChangeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logoUpload.value = '';
            logoUploadArea.style.display = 'flex';
            logoPreview.style.display = 'none';
            logoUpload.click();
        });
    }
    
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
}
</script>
@endsection