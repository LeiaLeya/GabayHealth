<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: #0b6ffd; mb-5;">
    <div class="container mb">
        <a class="navbar-brand d-flex align-items-center" href="#"
            style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 600;">
            <img src="{{ asset('images/GabayHealthDark.png') }}" style="height: 25px; margin-right: 10px;"
                alt="GabayHealth Logo">
            GabayHealth
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rhu.index') ? 'active' : '' }}" href="#">

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('BHUs.index') ? 'active' : '' }}"
                        href="{{ route('BHUs.index') }}">
                        BHUs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rhu.approvals') ? 'active' : '' }}"
                        href="{{ route('rhu.approvals') }}">
                        Approvals
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('rhu.notifications') ? 'active' : '' }}"
                        href="{{ route('rhu.notifications') }}">
                        Notifications
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
