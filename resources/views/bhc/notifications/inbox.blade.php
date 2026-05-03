@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h2 class="fw-bold text-dark mb-0">Notifications</h2>
    </div>
    <p class="text-muted mb-3">RHU alerts and system messages for your barangay. Messages you send to residents are under <strong>Sent</strong>.</p>

    @include('notifications._subnav', ['prefix' => 'bhc', 'active' => 'inbox'])

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
        <div class="card-body">
            @forelse($inbox as $n)
                @php
                    $title = \App\Services\NotificationPageSupport::inboxTitle($n);
                    $subtitle = \App\Services\NotificationPageSupport::inboxSubtitle($n);
                    $raw = $n['createdAt'] ?? $n['created_at'] ?? null;
                    $when = $raw ? \Carbon\Carbon::parse($raw)->format('M d, Y g:i A') : '—';
                    $isUnread = \App\Services\NotificationPageSupport::barangayInboxIsUnread($n, $linkedRhuId ?? null, $barangayId ?? (string) (session('user.id') ?? ''));
                    $msgPreview = $n['message'] ?? null;
                @endphp
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <h6 class="fw-bold mb-1">
                                {{ $title }}
                                @if($isUnread)
                                    <span class="badge bg-primary ms-1">New</span>
                                @endif
                            </h6>
                            @if($subtitle !== '')
                                <p class="text-muted mb-1 small">{{ $subtitle }}</p>
                            @endif
                            @if(($n['type'] ?? '') === 'barangay_registration')
                                <p class="small mb-0 text-muted">A barangay health center has submitted a registration for your review.</p>
                            @endif
                            @if(!empty($n['notification_type']) && !empty($msgPreview))
                                <p class="small mb-1 text-body">{{ \Illuminate\Support\Str::limit(strip_tags((string) $msgPreview), 200) }}</p>
                            @endif
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $when }}</small>
                        </div>
                        <div>
                            @if($isUnread)
                                <form action="{{ route('bhc.notifications.read', $n['id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Mark as read</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Read</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center mb-0 py-4">Your inbox is empty. Alerts from your RHU and system messages will appear here.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
