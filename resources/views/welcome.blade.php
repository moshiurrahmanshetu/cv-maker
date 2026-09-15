<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CV Maker - Professional SaaS CV & Resume Platform</title>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-saas sticky-top">
        <div class="container">
            <a class="navbar-brand-saas" href="{{ route('home') }}">
                <div class="brand-icon">
                    <i class="bi bi-file-earmark-person"></i>
                </div>
                <span>CV Maker</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="#templates-showcase" class="btn-saas-secondary btn-sm d-none d-sm-inline-flex">
                    <i class="bi bi-grid-3x3-gap"></i> Explore Templates
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-saas-primary btn-sm">
                        <i class="bi bi-grid-1x2"></i> Dashboard
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
    <section class="py-5 my-md-3">
        <div class="container text-center" style="max-width: 880px;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-4 rounded-pill border bg-white small text-secondary shadow-sm">
                <span class="badge-saas-published">Phase 3 Live</span>
                <span>Dynamic Template Engine & Multi-Layout Showcase</span>
            </div>

            <h1 class="display-4 fw-extrabold mb-3" style="letter-spacing: -0.03em;">
                One structured CV. Beautiful professional templates.
            </h1>

            <p class="lead text-secondary mb-4 mx-auto" style="max-width: 680px; font-size: 1.15rem; line-height: 1.6;">
                Build your normalized CV data once and effortlessly render it across executive, modern, and technical designs with zero re-typing or data loss.
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
                    <a href="#templates-showcase" class="btn-saas-secondary px-4 py-2 fs-6">
                        <i class="bi bi-eye"></i> Browse Templates
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Template Showcase Section -->
    <section id="templates-showcase" class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="text-center mb-4" style="max-width: 720px; margin: 0 auto;">
                <h2 class="h3 fw-bold mb-2">Designed for every career stage</h2>
                <p class="text-secondary small">
                    Select from our curated collection of professional templates. Every template is strictly compatible with all 11 normalized CV sections.
                </p>
            </div>

            <!-- Category Filter Tabs -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <button type="button" class="btn btn-sm btn-saas-primary category-filter-btn" data-category="all">
                    All Templates ({{ $templates->count() }})
                </button>
                @foreach($categories as $category)
                    <button type="button" class="btn btn-sm btn-saas-secondary category-filter-btn" data-category="{{ $category->slug }}">
                        {{ $category->name }} ({{ $category->activeTemplates->count() }})
                    </button>
                @endforeach
            </div>

            <!-- Templates Grid -->
            <div class="row g-4" id="templatesGrid">
                @forelse($templates as $template)
                    <div class="col-md-6 col-lg-4 template-card-wrapper" data-category="{{ $template->category->slug }}">
                        <div class="card card-saas h-100 border overflow-hidden shadow-sm d-flex flex-column">
                            <!-- Preview Thumbnail Header -->
                            <div class="position-relative bg-surface-subtle p-3 text-center border-bottom" style="height: 300px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <img src="{{ $template->preview_image_url }}" alt="{{ $template->name }}" class="img-fluid rounded border shadow-sm" style="max-height: 270px; object-fit: contain;">
                                
                                <div class="position-absolute top-0 start-0 m-3 d-flex gap-1">
                                    @if($template->is_premium)
                                        <span class="badge-saas-draft">Premium</span>
                                    @else
                                        <span class="badge-saas-published">Free</span>
                                    @endif
                                </div>

                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-white text-secondary border shadow-sm">{{ $template->category->name }}</span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column justify-content-between flex-grow-1">
                                <div>
                                    <h3 class="h5 fw-bold mb-1 text-dark">{{ $template->name }}</h3>
                                    <p class="text-secondary small mb-3" style="min-height: 40px; line-height: 1.5;">
                                        {{ $template->description }}
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                    <button type="button" class="btn btn-sm btn-saas-secondary flex-grow-1 preview-template-btn" data-template-id="{{ $template->id }}" data-template-name="{{ $template->name }}" data-bs-toggle="modal" data-bs-target="#templatePreviewModal">
                                        <i class="bi bi-eye"></i> Preview
                                    </button>

                                    @auth
                                        <a href="{{ route('cvs.create', ['template_id' => $template->id]) }}" class="btn btn-sm btn-saas-primary flex-grow-1 text-center">
                                            <i class="bi bi-check2-circle"></i> Use Template
                                        </a>
                                    @else
                                        <a href="{{ route('register', ['template_id' => $template->id]) }}" class="btn btn-sm btn-saas-primary flex-grow-1 text-center">
                                            <i class="bi bi-lightning-charge"></i> Use Template
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No templates available at this time.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Architecture & Features Highlights -->
    <section class="py-5 bg-light">
        <div class="container" style="max-width: 960px;">
            <div class="text-center mb-5">
                <h2 class="h3 fw-bold mb-2">Normalized Architecture & Extensibility</h2>
                <p class="text-secondary small">Built cleanly from the ground up for maximum reusability, safety, and performance.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-4 border rounded-2 h-100 bg-white shadow-sm">
                        <div class="stat-widget-icon mb-3">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <h4 class="h6 fw-bold mb-2 text-dark">Instant Template Switching</h4>
                        <p class="small text-secondary mb-0">Switch any CV between executive, modern, or technical layouts instantly. Zero data loss across all 11 normalized sections.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 border rounded-2 h-100 bg-white shadow-sm">
                        <div class="stat-widget-icon mb-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="h6 fw-bold mb-2 text-dark">Path Traversal Safe</h4>
                        <p class="small text-secondary mb-0">Dynamic view resolver prevents direct filesystem tampering with strict database whitelisting and fallback protections.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 border rounded-2 h-100 bg-white shadow-sm">
                        <div class="stat-widget-icon mb-3">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <h4 class="h6 fw-bold mb-2 text-dark">A4 Live Preview Engine</h4>
                        <p class="small text-secondary mb-0">Clean standard A4 paper presentation ready for live in-browser previewing, high-res printing, and future PDF rendering.</p>
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
                <span class="small text-muted">Phase 3 Template Engine Active</span>
            </div>
        </div>
    </footer>

    <!-- Interactive Realistic Template Preview Modal -->
    <div class="modal fade" id="templatePreviewModal" tabindex="-1" aria-labelledby="templatePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-3 px-4 bg-white border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-saas-published">Template Preview</span>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="templatePreviewModalLabel">Template Preview</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="previewModalContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Loading preview...</span>
                        </div>
                        <div class="small text-muted mt-2">Loading realistic template preview...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Category Filter & Modal Preview AJAX Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Category Tabs Filter
            const filterBtns = document.querySelectorAll('.category-filter-btn');
            const templateCards = document.querySelectorAll('.template-card-wrapper');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => {
                        b.classList.remove('btn-saas-primary');
                        b.classList.add('btn-saas-secondary');
                    });
                    this.classList.remove('btn-saas-secondary');
                    this.classList.add('btn-saas-primary');

                    const category = this.getAttribute('data-category');
                    templateCards.forEach(card => {
                        if (category === 'all' || card.getAttribute('data-category') === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Modal Live Preview AJAX Loader
            const previewBtns = document.querySelectorAll('.preview-template-btn');
            const previewContainer = document.getElementById('previewModalContainer');
            const previewModalLabel = document.getElementById('templatePreviewModalLabel');

            previewBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const templateId = this.getAttribute('data-template-id');
                    const templateName = this.getAttribute('data-template-name');
                    previewModalLabel.textContent = `Preview: ${templateName}`;

                    previewContainer.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status">
                                <span class="visually-hidden">Loading preview...</span>
                            </div>
                            <div class="small text-muted mt-2">Rendering real template with sample data...</div>
                        </div>
                    `;

                    fetch(`/templates/${templateId}/preview?modal=1`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        previewContainer.innerHTML = html;
                    })
                    .catch(err => {
                        previewContainer.innerHTML = `
                            <div class="p-4 text-center text-danger">
                                <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>
                                Failed to load template preview. Please try again.
                            </div>
                        `;
                    });
                });
            });
        });
    </script>
</body>
</html>
