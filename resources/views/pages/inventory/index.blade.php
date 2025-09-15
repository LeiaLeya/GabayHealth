@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1">Inventory Management</h2>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="{{ route('inventory.index') }}" class="mb-0">
                <div class="input-group" style="max-width: 350px;">
                    <input type="text" name="search" class="form-control" placeholder="Search inventory..." value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(!empty($search))
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-circle"></i>
                Add New Item
            </button>
        </div>
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

    @if(count($items) > 0)
        <!-- Inventory Table Card -->
        <div class="card shadow-sm border border-primary-subtle" style="border-width:2px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 inventory-table">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">Item Name</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Type</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Quantity</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Unit Type</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Status</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Description</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
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
        @if($items->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Inventory pagination">
                    <ul class="pagination justify-content-center mb-0">
                        {{-- Previous Page Link --}}
                        @if ($items->onFirstPage())
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $items->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                            @if ($page == $items->currentPage())
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
                        @if ($items->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $items->nextPageUrl() }}" rel="next">Next</a>
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
@if(count($items) > 0)
    @foreach($items as $item)
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .h5 {
        font-size: 1.5rem;
    }
}
</style>
@endsection
