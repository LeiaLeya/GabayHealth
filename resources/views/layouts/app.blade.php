<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GabayHealth</title>
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
            background: #f9f9f9;
            padding: 32px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <div class="main-wrapper">
        @include('partials.sidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</body>
</html>
