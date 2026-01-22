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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Add New Staff Member</h4>
                    <a href="{{ route('rhu.accounts.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to Account Management
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('rhu.accounts.staff.store') }}" method="POST">
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
                                
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" 
                                           id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" name="password_confirmation" required>
                                    @error('password_confirmation')
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
    // Auto-generate email from full name
    function generateEmailFromName(fullName) {
        if (!fullName || fullName.trim() === '') {
            return '';
        }
        
        // Split name into parts and filter out empty strings
        const nameParts = fullName.trim().split(/\s+/).filter(part => part.length > 0);
        
        if (nameParts.length === 0) {
            return '';
        }
        
        // Get first name (first part)
        const firstName = nameParts[0].toLowerCase().replace(/[^a-z]/g, '');
        
        // Get last name (last part)
        const lastName = nameParts[nameParts.length - 1].toLowerCase().replace(/[^a-z]/g, '');
        
        // Get current year's last two digits
        const yearSuffix = new Date().getFullYear().toString().slice(-2);
        
        // Generate email: firstname.lastname.26@gabay-health.local
        if (firstName && lastName) {
            return `${firstName}.${lastName}.${yearSuffix}@gabay-health.local`;
        } else if (firstName) {
            return `${firstName}.${yearSuffix}@gabay-health.local`;
        }
        
        return '';
    }
    
    // Auto-fill email when name changes
    document.getElementById('name').addEventListener('input', function() {
        const emailField = document.getElementById('email');
        const fullName = this.value;
        
        // Only auto-fill if email field is empty or matches the old pattern
        if (!emailField.value || emailField.value.includes('@gabay-health.local')) {
            const generatedEmail = generateEmailFromName(fullName);
            if (generatedEmail) {
                emailField.value = generatedEmail;
            }
        }
    });
    
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
        
        if (role === 'doctor') {
            specializationField.style.display = 'block';
        } else {
            specializationField.style.display = 'none';
            specializationInput.value = ''; // Clear value when hidden
        }
    });

    // Check on page load if role is already selected
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        if (roleSelect.value === 'doctor') {
            document.getElementById('specialization_field').style.display = 'block';
        }
    });
</script>
@endpush
@endsection 