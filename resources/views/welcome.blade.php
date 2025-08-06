@extends('layouts.publicApp')

@section('content')
    <div class="container py-5 mt-5">
        <div class="row align-items-center mt-5">
            <div class="col-md-6 text-center text-md-start">
                <img src="{{ asset('images/GabayHealthLight.png') }}" alt="Gabay Health Logo" style="width: 120px;"
                    class="mb-3">
                <h1 class="display-4 fw-bold mb-3" style="color: #1657c1;">Welcome to Gabay Health</h1>
                <p class="lead mb-4" style="font-size: 1.25rem;">
                    Bringing Public Health Closer to Home.
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg px-4">Get started</a>
                </div>
            </div>
            <div class="col-md-6 text-center mt-5 mt-md-0">
                <img src="{{ asset('images/GabayHealthMockup.png') }}" alt="Healthcare Illustration" class="img-fluid"
                    style="max-width: 400px;">
            </div>
        </div>
    </div>
@endsection
