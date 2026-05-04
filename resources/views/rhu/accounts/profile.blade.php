@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Flash Toasts --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow" role="alert" id="successToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Edit Health Center Profile</h2>
            <p class="text-muted mb-0 small">Update your health center information and settings</p>
        </div>
        <a href="{{ route('rhu.accounts.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    @if($errors->any())
    <div class="alert-custom danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    </div>
    @endif

    {{-- Main Profile Form --}}
    <form action="{{ route('rhu.accounts.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('PUT')

        {{-- Identity Section --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <div class="modal-type-badge">Identity</div>
                <div class="form-card-title">Health Center Logo & Name</div>
            </div>
            <div class="form-card-body">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="logo-zone" id="logoZone">
                        @if(!empty($healthCenter['logo_url']))
                            <img src="{{ $healthCenter['logo_url'] }}" alt="Logo" class="logo-preview-img" id="logoPreviewImg">
                        @else
                            <div class="logo-placeholder" id="logoPlaceholder">
                                <i class="bi bi-building"></i>
                                <span>No Logo</span>
                            </div>
                            <img src="" alt="Logo" class="logo-preview-img d-none" id="logoPreviewImg">
                        @endif
                        <label for="logo" class="logo-upload-btn" title="Change logo">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" id="logo" name="logo" accept="image/*" class="d-none" onchange="previewLogo(event)">
                    </div>
                    <div class="flex-fill" style="min-width:200px;">
                        <label class="form-label small fw-semibold">Health Center Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('healthCenterName') is-invalid @enderror"
                               name="healthCenterName" value="{{ old('healthCenterName', $healthCenter['healthCenterName'] ?? '') }}" required placeholder="e.g. Brgy. San Jose Health Center">
                        @error('healthCenterName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="text-muted mt-1" style="font-size:.74rem;">Accepted: JPEG, PNG, GIF, SVG · Max 5 MB</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <div class="modal-type-badge">Contact</div>
                <div class="form-card-title">Contact Information</div>
            </div>
            <div class="form-card-body">
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control rounded-3 @error('contact_number') is-invalid @enderror"
                                   name="contact_number" value="{{ old('contact_number', $healthCenter['contact_number'] ?? $healthCenter['contactInfo'] ?? '') }}" required placeholder="e.g. 09XX-XXX-XXXX">
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email', $healthCenter['email'] ?? '') }}" required placeholder="health@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Complete Address <span class="text-danger">*</span></label>
                            <div class="mb-address-wrap">
                                <input type="text" class="form-control rounded-3 @error('address') is-invalid @enderror"
                                       id="addressInput" name="address" value="{{ old('address', $healthCenter['address'] ?? $healthCenter['fullAddress'] ?? '') }}"
                                       required placeholder="Search address or type manually…" autocomplete="off">
                                <div id="addressSuggestions" class="mb-suggest"></div>
                            </div>
                            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Operating Hours --}}
        <div class="form-card mb-4">
            <div class="form-card-header">
                <div class="modal-type-badge">Schedule</div>
                <div class="form-card-title">Operating Hours</div>
            </div>
            <div class="form-card-body">
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Open Days <span class="text-danger">*</span></label>
                            @php
                                $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                                $selectedDays = old('open_days', $healthCenter['open_days'] ?? []);
                                if (is_string($selectedDays)) $selectedDays = explode(',', $selectedDays);
                                $selectedDays = array_map('trim', $selectedDays);
                            @endphp
                            <div class="day-selector mt-1">
                                @foreach($days as $day)
                                    <input type="checkbox" class="btn-check" name="open_days[]" id="day_{{ $day }}" value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                    <label class="day-pill-btn" for="day_{{ $day }}">{{ $day }}</label>
                                @endforeach
                            </div>
                            @error('open_days')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Opening Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 @error('open_time') is-invalid @enderror"
                                   name="open_time" value="{{ old('open_time', $healthCenter['open_time'] ?? '') }}" required>
                            @error('open_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Closing Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 @error('close_time') is-invalid @enderror"
                                   name="close_time" value="{{ old('close_time', $healthCenter['close_time'] ?? '') }}" required>
                            @error('close_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('rhu.accounts.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-dark rounded-pill px-4">
                <i class="bi bi-check2 me-1"></i>Save Changes
            </button>
        </div>
    </form>

    {{-- Change Password --}}
    <div class="form-card mb-4">
        <div class="form-card-header">
            <div class="modal-type-badge">Security</div>
            <div class="form-card-title">Change Password</div>
        </div>
        <div class="form-card-body">
            <form action="{{ route('rhu.accounts.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Current Password</label>
                            <input type="password" class="form-control rounded-3" name="current_password" required placeholder="Enter current password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">New Password</label>
                            <input type="password" class="form-control rounded-3" id="new_password" name="new_password" required placeholder="Min. 6 characters">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control rounded-3" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-dark rounded-pill px-4">
                        <i class="bi bi-key me-1"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.btn-back { background:transparent; border:1px solid #e9e9e7; color:#787774; font-size:.82rem; font-weight:500; padding:6px 16px; border-radius:6px; text-decoration:none; transition:all .1s; }
.btn-back:hover { background:#f1f1ef; color:#37352f; }
.form-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; }
.form-card-header { background:#1657c1; padding:18px 24px; }
.modal-type-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:6px; }
.form-card-title { font-size:1rem; font-weight:600; color:#fff; }
.form-card-body { padding:24px; }
.form-section { background:#fafaf9; border-radius:6px; padding:16px; border:1px solid #f1f1ef; }
.alert-custom { display:flex; align-items:flex-start; gap:10px; padding:12px 16px; border-radius:6px; font-size:.85rem; }
.alert-custom.danger { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
/* Logo zone */
.logo-zone { position:relative; width:100px; height:100px; border-radius:10px; overflow:visible; flex-shrink:0; }
.logo-preview-img { width:100px; height:100px; border-radius:10px; object-fit:contain; border:1px solid #e9e9e7; background:#fafaf9; }
.logo-placeholder { width:100px; height:100px; border-radius:10px; background:#f1f1ef; border:1px dashed #c4c4c2; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#9b9b9b; font-size:.65rem; font-weight:600; gap:4px; }
.logo-placeholder i { font-size:1.6rem; color:#c4c4c2; }
.logo-upload-btn { position:absolute; bottom:-8px; right:-8px; width:28px; height:28px; border-radius:50%; background:#37352f; color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.75rem; border:2px solid #fff; transition:background .1s; }
.logo-upload-btn:hover { background:#1e1d1b; }
/* Day selector */
.day-selector { display:flex; flex-wrap:wrap; gap:6px; }
.day-pill-btn { padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:600; cursor:pointer; border:1px solid #e9e9e7; background:#fff; color:#787774; transition:all .1s; user-select:none; }
.btn-check:checked + .day-pill-btn { background:#37352f; border-color:#37352f; color:#fff; }
/* Mapbox suggestions */
.mb-address-wrap { position:relative; }
.mb-suggest { position:absolute; top:calc(100% + 2px); left:0; right:0; background:#fff; border:1px solid #e9e9e7; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.12); z-index:200; max-height:220px; overflow-y:auto; display:none; }
.mb-suggest-item { padding:9px 12px; font-size:.82rem; cursor:pointer; color:#37352f; border-bottom:1px solid #f1f1ef; line-height:1.3; }
.mb-suggest-item:last-child { border-bottom:none; }
.mb-suggest-item:hover { background:#fafaf9; }
</style>

@push('scripts')
<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (!file) return;
    const img = document.getElementById('logoPreviewImg');
    const ph  = document.getElementById('logoPlaceholder');
    const reader = new FileReader();
    reader.onload = e => {
        img.src = e.target.result;
        img.classList.remove('d-none');
        if (ph) ph.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

document.getElementById('confirm_password').addEventListener('input', function() {
    const pw = document.getElementById('new_password').value;
    this.setCustomValidity(pw && this.value !== pw ? 'Passwords do not match' : '');
});

// Mapbox address autocomplete
(function() {
    const token = @json(env('MAPBOX_ACCESS_TOKEN'));
    const input = document.getElementById('addressInput');
    const list  = document.getElementById('addressSuggestions');
    if (!token || !input) return;
    let t;
    input.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) { list.innerHTML=''; list.style.display='none'; return; }
        clearTimeout(t);
        t = setTimeout(() => {
            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json?access_token=${token}&country=PH&limit=6`)
                .then(r => r.json())
                .then(d => {
                    list.innerHTML = '';
                    if (!d.features || !d.features.length) { list.style.display='none'; return; }
                    d.features.forEach(f => {
                        const div = document.createElement('div');
                        div.className = 'mb-suggest-item';
                        div.textContent = f.place_name;
                        div.addEventListener('mousedown', e => {
                            e.preventDefault();
                            input.value = f.place_name;
                            list.innerHTML = ''; list.style.display='none';
                        });
                        list.appendChild(div);
                    });
                    list.style.display = 'block';
                })
                .catch(() => { list.innerHTML=''; list.style.display='none'; });
        }, 300);
    });
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.innerHTML=''; list.style.display='none';
        }
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>
@endpush
@endsection
