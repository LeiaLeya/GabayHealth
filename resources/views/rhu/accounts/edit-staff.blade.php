@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Edit Staff Member</h2>
            <p class="text-muted mb-0 small">Update staff account details</p>
        </div>
        <a href="{{ route('rhu.accounts.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="modal-type-badge">Edit Account</div>
            <div class="form-card-title">{{ $staff['name'] }}</div>
        </div>
        <div class="form-card-body">

            @if($errors->any())
            <div class="alert-custom danger mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('rhu.accounts.staff.update', $staff['id']) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-section mb-4">
                    <div class="form-section-title"><i class="bi bi-person me-2"></i>Personal Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $staff['name']) }}" required placeholder="Enter full name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Role <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">— Select role —</option>
                                <option value="doctor"  {{ old('role', $staff['role']) === 'doctor'  ? 'selected' : '' }}>Doctor</option>
                                <option value="midwife" {{ old('role', $staff['role']) === 'midwife' ? 'selected' : '' }}>Midwife</option>
                                <option value="nurse"   {{ old('role', $staff['role']) === 'nurse'   ? 'selected' : '' }}>Nurse</option>
                                <option value="bhw"     {{ old('role', $staff['role']) === 'bhw'     ? 'selected' : '' }}>Barangay Health Worker</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control rounded-3 @error('contact_number') is-invalid @enderror"
                                   id="contact_number" name="contact_number" value="{{ old('contact_number', $staff['contact_number']) }}" required placeholder="e.g. 09XX-XXX-XXXX">
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6" id="specialization_field" style="display:none;">
                            <label class="form-label small fw-semibold">Specialization</label>
                            <input type="text" class="form-control rounded-3 @error('specialization') is-invalid @enderror"
                                   id="specialization" name="specialization" value="{{ old('specialization', $staff['specialization'] ?? '') }}"
                                   placeholder="e.g. Pediatrics, Obstetrics">
                            @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Address</label>
                            <div class="mb-address-wrap">
                                <input type="text" class="form-control rounded-3 @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address', $staff['address'] ?? '') }}" placeholder="Search address or type manually…" autocomplete="off">
                                <div id="addressSuggestions" class="mb-suggest"></div>
                            </div>
                            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-section mb-4">
                    <div class="form-section-title"><i class="bi bi-shield-lock me-2"></i>Account Credentials</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $staff['email']) }}" required placeholder="staff@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 @error('status') is-invalid @enderror" name="status" required>
                                <option value="active"   {{ old('status', $staff['status']) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $staff['status']) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Password</label>
                            <input type="password" class="form-control rounded-3 @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Leave blank to keep current password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control rounded-3 @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation" name="password_confirmation" placeholder="Re-enter new password">
                            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('rhu.accounts.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">
                        <i class="bi bi-check2 me-1"></i>Update Staff Account
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
.form-section-title { font-size:.7rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#787774; margin-bottom:14px; }
.alert-custom { display:flex; align-items:flex-start; gap:10px; padding:12px 16px; border-radius:6px; font-size:.85rem; }
.alert-custom.danger { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.mb-address-wrap { position:relative; }
.mb-suggest { position:absolute; top:calc(100% + 2px); left:0; right:0; background:#fff; border:1px solid #e9e9e7; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.12); z-index:200; max-height:220px; overflow-y:auto; display:none; }
.mb-suggest-item { padding:9px 12px; font-size:.82rem; cursor:pointer; color:#37352f; border-bottom:1px solid #f1f1ef; line-height:1.3; }
.mb-suggest-item:last-child { border-bottom:none; }
.mb-suggest-item:hover { background:#fafaf9; }
</style>

@push('scripts')
<script>
document.getElementById('password_confirmation').addEventListener('input', function() {
    const pw = document.getElementById('password').value;
    this.setCustomValidity(pw && this.value !== pw ? 'Passwords do not match' : '');
});

document.getElementById('role').addEventListener('change', function() {
    const sf = document.getElementById('specialization_field');
    sf.style.display = this.value === 'doctor' ? 'block' : 'none';
    if (this.value !== 'doctor') document.getElementById('specialization').value = '';
});

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('role').value === 'doctor') {
        document.getElementById('specialization_field').style.display = 'block';
    }
});

(function() {
    const token = @json(env('MAPBOX_ACCESS_TOKEN'));
    const input = document.getElementById('address');
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
                        div.addEventListener('mousedown', e => { e.preventDefault(); input.value = f.place_name; list.innerHTML=''; list.style.display='none'; });
                        list.appendChild(div);
                    });
                    list.style.display = 'block';
                }).catch(() => { list.innerHTML=''; list.style.display='none'; });
        }, 300);
    });
    document.addEventListener('click', e => { if (!input.contains(e.target) && !list.contains(e.target)) { list.innerHTML=''; list.style.display='none'; } });
})();
</script>
@endpush
@endsection
