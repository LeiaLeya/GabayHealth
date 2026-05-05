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
            <h2 class="fw-bold mb-1" style="color:#1e293b;">{{ $barangay['healthCenterName'] }}</h2>
            <p class="text-muted mb-0 small">{{ $barangay['barangayName'] ?? '' }} · Barangay Health Center Details</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(($barangay['status'] ?? '') === 'pending')
            <form method="POST" action="{{ route('rhu.barangays.approve', $barangay['id']) }}">
                @csrf
                <button type="submit" class="btn btn-dark rounded-pill px-4" style="font-size:.85rem;">
                    <i class="bi bi-check-lg me-1"></i>Approve
                </button>
            </form>
            @endif
            @if(($barangay['status'] ?? '') === 'archived')
            <form method="POST" action="{{ route('rhu.barangays.restore', $barangay['id']) }}">
                @csrf
                <button type="submit" class="btn-restore">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore Barangay
                </button>
            </form>
            @else
            <button class="btn-archive" onclick="openArchiveConfirm('{{ $barangay['id'] }}','{{ addslashes($barangay['healthCenterName']) }}','{{ route('rhu.barangays.archive', $barangay['id']) }}')">
                <i class="bi bi-archive me-1"></i>Archive
            </button>
            @endif
            <a href="{{ route('rhu.barangays.index') }}" class="btn-back">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main Details --}}
        <div class="col-lg-12">

            {{-- Identity Card --}}
            <div class="info-card mb-4">
                <div class="info-card-header">
                    <div class="info-badge">Health Center</div>
                    <div class="info-card-title">{{ $barangay['healthCenterName'] }}</div>
                </div>
                <div class="info-card-body">
                    <div class="d-flex align-items-center gap-4 mb-4">
                        <div class="brgy-logo-zone">
                            @if($barangay['logo_url'])
                                <img src="{{ $barangay['logo_url'] }}" alt="{{ $barangay['healthCenterName'] }}"
                                     class="brgy-logo-img" onerror="this.src='{{ asset('images/seal.png') }}'">
                            @else
                                <img src="{{ asset('images/seal.png') }}" alt="Seal" class="brgy-logo-img" style="opacity:.4;">
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1rem;color:#37352f;">{{ $barangay['healthCenterName'] ?? 'Health Center' }}</div>
                            @if(!empty($barangay['barangayName']))
                            <div class="text-muted small mt-1">{{ $barangay['barangayName'] }}</div>
                            @endif
                            @php
                                $s = $barangay['status'] ?? 'unknown';
                                $statusMap = [
                                    'active'        => ['label'=>'Active',        'cls'=>'active'],
                                    'pending_setup' => ['label'=>'Pending Setup', 'cls'=>'setup'],
                                    'approved'      => ['label'=>'Approved',      'cls'=>'approved'],
                                    'pending'       => ['label'=>'Pending',       'cls'=>'pending'],
                                    'archived'      => ['label'=>'Archived',      'cls'=>'archived'],
                                ];
                                $st = $statusMap[$s] ?? ['label'=>ucfirst($s),'cls'=>'pending'];
                            @endphp
                            <span class="brgy-status {{ $st['cls'] }} mt-2 d-inline-block">{{ $st['label'] }}</span>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $barangay['email'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <div class="info-label">Location</div>
                                <div class="info-value">
                                    @php
                                        if (is_array($barangay['location'] ?? null) && isset($barangay['location']['name'])) {
                                            echo $barangay['location']['name'];
                                        } elseif (is_string($barangay['location'] ?? null)) {
                                            echo $barangay['location'];
                                        } else {
                                            echo '—';
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-calendar-check-fill"></i></div>
                            <div>
                                <div class="info-label">Registered</div>
                                <div class="info-value">
                                    @php
                                        $createdAt = $barangay['createdAt'] ?? $barangay['created_at'] ?? $barangay['approved_at'] ?? null;
                                        echo ($createdAt && is_string($createdAt))
                                            ? \Carbon\Carbon::parse($createdAt)->format('M d, Y g:i A')
                                            : '—';
                                    @endphp
                                </div>
                            </div>
                        </div>
                        @if($barangay['username'] ?? false)
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-person-badge-fill"></i></div>
                            <div>
                                <div class="info-label">Username</div>
                                <div class="info-value"><code style="background:#f1f1ef;padding:2px 6px;border-radius:4px;color:#c026d3;font-size:.82rem;">{{ $barangay['username'] }}</code></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Address Card --}}
            @if(isset($barangay['fullAddress']) || isset($barangay['region']))
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-badge">Location</div>
                    <div class="info-card-title">Address Information</div>
                </div>
                <div class="info-card-body">
                    <div class="info-grid">
                        @php
                            $addr = $barangay['fullAddress'] ?? null;
                            if (!$addr) {
                                if (is_array($barangay['location'] ?? null) && isset($barangay['location']['name'])) {
                                    $addr = $barangay['location']['name'];
                                } elseif (is_string($barangay['location'] ?? null)) {
                                    $addr = $barangay['location'];
                                }
                            }
                        @endphp
                        @if($addr)
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <div class="info-label">Full Address</div>
                                <div class="info-value">{{ $addr }}</div>
                            </div>
                        </div>
                        @endif
                        @if(isset($barangay['region']) && $barangay['region'])
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-map-fill"></i></div>
                            <div>
                                <div class="info-label">Region</div>
                                <div class="info-value">{{ $barangay['displayRegion'] ?? $barangay['region'] }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-pin-map-fill"></i></div>
                            <div>
                                <div class="info-label">Province / City</div>
                                <div class="info-value">{{ $barangay['displayProvince'] ?? $barangay['province'] ?? '—' }}@if(!empty($barangay['displayCity'] ?? $barangay['city'] ?? null)), {{ $barangay['displayCity'] ?? $barangay['city'] }}@endif</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

<style>
.btn-back { background:transparent; border:1px solid #e9e9e7; color:#787774; font-size:.82rem; font-weight:500; padding:6px 16px; border-radius:6px; text-decoration:none; transition:all .1s; }
.btn-back:hover { background:#f1f1ef; color:#37352f; }
.btn-archive { background:transparent; border:1px solid #fde68a; color:#d97706; font-size:.82rem; font-weight:500; padding:6px 14px; border-radius:6px; cursor:pointer; transition:all .1s; }
.btn-archive:hover { background:#fef9c3; color:#b45309; border-color:#f59e0b; }
.btn-restore { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; font-size:.82rem; font-weight:500; padding:6px 14px; border-radius:6px; cursor:pointer; transition:all .1s; }
.btn-restore:hover { background:#bbf7d0; color:#14532d; }
.info-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; overflow:hidden; }
.info-card-header { background:#1657c1; padding:16px 20px 12px; }
.info-badge { display:inline-block; font-size:.62rem; font-weight:600; letter-spacing:.6px; text-transform:uppercase; background:rgba(255,255,255,.15); color:rgba(255,255,255,.85); padding:2px 7px; border-radius:3px; margin-bottom:4px; }
.info-card-title { font-size:.95rem; font-weight:600; color:#fff; }
.info-card-body { padding:20px; }
.brgy-logo-zone { width:64px; height:64px; border-radius:8px; background:#f1f1ef; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid #e9e9e7; }
.brgy-logo-img { width:56px; height:56px; object-fit:contain; }
.brgy-status { font-size:.68rem; font-weight:600; padding:2px 8px; border-radius:10px; }
.brgy-status.active   { background:#dcfce7; color:#166534; }
.brgy-status.setup    { background:#fef9c3; color:#92400e; }
.brgy-status.approved { background:#dbeafe; color:#1e40af; }
.brgy-status.pending  { background:#f1f1ef; color:#787774; }
.brgy-status.archived { background:#f1f1ef; color:#787774; }
.info-grid { display:flex; flex-direction:column; gap:0; }
.info-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f1f1ef; }
.info-row:last-child { border-bottom:none; }
.info-icon { width:30px; height:30px; border-radius:6px; background:#f1f1ef; display:flex; align-items:center; justify-content:center; color:#787774; font-size:.85rem; flex-shrink:0; margin-top:1px; }
.info-label { font-size:.65rem; font-weight:600; letter-spacing:.4px; text-transform:uppercase; color:#9b9b9b; margin-bottom:2px; }
.info-value { font-size:.85rem; font-weight:500; color:#37352f; }
.info-divider { border-top:1px solid #f1f1ef; margin:16px 0; }
</style>

@include('partials.archive-confirm-modal', ['modalId' => 'brgyArchiveModal'])

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>
@endpush
@endsection
