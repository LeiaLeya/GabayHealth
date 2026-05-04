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
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Registered Barangays</h2>
            <p class="text-muted mb-0 small">Barangay health centers under {{ session('user.name', 'this RHU') }}</p>
        </div>
    </div>

    @if(empty($barangays))
    <div class="brgy-empty">
        <i class="bi bi-building"></i>
        <div class="brgy-empty-title">No Registered Barangays</div>
        <div class="brgy-empty-text">No barangay health centers have been registered under this RHU yet.</div>
    </div>
    @else

    {{-- Stats Row --}}
    @php
        $total   = count($barangays);
        $active  = collect($barangays)->where('status','active')->count();
        $pending = collect($barangays)->whereIn('status',['pending','pending_setup','approved'])->count();
    @endphp
    <div class="brgy-stats mb-4">
        <div class="brgy-stat">
            <div class="brgy-stat-val">{{ $total }}</div>
            <div class="brgy-stat-lbl">Total Barangays</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#166534;">{{ $active }}</div>
            <div class="brgy-stat-lbl">Active</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#92400e;">{{ $pending }}</div>
            <div class="brgy-stat-lbl">Pending / Setup</div>
        </div>
    </div>

    <div class="brgy-card">
        <div class="brgy-card-header">
            <div class="brgy-badge">Barangays</div>
            <div class="brgy-card-title">All Registered Barangays</div>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">Logo</th>
                            <th>Health Center</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th style="width:80px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangays as $barangay)
                        <tr>
                            <td>
                                <div class="brgy-logo-wrap">
                                    @if($barangay['logo_url'])
                                        <img src="{{ $barangay['logo_url'] }}" alt="{{ $barangay['healthCenterName'] }}"
                                             class="brgy-logo-img"
                                             onerror="this.src='{{ asset('images/seal.png') }}'">
                                    @else
                                        <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:#37352f;">{{ $barangay['healthCenterName'] }}</div>
                                @if(!empty($barangay['barangayName']))
                                <div class="small text-muted">{{ $barangay['barangayName'] }}</div>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $barangay['email'] }}</td>
                            <td class="small text-muted">
                                @if(is_array($barangay['location'] ?? null) && isset($barangay['location']['name']))
                                    {{ $barangay['location']['name'] }}
                                @else
                                    {{ $barangay['location'] ?? '—' }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $s = $barangay['status'] ?? 'unknown';
                                    $statusMap = [
                                        'active'        => ['label'=>'Active',        'cls'=>'active'],
                                        'pending_setup' => ['label'=>'Pending Setup', 'cls'=>'setup'],
                                        'approved'      => ['label'=>'Approved',      'cls'=>'approved'],
                                        'pending'       => ['label'=>'Pending',       'cls'=>'pending'],
                                    ];
                                    $st = $statusMap[$s] ?? ['label'=>ucfirst($s),'cls'=>'pending'];
                                @endphp
                                <span class="brgy-status {{ $st['cls'] }}">{{ $st['label'] }}</span>
                            </td>
                            <td class="small text-muted">{{ $barangay['appliedDate'] }}</td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('rhu.barangays.show', $barangay['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.brgy-empty { text-align:center; padding:60px 20px; background:#fff; border:1px solid #e9e9e7; border-radius:8px; }
.brgy-empty i { font-size:2.5rem; color:#c4c4c2; display:block; margin-bottom:12px; }
.brgy-empty-title { font-size:.95rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.brgy-empty-text { font-size:.82rem; color:#9b9b9b; }
.brgy-stats { display:flex; gap:16px; flex-wrap:wrap; }
.brgy-stat { background:#fff; border:1px solid #e9e9e7; border-radius:8px; padding:14px 20px; min-width:120px; }
.brgy-stat-val { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.brgy-stat-lbl { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px; color:#9b9b9b; margin-top:4px; }
.brgy-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; }
.brgy-card-header { background:#1657c1; padding:16px 20px 12px; }
.brgy-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.brgy-card-title { font-size:.95rem; font-weight:600; color:#fff; }
.brgy-table { width:100%; border-collapse:collapse; }
.brgy-table thead tr { background:#1657c1; border-bottom:1px solid #1657c1; color:#fff; }
.brgy-table th { padding:10px 14px; font-size:.68rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#fff; white-space:nowrap; }
.brgy-table td { padding:12px 14px; font-size:.82rem; border-bottom:1px solid #f1f1ef; vertical-align:middle; }
.brgy-table tbody tr:last-child td { border-bottom:none; }
.brgy-table tbody tr:hover { background:#fafaf9; }
.brgy-logo-wrap { width:40px; height:40px; border-radius:6px; background:#f1f1ef; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.brgy-logo-img { width:36px; height:36px; object-fit:contain; }
.brgy-status { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.brgy-status.active   { background:#dcfce7; color:#166534; }
.brgy-status.setup    { background:#fef9c3; color:#92400e; }
.brgy-status.approved { background:#dbeafe; color:#1e40af; }
.brgy-status.pending  { background:#f1f1ef; color:#787774; }
.tbl-btn { width:28px; height:28px; border-radius:5px; display:flex; align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; text-decoration:none; transition:background .1s; }
.tbl-btn.view { background:#f1f1ef; color:#787774; }
.tbl-btn.view:hover { background:#dbeafe; color:#1e40af; }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>
@endpush
@endsection
