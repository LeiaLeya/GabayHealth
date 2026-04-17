@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark mb-0">Edit Health Center Profile</h2>
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit Health Center Profile</h4>
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
                    
                    <form action="{{ route('rhu.accounts.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="healthCenterName" class="form-label">Health Center Name *</label>
                                    <input type="text" class="form-control @error('healthCenterName') is-invalid @enderror" 
                                           id="healthCenterName" name="healthCenterName" value="{{ old('healthCenterName', $healthCenter['healthCenterName'] ?? '') }}" required>
                                    @error('healthCenterName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" 
                                           id="contact_number" name="contact_number" 
                                           value="{{ old('contact_number', $healthCenter['contact_number'] ?? $healthCenter['contactInfo'] ?? '') }}" required>
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $healthCenter['email'] ?? '') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Complete Address *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" required>{{ old('address', $healthCenter['address'] ?? $healthCenter['fullAddress'] ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Open Days *</label>
                                    <div class="day-selector mb-2">
                                        @php
                                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                            $selectedDays = old('open_days', $healthCenter['open_days'] ?? []);
                                            
                                            // Handle both array and string formats
                                            if (is_string($selectedDays)) {
                                                $selectedDays = explode(',', $selectedDays);
                                            }
                                        @endphp
                                        @foreach($days as $day)
                                            <input type="checkbox" class="btn-check" name="open_days[]" 
                                                   id="day_{{ $day }}" value="{{ $day }}" 
                                                   {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm day-btn" for="day_{{ $day }}">
                                                {{ $day }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('open_days')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="open_time" class="form-label">Opening Time *</label>
                                            <input type="time" class="form-control @error('open_time') is-invalid @enderror" 
                                                   id="open_time" name="open_time" 
                                                   value="{{ old('open_time', $healthCenter['open_time'] ?? '') }}" required>
                                            @error('open_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="close_time" class="form-label">Closing Time *</label>
                                            <input type="time" class="form-control @error('close_time') is-invalid @enderror" 
                                                   id="close_time" name="close_time" 
                                                   value="{{ old('close_time', $healthCenter['close_time'] ?? '') }}" required>
                                            @error('close_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('rhu.accounts.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning d-flex align-items-center gap-2">
                                <i class="bi bi-key"></i>
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.day-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.day-btn {
    width: 50px;
    height: 50px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.3s ease;
    border: 2px solid #dee2e6;
}

.btn-check:checked + .day-btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}

.day-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

    .btn-check:focus + .day-btn {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>

@push('scripts')
<script>
    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        
        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
</script>
@endpush
@endsection 