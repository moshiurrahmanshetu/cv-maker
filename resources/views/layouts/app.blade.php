<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CV Maker - Build Professional CVs & Resumes')</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-saas">
        <div class="container">
            <a class="navbar-brand-saas" href="{{ route('dashboard') }}">
                <div class="brand-icon">
                    <i class="bi bi-file-earmark-person"></i>
                </div>
                <span>CV Maker</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link nav-link-saas {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-saas {{ request()->routeIs('cvs.index') ? 'active' : '' }}" href="{{ route('cvs.index') }}">
                            <i class="bi bi-file-earmark-text me-1"></i> My Documents
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('cvs.create') }}" class="btn-saas-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Create Document
                    </a>

                    @auth
                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-saas-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-6"></i>
                                <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name }}</span>
                                @if(Auth::user()->isAdmin())
                                    <span class="badge-saas-role-admin ms-1">Admin</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end" aria-labelledby="userMenu">
                                <li class="px-3 py-2">
                                    <div class="small fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                                    <div class="small text-muted text-truncate">{{ Auth::user()->email }}</div>
                                </li>
                                <li><hr class="dropdown-divider-saas"></li>
                                
                                {{-- Admin Panel link only visible to Admins --}}
                                @if(Auth::user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item dropdown-item-saas text-primary fw-semibold" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-shield-lock-fill text-dark"></i> Admin Panel
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider-saas"></li>
                                @endif

                                <li>
                                    <a class="dropdown-item dropdown-item-saas" href="{{ route('cvs.index') }}">
                                        <i class="bi bi-file-earmark-text"></i> My Documents
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-saas" href="{{ route('billing.index') }}">
                                        <i class="bi bi-credit-card-2-front"></i> Billing & Purchases
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider-saas"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item dropdown-item-saas text-danger">
                                            <i class="bi bi-box-arrow-right"></i> Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="py-4 flex-grow-1">
        <div class="container">
            @include('components.alerts')
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-saas">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>CV Maker Platform</strong>. All rights reserved.
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">Version 1.0 (Phase 1)</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
