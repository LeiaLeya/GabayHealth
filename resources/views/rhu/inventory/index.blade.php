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
            <h2 class="fw-bold mb-1" style="color:#1e293b;">Inventory Management</h2>
            <p class="text-muted mb-0 small">Track medicines, vaccines, and health supplies</p>
        </div>
        <button class="btn-inv-add" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i>Add New Item
        </button>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('inventory.index') }}" class="mb-4" id="searchForm">
        <div class="inv-search-wrap">
            <i class="bi bi-search inv-search-icon"></i>
            <input type="text" name="search" class="inv-search-input" placeholder="Search inventory by name, type, or description…" value="{{ $search ?? '' }}" autocomplete="off">
            @if(!empty($search))
                <a href="{{ route('inventory.index') }}" class="inv-search-clear" title="Clear search"><i class="bi bi-x-lg"></i></a>
            @endif
            <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
            <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
            <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
            <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
            <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
        </div>
    </form>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ $inventorySummary['total_items'] ?? 0 }}</div>
                    <div class="stat-label">Total Items</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" style="color:#15803d;">{{ $inventorySummary['available'] ?? 0 }}</div>
                    <div class="stat-label">Available</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card {{ ($inventorySummary['low_stock'] ?? 0) > 0 ? 'stat-card-warn' : '' }}">
                <div class="stat-icon" style="background:#fef9c3;color:#a16207;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" style="color:#a16207;">{{ $inventorySummary['low_stock'] ?? 0 }}</div>
                    <div class="stat-label">Low Stock</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card {{ ($inventorySummary['out_of_stock'] ?? 0) > 0 ? 'stat-card-danger' : '' }}">
                <div class="stat-icon" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-info">
                    <div class="stat-value" style="color:#b91c1c;">{{ $inventorySummary['out_of_stock'] ?? 0 }}</div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Medicines & Vaccines Section --}}
    @if(!empty($medicineGroups) && count($medicineGroups) > 0)
    <div class="inv-section mb-4">
        <div class="inv-section-header mb-2">
            <span class="inv-section-label"><i class="bi bi-capsule me-2"></i>Medicines & Vaccines</span>
            <span class="inv-section-meta">{{ count($medicineGroups) }} generic {{ count($medicineGroups) === 1 ? 'name' : 'names' }} · grouped by brand</span>
        </div>
        <div class="inv-card">
            <div class="list-group list-group-flush">
                @foreach($medicineGroups as $group)
                    @php
                        $collapseId = 'gen_' . md5($group['generic_name']);
                        $expDays = $group['soonest_expiration_days'] ?? null;
                    @endphp
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <a class="inv-generic-toggle medicine-group-toggle" data-bs-toggle="collapse" href="#{{ $collapseId }}" role="button" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                    <i class="bi bi-chevron-right medicine-chevron"></i>
                                    <span class="inv-generic-name">{{ $group['generic_name'] }}</span>
                                    <span class="inv-brand-count">{{ count($group['items']) }} {{ count($group['items']) === 1 ? 'brand' : 'brands' }}</span>
                                </a>
                                @if(!empty($group['description']))
                                    <div class="inv-generic-desc ms-4">{{ $group['description'] }}</div>
                                @endif
                            </div>
                            <div class="ms-3 flex-shrink-0">
                                @if(!is_null($expDays))
                                    @if($expDays < 0)
                                        <span class="inv-badge danger">Expired</span>
                                    @elseif($expDays === 0)
                                        <span class="inv-badge danger">Expires Today</span>
                                    @elseif($expDays <= 30)
                                        <span class="inv-badge warn">{{ $expDays }}d left</span>
                                    @else
                                        <span class="inv-badge ok">{{ $expDays }}d left</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="collapse mt-3" id="{{ $collapseId }}">
                            <div class="table-responsive">
                                <table class="table inv-inner-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:22%">Brand Name</th>
                                            <th style="width:10%">Dosage</th>
                                            <th style="width:9%">Qty</th>
                                            <th style="width:11%">Unit</th>
                                            <th style="width:12%">Status</th>
                                            <th style="width:12%">Expiration</th>
                                            <th style="width:18%">Description</th>
                                            <th class="text-end" style="width:6%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['items'] as $brand)
                                            @php
                                                $badge = match($brand['status']) {
                                                    'available'    => 'ok',
                                                    'low_stock'    => 'warn',
                                                    'out_of_stock' => 'danger',
                                                    default        => 'neutral'
                                                };
                                                $displayStatus = ucwords(str_replace('_', ' ', $brand['status'] ?? ''));
                                                $days = $brand['soonest_expiration_days'] ?? null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <a href="{{ route('inventory.show', $brand['id']) }}" class="inv-item-link">
                                                        {{ $brand['name'] }}<i class="bi bi-arrow-right-short ms-1"></i>
                                                    </a>
                                                </td>
                                                <td class="text-muted">{{ $brand['milligrams'] ?? '—' }}</td>
                                                <td style="font-weight:600;color:#37352f;">{{ $brand['quantity'] ?? 'N/A' }}</td>
                                                <td><span class="inv-chip">{{ ucfirst($brand['unit_type'] ?? 'N/A') }}</span></td>
                                                <td><span class="inv-badge {{ $badge }}">{{ $displayStatus }}</span></td>
                                                <td>
                                                    @if(is_null($days))
                                                        <span class="text-muted small">N/A</span>
                                                    @elseif($days < 0)
                                                        <span class="inv-badge danger">Exp'd {{ abs($days) }}d</span>
                                                    @elseif($days === 0)
                                                        <span class="inv-badge danger">Today</span>
                                                    @elseif($days <= 30)
                                                        <span class="inv-badge warn">{{ $days }}d</span>
                                                    @else
                                                        <span class="inv-badge ok">{{ $days }}d</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">{{ Str::limit($brand['description'] ?? '—', 60) }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <button class="inv-action-btn" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $brand['id'] }}" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="inv-action-btn danger" onclick="openDeleteConfirm('{{ $brand['id'] }}','{{ addslashes($brand['name']) }}','{{ route('inventory.destroy', $brand['id']) }}','Delete Item')" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Equipment, Supplies & Other --}}
    @if(count($materials) > 0)
    <div class="inv-section mb-4">
        <div class="inv-section-header mb-2">
            <span class="inv-section-label"><i class="bi bi-grid-3x3-gap me-2"></i>Equipment, Supplies & Other</span>
            <span class="inv-section-meta">{{ $materials->total() }} {{ $materials->total() === 1 ? 'item' : 'items' }}</span>
        </div>
        <div class="inv-card overflow-hidden">
            <div class="table-responsive">
                <table class="table inv-table mb-0" style="table-layout:fixed;width:100%;">
                    <thead>
                        <tr>
                            <th style="width:18%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Item Name</span>
                                    @php
                                        $isNameDesc = (($sortBy ?? '') === 'name') && (($sortDir ?? 'asc') === 'desc');
                                        $nextDir = $isNameDesc ? 'asc' : 'desc';
                                        $nameToggleUrl = request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextDir, 'page' => 1]);
                                    @endphp
                                    <a href="{{ $nameToggleUrl }}" class="inv-sort-link" title="Sort">
                                        <i class="bi {{ $isNameDesc ? 'bi-arrow-down' : 'bi-arrow-up' }}"></i>
                                    </a>
                                </div>
                            </th>
                            <th style="width:11%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Type</span>
                                    <div class="dropdown">
                                        <button type="button" class="inv-filter-btn dropdown-toggle-no-arrow" data-bs-toggle="dropdown" title="Filter type">
                                            <i class="bi bi-funnel{{ !empty($filterType) ? '-fill' : '' }}"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end inv-filter-dropdown">
                                            <form method="GET" action="{{ route('inventory.index') }}" id="typeFilterForm">
                                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
                                                <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
                                                <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                @foreach(['' => 'All', 'Medicine' => 'Medicine', 'Equipment' => 'Equipment', 'Supplies' => 'Supplies', 'Vaccine' => 'Vaccine', 'Other' => 'Other'] as $val => $lbl)
                                                <li>
                                                    <label class="dropdown-item-text">
                                                        <input type="radio" name="type" value="{{ $val }}" class="form-check-input me-2" {{ ($filterType ?? '') == $val ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                        {{ $lbl }}
                                                    </label>
                                                </li>
                                                @if($val === '') <li><hr class="dropdown-divider"></li> @endif
                                                @endforeach
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                            <th style="width:10%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Quantity</span>
                                    @php
                                        $isQtyDesc = (($sortBy ?? '') === 'quantity') && (($sortDir ?? 'asc') === 'desc');
                                        $qtyNextDir = $isQtyDesc ? 'asc' : 'desc';
                                        $qtyToggleUrl = request()->fullUrlWithQuery(['sort_by' => 'quantity', 'sort_dir' => $qtyNextDir, 'page' => 1]);
                                    @endphp
                                    <a href="{{ $qtyToggleUrl }}" class="inv-sort-link" title="Sort">
                                        <i class="bi {{ $isQtyDesc ? 'bi-arrow-down' : 'bi-arrow-up' }}"></i>
                                    </a>
                                </div>
                            </th>
                            <th style="width:11%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Unit</span>
                                    <div class="dropdown">
                                        <button type="button" class="inv-filter-btn dropdown-toggle-no-arrow" data-bs-toggle="dropdown" title="Filter unit">
                                            <i class="bi bi-funnel{{ !empty($filterUnitType) ? '-fill' : '' }}"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end inv-filter-dropdown">
                                            <form method="GET" action="{{ route('inventory.index') }}" id="unitTypeFilterForm">
                                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
                                                <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
                                                <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                @foreach(['' => 'All', 'capsules' => 'Capsules', 'tablets' => 'Tablets', 'pieces' => 'Pieces', 'boxes' => 'Boxes', 'bottles' => 'Bottles', 'packs' => 'Packs', 'vials' => 'Vials', 'sachets' => 'Sachets'] as $val => $lbl)
                                                <li>
                                                    <label class="dropdown-item-text">
                                                        <input type="radio" name="unit_type" value="{{ $val }}" class="form-check-input me-2" {{ ($filterUnitType ?? '') == $val ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                        {{ $lbl }}
                                                    </label>
                                                </li>
                                                @if($val === '') <li><hr class="dropdown-divider"></li> @endif
                                                @endforeach
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                            <th style="width:11%;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Status</span>
                                    <div class="dropdown">
                                        <button type="button" class="inv-filter-btn dropdown-toggle-no-arrow" data-bs-toggle="dropdown" title="Filter status">
                                            <i class="bi bi-funnel{{ !empty($filterStatus) ? '-fill' : '' }}"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end inv-filter-dropdown">
                                            <form method="GET" action="{{ route('inventory.index') }}" id="statusFilterForm">
                                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
                                                <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
                                                <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                @foreach(['' => 'All', 'available' => 'Available', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock'] as $val => $lbl)
                                                <li>
                                                    <label class="dropdown-item-text">
                                                        <input type="radio" name="status" value="{{ $val }}" class="form-check-input me-2" {{ ($filterStatus ?? '') == $val ? 'checked' : '' }} onchange="document.getElementById('statusFilterForm').submit();">
                                                        {{ $lbl }}
                                                    </label>
                                                </li>
                                                @if($val === '') <li><hr class="dropdown-divider"></li> @endif
                                                @endforeach
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                            <th style="width:11%;">Expiration</th>
                            <th style="width:18%;">Description</th>
                            <th class="text-end" style="width:10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materials as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.show', $item['id']) }}" class="inv-item-link">
                                        {{ $item['name'] }}<i class="bi bi-arrow-right-short ms-1"></i>
                                    </a>
                                </td>
                                <td><span class="inv-chip">{{ $item['type'] }}</span></td>
                                <td style="font-weight:600;color:#37352f;">{{ $item['quantity'] ?? 'N/A' }}</td>
                                <td><span class="inv-chip">{{ ucfirst($item['unit_type'] ?? 'N/A') }}</span></td>
                                <td>
                                    @php
                                        $badge = match($item['status']) {
                                            'available'    => 'ok',
                                            'low_stock'    => 'warn',
                                            'out_of_stock' => 'danger',
                                            default        => 'neutral'
                                        };
                                        $displayStatus = ucwords(str_replace('_', ' ', $item['status']));
                                    @endphp
                                    <span class="inv-badge {{ $badge }}">{{ $displayStatus }}</span>
                                </td>
                                <td>
                                    @php $expirationDays = $item['soonest_expiration_days'] ?? null; @endphp
                                    @if(is_null($expirationDays))
                                        <span class="text-muted small">N/A</span>
                                    @elseif($expirationDays < 0)
                                        <span class="inv-badge danger">Exp'd {{ abs($expirationDays) }}d</span>
                                    @elseif($expirationDays === 0)
                                        <span class="inv-badge danger">Today</span>
                                    @elseif($expirationDays <= 30)
                                        <span class="inv-badge warn">{{ $expirationDays }}d</span>
                                    @else
                                        <span class="inv-badge ok">{{ $expirationDays }}d</span>
                                    @endif
                                </td>
                                <td class="text-muted small" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($item['description'] ?? 'No description', 50) }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="inv-action-btn" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item['id'] }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="inv-action-btn danger" onclick="openDeleteConfirm('{{ $item['id'] }}','{{ addslashes($item['name']) }}','{{ route('inventory.destroy', $item['id']) }}','Delete Item')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($materials->hasPages())
        <div class="d-flex justify-content-center mt-3">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    @if ($materials->onFirstPage())
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $materials->previousPageUrl() }}" rel="prev">Previous</a></li>
                    @endif
                    @foreach ($materials->getUrlRange(1, $materials->lastPage()) as $page => $url)
                        @if ($page == $materials->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                    @if ($materials->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $materials->nextPageUrl() }}" rel="next">Next</a></li>
                    @else
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Next</a></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
    @else
        @if(empty($medicineGroups) || count($medicineGroups) === 0)
        <div class="inv-empty">
            <i class="bi bi-inbox"></i>
            <div class="inv-empty-title">No inventory items found</div>
            @if(!empty($search))
                <div class="inv-empty-text">No results for "{{ $search }}". <a href="{{ route('inventory.index') }}" class="text-decoration-none" style="color:#1657c1;">Clear search</a></div>
            @else
                <div class="inv-empty-text">Add your first item using the button above.</div>
            @endif
        </div>
        @endif
    @endif
</div>

{{-- ─── Add Item Modal ─── --}}
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
            <div class="modal-header-custom">
                <div>
                    <div class="modal-type-badge">New Item</div>
                    <h5 class="modal-title-custom">Add Inventory Item</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('inventory.store') }}" id="addItemForm">
                @csrf
                <div class="modal-body p-4" style="max-height:70vh;overflow-y:auto;">

                    {{-- Basic Information --}}
                    <div class="modal-form-section mb-3">
                        <div class="modal-section-title"><i class="bi bi-info-circle me-2"></i>Basic Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Item Code</label>
                                <input type="text" class="form-control rounded-3" id="item_code" name="item_code" readonly placeholder="Auto-generated">
                                <small class="text-muted" style="font-size:.72rem;">Automatically generated</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="name" name="name" required placeholder="e.g., Paracetamol 500 mg Tablet">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="type" name="type" required>
                                    <option value="">Select category…</option>
                                    <option value="Medicine" {{ old('type') === 'Medicine' ? 'selected' : '' }}>Medicine</option>
                                    <option value="Vaccine" {{ old('type') === 'Vaccine' ? 'selected' : '' }}>Vaccine</option>
                                    <option value="Medical Supply" {{ old('type') === 'Medical Supply' ? 'selected' : '' }}>Medical Supply</option>
                                    <option value="Family Planning Supply" {{ old('type') === 'Family Planning Supply' ? 'selected' : '' }}>Family Planning Supply</option>
                                    <option value="Equipment" {{ old('type') === 'Equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="Other" {{ old('type') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Quantity Available <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-3" id="quantity" name="quantity" min="0" required placeholder="Enter quantity" value="{{ old('quantity') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Dispensing Unit <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="unit_type" name="unit_type" required>
                                    <option value="">Select unit…</option>
                                    <option value="tablets"  {{ old('unit_type') === 'tablets'  ? 'selected' : '' }}>Tablets</option>
                                    <option value="capsules" {{ old('unit_type') === 'capsules' ? 'selected' : '' }}>Capsules</option>
                                    <option value="bottles"  {{ old('unit_type') === 'bottles'  ? 'selected' : '' }}>Bottles</option>
                                    <option value="vials"    {{ old('unit_type') === 'vials'    ? 'selected' : '' }}>Vials</option>
                                    <option value="pieces"   {{ old('unit_type') === 'pieces'   ? 'selected' : '' }}>Pieces</option>
                                    <option value="boxes"    {{ old('unit_type') === 'boxes'    ? 'selected' : '' }}>Boxes</option>
                                    <option value="packs"    {{ old('unit_type') === 'packs'    ? 'selected' : '' }}>Packs</option>
                                    <option value="sachets"  {{ old('unit_type') === 'sachets'  ? 'selected' : '' }}>Sachets</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Description / Notes</label>
                                <textarea class="form-control rounded-3" id="description" name="description" rows="2" placeholder="Enter description or notes…">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Medicine Details --}}
                    <div class="modal-form-section mb-3" id="medicine_fields_create" style="display:none;">
                        <div class="modal-section-title"><i class="bi bi-capsule me-2"></i>Medicine Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Generic Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="generic_name" name="generic_name" placeholder="e.g., Paracetamol" value="{{ old('generic_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Dosage (mg) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control rounded-3" id="milligrams" name="milligrams" placeholder="e.g., 500" value="{{ old('milligrams') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Generic Description</label>
                                <input type="text" class="form-control rounded-3" id="generic_description" name="generic_description" placeholder="e.g., For fever and mild pain" value="{{ old('generic_description') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Therapeutic Classification</label>
                                <select class="form-select rounded-3" id="therapeutic_classification" name="therapeutic_classification">
                                    <option value="">Select classification…</option>
                                    @foreach(['Analgesic / Antipyretic','Antacid','Anthelmintic','Antidiarrheal Support','Antihistamine','Antihypertensive','Bronchodilator','Cough / Expectorant','NSAID / Anti-inflammatory','Oral Rehydration Therapy','Topical Antiseptic','Vitamins / Supplements','Wound Care Supplies'] as $tc)
                                    <option value="{{ $tc }}" {{ old('therapeutic_classification') === $tc ? 'selected' : '' }}>{{ $tc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Prescription Required</label>
                                <select class="form-select rounded-3" id="prescription_required" name="prescription_required">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Restricted Medicine</label>
                                <select class="form-select rounded-3" id="restricted_medicine" name="restricted_medicine">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Storage Condition</label>
                                <select class="form-select rounded-3" id="storage_condition" name="storage_condition">
                                    <option value="">Select storage condition…</option>
                                    <option value="Room Temperature">Room Temperature</option>
                                    <option value="Refrigerated">Refrigerated (2-8°C)</option>
                                    <option value="Protect from Light">Protect from Light</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Vaccine Details --}}
                    <div class="modal-form-section mb-3" id="vaccine_fields_create" style="display:none;">
                        <div class="modal-section-title"><i class="bi bi-shield-check me-2"></i>Vaccine Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Vaccine Type</label>
                                <input type="text" class="form-control rounded-3" id="vaccine_type" name="vaccine_type" placeholder="e.g., BCG, Pentavalent, OPV, COVID-19">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Vial Size</label>
                                <select class="form-select rounded-3" id="vial_size" name="vial_size">
                                    <option value="">Select vial size…</option>
                                    <option value="Single-dose">Single-dose</option>
                                    <option value="Multi-dose">Multi-dose</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Cold Chain Required</label>
                                <select class="form-select rounded-3" id="cold_chain_required" name="cold_chain_required">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Temperature Range</label>
                                <input type="text" class="form-control rounded-3" id="temperature_range" name="temperature_range" value="2-8°C" placeholder="2-8°C">
                            </div>
                        </div>
                    </div>

                    {{-- Initial Batch --}}
                    <div class="modal-form-section mb-3" id="batch_fields_create" style="display:none;">
                        <div class="modal-section-title"><i class="bi bi-archive me-2"></i>Initial Batch Details</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Lot Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="initial_lot_number" name="initial_lot_number" placeholder="e.g., LOT-2026-001" value="{{ old('initial_lot_number') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Expiration Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-3" id="initial_expiration_date" name="initial_expiration_date" value="{{ old('initial_expiration_date') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Batch Notes</label>
                                <textarea class="form-control rounded-3" id="initial_batch_notes" name="initial_batch_notes" rows="2" placeholder="Optional notes for this batch">{{ old('initial_batch_notes') }}</textarea>
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size:.72rem;">Initial batch quantity uses the quantity entered in Basic Information.</p>
                    </div>

                    {{-- Source Information --}}
                    <div class="modal-form-section">
                        <div class="modal-section-title"><i class="bi bi-building me-2"></i>Source Information</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Source Type</label>
                                <select class="form-select rounded-3" id="source_type" name="source_type">
                                    <option value="">Select source…</option>
                                    <option value="DOH">DOH</option>
                                    <option value="RHU">RHU</option>
                                    <option value="LGU">LGU</option>
                                    <option value="Donation">Donation</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Received By</label>
                                <select class="form-select rounded-3" id="received_by" name="received_by">
                                    <option value="">Select recipient…</option>
                                    <option value="Midwife">Midwife</option>
                                    <option value="BHW">BHW</option>
                                    <option value="Nurse">Nurse</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Delivery Reference No.</label>
                                <input type="text" class="form-control rounded-3" id="delivery_reference_no" name="delivery_reference_no" placeholder="Enter delivery reference number">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Edit Modals — Materials ─── --}}
@if(isset($materials) && count($materials) > 0)
    @foreach($materials as $item)
    <div class="modal fade" id="editItemModal{{ $item['id'] }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
                <div class="modal-header-custom">
                    <div>
                        <div class="modal-type-badge">Edit Item</div>
                        <h5 class="modal-title-custom">{{ $item['name'] }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('inventory.update', $item['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="modal-form-section mb-3">
                            <div class="modal-section-title"><i class="bi bi-info-circle me-2"></i>Basic Information</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3" name="name" value="{{ $item['name'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" id="edit_type_{{ $item['id'] }}" name="type" required>
                                        @foreach(['Medicine','Equipment','Supplies','Vaccine','Other'] as $t)
                                        <option value="{{ $t }}" {{ $item['type'] == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-form-section mb-3 generic-fields-edit" id="generic_fields_edit_{{ $item['id'] }}" style="display: {{ in_array($item['type'], ['Medicine','Vaccine']) ? 'block' : 'none' }};">
                            <div class="modal-section-title"><i class="bi bi-capsule me-2"></i>Medicine / Vaccine Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Generic Name</label>
                                    <input type="text" class="form-control rounded-3" id="edit_generic_name_{{ $item['id'] }}" name="generic_name" value="{{ $item['generic_name'] ?? '' }}" placeholder="e.g., Paracetamol">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Dosage (mg)</label>
                                    <input type="number" step="0.01" min="0" class="form-control rounded-3" id="edit_milligrams_{{ $item['id'] }}" name="milligrams" value="{{ $item['milligrams'] ?? '' }}" placeholder="e.g., 250">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Generic Description</label>
                                    <input type="text" class="form-control rounded-3" id="edit_generic_description_{{ $item['id'] }}" name="generic_description" value="{{ $item['generic_description'] ?? '' }}" placeholder="e.g., For fever and mild pain">
                                </div>
                            </div>
                        </div>
                        <div class="modal-form-section">
                            <div class="modal-section-title"><i class="bi bi-box me-2"></i>Stock Details</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control rounded-3" name="quantity" value="{{ $item['quantity'] ?? 0 }}" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Unit Type <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="unit_type" required>
                                        @foreach(['capsules'=>'Capsules','tablets'=>'Tablets','pieces'=>'Pieces','boxes'=>'Boxes','bottles'=>'Bottles','packs'=>'Packs','vials'=>'Vials','sachets'=>'Sachets'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($item['unit_type'] ?? '') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Description</label>
                                    <textarea class="form-control rounded-3" name="description" rows="2">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

{{-- ─── Edit Modals — Medicine Groups ─── --}}
@if(!empty($medicineGroups))
    @foreach($medicineGroups as $group)
        @foreach($group['items'] as $item)
        <div class="modal fade" id="editItemModal{{ $item['id'] }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
                    <div class="modal-header-custom">
                        <div>
                            <div class="modal-type-badge">Edit Item</div>
                            <h5 class="modal-title-custom">{{ $item['name'] }}</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.update', $item['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="modal-form-section mb-3">
                                <div class="modal-section-title"><i class="bi bi-info-circle me-2"></i>Basic Information</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-3" name="name" value="{{ $item['name'] }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-3" id="edit_type_{{ $item['id'] }}" name="type" required>
                                            @foreach(['Medicine','Equipment','Supplies','Vaccine','Other'] as $t)
                                            <option value="{{ $t }}" {{ ($item['type'] ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-form-section mb-3 generic-fields-edit" id="generic_fields_edit_{{ $item['id'] }}" style="display: {{ in_array(($item['type'] ?? ''), ['Medicine','Vaccine']) ? 'block' : 'none' }};">
                                <div class="modal-section-title"><i class="bi bi-capsule me-2"></i>Medicine / Vaccine Details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Generic Name</label>
                                        <input type="text" class="form-control rounded-3" id="edit_generic_name_{{ $item['id'] }}" name="generic_name" value="{{ $item['generic_name'] ?? '' }}" placeholder="e.g., Paracetamol">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Dosage (mg)</label>
                                        <input type="number" step="0.01" min="0" class="form-control rounded-3" id="edit_milligrams_{{ $item['id'] }}" name="milligrams" value="{{ $item['milligrams'] ?? '' }}" placeholder="e.g., 250">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Generic Description</label>
                                        <input type="text" class="form-control rounded-3" id="edit_generic_description_{{ $item['id'] }}" name="generic_description" value="{{ $item['generic_description'] ?? '' }}" placeholder="e.g., For fever and mild pain">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-form-section">
                                <div class="modal-section-title"><i class="bi bi-box me-2"></i>Stock Details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control rounded-3" name="quantity" value="{{ $item['quantity'] ?? 0 }}" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Unit Type <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-3" name="unit_type" required>
                                            @foreach(['capsules'=>'Capsules','tablets'=>'Tablets','pieces'=>'Pieces','boxes'=>'Boxes','bottles'=>'Bottles','packs'=>'Packs','vials'=>'Vials','sachets'=>'Sachets'] as $val => $lbl)
                                            <option value="{{ $val }}" {{ ($item['unit_type'] ?? '') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control rounded-3" name="description" rows="2">{{ $item['description'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pb-4 px-4 gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    @endforeach
@endif

<style>
*, *::before, *::after { box-sizing: border-box; }

/* ─── Add button ─── */
.btn-inv-add {
    background:#1657c1; border:none; color:#fff;
    font-size:.82rem; font-weight:600; padding:7px 16px;
    border-radius:6px; cursor:pointer; transition:opacity .1s;
}
.btn-inv-add:hover { opacity:.85; }

/* ─── Search ─── */
.inv-search-wrap {
    position:relative; max-width:520px;
    display:flex; align-items:center;
    background:#fff; border:1px solid #e9e9e7; border-radius:6px;
    padding:0 12px; transition:border-color .15s;
}
.inv-search-wrap:focus-within { border-color:#94a3b8; box-shadow:0 0 0 3px rgba(22,87,193,.07); }
.inv-search-icon { color:#9b9b9b; font-size:.85rem; flex-shrink:0; }
.inv-search-input {
    flex:1; border:none; padding:9px 10px;
    font-size:.875rem; color:#37352f; background:transparent;
}
.inv-search-input:focus { outline:none; }
.inv-search-input::placeholder { color:#b0b0a8; }
.inv-search-clear { color:#b0b0a8; text-decoration:none; font-size:.75rem; flex-shrink:0; transition:color .1s; }
.inv-search-clear:hover { color:#ef4444; }

/* ─── Stat cards ─── */
.stat-card {
    display:flex; align-items:center; gap:12px;
    padding:14px 18px; border-radius:6px;
    background:#fff; border:1px solid #e9e9e7;
    transition:box-shadow .15s;
}
.stat-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.06); }
.stat-icon {
    width:36px; height:36px; border-radius:6px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; flex-shrink:0;
    background:#f1f1ef; color:#787774;
}
.stat-value { font-size:1.5rem; font-weight:700; color:#37352f; line-height:1; }
.stat-label { font-size:.72rem; color:#9b9b9b; font-weight:400; margin-top:2px; }
.stat-card-warn   { border-color:#fef08a; }
.stat-card-danger { border-color:#fecaca; }

/* ─── Section headers ─── */
.inv-section-header { display:flex; align-items:center; gap:8px; }
.inv-section-label { font-size:.82rem; font-weight:700; color:#37352f; }
.inv-section-meta  { font-size:.72rem; color:#9b9b9b; margin-left:auto; }

/* ─── Card wrapper ─── */
.inv-card { background:#fff; border:1px solid #e9e9e7; border-radius:8px; }

/* ─── Medicine group toggle ─── */
.inv-generic-toggle {
    display:inline-flex; align-items:center; gap:8px;
    text-decoration:none; color:#37352f; cursor:pointer;
}
.inv-generic-toggle:hover { color:#1657c1; }
.medicine-chevron { font-size:.7rem; color:#9b9b9b; transition:transform .2s; }
.medicine-group-toggle[aria-expanded="true"] .medicine-chevron { transform:rotate(90deg); }
.inv-generic-name { font-size:.875rem; font-weight:600; }
.inv-brand-count {
    font-size:.65rem; font-weight:600;
    background:#f1f1ef; color:#787774;
    padding:1px 7px; border-radius:10px;
}
.inv-generic-desc { font-size:.75rem; color:#9b9b9b; margin-top:2px; }

.list-group-item { border-left:none; border-right:none; }
.list-group-item:first-child { border-top:none; border-radius:8px 8px 0 0; }
.list-group-item:last-child  { border-bottom:none; border-radius:0 0 8px 8px; }

/* ─── Badges ─── */
.inv-badge {
    display:inline-block; font-size:.68rem; font-weight:600;
    padding:2px 8px; border-radius:4px; white-space:nowrap;
}
.inv-badge.ok      { background:#dcfce7; color:#15803d; }
.inv-badge.warn    { background:#fef9c3; color:#a16207; }
.inv-badge.danger  { background:#fee2e2; color:#b91c1c; }
.inv-badge.neutral { background:#f1f1ef; color:#787774; }

/* ─── Chips ─── */
.inv-chip {
    display:inline-block; font-size:.7rem; font-weight:500;
    background:#f1f1ef; color:#787774;
    padding:2px 8px; border-radius:4px;
}

/* ─── Tables ─── */
.inv-table { border-collapse:collapse; }
.inv-table thead th {
    padding:8px 12px; background:#f8fafc;
    color:#6b7280; font-size:.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.4px;
    border-bottom:1px solid #e9e9e7;
    border-left:none; border-right:none;
    vertical-align:middle; white-space:nowrap;
}
.inv-table tbody tr:hover td { background:#fafaf9; }
.inv-table tbody td {
    padding:10px 12px; border-bottom:1px solid #f1f1ef;
    vertical-align:middle; font-size:.875rem; color:#37352f;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    border-left:none; border-right:none;
}
.inv-table tbody td:first-child { white-space:normal; }

.inv-inner-table { border-collapse:collapse; }
.inv-inner-table thead th {
    padding:6px 10px; background:#fafaf9;
    color:#9b9b9b; font-size:.68rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.3px;
    border-bottom:1px solid #f1f1ef;
    border-left:none; border-right:none;
    vertical-align:middle;
}
.inv-inner-table tbody td {
    padding:8px 10px; border-bottom:1px solid #f7f7f5;
    font-size:.82rem; color:#37352f; vertical-align:middle;
    border-left:none; border-right:none;
}

/* ─── Action buttons ─── */
.inv-action-btn {
    width:28px; height:28px; border-radius:4px; border:none;
    background:#f1f1ef; color:#787774;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:.75rem; cursor:pointer; transition:background .1s;
}
.inv-action-btn:hover       { background:#dbeafe; color:#1e40af; }
.inv-action-btn.danger:hover { background:#fee2e2; color:#b91c1c; }

/* ─── Sort/filter in thead ─── */
.inv-sort-link  { color:#b0b0a8; text-decoration:none; font-size:.7rem; transition:color .1s; }
.inv-sort-link:hover  { color:#37352f; }
.inv-filter-btn { background:none; border:none; padding:0; cursor:pointer; color:#b0b0a8; font-size:.7rem; transition:color .1s; }
.inv-filter-btn:hover { color:#37352f; }
.dropdown-toggle-no-arrow::after { display:none !important; }
.table-responsive { overflow:visible; }

/* ─── Filter dropdown ─── */
.inv-filter-dropdown {
    min-width:160px; border:1px solid #e9e9e7; border-radius:6px;
    box-shadow:0 4px 12px rgba(0,0,0,.1); padding:.3rem 0;
    background:#fff; z-index:2000;
}
.inv-filter-dropdown .dropdown-item-text {
    padding:.3rem 1rem; font-size:.8rem; color:#37352f;
    display:flex; align-items:center; cursor:pointer; transition:background .1s;
}
.inv-filter-dropdown .dropdown-item-text:hover { background:#fafaf9; }
.inv-filter-dropdown .dropdown-divider { margin:.2rem 0; border-color:#f1f1ef; }
.inv-filter-dropdown form { margin:0; }

/* ─── Item link ─── */
.inv-item-link { color:#37352f; text-decoration:none; font-weight:600; font-size:.875rem; }
.inv-item-link:hover { color:#1657c1; }

/* ─── Empty state ─── */
.inv-empty {
    text-align:center; padding:60px 20px;
    background:#fff; border:1px solid #e9e9e7; border-radius:8px; color:#9b9b9b;
}
.inv-empty i { font-size:2.5rem; display:block; margin-bottom:12px; }
.inv-empty-title { font-size:.95rem; font-weight:600; color:#37352f; margin-bottom:4px; }
.inv-empty-text  { font-size:.82rem; }

/* ─── Modal shell ─── */
.modal-header-custom {
    display:flex; align-items:flex-start; justify-content:space-between;
    background:#1657c1; padding:18px 22px;
}
.modal-type-badge {
    display:inline-block; font-size:.64rem; font-weight:600;
    letter-spacing:.6px; text-transform:uppercase;
    background:rgba(255,255,255,.12); color:rgba(255,255,255,.75);
    padding:2px 8px; border-radius:4px; margin-bottom:5px;
}
.modal-title-custom { font-size:1rem; font-weight:600; color:#fff; margin:0; }

/* ─── Modal form sections ─── */
.modal-form-section {
    background:#fafaf9; border-radius:6px;
    padding:16px; border:1px solid #f1f1ef;
}
.modal-section-title {
    font-size:.7rem; font-weight:700; letter-spacing:.4px;
    text-transform:uppercase; color:#787774; margin-bottom:12px;
}

/* ─── Pagination ─── */
.pagination .page-link {
    font-size:.8rem; color:#37352f;
    border:1px solid #e9e9e7; background:#fff; padding:4px 10px;
}
.pagination .page-item.active .page-link   { background:#1657c1; border-color:#1657c1; color:#fff; }
.pagination .page-item.disabled .page-link { color:#b0b0a8; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelectCreate = document.getElementById('type');
    const medicineFieldsCreate = document.getElementById('medicine_fields_create');
    const vaccineFieldsCreate  = document.getElementById('vaccine_fields_create');
    const batchFieldsCreate    = document.getElementById('batch_fields_create');

    function toggleCategoryFields() {
        if (!typeSelectCreate) return;
        const v = typeSelectCreate.value;
        if (medicineFieldsCreate) {
            medicineFieldsCreate.style.display = (v === 'Medicine') ? 'block' : 'none';
            if (medicineFieldsCreate.style.display === 'none') {
                ['generic_name','generic_description','milligrams','therapeutic_classification','prescription_required','restricted_medicine','storage_condition'].forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = '';
                });
            }
        }
        if (vaccineFieldsCreate) {
            vaccineFieldsCreate.style.display = (v === 'Vaccine') ? 'block' : 'none';
            if (vaccineFieldsCreate.style.display === 'none') {
                ['vaccine_type','vial_size','cold_chain_required','temperature_range'].forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = '';
                });
            }
        }
        if (batchFieldsCreate) {
            const show = (v === 'Medicine' || v === 'Vaccine');
            batchFieldsCreate.style.display = show ? 'block' : 'none';
            if (!show) {
                ['initial_lot_number','initial_expiration_date','initial_batch_notes'].forEach(f => {
                    const el = document.getElementById(f);
                    if (el) el.value = '';
                });
            }
        }
    }

    if (typeSelectCreate) {
        typeSelectCreate.addEventListener('change', toggleCategoryFields);
        toggleCategoryFields();
    }

    // Auto-generate item code when modal opens
    const addModal = document.getElementById('addItemModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            const codeInput = document.getElementById('item_code');
            if (codeInput) {
                const ts  = Date.now().toString(36).toUpperCase();
                const rnd = Math.random().toString(36).substring(2,5).toUpperCase();
                codeInput.value = `ITM-${ts}-${rnd}`;
            }
        });
    }

    @if($errors->any())
    new bootstrap.Modal(document.getElementById('addItemModal')).show();
    @endif

    // Edit modal type toggle
    document.querySelectorAll('[id^="edit_type_"]').forEach(function(select) {
        const id = select.id.replace('edit_type_', '');
        const container = document.getElementById('generic_fields_edit_' + id);
        function toggleGenericEdit() {
            const v = select.value;
            if (container) container.style.display = (v === 'Medicine' || v === 'Vaccine') ? 'block' : 'none';
            if (container && container.style.display === 'none') {
                ['edit_generic_name_','edit_generic_description_','edit_milligrams_'].forEach(p => {
                    const el = document.getElementById(p + id);
                    if (el) el.value = '';
                });
            }
        }
        select.addEventListener('change', toggleGenericEdit);
        toggleGenericEdit();
    });

    // Auto-dismiss toasts
    document.querySelectorAll('.toast').forEach(t => setTimeout(() => bootstrap.Toast.getOrCreateInstance(t).hide(), 4000));
});
</script>

@include('partials.delete-confirm-modal', ['modalId' => 'invDeleteModal'])
@endsection
