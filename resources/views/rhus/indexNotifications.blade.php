@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Notifications</h3>

        <div class="card">
            <div class="card-body">
                @if (count($notifications) > 0)
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Barangay Name</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications as $notification)
                                    <tr id="notification-row-{{ $notification['id'] }}">
                                        <td>
                                            <span
                                                class="badge {{ $notification['status'] === 'unread' ? 'bg-warning' : 'bg-success' }}"
                                                id="badge-{{ $notification['id'] }}">
                                                {{ ucfirst($notification['status']) }}
                                            </span>
                                        </td>
                                        <td>{{ $notification['type'] ?? 'N/A' }}</td>
                                        <td>{{ $notification['barangay_name'] ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($notification['created_at'])->format('M d, Y h:i A') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('notifications.view', $notification['id']) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <h5 class="text-muted">No Notifications</h5>
                        <p class="text-muted">You have no notifications at this time.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
