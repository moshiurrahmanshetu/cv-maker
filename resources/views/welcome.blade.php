<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CV Maker - Professional SaaS CV & Resume Platform</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-saas">
        <div class="container">
            <a class="navbar-brand-saas" href="{{ route('home') }}">
                <div class="brand-icon">
                    <i class="bi bi-file-earmark-person"></i>
                </div>
                <span>CV Maker</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-saas-primary btn-sm">
                        <i class="bi bi-grid-1x2"></i> Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-saas-secondary btn-sm">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="btn-saas-primary btn-sm">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-5 my-md-4">
        <div class="container text-center" style="max-width: 860px;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-4 rounded-pill border bg-white small text-secondary">
                <span class="badge-saas-published">New</span>
                <span>Phase 1 Platform Engine Live</span>
            </div>

            <h1 class="display-4 fw-extrabold mb-3" style="letter-spacing: -0.03em;">
                Craft professional resumes with precision and ease.
            </h1>

            <p class="lead text-secondary mb-4 mx-auto" style="max-width: 680px; font-size: 1.15rem; line-height: 1.6;">
                A normalized, extensible CV management platform built for modern professionals. Draft freely, organize comprehensive career data, and prepare publication-ready resumes.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                @auth
                    <a href="{{ route('cvs.create') }}" class="btn-saas-primary px-4 py-2 fs-6">
                        <i class="bi bi-plus-lg"></i> Create New CV
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn-saas-secondary px-4 py-2 fs-6">
                        <i class="bi bi-grid-1x2"></i> Open Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-saas-primary px-4 py-2 fs-6">
                        <i class="bi bi-lightning-charge"></i> Start Building Free
                    </a>
                    <a href="{{ route('login') }}" class="btn-saas-secondary px-4 py-2 fs-6">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In to Account
                    </a>
                @endauth
            </div>

            <!-- Preview Showcase Box -->
            <div class="card card-saas text-start overflow-hidden border-2">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-secondary-subtle" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-secondary-subtle" style="width: 10px; height: 10px;"></span>
                        <span class="rounded-circle bg-secondary-subtle" style="width: 10px; height: 10px;"></span>
                        <span class="small text-muted ms-2 fw-semibold">CV Workspace Preview</span>
                    </div>
                    <span class="badge-saas-published">Normalized Architecture</span>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-2 h-100 bg-surface-subtle">
                                <div class="fw-bold mb-1"><i class="bi bi-shield-check me-1"></i> Role Authorization</div>
                                <p class="small text-muted mb-0">Complete RBAC with Admin & User roles, protected routes, and ownership policies.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-2 h-100 bg-surface-subtle">
                                <div class="fw-bold mb-1"><i class="bi bi-file-earmark-diff me-1"></i> Draft & Publish Flow</div>
                                <p class="small text-muted mb-0">Never forced to publish incomplete data. Save drafts at any stage seamlessly.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 border rounded-2 h-100 bg-surface-subtle">
                                <div class="fw-bold mb-1"><i class="bi bi-layout-sidebar-inset me-1"></i> Responsive Admin Shell</div>
                                <p class="small text-muted mb-0">Collapsible persistent sidebar, mobile offcanvas, and SaaS dashboard metrics.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 border-top bg-white">
        <div class="container" style="max-width: 960px;">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold mb-2">Designed for extensibility and scale</h2>
                <p class="text-muted small">Built with clean architecture ready for template selection, customization, and PDF rendering.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="stat-widget-icon flex-shrink-0">
                            <i class="bi bi-database"></i>
                        </div>
                        <div>
                            <h5 class="h6 fw-bold mb-1">Normalized Data Storage</h5>
                            <p class="small text-muted">Dedicated tables for Experiences, Educations, Skills, Languages, Projects, Certifications, and Custom Sections.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div class="stat-widget-icon flex-shrink-0">
                            <i class="bi bi-lock"></i>
                        </div>
                        <div>
                            <h5 class="h6 fw-bold mb-1">Strict Ownership Policies</h5>
                            <p class="small text-muted">Policy-level authorization prevents unauthorized viewing, editing, or deleting of any user's CV via URL tampering.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-saas">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>CV Maker Platform</strong>. All rights reserved.
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">Built with Laravel & Bootstrap 5</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
