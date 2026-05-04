@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pt-2 flex-wrap gap-2">
        <div>
            <p class="text-muted mb-0" style="font-size:.78rem;letter-spacing:.04em;text-transform:uppercase;font-weight:600;">Inventory / Distribution History</p>
            <h1 class="fw-bold mb-0" style="font-size:1.45rem;color:#37352f;">{{ $parentData['name'] }}</h1>
            <p class="mb-0" style="font-size:.8rem;color:#9b9b9b;">Lot No: {{ $batchData['lot_number'] ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('inventory.show', $parentData['id']) }}" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left" style="font-size:.8rem;"></i>
            <span style="font-size:.82rem;">Back to {{ $parentData['name'] }}</span>
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1100;">
        <div id="flashToast" class="toast show align-items-center border-0 text-bg-success" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle"></i>{{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1100;">
        <div id="flashToast" class="toast show align-items-center border-0 text-bg-danger" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i>{{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Batch Summary Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-stack"></i></div>
                <div class="stat-value">{{ $batchData['quantity'] }}</div>
                <div class="stat-label">Current Stock</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-arrow-left-right"></i></div>
                <div class="stat-value">{{ count($distributions) }}</div>
                <div class="stat-label">Distributions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-people"></i></div>
                <div class="stat-value">{{ collect($distributions)->sum('quantity_distributed') }}</div>
                <div class="stat-label">Total Distributed</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value" style="font-size:1rem;">{{ \Carbon\Carbon::parse($batchData['expiration_date'])->format('M d, Y') }}</div>
                <div class="stat-label">Expiration Date</div>
            </div>
        </div>
    </div>

    {{-- Distribution History Table --}}
    <div class="inv-card p-0">
        <div class="inv-section-header px-4 py-3">
            <span class="inv-section-label">Distribution History</span>
            @if(count($distributions) > 0)
                <span class="inv-section-meta ms-2">{{ count($distributions) }} {{ count($distributions) === 1 ? 'record' : 'records' }}</span>
            @endif
        </div>
        @if(count($distributions) > 0)
            <div class="table-responsive">
                <table class="table inv-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Resident Name</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Distributed By</th>
                            <th>Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distributions as $distribution)
                            <tr>
                                <td>
                                    <div class="fw-semibold" style="font-size:.88rem;color:#37352f;">
                                        {{ \Carbon\Carbon::parse($distribution['distribution_date'])->format('M d, Y') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold" style="font-size:.88rem;color:#37352f;">{{ $distribution['resident_name'] }}</span>
                                </td>
                                <td>
                                    <span class="inv-badge ok">{{ $distribution['quantity_distributed'] }} {{ ucfirst($parentData['unit_type']) }}</span>
                                </td>
                                <td>
                                    <span style="font-size:.85rem;color:#787774;">{{ Str::limit($distribution['reason'] ?? 'No reason provided', 50) }}</span>
                                </td>
                                <td>
                                    <span style="font-size:.82rem;color:#787774;">{{ $distribution['distributed_by'] ?? 'Health Worker' }}</span>
                                </td>
                                <td>
                                    <span style="font-size:.78rem;color:#9b9b9b;">{{ \Carbon\Carbon::parse($distribution['distributed_at'])->format('M d, Y g:i A') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="inv-empty m-4">
                <i class="bi bi-clock-history inv-empty-icon"></i>
                <div class="inv-empty-title">No distribution history</div>
                <div class="inv-empty-text">This batch hasn't been distributed yet.</div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('flashToast');
    if (toast) { setTimeout(() => { bootstrap.Toast.getOrCreateInstance(toast).hide(); }, 4000); }
});
</script>

<style>
.stat-card { border: 1px solid #e9e9e7; border-radius: 8px; background: #fff; transition: box-shadow .15s; }
.stat-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.stat-icon { width: 36px; height: 36px; border-radius: 6px; background: #f1f1ef; color: #787774; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #37352f; line-height: 1.2; }
.stat-label { font-size: .72rem; color: #9b9b9b; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }

.inv-card { border: 1px solid #e9e9e7; border-radius: 8px; background: #fff; }
.inv-section-header { border-bottom: 1px solid #f1f1ef; }
.inv-section-label { font-size: .82rem; font-weight: 600; color: #37352f; }
.inv-section-meta { font-size: .75rem; color: #9b9b9b; }

.inv-badge { display: inline-flex; align-items: center; font-size: .72rem; font-weight: 600; border-radius: 4px; padding: 2px 8px; }
.inv-badge.ok     { background: #dcfce7; color: #15803d; }
.inv-badge.warn   { background: #fef9c3; color: #a16207; }
.inv-badge.danger { background: #fee2e2; color: #b91c1c; }
.inv-badge.neutral { background: #f1f1ef; color: #787774; }

.inv-table { font-size: .88rem; }
.inv-table thead th { background: #f8fafc; color: #6b7280; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; border-bottom: 1px solid #e9e9e7; padding: 10px 16px; }
.inv-table tbody td { border-bottom: 1px solid #f5f5f4; padding: 12px 16px; vertical-align: middle; color: #37352f; background: #fff; }
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr:hover td { background: #fafaf9; }

.inv-empty { border: 1px dashed #e9e9e7; border-radius: 8px; padding: 48px 24px; text-align: center; background: #fafaf9; }
.inv-empty-icon { font-size: 2rem; color: #d4d4d0; display: block; margin-bottom: 12px; }
.inv-empty-title { font-size: .95rem; font-weight: 600; color: #787774; margin-bottom: 4px; }
.inv-empty-text { font-size: .82rem; color: #9b9b9b; }
</style>
@endsection
