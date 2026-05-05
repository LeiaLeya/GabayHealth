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

    @php
        $active   = collect($barangays)->where('status', '!=', 'archived')->values();
        $archived = collect($barangays)->where('status', 'archived')->values();
        $total    = $active->count();
        $activeCount  = $active->where('status', 'active')->count();
        $pendingCount = $active->whereIn('status', ['pending', 'pending_setup', 'approved'])->count();
    @endphp

    @if($active->isEmpty() && $archived->isEmpty())
    <div class="brgy-empty">
        <i class="bi bi-building"></i>
        <div class="brgy-empty-title">No Registered Barangays</div>
        <div class="brgy-empty-text">No barangay health centers have been registered under this RHU yet.</div>
    </div>
    @else

    {{-- Stats Row --}}
    <div class="brgy-stats mb-4">
        <div class="brgy-stat">
            <div class="brgy-stat-val">{{ $total }}</div>
            <div class="brgy-stat-lbl">Total Active</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#166534;">{{ $activeCount }}</div>
            <div class="brgy-stat-lbl">Active</div>
        </div>
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#92400e;">{{ $pendingCount }}</div>
            <div class="brgy-stat-lbl">Pending / Setup</div>
        </div>
        @if($archived->count() > 0)
        <div class="brgy-stat">
            <div class="brgy-stat-val" style="color:#787774;">{{ $archived->count() }}</div>
            <div class="brgy-stat-lbl">Archived</div>
        </div>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="brgy-tabs mb-0">
        <button class="brgy-tab active" id="tabActive" onclick="switchBrgyTab('active')">
            <i class="bi bi-building me-1"></i>Active
            <span class="brgy-tab-count">{{ $total }}</span>
        </button>
        @if($archived->count() > 0)
        <button class="brgy-tab" id="tabArchived" onclick="switchBrgyTab('archived')">
            <i class="bi bi-archive me-1"></i>Archived
            <span class="brgy-tab-count">{{ $archived->count() }}</span>
        </button>
        @endif
    </div>

    {{-- Active Tab --}}
    <div id="paneActive" class="brgy-card" style="border-top-left-radius:0;">
        <div class="brgy-card-header">
            <div class="brgy-badge">Barangays</div>
            <div class="brgy-card-title">Active Barangays</div>
        </div>
        @if($active->isEmpty())
        <div class="brgy-empty" style="border:none;border-radius:0;">
            <i class="bi bi-building"></i>
            <div class="brgy-empty-title">No Active Barangays</div>
            <div class="brgy-empty-text">All barangays may have been archived.</div>
        </div>
        @else
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
                        @foreach($active as $barangay)
                        <tr>
                            <td>
                                <div class="brgy-logo-wrap">
                                    @if($barangay['logo_url'])
                                        <img src="{{ $barangay['logo_url'] }}" alt="{{ $barangay['healthCenterName'] }}"
                                             class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
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
                                <div class="d-flex justify-content-center gap-1">
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
        @endif
    </div>

    {{-- Archived Tab --}}
    @if($archived->count() > 0)
    <div id="paneArchived" class="brgy-card d-none" style="border-top-left-radius:0;border-top-right-radius:0;">
        <div class="brgy-card-header" style="background:#787774;">
            <div class="brgy-badge">Archived</div>
            <div class="brgy-card-title">Archived Barangays</div>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="brgy-table">
                    <thead style="background:#787774;">
                        <tr>
                            <th style="width:60px;">Logo</th>
                            <th>Health Center</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Archived On</th>
                            <th style="width:100px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archived as $barangay)
                        <tr>
                            <td>
                                <div class="brgy-logo-wrap" style="opacity:.6;">
                                    @if($barangay['logo_url'])
                                        <img src="{{ $barangay['logo_url'] }}" alt="{{ $barangay['healthCenterName'] }}"
                                             class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
                                    @else
                                        <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:#787774;">{{ $barangay['healthCenterName'] }}</div>
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
                            <td class="small text-muted">{{ $barangay['appliedDate'] }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('rhu.barangays.show', $barangay['id']) }}" class="tbl-btn view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('rhu.barangays.restore', $barangay['id']) }}">
                                        @csrf
                                        <button type="submit" class="tbl-btn restore" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
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

.brgy-tabs { display:flex; gap:0; }
.brgy-tab { background:#f8fafc; border:1px solid #e9e9e7; border-bottom:none; border-radius:8px 8px 0 0; padding:9px 20px; font-size:.82rem; font-weight:500; color:#787774; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:6px; }
.brgy-tab:first-child { margin-right:4px; }
.brgy-tab.active { background:#fff; color:#37352f; font-weight:600; border-color:#e9e9e7; position:relative; z-index:1; }
.brgy-tab-count { background:#f1f1ef; color:#787774; font-size:.65rem; font-weight:700; border-radius:10px; padding:1px 6px; }

.brgy-card { background:#fff; border:1px solid #e9e9e7; border-radius:0 8px 8px 8px; overflow:hidden; }
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
.tbl-btn { width:28px; height:28px; border-radius:5px; display:flex; align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; text-decoration:none; transition:background .1s; border:none; }
.tbl-btn.view    { background:#f1f1ef; color:#787774; }
.tbl-btn.view:hover { background:#dbeafe; color:#1e40af; }
.tbl-btn.restore { background:#f1f1ef; color:#787774; }
.tbl-btn.restore:hover { background:#dcfce7; color:#166534; }
</style>

@push('scripts')
<script>
function switchBrgyTab(tab) {
    document.getElementById('paneActive').classList.toggle('d-none', tab !== 'active');
    const archived = document.getElementById('paneArchived');
    if (archived) archived.classList.toggle('d-none', tab !== 'archived');
    document.getElementById('tabActive').classList.toggle('active', tab === 'active');
    const tabArc = document.getElementById('tabArchived');
    if (tabArc) tabArc.classList.toggle('active', tab === 'archived');
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>
@endpush
@endsection
