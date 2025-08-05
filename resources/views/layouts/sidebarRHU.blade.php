<div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 280px; height: 100vh; background-color: #0b6ffd;">
    <a href="{{ url('/') }}" class="d-flex align-items-center mt-3 mb-3 text-white text-decoration-none"
        style="font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 600;">
        <img src="{{ asset('images/GabayHealthDark.png') }}" style="height: 40px; margin-right: 10px; font-weight: 700;">
        GabayHealth
    </a>
    {{-- <hr style="border-color: #f0f0f000;" class="mb-5"> --}}


    <h4 class="text-center mt-5 mb-5" style="width: 100%;">RHU</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="{{ route('BHUs.index') }}"
                    class="nav-link text-white {{ request()->routeIs('BHUs.index') ? 'active' : '' }}">
                    <i class=""></i> BHUs
                </a>
            </li>
            <li>
                <a href="{{ route('rhu.approvals') }}"
                    class="nav-link text-white {{ request()->routeIs('rhu.approvals') ? 'active' : '' }}">
                    <i class=""></i> BHU Approvals
                </a>
            </li>
            {{-- <li>
                <a href="{{ route('rhu.doctors') }}"
                    class="nav-link text-white {{ request()->routeIs('rhu.doctors') ? 'active' : '' }}">
                    <i class=""></i> Doctors
                </a>
            </li> --}}
            <li>
                <a href="{{ route('rhu.notifications') }}"
                    class="nav-link text-white {{ request()->routeIs('rhu.notifications') ? 'active' : '' }}">
                    <i class=""></i> Notifications
                </a>
            </li>
        </ul>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm mt-3 mb-4">Logout</button>
        </form>
</div>
