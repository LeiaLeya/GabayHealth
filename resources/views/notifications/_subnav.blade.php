@php
    $inboxRoute = route($prefix . '.notifications.index');
    $createRoute = route($prefix . '.notifications.create');
    $sentRoute = route($prefix . '.notifications.sent');
    $inboxUnreadNav = $notificationInboxUnreadCount ?? 0;
@endphp
<ul class="nav nav-pills flex-wrap gap-2 mb-4">
    <li class="nav-item">
        <a class="nav-link d-inline-flex align-items-center gap-2 @if(($active ?? '') === 'inbox') active @endif" href="{{ $inboxRoute }}">
            <span><i class="bi bi-inbox me-1"></i>Inbox</span>
            <span class="badge rounded-pill @if(($active ?? '') === 'inbox') bg-light text-primary @else bg-secondary @endif @if($inboxUnreadNav === 0) opacity-75 @endif" title="Unread in inbox">{{ $inboxUnreadNav > 99 ? '99+' : $inboxUnreadNav }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(($active ?? '') === 'create') active @endif" href="{{ $createRoute }}">
            <i class="bi bi-send me-1"></i>Send notification
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if(($active ?? '') === 'sent') active @endif" href="{{ $sentRoute }}">
            <i class="bi bi-clock-history me-1"></i>Sent
        </a>
    </li>
</ul>
