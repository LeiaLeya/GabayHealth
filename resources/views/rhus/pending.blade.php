@extends('layouts.app')

@section('content')
    <div class="container py-5 mt-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Account Pending Approval
                        </h4>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split display-1 text-warning"></i>
                        </div>

                        <h5 class="mb-3">Your Registration is Under Review</h5>
                        <p class="text-muted mb-4">
                            Your Rural Health Unit registration is currently being reviewed by our administrators.
                            You will be notified once your account has been approved.
                        </p>

                        <div class="mt-4">
                            <h6><strong>RHU Name:</strong> {{ $rhuData['name'] ?? 'N/A' }}</h6>
                            <p class="text-muted">Status: <span
                                    class="badge bg-warning">{{ ucfirst($rhuData['status'] ?? 'Pending') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
