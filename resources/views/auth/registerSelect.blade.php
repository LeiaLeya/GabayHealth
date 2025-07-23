@extends('layouts.publicApp')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header text-center bg-primary text-white">
                    <h4 class="mt-2">GabayHealth</h4>
                </div>
                <div class="card-body py-4">
                    <div class="d-grid gap-2">

                        <a href="{{ route('register.admin') }}" class="btn btn-primary">
                            Register as Admin
                        </a>

                        <a href="{{ route('rhu.register') }}" class="btn btn-outline-dark">
                            Register as RHU
                        </a>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account? <a href="{{ route('login') }}">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection