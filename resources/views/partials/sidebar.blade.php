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
        <img src="{{ asset('images/seal.png') }}" class="seal" alt="Municipal Seal">
        <div class="center-name">Pob. Ward IV Health Center</div>
    </div>

    <ul class="nav-links">
        @php
            $navItems = [
                ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'Reports.png'],
                ['route' => 'schedules.index', 'label' => 'Schedules', 'icon' => 'Schedule.png'],
                ['route' => 'events.index', 'label' => 'Events', 'icon' => 'Events.png'],
                ['route' => 'inventory.index', 'label' => 'Inventory', 'icon' => 'Inventory.png'],
                ['route' => 'personnel.index', 'label' => 'Personnel', 'icon' => 'Personnel.png'],
            ];
        @endphp

        @foreach ($navItems as $item)
            <li class="{{ $currentRoute == $item['route'] ? 'active' : '' }}">
                <a href="{{ route($item['route']) }}">
                    @php
                        $iconMap = [
                            'Reports' => 'bi-file-earmark-bar-graph',
                            'Schedules' => 'bi-calendar-event',
                            'Events' => 'bi-calendar2-event',
                            'Inventory' => 'bi-box-seam',
                            'Personnel' => 'bi-people',
                        ];
                    @endphp
                    <i class="bi {{ $iconMap[$item['label']] }} nav-icon"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <!-- Logout -->
    <div class="logout-section mt-auto">
        <a href="{{ route('logout') }}">
            <i class="bi bi-door-open nav-icon"></i>
            <span>Logout</span>
        </a>
    </div>
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
    }

    .seal {
        width: 80px;
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
        padding: 18px 35px;
        border-radius: 6px;
        transition: background 0.2s;
        margin-bottom: 12px;
    }

    .nav-links li.active,
    .nav-links li:hover {
        background-color: #113d96;
        border-radius: 6px;
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

    .logout-section {
        padding: 0 35px 16px 35px;
        margin-top: auto;
    }

    .logout-section a {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        line-height: 1;
    }

    .logout-section .nav-icon {
        width: 20px;
        height: 20px;
    }

    .logout-section a span {
        margin-top: -2px;
    }
</style>
