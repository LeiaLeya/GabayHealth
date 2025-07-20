@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark mb-1">Inventory Management</h2>
            <p class="text-muted mb-0">Manage your health center's inventory items</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" action="" class="mb-0">
                <div class="input-group" style="max-width: 350px;">
                    <input type="text" name="search" class="form-control" placeholder="Search inventory..." value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
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

    @if($items->count())
        <!-- Inventory Table Card -->
        <div class="card shadow-sm border border-primary-subtle" style="border-width:2px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 inventory-table">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-4 py-3 fw-semibold">Item Name</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Type</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Unit</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Status</th>
                                <th class="border-0 px-4 py-3 fw-semibold">Description</th>
                                <th class="border-0 px-4 py-3 fw-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">{{ $item['type'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $item['unit'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badge = match($item['status']) {
                                                'Available' => 'success',
                                                'Low Stock' => 'warning',
                                                'Out of Stock' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ $item['status'] }}</span>
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

                                <!-- Edit Modal for this item -->
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
                                                            <label for="edit_unit_{{ $item['id'] }}" class="form-label fw-semibold">Unit</label>
                                                            <input type="text" class="form-control" id="edit_unit_{{ $item['id'] }}" name="unit" value="{{ $item['unit'] }}" placeholder="e.g., pieces, bottles, boxes">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="edit_status_{{ $item['id'] }}" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="edit_status_{{ $item['id'] }}" name="status" required>
                                                                <option value="Available" {{ $item['status'] == 'Available' ? 'selected' : '' }}>Available</option>
                                                                <option value="Low Stock" {{ $item['status'] == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                                                                <option value="Out of Stock" {{ $item['status'] == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_description_{{ $item['id'] }}" class="form-label fw-semibold">Description</label>
                                                        <textarea class="form-control" id="edit_description_{{ $item['id'] }}" name="description" rows="3" placeholder="Enter item description...">{{ $item['description'] }}</textarea>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <h5>No inventory items found</h5>
                <p class="mb-0">Start by adding your first inventory item using the button above.</p>
            </div>
        </div>
    @endif
    <!-- Pagination -->
    <div class="d-flex justify-content-center my-4">
        {{ $items->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
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
                            <label for="unit" class="form-label fw-semibold">Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" placeholder="e.g., pieces, bottles, boxes">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select status...</option>
                                <option value="Available">Available</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
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
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
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
</style>
@endsection
