@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark mb-0">Inventory Management</h2>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-circle"></i>
                Add New Item
            </button>
        </div>
        <form method="GET" action="{{ route('inventory.index') }}" class="mb-0" id="searchForm">
            <div class="search-container">
                <div class="input-group search-input-group">
                    <span class="input-group-text search-icon-wrapper">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control search-input" placeholder="Search inventory by name, type, or description..." value="{{ $search ?? '' }}" autocomplete="off">
                    @if(!empty($search))
                        <a href="{{ route('inventory.index') }}" class="input-group-text search-clear-btn" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                    <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
                    <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
                    <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                    <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                </div>
            </div>
        </form>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Inventory Summary Cards -->
    <div class="row mb-4">
        <!-- Total Items -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                            Total Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-dark">{{ $inventorySummary['total_items'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Items -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                            Available Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-dark">{{ $inventorySummary['available'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                            Low Stock
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-dark">{{ $inventorySummary['low_stock'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                            Out of Stock
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-dark">{{ $inventorySummary['out_of_stock'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($medicineGroups) && count($medicineGroups) > 0)
        <div class="card shadow-sm border border-success-subtle inventory-table-card mb-4" style="border-width:2px;">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Medicines & Vaccines</h5>
                <div class="text-muted small">Grouped by Generic Name. Click a generic name to see brands.</div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($medicineGroups as $group)
                        @php
                            $collapseId = 'gen_' . md5($group['generic_name']);
                            $expDays = $group['soonest_expiration_days'] ?? null;
                        @endphp
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <a class="fw-semibold text-decoration-none" data-bs-toggle="collapse" href="#{{ $collapseId }}" role="button" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        {{ $group['generic_name'] }}
                                    </a>
                                    @if(!empty($group['description']))
                                        <div class="text-muted small">{{ $group['description'] }}</div>
                                    @endif
                                </div>
                                <div class="text-nowrap">
                                    @if(is_null($expDays))
                                        <span class="badge bg-secondary">No Expiry</span>
                                    @elseif($expDays < 0)
                                        <span class="badge bg-danger">Expired {{ abs($expDays) }}d</span>
                                    @elseif($expDays === 0)
                                        <span class="badge bg-danger">Expires Today</span>
                                    @elseif($expDays <= 30)
                                        <span class="badge bg-warning text-dark">{{ $expDays }}d left</span>
                                    @else
                                        <span class="badge bg-success">{{ $expDays }}d left</span>
                                    @endif
                                </div>
                            </div>
                            <div class="collapse mt-3" id="{{ $collapseId }}">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 22%;">Brand Name</th>
                                                <th style="width: 10%;">Dosage (mg)</th>
                                                <th style="width: 9%;">Quantity</th>
                                                <th style="width: 11%;">Unit</th>
                                                <th style="width: 12%;">Status</th>
                                                <th style="width: 12%;">Expiration</th>
                                                <th style="width: 18%;">Description</th>
                                                <th class="text-end" style="width: 6%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['items'] as $brand)
                                                @php
                                                    $badge = match($brand['status']) {
                                                        'available' => 'success',
                                                        'low_stock' => 'warning',
                                                        'out_of_stock' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    $displayStatus = ucwords(str_replace('_', ' ', $brand['status'] ?? ''));
                                                    $days = $brand['soonest_expiration_days'] ?? null;
                                                @endphp
                                                <tr>
                                                    <td class="fw-semibold">
                                                        <a href="{{ route('inventory.show', $brand['id']) }}" class="text-decoration-none">
                                                            {{ $brand['name'] }}
                                                            <i class="bi bi-arrow-right-circle ms-2 text-primary"></i>
                                                        </a>
                                                    </td>
                                                    <td>{{ $brand['milligrams'] ?? '—' }}</td>
                                                    <td>{{ $brand['quantity'] ?? 'N/A' }}</td>
                                                    <td>{{ ucfirst($brand['unit_type'] ?? 'N/A') }}</td>
                                                    <td><span class="badge bg-{{ $badge }}">{{ $displayStatus }}</span></td>
                                                    <td>
                                                        @if(is_null($days))
                                                            <span class="text-muted">N/A</span>
                                                        @elseif($days < 0)
                                                            <span class="badge bg-danger">Expired {{ abs($days) }}d</span>
                                                        @elseif($days === 0)
                                                            <span class="badge bg-danger">Expires Today</span>
                                                        @elseif($days <= 30)
                                                            <span class="badge bg-warning text-dark">{{ $days }}d left</span>
                                                        @else
                                                            <span class="badge bg-success">{{ $days }}d left</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ Str::limit($brand['description'] ?? '—', 80) }}</td>
                                                    <td class="text-end">
                                                        <div class="btn-group" role="group" style="gap: 0.3rem;">
                                                            <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $brand['id'] }}" title="Edit Item">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form action="{{ route('inventory.destroy', $brand['id']) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" onclick="return confirm('Are you sure you want to delete this item?')" title="Delete Item">
                                                                    <i class="bi bi-trash"></i>
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
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(count($materials) > 0)
        <!-- Inventory Table Card -->
        <div class="card shadow-sm border border-primary-subtle inventory-table-card" style="border-width:2px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 inventory-table" style="table-layout: fixed; width: 100%;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 18%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Item Name</span>
                                        @php
                                            $isNameDesc = (($sortBy ?? '') === 'name') && (($sortDir ?? 'asc') === 'desc');
                                            $nextDir = $isNameDesc ? 'asc' : 'desc';
                                            $nameToggleUrl = request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => $nextDir, 'page' => 1]);
                                        @endphp
                                        <a href="{{ $nameToggleUrl }}" class="text-decoration-none text-muted" title="Toggle sort">
                                            <i class="bi {{ $isNameDesc ? 'bi-arrow-down' : 'bi-arrow-up' }} text-primary" style="font-size: 0.8rem;"></i>
                                        </a>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 11%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Type</span>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-muted dropdown-toggle-no-arrow" data-bs-toggle="dropdown" aria-expanded="false" title="Filter Type">
                                                <i class="bi bi-funnel{{ !empty($filterType) ? '-fill text-primary' : '' }}" style="font-size: 0.75rem;"></i>
                                            </button>
                                                <ul class="dropdown-menu dropdown-menu-end filter-dropdown">
                                                <form method="GET" action="{{ route('inventory.index') }}" id="typeFilterForm">
                                                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                    <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
                                                    <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
                                                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                    <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="" class="form-check-input me-2" {{ empty($filterType) ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            All
                                                        </label>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="Medicine" class="form-check-input me-2" {{ ($filterType ?? '') == 'Medicine' ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            Medicine
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="Equipment" class="form-check-input me-2" {{ ($filterType ?? '') == 'Equipment' ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            Equipment
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="Supplies" class="form-check-input me-2" {{ ($filterType ?? '') == 'Supplies' ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            Supplies
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="Vaccine" class="form-check-input me-2" {{ ($filterType ?? '') == 'Vaccine' ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            Vaccine
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="type" value="Other" class="form-check-input me-2" {{ ($filterType ?? '') == 'Other' ? 'checked' : '' }} onchange="document.getElementById('typeFilterForm').submit();">
                                                            Other
                                                        </label>
                                                    </li>
                                                </form>
                                            </ul>
                                        </div>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 10%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Quantity</span>
                                        @php
                                            $isQtyDesc = (($sortBy ?? '') === 'quantity') && (($sortDir ?? 'asc') === 'desc');
                                            $qtyNextDir = $isQtyDesc ? 'asc' : 'desc';
                                            $qtyToggleUrl = request()->fullUrlWithQuery(['sort_by' => 'quantity', 'sort_dir' => $qtyNextDir, 'page' => 1]);
                                        @endphp
                                        <a href="{{ $qtyToggleUrl }}" class="text-decoration-none text-muted" title="Toggle sort">
                                            <i class="bi {{ $isQtyDesc ? 'bi-arrow-down' : 'bi-arrow-up' }} text-primary" style="font-size: 0.8rem;"></i>
                                        </a>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 11%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Unit Type</span>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-muted dropdown-toggle-no-arrow" data-bs-toggle="dropdown" aria-expanded="false" title="Filter Unit Type">
                                                <i class="bi bi-funnel{{ !empty($filterUnitType) ? '-fill text-primary' : '' }}" style="font-size: 0.75rem;"></i>
                                            </button>
                                                <ul class="dropdown-menu dropdown-menu-end filter-dropdown">
                                                <form method="GET" action="{{ route('inventory.index') }}" id="unitTypeFilterForm">
                                                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                    <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
                                                    <input type="hidden" name="status" value="{{ $filterStatus ?? '' }}">
                                                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                    <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="" class="form-check-input me-2" {{ empty($filterUnitType) ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            All
                                                        </label>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="capsules" class="form-check-input me-2" {{ ($filterUnitType ?? '') == 'capsules' ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            Capsules
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="tablets" class="form-check-input me-2" {{ ($filterUnitType ?? '') == 'tablets' ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            Tablets
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="pieces" class="form-check-input me-2" {{ ($filterUnitType ?? '') == 'pieces' ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            Pieces
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="boxes" class="form-check-input me-2" {{ ($filterUnitType ?? '') == 'boxes' ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            Boxes
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="unit_type" value="packs" class="form-check-input me-2" {{ ($filterUnitType ?? '') == 'packs' ? 'checked' : '' }} onchange="document.getElementById('unitTypeFilterForm').submit();">
                                                            Packs
                                                        </label>
                                                    </li>
                                                </form>
                                            </ul>
                                        </div>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 11%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Status</span>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-muted dropdown-toggle-no-arrow" data-bs-toggle="dropdown" aria-expanded="false" title="Filter Status">
                                                <i class="bi bi-funnel{{ !empty($filterStatus) ? '-fill text-primary' : '' }}" style="font-size: 0.75rem;"></i>
                                            </button>
                                                <ul class="dropdown-menu dropdown-menu-end filter-dropdown">
                                                <form method="GET" action="{{ route('inventory.index') }}" id="statusFilterForm">
                                                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                                    <input type="hidden" name="type" value="{{ $filterType ?? '' }}">
                                                    <input type="hidden" name="unit_type" value="{{ $filterUnitType ?? '' }}">
                                                    <input type="hidden" name="sort_by" value="{{ $sortBy ?? '' }}">
                                                    <input type="hidden" name="sort_dir" value="{{ $sortDir ?? '' }}">
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="status" value="" class="form-check-input me-2" {{ empty($filterStatus) ? 'checked' : '' }} onchange="document.getElementById('statusFilterForm').submit();">
                                                            All
                                                        </label>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="status" value="available" class="form-check-input me-2" {{ ($filterStatus ?? '') == 'available' ? 'checked' : '' }} onchange="document.getElementById('statusFilterForm').submit();">
                                                            Available
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="status" value="low_stock" class="form-check-input me-2" {{ ($filterStatus ?? '') == 'low_stock' ? 'checked' : '' }} onchange="document.getElementById('statusFilterForm').submit();">
                                                            Low Stock
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="dropdown-item-text">
                                                            <input type="radio" name="status" value="out_of_stock" class="form-check-input me-2" {{ ($filterStatus ?? '') == 'out_of_stock' ? 'checked' : '' }} onchange="document.getElementById('statusFilterForm').submit();">
                                                            Out of Stock
                                                        </label>
                                                    </li>
                                                </form>
                                            </ul>
                                        </div>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold position-relative" style="width: 11%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Expiration</span>
                                    </div>
                                </th>
                                <th class="border-0 px-4 py-3 fw-semibold" style="width: 18%;">Description</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-end" style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $item)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">
                                            <a href="{{ route('inventory.show', $item['id']) }}" class="text-decoration-none">
                                                {{ $item['name'] }}
                                                <i class="bi bi-arrow-right-circle ms-2 text-primary"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">{{ $item['type'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold text-dark">{{ $item['quantity'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">{{ ucfirst($item['unit_type'] ?? 'N/A') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badge = match($item['status']) {
                                                'available' => 'success',
                                                'low_stock' => 'warning',
                                                'out_of_stock' => 'danger',
                                                default => 'secondary'
                                            };
                                            $displayStatus = ucwords(str_replace('_', ' ', $item['status']));
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ $displayStatus }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $expirationDays = $item['soonest_expiration_days'] ?? null;
                                        @endphp
                                        @if(is_null($expirationDays))
                                            <span class="text-muted">N/A</span>
                                        @elseif($expirationDays < 0)
                                            <span class="badge bg-danger">Expired {{ abs($expirationDays) }}d</span>
                                        @elseif($expirationDays === 0)
                                            <span class="badge bg-danger">Expires Today</span>
                                        @elseif($expirationDays <= 30)
                                            <span class="badge bg-warning text-dark">{{ $expirationDays }}d left</span>
                                        @else
                                            <span class="badge bg-success">{{ $expirationDays }}d left</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-muted" style="max-width: 200px;">
                                            {{ Str::limit($item['description'] ?? 'No description', 50) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group" role="group" style="gap: 0.4rem;">
                                            <button class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item['id'] }}" title="Edit Item">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('inventory.destroy', $item['id']) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" onclick="return confirm('Are you sure you want to delete this item?')" title="Delete Item">
                                                    <i class="bi bi-trash"></i>
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

        <!-- Pagination -->
        @if($materials->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Inventory pagination">
                    <ul class="pagination justify-content-center mb-0">
                        {{-- Previous Page Link --}}
                        @if ($materials->onFirstPage())
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $materials->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($materials->getUrlRange(1, $materials->lastPage()) as $page => $url)
                            @if ($page == $materials->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($materials->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $materials->nextPageUrl() }}" rel="next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <h5>No inventory items found</h5>
                @if(!empty($search))
                    <p class="mb-0">No items found matching "{{ $search }}". Try a different search term or <a href="{{ route('inventory.index') }}" class="text-decoration-none">view all items</a>.</p>
                @else
                    <p class="mb-0">Start by adding your first inventory item using the button above.</p>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Enter item name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select type...</option>
                                <option value="Medicine">Medicine</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Vaccine">Vaccine</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="generic_fields_create" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <label for="generic_name" class="form-label fw-semibold">Generic Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="generic_name" name="generic_name" placeholder="e.g., Paracetamol">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="generic_description" class="form-label fw-semibold">Generic Description</label>
                            <input type="text" class="form-control" id="generic_description" name="generic_description" placeholder="e.g., For fever and mild pain">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="milligrams" class="form-label fw-semibold">Dosage (mg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="milligrams" name="milligrams" placeholder="e.g., 250">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required placeholder="Enter quantity">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit_type" class="form-label fw-semibold">Unit Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="unit_type" name="unit_type" required>
                                <option value="">Select unit type...</option>
                                <option value="capsules">Capsules</option>
                                <option value="tablets">Tablets</option>
                                <option value="pieces">Pieces</option>
                                <option value="boxes">Boxes</option>
                                <option value="packs">Packs</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter item description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modals (Outside Table Structure) -->
@if(isset($materials) && count($materials) > 0)
    @foreach($materials as $item)
        <div class="modal fade" id="editItemModal{{ $item['id'] }}" tabindex="-1" aria-labelledby="editItemModalLabel{{ $item['id'] }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editItemModalLabel{{ $item['id'] }}">
                            <i class="bi bi-pencil-square me-2"></i>Edit Item
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('inventory.update', $item['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_name_{{ $item['id'] }}" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name_{{ $item['id'] }}" name="name" value="{{ $item['name'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_type_{{ $item['id'] }}" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_type_{{ $item['id'] }}" name="type" required>
                                        <option value="Medicine" {{ $item['type'] == 'Medicine' ? 'selected' : '' }}>Medicine</option>
                                        <option value="Equipment" {{ $item['type'] == 'Equipment' ? 'selected' : '' }}>Equipment</option>
                                        <option value="Supplies" {{ $item['type'] == 'Supplies' ? 'selected' : '' }}>Supplies</option>
                                        <option value="Vaccine" {{ $item['type'] == 'Vaccine' ? 'selected' : '' }}>Vaccine</option>
                                        <option value="Other" {{ $item['type'] == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row generic-fields-edit" id="generic_fields_edit_{{ $item['id'] }}" style="display: {{ in_array($item['type'], ['Medicine','Vaccine']) ? 'flex' : 'none' }};">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_generic_name_{{ $item['id'] }}" class="form-label fw-semibold">Generic Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_generic_name_{{ $item['id'] }}" name="generic_name" value="{{ $item['generic_name'] ?? '' }}" placeholder="e.g., Paracetamol">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_generic_description_{{ $item['id'] }}" class="form-label fw-semibold">Generic Description</label>
                                    <input type="text" class="form-control" id="edit_generic_description_{{ $item['id'] }}" name="generic_description" value="{{ $item['generic_description'] ?? '' }}" placeholder="e.g., For fever and mild pain">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_milligrams_{{ $item['id'] }}" class="form-label fw-semibold">Dosage (mg) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_milligrams_{{ $item['id'] }}" name="milligrams" value="{{ $item['milligrams'] ?? '' }}" placeholder="e.g., 250">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_quantity_{{ $item['id'] }}" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_quantity_{{ $item['id'] }}" name="quantity" value="{{ $item['quantity'] ?? 0 }}" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_unit_type_{{ $item['id'] }}" class="form-label fw-semibold">Unit Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="edit_unit_type_{{ $item['id'] }}" name="unit_type" required>
                                        <option value="capsules" {{ ($item['unit_type'] ?? '') == 'capsules' ? 'selected' : '' }}>Capsules</option>
                                        <option value="tablets" {{ ($item['unit_type'] ?? '') == 'tablets' ? 'selected' : '' }}>Tablets</option>
                                        <option value="pieces" {{ ($item['unit_type'] ?? '') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                        <option value="boxes" {{ ($item['unit_type'] ?? '') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                        <option value="packs" {{ ($item['unit_type'] ?? '') == 'packs' ? 'selected' : '' }}>Packs</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description_{{ $item['id'] }}" class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" id="edit_description_{{ $item['id'] }}" name="description" rows="3">{{ $item['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

@if(!empty($medicineGroups))
    @foreach($medicineGroups as $group)
        @foreach($group['items'] as $item)
            <div class="modal fade" id="editItemModal{{ $item['id'] }}" tabindex="-1" aria-labelledby="editItemModalLabel{{ $item['id'] }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editItemModalLabel{{ $item['id'] }}">
                                <i class="bi bi-pencil-square me-2"></i>Edit Item
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('inventory.update', $item['id']) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_name_{{ $item['id'] }}" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_name_{{ $item['id'] }}" name="name" value="{{ $item['name'] }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_type_{{ $item['id'] }}" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_type_{{ $item['id'] }}" name="type" required>
                                            <option value="Medicine" {{ ($item['type'] ?? '') == 'Medicine' ? 'selected' : '' }}>Medicine</option>
                                            <option value="Equipment" {{ ($item['type'] ?? '') == 'Equipment' ? 'selected' : '' }}>Equipment</option>
                                            <option value="Supplies" {{ ($item['type'] ?? '') == 'Supplies' ? 'selected' : '' }}>Supplies</option>
                                            <option value="Vaccine" {{ ($item['type'] ?? '') == 'Vaccine' ? 'selected' : '' }}>Vaccine</option>
                                            <option value="Other" {{ ($item['type'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row generic-fields-edit" id="generic_fields_edit_{{ $item['id'] }}" style="display: {{ in_array(($item['type'] ?? ''), ['Medicine','Vaccine']) ? 'flex' : 'none' }};">
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_generic_name_{{ $item['id'] }}" class="form-label fw-semibold">Generic Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_generic_name_{{ $item['id'] }}" name="generic_name" value="{{ $item['generic_name'] ?? '' }}" placeholder="e.g., Paracetamol">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_generic_description_{{ $item['id'] }}" class="form-label fw-semibold">Generic Description</label>
                                        <input type="text" class="form-control" id="edit_generic_description_{{ $item['id'] }}" name="generic_description" value="{{ $item['generic_description'] ?? '' }}" placeholder="e.g., For fever and mild pain">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_milligrams_{{ $item['id'] }}" class="form-label fw-semibold">Dosage (mg) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control" id="edit_milligrams_{{ $item['id'] }}" name="milligrams" value="{{ $item['milligrams'] ?? '' }}" placeholder="e.g., 250">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_quantity_{{ $item['id'] }}" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="edit_quantity_{{ $item['id'] }}" name="quantity" value="{{ $item['quantity'] ?? 0 }}" min="0" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_unit_type_{{ $item['id'] }}" class="form-label fw-semibold">Unit Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_unit_type_{{ $item['id'] }}" name="unit_type" required>
                                            <option value="capsules" {{ ($item['unit_type'] ?? '') == 'capsules' ? 'selected' : '' }}>Capsules</option>
                                            <option value="tablets" {{ ($item['unit_type'] ?? '') == 'tablets' ? 'selected' : '' }}>Tablets</option>
                                            <option value="pieces" {{ ($item['unit_type'] ?? '') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                                            <option value="boxes" {{ ($item['unit_type'] ?? '') == 'boxes' ? 'selected' : '' }}>Boxes</option>
                                            <option value="packs" {{ ($item['unit_type'] ?? '') == 'packs' ? 'selected' : '' }}>Packs</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description_{{ $item['id'] }}" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" id="edit_description_{{ $item['id'] }}" name="description" rows="3">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Update Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endif

<style>
.table th, .table td {
    vertical-align: middle;
    background: #fff;
    font-size: 1rem;
}
.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #e9ecef;
}
.table tr {
    border-radius: 0.5rem;
}
.table tbody tr {
    border-top: none;
    border-bottom: 1px solid #f1f1f1;
}

.btn-group .btn {
    border-radius: 0.5rem !important;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    color: #495057;
    margin-bottom: 0.5rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.card {
    border-radius: 0.75rem;
    overflow: hidden;
    border: 2px solid #1657c1 !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: all 0.2s ease;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Summary Cards Styling */
.text-xs {
    font-size: 0.7rem;
}

.text-dark {
    color: #212529 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Summary Cards Hover Effects */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transition: all 0.2s ease;
}

/* Card Body Enhancements */
.card-body {
    padding: 1.25rem;
}

/* Text Enhancements */
.text-uppercase {
    letter-spacing: 0.5px;
    font-weight: 600;
}

.h5 {
    font-size: 1.75rem;
    font-weight: 700;
}

/* Search Bar Styling */
.search-container {
    max-width: 600px;
}

.search-input-group {
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.search-input-group:focus-within {
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    border-color: #0d6efd;
}

.search-icon-wrapper {
    background-color: #f8f9fa;
    border: none;
    border-right: 1px solid #e9ecef;
    color: #6c757d;
    padding: 0.75rem 1rem;
}

.search-input {
    border: none;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    background-color: #fff;
}

.search-input:focus {
    box-shadow: none;
    border: none;
    background-color: #fff;
}

.search-input::placeholder {
    color: #adb5bd;
}

.search-clear-btn {
    background-color: #fff;
    border: none;
    border-left: 1px solid #e9ecef;
    color: #6c757d;
    padding: 0.75rem 1rem;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.search-clear-btn:hover {
    background-color: #f8f9fa;
    color: #dc3545;
}

/* Add visible outline inside the table */
.inventory-table th, .inventory-table td {
    border-left: none !important;
    border-right: none !important;
    background: #fff;
}
.inventory-table thead th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #1657c1 !important;
}
.inventory-table tr {
    border-radius: 0.5rem;
}
.inventory-table tbody tr {
    border-top: none;
    border-bottom: 1.5px solid #b6c6e3 !important;
}

/* Table Stability */
.inventory-table {
    table-layout: fixed;
}

.inventory-table td {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.inventory-table th {
    overflow: visible; /* allow dropdowns to extend outside header cell */
    position: relative;
}

.inventory-table td:first-child {
    white-space: normal;
    word-wrap: break-word;
}

.inventory-table td:nth-child(6) {
    white-space: normal;
    word-wrap: break-word;
    overflow: visible;
}

/* Ensure dropdowns are not clipped by the responsive wrapper */
.table-responsive {
    overflow: visible; /* allow dropdowns to extend beyond header */
}

/* Make sure dropdown overlays table rows */
.inventory-table thead { z-index: 2; position: relative; }
.filter-dropdown { z-index: 2000; }

/* Override the generic .card overflow for the inventory table card only */
.inventory-table-card {
    overflow: hidden; /* ensure rounded corners clip inner content */
}

/* Filter Dropdown Styles */
.filter-dropdown {
    min-width: 180px;
    padding: 0.5rem 0;
    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.15);
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-top: 0.25rem;
    background: white;
}

.filter-dropdown .dropdown-item-text {
    padding: 0.375rem 1rem;
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #495057;
    transition: background-color 0.15s ease;
}

.filter-dropdown .dropdown-item-text:hover {
    background-color: #f8f9fa;
}

.filter-dropdown .form-check-input {
    margin-top: 0;
    cursor: pointer;
}

.filter-dropdown .dropdown-divider {
    margin: 0.25rem 0;
}

.filter-dropdown form {
    margin: 0;
}

/* Sort arrow styles */
th a i {
    transition: color 0.15s ease;
}

th a:hover i {
    color: #0d6efd !important;
}

/* Filter button hover */
th button:hover {
    background-color: #f8f9fa !important;
    border-radius: 0.25rem;
}

/* Hide default dropdown arrow */
.dropdown-toggle-no-arrow::after {
    display: none !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .h5 {
        font-size: 1.5rem;
    }
    
    .filter-dropdown {
        min-width: 160px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelectCreate = document.getElementById('type');
    const genericFieldsCreate = document.getElementById('generic_fields_create');
    function toggleGenericCreate() {
        if (!typeSelectCreate || !genericFieldsCreate) return;
        const v = typeSelectCreate.value;
        genericFieldsCreate.style.display = (v === 'Medicine' || v === 'Vaccine') ? 'flex' : 'none';
        if (genericFieldsCreate.style.display === 'none') {
            const g = document.getElementById('generic_name');
            const d = document.getElementById('generic_description');
            if (g) g.value = '';
            if (d) d.value = '';
        }
    }
    if (typeSelectCreate) {
        typeSelectCreate.addEventListener('change', toggleGenericCreate);
        toggleGenericCreate();
    }

    document.querySelectorAll('[id^="edit_type_"]').forEach(function(select) {
        const id = select.id.replace('edit_type_', '');
        const container = document.getElementById('generic_fields_edit_' + id);
        function toggleGenericEdit() {
            const v = select.value;
            if (container) container.style.display = (v === 'Medicine' || v === 'Vaccine') ? 'flex' : 'none';
            if (container && container.style.display === 'none') {
                const g = document.getElementById('edit_generic_name_' + id);
                const d = document.getElementById('edit_generic_description_' + id);
                if (g) g.value = '';
                if (d) d.value = '';
            }
        }
        select.addEventListener('change', toggleGenericEdit);
        toggleGenericEdit();
    });
});
</script>
@endsection
