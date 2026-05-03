@php
    $user = session('user');
    $role = $user['role'] ?? null;
@endphp
@if(in_array($role, ['rhu', 'barangay'], true) && !empty($notificationInboxUrl))
<div class="dropdown">
    <button class="btn btn-light border position-relative rounded-circle p-2" type="button" id="notificationBellBtn"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
            aria-label="Notifications">
        <i class="bi bi-bell fs-5 text-dark"></i>
        @if(($notificationInboxUnreadCount ?? 0) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ $notificationInboxUnreadCount > 99 ? '99+' : $notificationInboxUnreadCount }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow pt-0 overflow-hidden" style="min-width: 300px; max-width: 360px;" aria-labelledby="notificationBellBtn">
        <div class="px-3 py-2 border-bottom bg-light small fw-semibold text-muted">Unread</div>
        <div style="max-height: 280px; overflow-y: auto;">
            @forelse($notificationBellItems ?? [] as $item)
                <a class="dropdown-item py-2 border-bottom" href="{{ $notificationInboxUrl }}">
                    <div class="fw-semibold small text-dark text-truncate">{{ $item['title'] }}</div>
                    @if(!empty($item['subtitle']))
                        <div class="text-muted small text-truncate">{{ $item['subtitle'] }}</div>
                    @endif
                </a>
            @empty
                <div class="dropdown-item-text text-muted small py-3 text-center">No unread notifications</div>
            @endforelse
        </div>
        <a class="dropdown-item text-center small fw-semibold py-2 bg-light" href="{{ $notificationInboxUrl }}">Open inbox</a>
    </div>
</div>
@endif
