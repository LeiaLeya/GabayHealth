@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Notification Details</h5>
                        <a href="{{ route('rhu.notifications') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i>Back
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($notification)
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Status:</strong>
                                </div>
                                <div class="col-md-9">
                                    <span
                                        class="badge {{ $notification['status'] === 'unread' ? 'bg-warning' : 'bg-success' }}">
                                        {{ ucfirst($notification['status']) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Type:</strong>
                                </div>
                                <div class="col-md-9">
                                    {{ $notification['type'] ?? 'N/A' }}
                                </div>
                            </div>

                            @if (isset($notification['barangay_name']))
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <strong>Barangay:</strong>
                                    </div>
                                    <div class="col-md-9">
                                        {{ $notification['barangay_name'] }}
                                    </div>
                                </div>
                            @endif

                            @if (isset($notification['barangay_id']))
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <strong>Barangay ID:</strong>
                                    </div>
                                    <div class="col-md-9">
                                        <code>{{ $notification['barangay_id'] }}</code>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Date:</strong>
                                </div>
                                <div class="col-md-9">
                                    {{ \Carbon\Carbon::parse($notification['created_at'])->format('M d, Y h:i A') }}
                                    <small class="text-muted">
                                        ({{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }})
                                    </small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Description:</strong>
                                </div>
                                <div class="col-md-9">
                                    <div class="alert alert-info">
                                        @if ($notification['type'] === 'barangay_registration')
                                            <strong>{{ $notification['barangay_name'] ?? 'A barangay' }}</strong> has
                                            submitted a new barangay health unit registration request. Please review the
                                            details and take appropriate action.
                                        @elseif($notification['type'] === 'appointment')
                                            There is an appointment update for
                                            <strong>{{ $notification['barangay_name'] ?? 'a barangay' }}</strong>. Please
                                            check the appointment details.
                                        @else
                                            New notification received. Please review the details above.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Notification not found or you don't have permission to view it.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
