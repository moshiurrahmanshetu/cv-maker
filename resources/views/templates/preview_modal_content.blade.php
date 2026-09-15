<div class="template-modal-preview-body p-2 p-md-4 bg-light">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">{{ $template->name }}</h5>
            <div class="small text-muted">{{ $template->category->name }} &bull; {{ $template->is_premium ? 'Premium Template' : 'Free Template' }}</div>
        </div>
        <div>
            @auth
                <a href="{{ route('cvs.create', ['template_id' => $template->id]) }}" class="btn-saas-primary btn-sm">
                    <i class="bi bi-check2-circle"></i> Use This Template
                </a>
            @else
                <a href="{{ route('register', ['template_id' => $template->id]) }}" class="btn-saas-primary btn-sm">
                    <i class="bi bi-lightning-charge"></i> Use This Template
                </a>
            @endauth
        </div>
    </div>

    <div class="cv-preview-paper shadow-sm mx-auto" style="max-width: 820px; background: #ffffff; padding: 32px; border-radius: 6px; border: 1px solid #e2e8f0;">
        @include($templateView, ['cvData' => $cvData])
    </div>
</div>
