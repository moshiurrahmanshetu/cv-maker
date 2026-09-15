<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CV Maker - Authentication')</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <div class="container" style="max-width: 480px;">
        <!-- Brand Header -->
        <div class="text-center mb-4">
            <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark">
                <div class="brand-icon" style="width: 40px; height: 40px; font-size: 1.25rem;">
                    <i class="bi bi-file-earmark-person"></i>
                </div>
                <span class="fs-4 fw-bold">CV Maker</span>
            </a>
            <p class="text-muted small mt-1 mb-0">Professional CV & Resume Management</p>
        </div>

        @include('components.alerts')

        <!-- Auth Card Container -->
        <div class="card card-saas">
            <div class="card-body p-4 p-md-5">
                @yield('content')
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Back to Homepage
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
