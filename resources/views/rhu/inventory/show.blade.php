@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Toast Flash --}}
    @if(session('success') || session('error'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index:1100;">
        <div id="flashToast" class="toast show align-items-center border-0 {{ session('success') ? 'text-bg-success' : 'text-bg-danger' }}" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi {{ session('success') ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                    {{ session('success') ?? session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pt-2 flex-wrap gap-2">
        <div>
            <p class="text-muted mb-0" style="font-size:.78rem;letter-spacing:.04em;text-transform:uppercase;font-weight:600;">Inventory</p>
            <h1 class="fw-bold mb-0" style="font-size:1.45rem;color:#37352f;">{{ $parentData['name'] }}</h1>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('inventory.release-history', $parentData['id']) }}" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-1">
                <i class="bi bi-clock-history" style="font-size:.8rem;"></i>
                <span style="font-size:.82rem;">Release History</span>
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left" style="font-size:.8rem;"></i>
                <span style="font-size:.82rem;">Back to Inventory</span>
            </a>
            <button class="btn btn-inv-add btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                <i class="bi bi-plus" style="font-size:.9rem;"></i>
                <span style="font-size:.82rem;">Add Batch</span>
            </button>
        </div>
    </div>

    {{-- Item Summary Stat Cards --}}
    @php
        $statusBadgeClass = match($parentData['status'] ?? '') {
            'available'    => 'ok',
            'low_stock'    => 'warn',
            'out_of_stock' => 'danger',
            default        => 'neutral',
        };
        $statusLabel = ucwords(str_replace('_', ' ', $parentData['status'] ?? 'Unknown'));
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-capsule"></i></div>
                <div class="stat-value">{{ $parentData['type'] ?? 'N/A' }}</div>
                <div class="stat-label">Type</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-stack"></i></div>
                <div class="stat-value">{{ $parentData['quantity'] ?? 0 }}</div>
                <div class="stat-label">Total Stock ({{ ucfirst($parentData['unit_type'] ?? 'units') }})</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-archive"></i></div>
                <div class="stat-value">{{ count($batches) }}</div>
                <div class="stat-label">Active {{ count($batches) === 1 ? 'Batch' : 'Batches' }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card p-3">
                <div class="stat-icon mb-2"><i class="bi bi-circle-fill" style="font-size:.55rem;"></i></div>
                <div class="stat-value"><span class="inv-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span></div>
                <div class="stat-label">Status</div>
            </div>
        </div>
    </div>

    @if(count($batches) > 0)
        {{-- Release Medicine Section --}}
        <div class="inv-card mb-4 p-0">
            <div class="inv-section-header px-4 py-3">
                <div>
                    <span class="inv-section-label">Release Medicine</span>
                    <span class="inv-section-meta ms-2">Distribute to a resident</span>
                </div>
            </div>
            <div class="px-4 pb-4 pt-3">
                <div id="residentInlineAlert" class="alert d-none" role="alert"></div>
                <form action="{{ route('inventory.release', $parentData['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="inv-form-label">Resident Name <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control inv-input" id="resident_search" autocomplete="off" placeholder="Search resident..." required>
                                <input type="hidden" id="resident_name" name="resident_name" required>
                                <input type="hidden" id="resident_id" name="resident_id">
                                <div id="resident_dropdown" class="dropdown-menu w-100" style="max-height:250px;overflow-y:auto;display:none;z-index:1050;"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="inv-form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control inv-input" id="quantity_to_release" name="quantity_to_release" min="1" max="{{ $parentData['quantity'] }}" required placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <label class="inv-form-label">Release Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control inv-input" id="release_date" name="release_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="inv-form-label">Released By <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control inv-input" id="personnel_search" autocomplete="off" placeholder="Search personnel..." required>
                                <input type="hidden" id="released_by" name="released_by" required>
                                <input type="hidden" id="personnel_id" name="personnel_id">
                                <div id="personnel_dropdown" class="dropdown-menu w-100" style="max-height:250px;overflow-y:auto;display:none;z-index:1050;"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="inv-form-label">Reason</label>
                            <input type="text" class="form-control inv-input" id="reason" name="reason" placeholder="e.g., fever">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-inv-add btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-heart-pulse" style="font-size:.85rem;"></i>
                            <span style="font-size:.82rem;">Release Medicine</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Batches Table --}}
        <div class="inv-card mb-4 p-0">
            <div class="inv-section-header px-4 py-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="inv-section-label">Medicine Batches</span>
                    <span class="inv-section-meta ms-2">{{ count($batches) }} {{ count($batches) === 1 ? 'batch' : 'batches' }}</span>
                </div>
                <button class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center gap-1" id="sortExpirationBtn" onclick="toggleExpirationSort()">
                    <i class="bi bi-funnel" style="font-size:.78rem;"></i>
                    <span id="sortText" style="font-size:.78rem;">Sort by Expiry</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table inv-table mb-0">
                    <thead>
                        <tr>
                            <th>Item / Lot No.</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiration Date</th>
                            <th>Status</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                            @php
                                $expTs   = strtotime($batch['expiration_date']);
                                $todayTs = strtotime('today');
                                $thirtyTs = strtotime('+30 days');
                                if ($expTs <= $todayTs) {
                                    $bStatus = 'expired'; $bBadge = 'danger'; $bText = 'Expired';
                                } elseif ($expTs <= $thirtyTs) {
                                    $bStatus = 'expiring_soon'; $bBadge = 'warn'; $bText = 'Expiring Soon';
                                } else {
                                    $bStatus = 'good'; $bBadge = 'ok'; $bText = 'Good';
                                }
                                $daysLeft = ceil(($expTs - $todayTs) / 86400);
                            @endphp
                            <tr class="{{ $bStatus === 'expired' ? 'row-expired' : ($bStatus === 'expiring_soon' ? 'row-expiring' : '') }}">
                                <td>
                                    <div class="fw-semibold" style="color:#37352f;font-size:.9rem;">{{ $parentData['name'] }}</div>
                                    <div style="font-size:.75rem;color:#9b9b9b;">Lot: {{ $batch['lot_number'] ?? 'N/A' }}</div>
                                </td>
                                <td><span class="inv-chip">{{ $parentData['type'] }}</span></td>
                                <td><span class="fw-semibold" style="color:#37352f;">{{ $batch['quantity'] }}</span></td>
                                <td><span class="inv-chip">{{ ucfirst($parentData['unit_type']) }}</span></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:.88rem;color:#37352f;">
                                        {{ \Carbon\Carbon::parse($batch['expiration_date'])->format('M d, Y') }}
                                    </div>
                                    @if($bStatus === 'expired')
                                        <div style="font-size:.72rem;color:#b91c1c;">Expired</div>
                                    @else
                                        <div style="font-size:.72rem;color:#9b9b9b;">{{ $daysLeft }} days left</div>
                                    @endif
                                </td>
                                <td><span class="inv-badge {{ $bBadge }}">{{ $bText }}</span></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="inv-action-btn edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editBatchModal{{ $batch['id'] }}"
                                                title="Edit Batch">
                                            <i class="bi bi-pencil" style="font-size:.75rem;"></i>
                                        </button>
                                        <form action="{{ route('inventory.batches.destroy', [$parentData['id'], $batch['id']]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inv-action-btn del"
                                                    onclick="return confirm('Delete this batch?')"
                                                    title="Delete Batch">
                                                <i class="bi bi-trash" style="font-size:.75rem;"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('inventory.batches.history', [$parentData['id'], $batch['id']]) }}"
                                           class="inv-action-btn"
                                           title="Distribution History">
                                            <i class="bi bi-clock-history" style="font-size:.75rem;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="inv-empty">
            <i class="bi bi-box inv-empty-icon"></i>
            <div class="inv-empty-title">No batches yet</div>
            <div class="inv-empty-text">Add the first batch for {{ $parentData['name'] }} using the button above.</div>
        </div>
    @endif
</div>

{{-- Add New Resident Modal --}}
<div class="modal fade" id="newResidentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom d-flex align-items-start justify-content-between">
                <div>
                    <div class="modal-type-badge mb-1">INVENTORY</div>
                    <div class="modal-title-custom">Add New Resident</div>
                </div>
                <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
            </div>
            <form id="newResidentForm">
                <div class="modal-body p-4">
                    <div id="newResidentFormAlert" class="alert d-none" role="alert"></div>
                    <div class="modal-form-section mb-3">
                        <div class="modal-section-title mb-3">Personal Info</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="inv-form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="newResidentFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="newResidentLastName" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control inv-input" id="newResidentEmail" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Purok / Street <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" id="newResidentPurok" name="purok" placeholder="e.g., Purok Sunflower" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-form-section">
                        <div class="modal-section-title mb-3">Account</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="inv-form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control inv-input" id="newResidentPassword" name="password" minlength="8" autocomplete="new-password" required>
                                <small class="text-muted" style="font-size:.72rem;">Minimum 8 characters.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control inv-input" id="newResidentPasswordConfirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Role</label>
                                <input type="text" class="form-control inv-input" value="User" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4" id="newResidentSubmitBtn">
                        <span class="submit-label">Create Resident Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Batch Modal --}}
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom d-flex align-items-start justify-content-between">
                <div>
                    <div class="modal-type-badge mb-1">INVENTORY</div>
                    <div class="modal-title-custom">Add New Batch</div>
                </div>
                <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.batches.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="modal-form-section mb-3">
                        <div class="modal-section-title mb-3">Batch Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="inv-form-label">Medicine <span class="text-danger">*</span></label>
                                <select class="form-select inv-input" id="parent_medicine_id" name="parent_medicine_id" required>
                                    <option value="">Select a medicine...</option>
                                    @foreach($allMedicines as $medicine)
                                        <option value="{{ $medicine['id'] }}" {{ $medicine['id'] === $parentData['id'] ? 'selected' : '' }}>
                                            {{ $medicine['name'] }} ({{ ucfirst($medicine['unit_type']) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Lot Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inv-input" name="lot_number" required placeholder="Enter lot number">
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control inv-input" name="quantity" min="1" required placeholder="Enter quantity">
                            </div>
                            <div class="col-md-6">
                                <label class="inv-form-label">Expiration Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control inv-input" name="expiration_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-form-section">
                        <label class="inv-form-label">Notes</label>
                        <textarea class="form-control inv-input mt-2" name="notes" rows="3" placeholder="Batch notes (optional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Save Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Batch Modals --}}
@if(count($batches) > 0)
    @foreach($batches as $batch)
    <div class="modal fade" id="editBatchModal{{ $batch['id'] }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
                <div class="modal-header-custom d-flex align-items-start justify-content-between">
                    <div>
                        <div class="modal-type-badge mb-1">BATCH</div>
                        <div class="modal-title-custom">Edit Batch</div>
                        <div style="font-size:.75rem;color:rgba(255,255,255,.6);margin-top:2px;">Lot: {{ $batch['lot_number'] ?? 'N/A' }}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('inventory.batches.update', [$parentData['id'], $batch['id']]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title mb-3">Batch Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="inv-form-label">Lot Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control inv-input" name="lot_number" value="{{ $batch['lot_number'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="inv-form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control inv-input" name="quantity" min="0" value="{{ $batch['quantity'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="inv-form-label">Expiration Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control inv-input" name="expiration_date" value="{{ $batch['expiration_date'] }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-form-section">
                            <label class="inv-form-label">Notes</label>
                            <textarea class="form-control inv-input mt-2" name="notes" rows="3">{{ $batch['notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Update Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

<style>
/* ── Notion Design Tokens ── */
.btn-inv-add {
    background: #1657c1; color: #fff; border: none;
    border-radius: 6px; font-size: .82rem; font-weight: 500;
    padding: 6px 14px; transition: background .15s;
}
.btn-inv-add:hover { background: #1249a8; color: #fff; }

.stat-card {
    border: 1px solid #e9e9e7; border-radius: 8px;
    background: #fff; transition: box-shadow .15s;
}
.stat-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.stat-icon {
    width: 36px; height: 36px; border-radius: 6px;
    background: #f1f1ef; color: #787774;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
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
.inv-table thead th {
    background: #f8fafc; color: #6b7280;
    font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px;
    border-bottom: 1px solid #e9e9e7; padding: 10px 16px;
}
.inv-table tbody td { border-bottom: 1px solid #f5f5f4; padding: 12px 16px; vertical-align: middle; color: #37352f; background: #fff; }
.inv-table tbody tr:last-child td { border-bottom: none; }
.inv-table tbody tr:hover td { background: #fafaf9; }

.row-expired td   { background: #fff5f5 !important; }
.row-expiring td  { background: #fffbeb !important; }
.row-expired:hover td   { background: #fee2e2 !important; }
.row-expiring:hover td  { background: #fef3c7 !important; }

.inv-action-btn {
    width: 28px; height: 28px; border-radius: 5px;
    border: none; background: #f1f1ef; color: #787774;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .12s, color .12s;
    text-decoration: none;
}
.inv-action-btn:hover          { background: #e9e9e7; color: #37352f; }
.inv-action-btn.edit:hover     { background: #dde9ff; color: #1657c1; }
.inv-action-btn.del:hover      { background: #fee2e2; color: #b91c1c; }

.inv-empty { border: 1px dashed #e9e9e7; border-radius: 8px; padding: 48px 24px; text-align: center; background: #fafaf9; }
.inv-empty-icon { font-size: 2rem; color: #d4d4d0; display: block; margin-bottom: 12px; }
.inv-empty-title { font-size: .95rem; font-weight: 600; color: #787774; margin-bottom: 4px; }
.inv-empty-text { font-size: .82rem; color: #9b9b9b; }

.inv-form-label { font-size: .78rem; font-weight: 600; color: #37352f; margin-bottom: 4px; display: block; }
.inv-input { border: 1px solid #e9e9e7; border-radius: 6px; font-size: .85rem; color: #37352f; background: #fff; }
.inv-input:focus { border-color: #1657c1; box-shadow: 0 0 0 3px rgba(22,87,193,.1); }

/* Notion modal */
.modal-header-custom { background: #1657c1; padding: 18px 22px; }
.modal-type-badge { background: rgba(255,255,255,.12); color: rgba(255,255,255,.75); font-size: .64rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; border-radius: 4px; padding: 2px 8px; display: inline-block; }
.modal-title-custom { font-size: 1.05rem; font-weight: 700; color: #fff; margin-top: 4px; }
.modal-form-section { background: #fafaf9; border-radius: 6px; padding: 16px; border: 1px solid #f1f1ef; }
.modal-section-title { font-size: .72rem; font-weight: 700; color: #9b9b9b; text-transform: uppercase; letter-spacing: .5px; }
</style>

<script>
let currentSortDirection = '{{ $sortDirection ?? "asc" }}';
const residentStoreUrl = '{{ route('inventory.residents.store') }}';
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
}
let newResidentModalInstance = null;

function toggleExpirationSort() {
    currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    const sortText = document.getElementById('sortText');
    if (currentSortDirection === 'desc') {
        sortText.textContent = 'Latest Expiry';
    } else {
        sortText.textContent = 'Earliest Expiry';
    }
    const currentUrl = window.location.pathname;
    let baseUrl = currentUrl.includes('/sort') ? currentUrl.replace('/sort', '') : currentUrl;
    window.location.href = baseUrl + '/sort?direction=' + currentSortDirection;
}

document.addEventListener('DOMContentLoaded', function() {
    const sortText = document.getElementById('sortText');
    if (sortText) {
        sortText.textContent = currentSortDirection === 'desc' ? 'Latest Expiry' : 'Earliest Expiry';
    }
    initializeResidentSearch();
    initializePersonnelSearch();
    setupNewResidentModal();

    // Auto-dismiss toast
    const toast = document.getElementById('flashToast');
    if (toast) { setTimeout(() => { const b = bootstrap.Toast.getOrCreateInstance(toast); b.hide(); }, 4000); }
});

function setupNewResidentModal() {
    const modalElement = document.getElementById('newResidentModal');
    if (modalElement && typeof bootstrap !== 'undefined') {
        newResidentModalInstance = new bootstrap.Modal(modalElement);
        modalElement.addEventListener('hidden.bs.modal', resetNewResidentForm);
    }
    const residentForm = document.getElementById('newResidentForm');
    if (residentForm) residentForm.addEventListener('submit', submitNewResidentForm);
}

function openNewResidentModal(initialName = '') {
    resetNewResidentForm();
    if (initialName) {
        const parts = initialName.trim().split(/\s+/);
        const fn = document.getElementById('newResidentFirstName');
        const ln = document.getElementById('newResidentLastName');
        if (fn) fn.value = parts.shift() || '';
        if (ln) ln.value = parts.join(' ');
    }
    const emailField = document.getElementById('newResidentEmail');
    if (emailField && !emailField.value) emailField.focus();
    if (newResidentModalInstance) newResidentModalInstance.show();
}

function resetNewResidentForm() {
    const form = document.getElementById('newResidentForm');
    if (form) { form.reset(); form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); }
    clearNewResidentFormAlert();
}

function clearNewResidentFormAlert() {
    const alertEl = document.getElementById('newResidentFormAlert');
    if (!alertEl) return;
    alertEl.classList.add('d-none');
    alertEl.classList.remove('alert-success', 'alert-danger');
    alertEl.innerHTML = '';
}

function showNewResidentFormMessage(type, message) {
    const alertEl = document.getElementById('newResidentFormAlert');
    if (!alertEl) return;
    alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
    alertEl.classList.add(`alert-${type}`);
    alertEl.innerHTML = message;
}

function displayNewResidentErrors(errors) {
    const messages = [];
    Object.keys(errors || {}).forEach(field => {
        const fieldErrors = errors[field];
        const input = document.querySelector(`#newResidentForm [name="${field}"]`);
        if (input) input.classList.add('is-invalid');
        (fieldErrors || []).forEach(message => messages.push(message));
    });
    if (messages.length) {
        showNewResidentFormMessage('danger', `<ul class="mb-0 ps-3">${messages.map(m => `<li>${m}</li>`).join('')}</ul>`);
    }
}

function submitNewResidentForm(event) {
    event.preventDefault();
    const form = event.target;
    clearNewResidentFormAlert();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const submitBtn = document.getElementById('newResidentSubmitBtn');
    const originalLabel = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...';
    }
    const payload = {
        first_name: form.first_name?.value.trim() ?? '',
        last_name: form.last_name?.value.trim() ?? '',
        email: form.email?.value.trim() ?? '',
        purok: form.purok?.value.trim() ?? '',
        password: form.password?.value ?? '',
        password_confirmation: form.password_confirmation?.value ?? '',
    };
    fetch(residentStoreUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(payload),
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 422 && data.errors) displayNewResidentErrors(data.errors);
            else showNewResidentFormMessage('danger', data.message || 'Failed to create resident.');
            throw new Error('Request failed');
        }
        return data;
    })
    .then(data => {
        selectResident(data.id, data.name, data.email, data.username);
        showResidentInlineAlert('success', `Resident account created for ${data.name}.`);
        if (newResidentModalInstance) newResidentModalInstance.hide();
    })
    .catch(error => console.error('Error creating resident:', error))
    .finally(() => {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalLabel; }
    });
}

function showResidentInlineAlert(type, message) {
    const alertEl = document.getElementById('residentInlineAlert');
    if (!alertEl) return;
    alertEl.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
    alertEl.classList.add(`alert-${type}`);
    alertEl.innerHTML = message;
}

function hideResidentInlineAlert() {
    const alertEl = document.getElementById('residentInlineAlert');
    if (!alertEl) return;
    alertEl.classList.add('d-none');
    alertEl.innerHTML = '';
}

// Resident Search
function initializeResidentSearch() {
    const searchInput = document.getElementById('resident_search');
    const dropdown = document.getElementById('resident_dropdown');
    const hiddenNameInput = document.getElementById('resident_name');
    const hiddenIdInput = document.getElementById('resident_id');
    let searchTimeout, hasShownInitialList = false;

    searchInput.addEventListener('focus', function() {
        if (!hasShownInitialList && this.value.trim() === '') { searchResidents(''); hasShownInitialList = true; }
    });
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        hideResidentInlineAlert();
        if (query === '') hasShownInitialList = false;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchResidents(query), query.length >= 2 ? 300 : 100);
    });
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
    });
    searchInput.addEventListener('keydown', function() { hiddenNameInput.value = ''; hiddenIdInput.value = ''; });
}

function searchResidents(query) {
    fetch(`{{ route('inventory.residents.search') }}?q=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then(residents => displayResidents(residents, query))
        .catch(() => {
            document.getElementById('resident_dropdown').innerHTML = '<div class="dropdown-item text-danger">Error searching residents</div>';
            showDropdown();
        });
}

function displayResidents(residents, query) {
    const dropdown = document.getElementById('resident_dropdown');
    if (residents.length === 0) {
        dropdown.innerHTML = query.length >= 2
            ? `<div class="dropdown-item" style="white-space:normal;padding:12px;cursor:pointer;"><i class="bi bi-person-plus me-2 text-success"></i><strong>No residents found</strong><br><small class="text-muted">Click to add "${query}" as new resident</small></div>`
            : `<div class="dropdown-item" style="white-space:normal;padding:12px;"><i class="bi bi-info-circle me-2 text-info"></i><strong>No residents found</strong><br><small class="text-muted">Start typing to search</small></div>`;
        if (query.length >= 2) {
            dropdown.querySelector('.dropdown-item').addEventListener('click', () => addNewResident(query));
        }
    } else {
        let html = residents.map(r => `
            <div class="dropdown-item" data-resident-id="${r.id}" data-resident-name="${r.name}" data-resident-email="${r.email}" data-resident-username="${r.username}" style="white-space:normal;padding:12px;cursor:pointer;">
                <i class="bi bi-person me-2 text-primary"></i>
                <strong>${r.name}</strong><br>
                <small class="text-muted">${r.email || '@' + r.username || ''}</small>
            </div>`).join('');
        if (query.length >= 2) {
            html += `<div class="dropdown-divider"></div><div class="dropdown-item" data-new-resident="${query}" style="white-space:normal;padding:12px;cursor:pointer;"><i class="bi bi-person-plus me-2 text-success"></i><strong>Add "${query}" as new resident</strong></div>`;
        }
        dropdown.innerHTML = html;
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.dataset.newResident) addNewResident(this.dataset.newResident);
                else selectResident(this.dataset.residentId, this.dataset.residentName, this.dataset.residentEmail, this.dataset.residentUsername);
            });
        });
    }
    showDropdown();
}

function selectResident(id, name, email, username) {
    const displayText = email ? `${name} (${email})` : (username ? `${name} (@${username})` : name);
    document.getElementById('resident_search').value = displayText;
    document.getElementById('resident_name').value = name;
    document.getElementById('resident_id').value = id;
    hideDropdown();
}
function addNewResident(name) { hideDropdown(); openNewResidentModal(name); }
function showDropdown() { document.getElementById('resident_dropdown').style.display = 'block'; }
function hideDropdown() { document.getElementById('resident_dropdown').style.display = 'none'; }

// Personnel Search
function initializePersonnelSearch() {
    const searchInput = document.getElementById('personnel_search');
    const dropdown = document.getElementById('personnel_dropdown');
    const hiddenNameInput = document.getElementById('released_by');
    const hiddenIdInput = document.getElementById('personnel_id');
    if (!searchInput || !dropdown || !hiddenNameInput) return;
    let searchTimeout, hasShownInitialList = false;

    searchInput.addEventListener('focus', function() {
        if (!hasShownInitialList && this.value.trim() === '') { searchPersonnel(''); hasShownInitialList = true; }
    });
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        if (query === '') hasShownInitialList = false;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => searchPersonnel(query), query.length >= 2 ? 300 : 100);
    });
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) hidePersonnelDropdown();
    });
    searchInput.addEventListener('keydown', function() { hiddenNameInput.value = ''; hiddenIdInput.value = ''; });
}

function searchPersonnel(query) {
    fetch(`{{ route('inventory.personnel.search') }}?q=${encodeURIComponent(query)}`)
        .then(r => r.json())
        .then(personnel => displayPersonnel(personnel, query))
        .catch(() => {
            document.getElementById('personnel_dropdown').innerHTML = '<div class="dropdown-item text-danger">Error searching personnel</div>';
            showPersonnelDropdown();
        });
}

function displayPersonnel(personnel, query) {
    const dropdown = document.getElementById('personnel_dropdown');
    if (personnel.length === 0) {
        dropdown.innerHTML = query.length >= 2
            ? `<div class="dropdown-item" style="white-space:normal;padding:12px;"><i class="bi bi-person-x me-2 text-warning"></i><strong>No personnel found</strong><br><small class="text-muted">No match for "${query}"</small></div>`
            : `<div class="dropdown-item" style="white-space:normal;padding:12px;"><i class="bi bi-info-circle me-2 text-info"></i><strong>No personnel found</strong><br><small class="text-muted">Start typing to search.</small></div>`;
    } else {
        dropdown.innerHTML = personnel.map(p => `
            <div class="dropdown-item" data-personnel-id="${p.id}" data-personnel-name="${p.name}" style="white-space:normal;padding:12px;cursor:pointer;">
                <i class="bi bi-person-badge me-2 text-primary"></i>
                <strong>${p.name}</strong>${p.position ? `<br><small class="text-muted">${p.position}</small>` : ''}
            </div>`).join('');
        dropdown.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                selectPersonnel(this.dataset.personnelId, this.dataset.personnelName);
            });
        });
    }
    showPersonnelDropdown();
}

function selectPersonnel(id, name) {
    document.getElementById('personnel_search').value = name;
    document.getElementById('released_by').value = name;
    document.getElementById('personnel_id').value = id;
    hidePersonnelDropdown();
}
function showPersonnelDropdown() { const d = document.getElementById('personnel_dropdown'); if (d) d.style.display = 'block'; }
function hidePersonnelDropdown() { const d = document.getElementById('personnel_dropdown'); if (d) d.style.display = 'none'; }
</script>
@endsection
