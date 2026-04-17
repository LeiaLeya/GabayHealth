@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark mb-0">Add New Staff Member</h2>
            <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                Back to Account Management
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Staff Information</h5>
                </div>
                <div class="card-body">
                    
                    <form action="{{ route('accounts.staff.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" 
                                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password *</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" name="password" required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Min. 6 characters</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                                   id="password_confirmation" name="password_confirmation" required>
                                            @error('password_confirmation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role *</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>Doctor</option>
                                        <option value="midwife" {{ old('role') === 'midwife' ? 'selected' : '' }}>Midwife</option>
                                        <option value="nurse" {{ old('role') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                                        <option value="bhw" {{ old('role') === 'bhw' ? 'selected' : '' }}>Barangay Health Worker</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3" id="specialization_field" style="display: none;">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                           id="specialization" name="specialization" value="{{ old('specialization') }}" 
                                           placeholder="e.g., Pediatrics, Obstetrics, General Medicine">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        

                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle"></i>
                                Create Staff Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Password confirmation validation
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    // Show/hide specialization field based on role selection
    document.getElementById('role').addEventListener('change', function() {
        const role = this.value;
        const specializationField = document.getElementById('specialization_field');
        const specializationInput = document.getElementById('specialization');
        
        if (role === 'doctor' || role === 'nurse') {
            specializationField.style.display = 'block';
        } else {
            specializationField.style.display = 'none';
            specializationInput.value = ''; // Clear value when hidden
        }
    });

    // Check on page load if role is already selected
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        if (roleSelect.value === 'doctor' || roleSelect.value === 'nurse') {
            document.getElementById('specialization_field').style.display = 'block';
        }
    });
</script>
@endpush
@endsection 