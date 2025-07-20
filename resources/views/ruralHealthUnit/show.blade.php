@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Barangay Health Units</h2>
        <div class="row">
            <!-- Dummy BHU Card Example -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="https://via.placeholder.com/400x200?text=BHU+Image" class="card-img-top" alt="BHU Image">
                    <div class="card-body">
                        <h5 class="card-title">Sample BHU Name</h5>
                        <p class="card-text mb-1"><strong>Address:</strong> 123 Sample St., Barangay Example</p>
                        <p class="card-text mb-1"><strong>Contact:</strong> (0912) 345-6789</p>
                        <p class="card-text"><strong>Head:</strong> Dr. Juan Dela Cruz</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="#" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <!-- You can duplicate the above card for more dummy BHUs -->
        </div>
    </div>
@endsection
