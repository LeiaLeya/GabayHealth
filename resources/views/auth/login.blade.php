@extends('layouts.publicApp')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-3 col-lg-3 mt-5">
            <div class="card shadow mt-5">
                <div class="card-header text-center bg-primary text-white">
                    <h3 class="mt-2">Sign In</h3>
                </div>
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            {{-- <label class="form-label">Username</label> --}}
                            <input type="text" name="loginField" value="{{ old('loginField') }}" class="form-control"
                                placeholder="Username or email">
                            @error('loginField')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            {{-- <label class="form-label">Password</label> --}}
                            <input type="password" name="password" class="form-control" placeholder="Password">
                            @error('password')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">LOGIN</button>
                        </div>
                    </form>
                </div>
                <div class="text-center mt-2 mb-4 ">
                    <h5><a href="{{ route('register.select') }}" style="text-decoration: none; color: black;">Create new account</a></h5>
                </div>
            </div>
        </div>
    </div>
@endsection
