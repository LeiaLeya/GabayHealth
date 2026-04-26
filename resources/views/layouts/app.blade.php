<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GabayHealth</title>
    <link rel="icon" type="image/png" href="{{ asset('images/gabayhealth_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            background: #f4f6fb;
            padding: 32px;
        }

        .sidebar .nav-link.active {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
    </style>
    @stack('styles')

</head>

<body>
    <div class="main-wrapper">
        @if(empty($hideSidebar))
            @include('partials.sidebar')
        @endif
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</body>

</html>
