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
        @if(session('user.logo_url'))
            <img src="{{ session('user.logo_url') }}" 
                 class="seal" 
                 alt="RHU Logo"
                 onerror="this.src='{{ asset('images/seal.png') }}'">
        @else
            <img src="{{ asset('images/seal.png') }}" class="seal" alt="Municipal Seal">
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
                    ['route' => 'RHUs.index', 'label' => 'Rural Health Units', 'icon' => 'bi-building'],
                    ['route' => 'RHUs.approvals', 'label' => 'Pending Approvals', 'icon' => 'bi-clock-history'],
                    ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'Reports.png'],
                    ['route' => 'reports.verify', 'label' => 'Verify Reports', 'icon' => 'bi-patch-check'],
                    ['route' => 'accounts.index', 'label' => 'Account Management', 'icon' => 'bi-person-gear'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            } else {
                // Determine route prefix based on role
                $routePrefix = ($userRole === 'rhu') ? 'rhu.' : 'bhc.';
                
                $navItems = [
                    ['route' => 'rhu.reports.index', 'label' => 'Reports', 'icon' => 'Reports.png'],
                    ['route' => 'rhu.reports.verify', 'label' => 'Verify Reports', 'icon' => 'bi-patch-check'],
                    ['route' => 'rhu.schedules.index', 'label' => 'Schedules', 'icon' => 'Schedule.png'],
                    ['route' => 'rhu.calendars.index', 'label' => 'Calendars', 'icon' => 'bi-calendar3'],
                    ['route' => 'rhu.events.index', 'label' => 'Events', 'icon' => 'Events.png'],
                    ['route' => 'rhu.notifications.index', 'label' => 'Notifications', 'icon' => 'bi-bell'],
                    ['route' => 'rhu.inventory.index', 'label' => 'Inventory', 'icon' => 'Inventory.png'],
                    ['route' => 'rhu.services.index', 'label' => 'Services', 'icon' => 'bi-heart-pulse'],
                    ['route' => 'rhu.personnel.index', 'label' => 'Personnel', 'icon' => 'Personnel.png'],
                    ['route' => 'rhu.user-requests.index', 'label' => 'User Requests', 'icon' => 'bi-person-plus'],
                    ['route' => 'rhu.accounts.index', 'label' => 'Account Management', 'icon' => 'bi-person-gear'],
                    ['route' => 'logout', 'label' => 'Logout', 'icon' => 'bi-door-open'],
                ];
            }
        @endphp

        @foreach ($navItems as $item)
            <li class="{{ $currentRoute == $item['route'] ? 'active' : '' }}">
                <a href="{{ route($item['route']) }}">
                    @php
                        $iconMap = [
                            'Rural Health Units' => 'bi-building',
                            'Pending Approvals' => 'bi-clock-history',
                            'User Requests' => 'bi-person-plus',
                            'Reports' => 'bi-file-earmark-bar-graph',
                            'Verify Reports' => 'bi-patch-check',
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
        padding: 8px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .rhu-logo {
        width: 100px;
        height: 100px;
        object-fit: contain;
        margin-top: 8px;
    }

    .seal {
        width: 80px;
        border-radius: 50%;
        object-fit: cover;
    }

    .center-name {
        font-size: 16px;
        margin-top: 8px;
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
