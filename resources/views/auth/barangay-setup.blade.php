@extends('layouts.app')
@php($hideSidebar = true)

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Set Your Password</h4>
                </div>
                
                <div class="card-body">
                    <p class="text-muted mb-4">Your Barangay Health Center account has been approved. Please set a secure password to activate your account.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <h5>Setup Failed</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('barangay.setup-password.store') }}">
                        @csrf
                        
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" value="{{ $email }}" disabled>
                            <small class="text-muted">This is the email address registered for your account.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            <small class="text-muted">Minimum 8 characters. Use a combination of uppercase, lowercase, numbers, and symbols for security.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info" role="alert">
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>At least 8 characters long</li>
                                <li>Mix of uppercase and lowercase letters</li>
                                <li>Include numbers and special characters</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Activate Account & Set Password
                        </button>
                    </form>

                    <hr>

                    <p class="text-center text-muted small mb-0">
                        After setting your password, you'll be able to login immediately using your username and new password.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 8px;
    }
    
    .card-header {
        border-radius: 8px 8px 0 0;
    }
    
    .btn-success {
        background: #28a745;
        border-color: #28a745;
    }
    
    .btn-success:hover {
        background: #218838;
        border-color: #218838;
    }
</style>
@endsection
