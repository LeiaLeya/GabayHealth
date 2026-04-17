@php
    $currentRoute = Route::currentRouteName();
@endphp

<div class="sidebar">
    <!-- Logo and App Title -->
    <div class="logo-section">
        <img src="{{ asset('images/gabayhealth_logo.png') }}" class="logo" alt="GabayHealth Logo">
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
                    ['route' => 'rhu.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-bar-graph'],
                    ['route' => 'rhu.reports.verify', 'label' => 'Verify Reports', 'icon' => 'bi-patch-check'],
                    ['route' => 'rhu.barangays.index', 'label' => 'Barangays', 'icon' => 'bi-geo-alt'],
                    ['route' => 'rhu.schedules.index', 'label' => 'Schedules', 'icon' => 'bi-calendar-event'],
                    ['route' => 'rhu.calendars.index', 'label' => 'Calendars', 'icon' => 'bi-calendar3'],
                    ['route' => 'rhu.events.index', 'label' => 'Events', 'icon' => 'bi-calendar2-event'],
                    ['route' => 'rhu.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
                    ['route' => 'rhu.inventory.index', 'label' => 'Inventory', 'icon' => 'bi-box-seam'],
                    ['route' => 'rhu.services.index', 'label' => 'Services', 'icon' => 'bi-heart-pulse'],
                    ['route' => 'rhu.personnel.index', 'label' => 'Personnel', 'icon' => 'bi-people'],
                    ['route' => 'rhu.user-requests.index', 'label' => 'User Requests', 'icon' => 'bi-person-plus'],
                    ['route' => 'rhu.accounts.index', 'label' => 'Account Management', 'icon' => 'bi-person-gear'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            } else {
                $navItems = [
                    ['route' => 'bhc.reports.index', 'label' => 'Reports', 'icon' => 'bi-file-earmark-bar-graph'],
                    ['route' => 'bhc.reports.verify', 'label' => 'Verify Reports', 'icon' => 'bi-patch-check'],
                    ['route' => 'bhc.schedules.index', 'label' => 'Schedules', 'icon' => 'bi-calendar-event'],
                    ['route' => 'bhc.calendars.index', 'label' => 'Calendars', 'icon' => 'bi-calendar3'],
                    ['route' => 'bhc.events.index', 'label' => 'Events', 'icon' => 'bi-calendar2-event'],
                    ['route' => 'bhc.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
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
                            'Verify Reports' => 'bi-patch-check',
                            'Barangays' => 'bi-geo-alt',
                            'Schedules' => 'bi-calendar-event',
                            'Calendars' => 'bi-calendar3',
                            'Events' => 'bi-calendar2-event',
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

    .nav-links li {
        padding: 10px 24px;
        border-radius: 6px;
        transition: background 0.2s;
        margin-bottom: -4px;
    }

    .nav-links li.active,
    .nav-links li:hover {
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
</style>
