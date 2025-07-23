<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GabayHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Bilo';
            src: url('/fonts/Bilo.otf') format('opentype');
        }

        * {

            font-family: 'Bilo', sans-serif;
        }

        .main-wrapper {
            display: flex;
            height: 100vh;
            min-height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            flex-shrink: 0;
            height: 100vh;
            color: #f9f9f9;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 32px;
        }

        .sidebar .nav-link.active {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        @if (Session::has('user'))
            @if (Session::get('user.role') === 'admin')
                @include('layouts.sidebarAdmin')
            @elseif(Session::get('user.role') === 'rhu')
                @include('layouts.sidebarRHU')
            @else
                @include('layouts.publicSidebar')
            @endif
        @else
            @include('layouts.publicSidebar')
        @endif

        <div class="main-content">
            @yield('content')
        </div>
    </div>

    @yield('scripts')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
