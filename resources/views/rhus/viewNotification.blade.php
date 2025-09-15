@extends('layouts.app')

@section('content')
    <div class="container pt-3 mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Notification Details</h5>
                        <div class="d-flex gap-2">
                            @if (($notification['status'] ?? '') !== 'read')
                                <form action="{{ route('rhu.notifications.read', $notification['id']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark as read</button>
                                </form>
                            @endif
                            <a href="{{ route('rhu.notifications') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i>Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($notification)
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Status:</strong></div>
                                <div class="col-md-9">
                                    <span
                                        class="badge {{ ($notification['status'] ?? '') === 'read' ? 'bg-success' : 'bg-primary' }}">
                                        {{ ucfirst($notification['status'] ?? 'unread') }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Type:</strong></div>
                                <div class="col-md-9">{{ $notification['type'] ?? 'N/A' }}</div>
                            </div>

                            @if (isset($notification['barangay_name']) || isset($notification['barangayName']))
                                <div class="row mb-3">
                                    <div class="col-md-3"><strong>Barangay:</strong></div>
                                    <div class="col-md-9">
                                        {{ $notification['barangay_name'] ?? $notification['barangayName'] }}</div>
                                </div>
                            @endif

                            @if (isset($notification['barangay_id']) || isset($notification['barangayId']))
                                <div class="row mb-3">
                                    <div class="col-md-3"><strong>Barangay ID:</strong></div>
                                    <div class="col-md-9">{{ $notification['barangay_id'] ?? $notification['barangayId'] }}
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Date:</strong></div>
                                <div class="col-md-9">
                                    {{ \Carbon\Carbon::parse($notification['created_at'] ?? now())->format('M d, Y h:i A') }}
                                    <small class="text-muted ms-2">
                                        {{ \Carbon\Carbon::parse($notification['created_at'] ?? now())->diffForHumans() }}
                                    </small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Description:</strong></div>
                                <div class="col-md-9">
                                    <div class="alert alert-info mb-0">
                                        @php $t = $notification['type'] ?? ''; @endphp
                                        @if ($t === 'barangay_registration')
                                            {{ ($notification['barangay_name'] ?? 'A BHU') . ' submitted a registration request.' }}
                                        @elseif ($t === 'report_submitted')
                                            A new health report was submitted by
                                            {{ $notification['barangay_name'] ?? 'a BHU' }}.
                                        @else
                                            {{ $notification['message'] ?? 'No additional details provided.' }}
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
