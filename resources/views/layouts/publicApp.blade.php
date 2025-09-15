<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>GabayHealth</title>
    <style>
        /* Removed custom font-family, use default */
        * {
            /* font-family intentionally left blank for default */
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

</head>

<body>
    <div class="main-wrapper">
        @include('layouts.publicSidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</body>

</html>
