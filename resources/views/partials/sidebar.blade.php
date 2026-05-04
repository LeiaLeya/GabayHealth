@php
    $currentRoute = Route::currentRouteName();
@endphp

<div class="sidebar">
    <!-- Logo and App Title -->
    <div class="logo-section">
        <img src="{{ asset('images/GabayHealthLogoDark.png') }}" class="logo" alt="GabayHealth Logo">
        <span class="title">GabayHealth</span>
    </div>

    <div class="seal-section">
        @php
            $logoUrl = session('user.logo_url');
            $userRole = session('user.role');
        @endphp
        @if($userRole === 'admin')
            <i class="bi bi-gear-fill" style="font-size: 3rem; color: white;"></i>
        @elseif($logoUrl && !empty($logoUrl))
            <img src="{{ $logoUrl }}" 
                 class="seal" 
                 alt="RHU Logo"
                 loading="eager"
                 crossorigin="anonymous"
                 onerror="console.error('Logo failed to load:', this.src); this.src='{{ asset('images/seal.png') }}'; this.classList.add('fallback-seal');"
                 style="border-radius: 50%;">
        @else
            <img src="{{ asset('images/seal.png') }}" class="seal fallback-seal" alt="Municipal Seal" style="border-radius: 50%;">
        @endif
        <div class="center-name">
            {{ session('user.name', 'Health Center') }}
        </div>
    </div>

    <ul class="nav-links">
        @php
            $userRole = session('user.role');
            
            if ($userRole === 'admin') {
                $navItems = [
                    ['route' => 'admin.system-admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
                    ['route' => 'admin.system-admin.all-rhus', 'label' => 'All RHUs', 'icon' => 'bi-building'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            } elseif ($userRole === 'rhu') {
                $navItems = [
                    [
                        'type' => 'group',
                        'label' => 'Reports',
                        'icon' => 'bi-file-earmark-bar-graph',
                        'routes' => ['rhu.reports.index', 'rhu.reports.verify', 'rhu.reports.rejected'],
                        'children' => [
                            ['route' => 'rhu.reports.index', 'label' => 'Overview', 'icon' => 'bi-grid-1x2'],
                            ['route' => 'rhu.reports.verify', 'label' => 'Pending Reports', 'icon' => 'bi-hourglass-split', 'pending_badge' => true],
                        ],
                    ],
                    ['route' => 'rhu.barangays.index', 'label' => 'Barangays', 'icon' => 'bi-geo-alt'],
                    [
                        'type' => 'group',
                        'label' => 'Calendars',
                        'icon' => 'bi-calendar3',
                        'routes' => ['rhu.calendars.index', 'rhu.schedules.index'],
                        'children' => [
                            ['route' => 'rhu.calendars.index', 'label' => 'Overview', 'icon' => 'bi-calendar3'],
                            ['route' => 'rhu.schedules.index', 'label' => 'Schedules', 'icon' => 'bi-calendar-event'],
                        ],
                    ],
                    ['route' => 'rhu.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
                    ['route' => 'rhu.events.index', 'label' => 'Events', 'icon' => 'bi-calendar2-event'],
                    ['route' => 'rhu.inventory.index', 'label' => 'Inventory', 'icon' => 'bi-box-seam'],
                    ['route' => 'rhu.services.index', 'label' => 'Services', 'icon' => 'bi-heart-pulse'],
                    ['route' => 'rhu.accounts.index', 'label' => 'Account Management', 'icon' => 'bi-person-gear'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            } else {
                $navItems = [
                    [
                        'type' => 'group',
                        'label' => 'Reports',
                        'icon' => 'bi-file-earmark-bar-graph',
                        'routes' => ['bhc.reports.index', 'bhc.reports.verify', 'bhc.reports.rejected'],
                        'children' => [
                            ['route' => 'bhc.reports.index', 'label' => 'Overview', 'icon' => 'bi-grid-1x2'],
                            ['route' => 'bhc.reports.verify', 'label' => 'Pending Reports', 'icon' => 'bi-hourglass-split', 'pending_badge' => true],
                        ],
                    ],
                    [
                        'type' => 'group',
                        'label' => 'Calendars',
                        'icon' => 'bi-calendar3',
                        'routes' => ['bhc.calendars.index', 'bhc.schedules.index'],
                        'children' => [
                            ['route' => 'bhc.calendars.index', 'label' => 'Overview', 'icon' => 'bi-calendar3'],
                            ['route' => 'bhc.schedules.index', 'label' => 'Schedules', 'icon' => 'bi-calendar-event'],
                        ],
                    ],
                    ['route' => 'bhc.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
                    ['route' => 'bhc.events.index', 'label' => 'Events', 'icon' => 'bi-calendar2-event'],
                    ['route' => 'bhc.inventory.index', 'label' => 'Inventory', 'icon' => 'bi-box-seam'],
                    ['route' => 'bhc.services.index', 'label' => 'Services', 'icon' => 'bi-heart-pulse'],
                    ['route' => 'bhc.personnel.index', 'label' => 'Personnel', 'icon' => 'bi-people'],
                    ['route' => 'bhc.user-requests.index', 'label' => 'User Requests', 'icon' => 'bi-person-plus'],
                    ['route' => 'bhc.accounts.index', 'label' => 'Account Management', 'icon' => 'bi-person-gear'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            }
        @endphp

        @foreach ($navItems as $item)
            @if (!empty($item['type']) && $item['type'] === 'group')
                @php
                    $groupOpen = in_array($currentRoute, $item['routes'] ?? [], true);
                @endphp
                <li class="nav-group-wrap {{ $groupOpen ? 'nav-group-wrap--open' : '' }}">
                    <details class="nav-details" @if($groupOpen) open @endif>
                        <summary class="nav-details-summary">
                            <span class="nav-details-summary-inner">
                                <i class="bi {{ $item['icon'] }} nav-icon"></i>
                                <span>{{ $item['label'] }}</span>
                                @if (!empty($item['notification_group_badge']))
                                    @php $notifGroupCount = $notificationInboxUnreadCount ?? 0; @endphp
                                    <span class="nav-pending-badge nav-notifications-group-count flex-shrink-0" aria-label="{{ $notifGroupCount }} unread notifications">{{ $notifGroupCount > 99 ? '99+' : $notifGroupCount }}</span>
                                @endif
                                <i class="bi bi-chevron-down nav-chevron" aria-hidden="true"></i>
                            </span>
                        </summary>
                        <ul class="nav-sub">
                            @foreach ($item['children'] ?? [] as $child)
                                <li class="{{ $currentRoute === $child['route'] ? 'active' : '' }}">
                                    <a href="{{ route($child['route']) }}" class="nav-sub-link">
                                        <i class="bi {{ $child['icon'] }} nav-icon nav-icon-sub"></i>
                                        <span class="nav-sub-label">{{ $child['label'] }}</span>
                                        @if (!empty($child['pending_badge']))
                                            <span class="nav-pending-badge" aria-label="{{ $pendingReportsSidebarCount }} pending">{{ $pendingReportsSidebarCount }}</span>
                                        @endif
                                        @if (!empty($child['notification_inbox_badge']))
                                            @php $inboxUnread = $notificationInboxUnreadCount ?? 0; @endphp
                                            <span class="nav-pending-badge @if($inboxUnread === 0) nav-pending-badge--muted @endif" aria-label="{{ $inboxUnread }} unread">{{ $inboxUnread > 99 ? '99+' : $inboxUnread }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </li>
            @else
                <li class="{{ $currentRoute == $item['route'] ? 'active' : '' }}">
                    <a href="{{ route($item['route']) }}">
                        @php
                            $iconMap = [
                                'Dashboard' => 'bi-speedometer2',
                                'All RHUs' => 'bi-building',
                                'Approved RHUs' => 'bi-check-circle',
                                'Rural Health Units' => 'bi-building',
                                'Pending Approvals' => 'bi-clock-history',
                                'User Requests' => 'bi-person-plus',
                                'Reports' => 'bi-file-earmark-bar-graph',
                                'Pending Reports' => 'bi-hourglass-split',
                                'Overview' => 'bi-grid-1x2',
                                'Barangays' => 'bi-geo-alt',
                                'Schedules' => 'bi-calendar-event',
                                'Calendars' => 'bi-calendar3',
                                'Events' => 'bi-calendar2-event',
                                'Inbox' => 'bi-inbox',
                                'Send notification' => 'bi-send',
                                'Sent' => 'bi-clock-history',
                                'Notifications' => 'bi-bell',
                                'Inventory' => 'bi-box-seam',
                                'Services' => 'bi-heart-pulse',
                                'Personnel' => 'bi-people',
                                'Account Management' => 'bi-person-gear',
                                'Logout' => 'bi-door-open',
                            ];
                        @endphp
                        <i class="bi {{ $iconMap[$item['label']] ?? $item['icon'] }} nav-icon"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</div>

<style>
    .sidebar {
        width: 250px;
        background-color: #1657c1;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        height: 100vh;
        font-family: 'Poppins', sans-serif;
    }

    .logo-section {
        display: flex;
        align-items: center;
        padding: 16px;
        gap: 10px;
    }

    .logo {
        width: 40px;
        height: 40px;
    }

    .title {
        font-size: 22px;
        font-weight: 600;
    }

    .seal-section {
        text-align: center;
        padding: 16px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        background-color: rgba(255, 255, 255, 0.05);
        margin: 16px;
        border-radius: 8px;
    }

    .rhu-logo {
        width: 100px;
        height: 100px;
        object-fit: contain;
        margin-top: 8px;
    }

    .seal {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        background-color: white;
        flex-shrink: 0;
        transition: opacity 0.3s ease;
    }

    .seal.fallback-seal {
        background-color: #e0e0e0;
    }

    .center-name {
        font-size: 14px;
        font-weight: 500;
        color: white;
        line-height: 1.4;
        word-break: break-word;
    }

    .nav-links {
        list-style: none;
        padding: 0;
        margin-top: 24px;
    }

    .nav-links > li {
        padding: 10px 24px;
        border-radius: 6px;
        transition: background 0.2s;
        margin-bottom: -4px;
    }

    .nav-links > li.active {
        background-color: #113d96;
        border-radius: 6px;
        padding: 10px 24px;
    }

    .nav-links > li:hover:not(.nav-group-wrap) {
        background-color: #113d96;
        border-radius: 6px;
        padding: 10px 24px;
    }

    .nav-links li a {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
    }

    .nav-icon {
        width: 18px;
        height: 18px;
    }

    /* Expandable nav groups (Reports, Calendars, etc.) */
    .nav-group-wrap {
        padding: 0 !important;
        margin-bottom: -4px;
        list-style: none;
    }

    .nav-group-wrap:hover {
        background-color: transparent !important;
    }

    .nav-details {
        border-radius: 6px;
    }

    .nav-group-wrap--open > .nav-details > .nav-details-summary,
    .nav-details-summary:hover {
        background-color: rgba(17, 61, 150, 0.45);
    }

    .nav-details-summary {
        list-style: none;
        cursor: pointer;
        padding: 10px 24px;
        border-radius: 6px;
        user-select: none;
    }

    .nav-details-summary::-webkit-details-marker {
        display: none;
    }

    .nav-details-summary-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        color: white;
    }

    .nav-chevron {
        margin-left: auto;
        font-size: 12px;
        opacity: 0.85;
        transition: transform 0.2s ease;
    }

    .nav-details[open] .nav-chevron {
        transform: rotate(-180deg);
    }

    .nav-sub {
        list-style: none;
        padding: 4px 0 8px 0;
        margin: 0;
    }

    .nav-sub li {
        padding: 6px 12px 6px 36px;
        margin: 0 8px 2px 8px;
        border-radius: 6px;
    }

    .nav-sub li a.nav-sub-link {
        font-size: 14px;
        gap: 10px;
        width: 100%;
    }

    .nav-sub-link {
        display: flex;
        align-items: center;
    }

    .nav-sub-label {
        flex: 1;
        min-width: 0;
    }

    .nav-pending-badge {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        min-width: 1.35rem;
        padding: 2px 7px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.28);
        line-height: 1.2;
    }

    .nav-pending-badge--muted {
        opacity: 0.65;
    }

    .nav-sub li.active .nav-pending-badge {
        background: rgba(255, 255, 255, 0.4);
    }

    .nav-icon-sub {
        width: 16px;
        height: 16px;
        opacity: 0.95;
    }

    .nav-sub li.active,
    .nav-sub li:hover {
        background-color: #113d96;
    }

    .nav-sub li:hover {
        opacity: 1;
    }
</style>
