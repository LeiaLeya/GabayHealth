@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="fw-bold text-dark mb-0">Sent notifications</h2>
    </div>
    <p class="text-muted mb-3">Broadcasts you have created for residents.</p>

    @include('notifications._subnav', ['prefix' => 'rhu', 'active' => 'sent'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
            @forelse($sent as $notification)
                @php
                    $notificationType = $notification['notification_type'] ?? 'announcement';
                    $typeLabels = [
                        'health_alert' => '🚨 Health Alert',
                        'announcement' => '📢 Announcement',
                        'reminder' => '📝 Reminder',
                        'vaccination_update' => '💉 Vaccination Update',
                        'clinic_schedule_update' => '🏥 Clinic Schedule Update',
                    ];
                    $status = $notification['status'] ?? 'sent';
                    $createdAt = $notification['createdAt'] ?? '';
                    $formattedDate = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y h:i A') : 'Unknown';
                @endphp
                <div class="border-bottom pb-3 mb-3 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0 fw-bold">{{ $notification['title'] ?? 'Untitled' }}</h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="{{ route('notifications.destroy', $notification['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">{{ $typeLabels[$notificationType] ?? 'Notification' }}</p>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted"><i class="bi bi-clock"></i> {{ $formattedDate }}</small>
                        <span class="badge bg-{{ $status === 'sent' ? 'success' : 'warning' }}">{{ ucfirst($status) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center mb-0 py-4">No sent notifications yet. Use <strong>Send notification</strong> to create one.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
