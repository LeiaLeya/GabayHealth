@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Rural Health Unit Registration</h4>
                        <small>Please fill out all required information for RHU registration</small>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('rhu.register.submit') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="loginField" class="form-label">Email or Username <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="loginField" name="loginField"
                                        value="{{ old('loginField') }}" placeholder="Enter email or username" required>
                                    <small class="form-text text-muted">This will be used for login</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contactNumber" class="form-label">Mobile Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contactNumber" name="contactNumber"
                                        value="{{ old('contactNumber') }}" placeholder="Enter mobile number" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rhuName" class="form-label">RHU Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="rhuName" name="rhuName"
                                        value="{{ old('rhuName') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="headName" class="form-label">RHU Head Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="headName" name="headName"
                                        value="{{ old('headName') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="operatingHours" class="form-label">Operating Hours</label>
                                    <input type="text" class="form-control" id="operatingHours" name="operatingHours"
                                        placeholder="e.g., Monday-Friday 8:00 AM - 5:00 PM"
                                        value="{{ old('operatingHours') }}">
                                </div>
                            </div>

                            {{-- <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Brief description of the RHU services and facilities">{{ old('description') }}</textarea>
                            </div> --}}

                            <hr class="my-4">

                            <div class="mb-3">
                                <label for="fullAddress" class="form-label">Full Address <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="fullAddress" name="fullAddress" rows="2"
                                    placeholder="Street address, building number, etc." required>{{ old('fullAddress') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Region <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="">Select Region</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Province <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="province" name="province" required disabled>
                                        <option value="">Select Province</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City or Municipality <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="city" name="city" required disabled>
                                        <option value="">Select City or Municipality</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="zipCode" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="zipCode" name="zipCode"
                                        value="{{ old('zipCode') }}">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route('register.select') }}" class="btn btn-outline-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit Registration</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/registerRHU.js') }}"></script>
@endsection
