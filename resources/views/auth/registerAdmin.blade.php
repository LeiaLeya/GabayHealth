@extends('layouts.publicApp')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header text-center bg-primary text-white">
                    <h4 class="mt-2">GabayHealth</h4>
                </div>
                <div class="card-body">

                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                    <form method="POST" action="{{ route('register.admin.submit') }}">
                        @csrf

                        <div class="mb-3">
                            {{-- <label class="form-label">Username <span class="text-danger">*</span></label> --}}
                            <input type="text" name="username" value="{{ old('username') }}" class="form-control"
                                placeholder="Enter username">
                            @error('username')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            {{-- <label class="form-label">Password <span class="text-danger">*</span></label> --}}
                            <input type="password" name="password" class="form-control"  placeholder="Password">
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            {{-- <label class="form-label">Confirm Password <span class="text-danger">*</span></label> --}}
                            <input type="password" name="password_confirmation" class="form-control"  placeholder="Confirm password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                </div>
                <div class="text-center mt-2 mb-4">
                    <a href="{{ route('login') }}">Already have an account? </a>
                </div>
            </div>
        </div>
    </div>
@endsection
