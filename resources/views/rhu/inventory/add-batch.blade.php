@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center mb-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Inventory
                </a>
                <h2 class="fw-bold text-dark mb-0">Add New Batch</h2>
            </div>
            <p class="text-muted mb-0">Add a new batch to track expiration dates</p>
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

    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Add Batch Form Card -->
    <div class="card shadow-sm border border-primary-subtle" style="border-width:2px;">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-plus-circle me-2"></i>Add New Batch
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.batches.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="parent_medicine_id" class="form-label fw-semibold">Select Medicine <span class="text-danger">*</span></label>
                        <select class="form-control @error('parent_medicine_id') is-invalid @enderror" id="parent_medicine_id" name="parent_medicine_id" required>
                            <option value="">Select a medicine...</option>
                            @foreach($allMedicines as $medicine)
                                <option value="{{ $medicine['id'] }}" {{ old('parent_medicine_id') == $medicine['id'] ? 'selected' : '' }}>
                                    {{ $medicine['name'] }} ({{ ucfirst($medicine['unit_type']) }}) - Current Stock: {{ $medicine['quantity'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_medicine_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" required placeholder="Enter quantity">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expiration_date" class="form-label fw-semibold">Expiration Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}" required>
                        @error('expiration_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Enter batch notes...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Add Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Available Medicines Info -->
    @if(count($allMedicines) > 0)
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Available Medicines
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($allMedicines as $medicine)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="fw-bold text-dark mb-1">{{ $medicine['name'] }}</h6>
                                <p class="text-muted mb-1">{{ $medicine['type'] }} • {{ ucfirst($medicine['unit_type']) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $medicine['status'] === 'available' ? 'success' : ($medicine['status'] === 'low_stock' ? 'warning' : 'danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $medicine['status'])) }}
                                    </span>
                                    <small class="text-muted">Stock: {{ $medicine['quantity'] }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-box display-4 d-block mb-3"></i>
                <h5>No medicines available</h5>
                <p class="mb-0">Please add medicines to your inventory first.</p>
            </div>
        </div>
    @endif
</div>

<style>
.card {
    border-radius: 0.75rem;
    overflow: hidden;
}

.form-control:focus {
    border-color: #1657c1;
    box-shadow: 0 0 0 0.2rem rgba(22, 87, 193, 0.25);
}

.btn {
    border-radius: 0.5rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>
@endsection 