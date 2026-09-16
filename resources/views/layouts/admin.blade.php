<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel - CV Maker')</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="admin-wrapper">
        <!-- Desktop Collapsible Sidebar -->
        <aside class="admin-sidebar d-none d-lg-flex" id="adminSidebar">
            <div class="sidebar-brand">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                    <div class="brand-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <span class="sidebar-brand-text fw-bold fs-6">Admin Panel</span>
                </a>
                <button type="button" class="btn btn-sm btn-link text-muted p-0 sidebar-brand-text" id="sidebarToggleBtn" title="Toggle Sidebar">
                    <i class="bi bi-chevron-left fs-6"></i>
                </button>
            </div>

            <ul class="sidebar-nav">
                <li class="sidebar-section-title">Core</li>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard Overview">
                        <i class="bi bi-grid-1x2"></i>
                        <span class="nav-item-text">Overview</span>
                    </a>
                </li>

                <li class="sidebar-section-title">Management</li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Users Directory">
                        <i class="bi bi-people"></i>
                        <span class="nav-item-text">Users</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.cvs.index') }}" class="sidebar-link {{ request()->routeIs('admin.cvs.*') ? 'active' : '' }}" title="All Documents">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="nav-item-text">All Documents</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.document-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}" title="Document Types">
                        <i class="bi bi-file-earmark-ruled"></i>
                        <span class="nav-item-text">Document Types</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.ai.index') }}" class="sidebar-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}" title="AI Assistant Logs">
                        <i class="bi bi-robot"></i>
                        <span class="nav-item-text">AI Assistant</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" title="Orders & Transactions">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span class="nav-item-text">Orders & Payments</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.templates.index') }}" class="sidebar-link {{ request()->routeIs('admin.templates.index', 'admin.templates.create', 'admin.templates.edit') ? 'active' : '' }}" title="CV Templates">
                        <i class="bi bi-palette"></i>
                        <span class="nav-item-text">Templates</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.templates.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.templates.categories.*') ? 'active' : '' }}" title="Template Categories">
                        <i class="bi bi-tags"></i>
                        <span class="nav-item-text">Categories</span>
                    </a>
                </li>

                <li class="sidebar-section-title">Platform</li>
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link" title="Return to App">
                        <i class="bi bi-arrow-left-circle"></i>
                        <span class="nav-item-text">Back to App</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle fs-5 text-secondary"></i>
                    <div class="user-info-text text-truncate">
                        <div class="small fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Offcanvas Sidebar Drawer -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h5 class="offcanvas-title fw-bold fs-6" id="mobileSidebarLabel">Admin Panel</h5>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-direction-column">
                <ul class="sidebar-nav w-100">
                    <li class="sidebar-section-title">Core</li>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2"></i>
                            <span>Overview</span>
                        </a>
                    </li>
                    <li class="sidebar-section-title">Management</li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.cvs.index') }}" class="sidebar-link {{ request()->routeIs('admin.cvs.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>All Documents</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.document-types.index') }}" class="sidebar-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-ruled"></i>
                            <span>Document Types</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.ai.index') }}" class="sidebar-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
                            <i class="bi bi-robot"></i>
                            <span>AI Assistant</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span>Orders & Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.templates.index') }}" class="sidebar-link {{ request()->routeIs('admin.templates.index', 'admin.templates.create', 'admin.templates.edit') ? 'active' : '' }}">
                            <i class="bi bi-palette"></i>
                            <span>Templates</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.templates.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.templates.categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="sidebar-section-title">Platform</li>
                    <li>
                        <a href="{{ route('dashboard') }}" class="sidebar-link">
                            <i class="bi bi-arrow-left-circle"></i>
                            <span>Back to App</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content Column -->
        <div class="admin-main">
            <!-- Admin Top Header -->
            <header class="admin-header">
                <div class="d-flex align-items-center gap-3">
                    <!-- Mobile Hamburger -->
                    <button class="btn btn-sm btn-saas-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <!-- Desktop Collapsed Sidebar Toggle (when collapsed) -->
                    <button class="btn btn-sm btn-saas-secondary d-none d-lg-inline-flex" id="sidebarExpandBtn" style="display: none !important;">
                        <i class="bi bi-layout-sidebar fs-6"></i>
                    </button>

                    <h1 class="h5 mb-0 fw-bold">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="btn-saas-secondary btn-sm d-none d-sm-inline-flex">
                        <i class="bi bi-box-arrow-up-right"></i> View User App
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-saas-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="adminUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name }}</span>
                            <span class="badge-saas-role-admin ms-1">Admin</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end" aria-labelledby="adminUserMenu">
                            <li class="px-3 py-2">
                                <div class="small fw-semibold text-truncate">{{ Auth::user()->name }}</div>
                                <div class="small text-muted text-truncate">{{ Auth::user()->email }}</div>
                            </li>
                            <li><hr class="dropdown-divider-saas"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-saas" href="{{ route('dashboard') }}">
                                    <i class="bi bi-person"></i> User Dashboard
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
                </div>
            </header>

            <!-- Admin Content Body -->
            <main class="admin-content-body">
                <div class="container-fluid px-0">
                    @include('components.alerts')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Collapse & LocalStorage Persistence Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const expandBtn = document.getElementById('sidebarExpandBtn');
            const storageKey = 'cvmaker_admin_sidebar_collapsed';

            // Apply saved state from localStorage
            const isCollapsed = localStorage.getItem(storageKey) === 'true';
            if (isCollapsed && sidebar) {
                sidebar.classList.add('collapsed');
                if (expandBtn) expandBtn.style.setProperty('display', 'inline-flex', 'important');
            }

            function setSidebarCollapsed(collapsed) {
                if (!sidebar) return;
                if (collapsed) {
                    sidebar.classList.add('collapsed');
                    if (expandBtn) expandBtn.style.setProperty('display', 'inline-flex', 'important');
                    localStorage.setItem(storageKey, 'true');
                } else {
                    sidebar.classList.remove('collapsed');
                    if (expandBtn) expandBtn.style.setProperty('display', 'none', 'important');
                    localStorage.setItem(storageKey, 'false');
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    setSidebarCollapsed(true);
                });
            }

            if (expandBtn) {
                expandBtn.addEventListener('click', function () {
                    setSidebarCollapsed(false);
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
