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
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow" role="alert" id="errorToast">
            <div class="d-flex">
                <div class="toast-body fw-semibold"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Personnel</h2>
            <p class="text-muted mb-0 small">Manage health center personnel</p>
        </div>
        <button class="btn-add-personnel" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
            <i class="bi bi-plus-lg me-1"></i>Add Personnel
        </button>
    </div>

    {{-- Personnel Grid --}}
    @if(count($personnel) > 0)
    <div class="personnel-grid">
        @foreach($personnel as $person)
        <div class="p-card">
            <div class="p-card-header">
                <div class="p-avatar">
                    @if(isset($person['image_url']))
                        <img src="{{ $person['image_url'] }}" alt="{{ $person['name'] ?? '' }}" class="p-avatar-img">
                    @else
                        <div class="p-avatar-placeholder"><i class="bi bi-person-fill"></i></div>
                    @endif
                </div>
                @php
                    $status = $person['status'] ?? 'Active';
                    $statusOk = strtolower($status) === 'active';
                @endphp
                <span class="p-status-pill {{ $statusOk ? 'active' : 'inactive' }}">{{ ucfirst($status) }}</span>
            </div>
            <div class="p-card-body">
                <div class="p-name">{{ $person['name'] ?? 'Unknown' }}</div>
                <div class="p-position">{{ $person['position'] ?? 'No Position' }}</div>
                @if(!empty($person['address']))
                <div class="p-address"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($person['address'], 80) }}</div>
                @endif
            </div>
            <div class="p-card-footer">
                <button class="p-btn edit" data-bs-toggle="modal" data-bs-target="#editPersonnelModal{{ $person['id'] }}">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
                <button class="p-btn delete" onclick="deletePersonnel('{{ $person['id'] }}', {{ json_encode($person['name'] ?? 'Unknown') }})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="modal fade" id="editPersonnelModal{{ $person['id'] }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
                    <div class="modal-header-custom">
                        <div>
                            <div class="modal-type-badge">Edit Personnel</div>
                            <h5 class="modal-title-custom">{{ $person['name'] ?? 'Personnel' }}</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('rhu.personnel.update', $person['id']) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="modal-form-section mb-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control rounded-3" value="{{ $person['name'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Position <span class="text-danger">*</span></label>
                                        <input type="text" name="position" class="form-control rounded-3" value="{{ $person['position'] ?? '' }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Address <span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control rounded-3" value="{{ $person['address'] ?? '' }}" required placeholder="House/Unit No., Street, Barangay, City">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Profile Photo</label>
                                        <input type="file" name="image" class="form-control rounded-3 personnel-image-input" accept="image/*">
                                        <div class="image-preview-container mt-2" style="display:none;"></div>
                                        <div class="text-muted" style="font-size:.75rem;margin-top:4px;">Leave empty to keep current photo.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pb-4 px-4 gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Update Personnel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-empty">
        <i class="bi bi-people"></i>
        <div class="p-empty-title">No Personnel Added</div>
        <div class="p-empty-text">Add your first personnel member to get started.</div>
        <button class="btn btn-dark rounded-pill px-4 mt-3 btn-sm" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">Add Personnel</button>
    </div>
    @endif
</div>

{{-- Add Personnel Modal --}}
<div class="modal fade" id="addPersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom">
                <div>
                    <div class="modal-type-badge">New Personnel</div>
                    <h5 class="modal-title-custom">Add Personnel</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('rhu.personnel.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="modal-form-section mb-3">
                        <div class="modal-section-title"><i class="bi bi-person-check me-2"></i>Autofill from Account Management</div>
                        <select class="form-select rounded-3" id="personnelSelect" onchange="fillPersonnelData()">
                            <option value="">— Choose to auto-fill —</option>
                            @foreach($availablePersonnel as $ap)
                                <option value="{{ $ap['id'] }}"
                                        data-name="{{ $ap['name'] ?? $ap['full_name'] ?? '' }}"
                                        data-position="{{ $ap['role'] ?? '' }}"
                                        data-address="{{ $ap['address'] ?? '' }}">
                                    {{ $ap['name'] ?? $ap['full_name'] ?? 'Unknown' }} ({{ ucfirst($ap['role'] ?? 'Unknown') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-form-section">
                        <div class="modal-section-title"><i class="bi bi-person me-2"></i>Personnel Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="nameInput" class="form-control rounded-3" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Position <span class="text-danger">*</span></label>
                                <input type="text" name="position" id="positionInput" class="form-control rounded-3" required placeholder="Enter position">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Address <span class="text-danger">*</span></label>
                                <div class="mb-address-wrap">
                                    <input type="text" name="address" id="addressInput" class="form-control rounded-3" required placeholder="Search address or type manually…" autocomplete="off">
                                    <div id="addressSuggestions" class="mb-suggest"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Profile Photo</label>
                                <input type="file" name="image" class="form-control rounded-3 personnel-image-input" accept="image/*">
                                <div class="image-preview-container mt-2" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Save Personnel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deletePersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom" style="background:#b91c1c;">
                <div>
                    <div class="modal-type-badge" style="background:rgba(255,255,255,.15);">Confirm Delete</div>
                    <h5 class="modal-title-custom">Delete Personnel</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-1">Are you sure you want to delete <strong id="personnelName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="deletePersonnelForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-trash me-1"></i>Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.personnel_image_crop')

<style>
.btn-add-personnel { background:#1657c1; border:none; color:#fff; font-size:.82rem; font-weight:600; padding:8px 18px; border-radius:6px; cursor:pointer; text-decoration:none; transition:opacity .1s; }
.btn-add-personnel:hover { opacity:.85; }
.personnel-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; }
.p-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .15s; }
.p-card:hover { box-shadow:0 2px 12px rgba(0,0,0,.08); }
.p-card-header { background:#1657c1; padding:20px 16px 16px; display:flex; flex-direction:column; align-items:center; gap:10px; position:relative; }
.p-avatar { width:72px; height:72px; border-radius:50%; overflow:hidden; border:2px solid rgba(255,255,255,.25); flex-shrink:0; }
.p-avatar-img { width:100%; height:100%; object-fit:cover; }
.p-avatar-placeholder { width:100%; height:100%; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.7); font-size:2rem; }
.p-status-pill { font-size:.62rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.p-status-pill.active { background:#dcfce7; color:#166534; }
.p-status-pill.inactive { background:#fee2e2; color:#991b1b; }
.p-card-body { padding:14px 16px; flex:1; }
.p-name { font-size:.9rem; font-weight:700; color:#37352f; margin-bottom:2px; }
.p-position { font-size:.75rem; font-weight:600; color:#787774; text-transform:uppercase; letter-spacing:.3px; margin-bottom:8px; }
.p-address { font-size:.75rem; color:#9b9b9b; display:flex; align-items:flex-start; gap:2px; }
.p-card-footer { padding:10px 16px 14px; display:flex; gap:8px; border-top:1px solid #f1f1ef; }
.p-btn { flex:1; height:30px; border-radius:6px; border:none; font-size:.75rem; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .1s; }
.p-btn.edit { background:#f1f1ef; color:#37352f; }
.p-btn.edit:hover { background:#dbeafe; color:#1e40af; }
.p-btn.delete { flex:0 0 30px; background:#f1f1ef; color:#787774; }
.p-btn.delete:hover { background:#fee2e2; color:#b91c1c; }
.p-empty { text-align:center; padding:60px 20px; background:#fff; border:1px solid #e9e9e7; border-radius:8px; }
.p-empty i { font-size:2.5rem; color:#c4c4c2; display:block; margin-bottom:12px; }
.p-empty-title { font-size:.95rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.p-empty-text { font-size:.82rem; color:#9b9b9b; }
.modal-header-custom { display:flex; align-items:flex-start; justify-content:space-between; background:#1657c1; padding:18px 22px; }
.modal-type-badge { display:inline-block; font-size:.64rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.12); color:rgba(255,255,255,.75); padding:2px 8px; border-radius:4px; margin-bottom:5px; }
.modal-title-custom { font-size:1rem; font-weight:600; color:#fff; margin:0; }
.modal-form-section { background:#fafaf9; border-radius:6px; padding:16px; border:1px solid #f1f1ef; margin-bottom:12px; }
.modal-section-title { font-size:.7rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#787774; margin-bottom:12px; }
.mb-address-wrap { position:relative; }
.mb-suggest { position:absolute; top:calc(100% + 2px); left:0; right:0; background:#fff; border:1px solid #e9e9e7; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.12); z-index:300; max-height:200px; overflow-y:auto; display:none; }
.mb-suggest-item { padding:9px 12px; font-size:.82rem; cursor:pointer; color:#37352f; border-bottom:1px solid #f1f1ef; line-height:1.3; }
.mb-suggest-item:last-child { border-bottom:none; }
.mb-suggest-item:hover { background:#fafaf9; }
</style>

@push('scripts')
<script>
function deletePersonnel(personnelId, personnelName) {
    document.getElementById('personnelName').textContent = personnelName;
    document.getElementById('deletePersonnelForm').action = '{{ url("rhu/personnel") }}/' + personnelId;
    new bootstrap.Modal(document.getElementById('deletePersonnelModal')).show();
}

function fillPersonnelData() {
    const select = document.getElementById('personnelSelect');
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
        document.getElementById('nameInput').value = opt.dataset.name || '';
        const pos = opt.dataset.position || '';
        document.getElementById('positionInput').value =
            pos.toLowerCase() === 'bhw' ? 'Barangay Health Worker'
            : pos.charAt(0).toUpperCase() + pos.slice(1).toLowerCase();
        document.getElementById('addressInput').value = opt.dataset.address || '';
    } else {
        document.getElementById('nameInput').value = '';
        document.getElementById('positionInput').value = '';
        document.getElementById('addressInput').value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});

(function() {
    const token = @json(env('MAPBOX_ACCESS_TOKEN'));
    const input = document.getElementById('addressInput');
    const list  = document.getElementById('addressSuggestions');
    if (!token || !input) return;
    let t;
    input.addEventListener('input', function() {
        // If this was auto-filled, don't show suggestions until user edits
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
