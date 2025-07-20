<div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px; height: 100vh; background-color: #0b6ffd;">
    <a href="{{ url('/') }}" class="d-flex align-items-center mt-3 mb-3 text-white text-decoration-none"
        style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 600;">
        <img src="{{ asset('images/gabayhealth_logo.png') }}" style="height: 40px; margin-right: 10px; font-weight: 700;">
        GabayHealth
    </a>
    <hr style="border-color: #f0f0f000;" class="mb-5">


    <h4 class="text-center mt-5 mb-5" style="width: 100%;">Admin</h1>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="{{ route('RHUs.index') }}"
                    class="nav-link text-white {{ request()->routeIs('RHUs.index') ? 'active' : '' }}">
                    <i class=""></i> RHUs
                </a>
            </li>
            <li>
                <a href="{{ route('RHUs.approvals') }}"
                    class="nav-link text-white {{ request()->routeIs('RHUs.approvals') ? 'active' : '' }}">
                    <i class=""></i> RHU Approvals
                </a>
            </li>
        </ul>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm mt-3 mb-4">Logout</button>
        </form>
</div>
