@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pt-2 flex-wrap gap-2">
        <div>
            <p class="text-muted mb-0" style="font-size:.78rem;letter-spacing:.04em;text-transform:uppercase;font-weight:600;">Inventory / Release History</p>
            <h1 class="fw-bold mb-0" style="font-size:1.45rem;color:#37352f;">{{ $parentData['name'] }}</h1>
        </div>
        <a href="{{ route('inventory.show', $parentData['id']) }}" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left" style="font-size:.8rem;"></i>
            <span style="font-size:.82rem;">Back to Medicine</span>
        </a>
    </div>

    {{-- Summary Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-box-seam"></i></div>
                <div class="stat-value">{{ $totalReleased }}</div>
                <div class="stat-label">{{ ucfirst($parentData['unit_type'] ?? 'Units') }} Released</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-people"></i></div>
                <div class="stat-value">{{ $totalRecipients }}</div>
                <div class="stat-label">Unique Recipients</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-clock-history"></i></div>
                <div class="stat-value">{{ $releaseCount }}</div>
                <div class="stat-label">Total Releases</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-stack"></i></div>
                <div class="stat-value">{{ $parentData['quantity'] ?? 0 }}</div>
                <div class="stat-label">Current Stock</div>
            </div>
        </div>
    </div>

    {{-- Release History Table --}}
    <div class="inv-card p-0">
        <div class="inv-section-header px-4 py-3">
            <span class="inv-section-label">Medicine Release History</span>
            @if(count($allReleases) > 0)
                <span class="inv-section-meta ms-2">{{ count($allReleases) }} {{ count($allReleases) === 1 ? 'record' : 'records' }}</span>
            @endif
        </div>
        @if(count($allReleases) > 0)
            <div class="table-responsive">
                <table class="table inv-table mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Recipient</th>
                            <th>Quantity</th>
                            <th>Lot Number</th>
                            <th>Batch Expiry</th>
                            <th>Reason</th>
                            <th>Released By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allReleases as $release)
                            <tr>
                                <td>
                                    <div class="fw-semibold" style="font-size:.88rem;color:#37352f;">
                                        {{ $release['release_date'] ? \Carbon\Carbon::parse($release['release_date'])->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div style="font-size:.72rem;color:#9b9b9b;">
                                        {{ $release['released_at'] ? \Carbon\Carbon::parse($release['released_at'])->format('h:i A') : '' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle" style="color:#1657c1;font-size:1rem;"></i>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.88rem;color:#37352f;">{{ $release['resident_name'] }}</div>
                                            @if($release['resident_id'])
                                                <div style="font-size:.72rem;color:#9b9b9b;">ID: {{ substr($release['resident_id'], 0, 8) }}...</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="inv-badge ok">{{ $release['quantity_released'] }} {{ ucfirst($parentData['unit_type'] ?? 'units') }}</span>
                                </td>
                                <td>
                                    <span class="inv-chip font-monospace" style="font-size:.72rem;">{{ $release['lot_number'] ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @php
                                        $expiryDate = $release['batch_expiration'];
                                        $isExpired = $expiryDate && \Carbon\Carbon::parse($expiryDate)->isPast();
                                        $isExpiringSoon = $expiryDate && !$isExpired && \Carbon\Carbon::parse($expiryDate)->diffInDays(now()) <= 30;
                                    @endphp
                                    @if($expiryDate)
                                        <span class="inv-badge {{ $isExpired ? 'danger' : ($isExpiringSoon ? 'warn' : 'neutral') }}">
                                            {{ \Carbon\Carbon::parse($expiryDate)->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span style="font-size:.8rem;color:#9b9b9b;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:.85rem;color:#787774;">{{ $release['reason'] ?: 'Not specified' }}</span>
                                </td>
                                <td>
                                    <span style="font-size:.82rem;color:#787774;">{{ $release['released_by'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="inv-empty m-4">
                <i class="bi bi-inbox inv-empty-icon"></i>
                <div class="inv-empty-title">No Release History</div>
                <div class="inv-empty-text">No medicine has been released for this item yet.</div>
            </div>
        @endif
    </div>
</div>

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

.inv-chip { display: inline-flex; align-items: center; font-size: .72rem; font-weight: 500; border-radius: 4px; padding: 2px 8px; background: #f1f1ef; color: #37352f; }

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
