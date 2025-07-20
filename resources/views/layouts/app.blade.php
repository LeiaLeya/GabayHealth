<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GabayHealth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
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
        }
        .main-content {
            flex: 1;
            overflow-y: auto;
            background: #fff;
            padding: 0;
            padding: 32px;
        }
    </style>
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
</body>
</html>
