@extends('layouts.app')

@section('title', 'Template Preview: ' . $template->name)

@section('content')
<div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 d-print-none">
    <div>
        <a href="{{ url()->previous() ?: route('home') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h3 fw-bold mb-0">{{ $template->name }}</h1>
            @if($template->is_premium)
                <span class="badge-saas-draft">Premium</span>
            @else
                <span class="badge-saas-published">Free</span>
            @endif
            <span class="badge bg-light text-secondary border">{{ $template->category->name }}</span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-saas-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
        @auth
            <a href="{{ route('cvs.create', ['template_id' => $template->id]) }}" class="btn-saas-primary">
                <i class="bi bi-plus-lg"></i> Use This Template
            </a>
        @else
            <a href="{{ route('register', ['template_id' => $template->id]) }}" class="btn-saas-primary">
                <i class="bi bi-lightning-charge"></i> Use This Template
            </a>
        @endauth
    </div>
</div>

<div class="cv-preview-paper mb-5 shadow-sm mx-auto" style="max-width: 860px; background: #ffffff; padding: 40px; border-radius: 8px; border: 1px solid #e2e8f0;">
    @include($templateView, ['cvData' => $cvData])
</div>
@endsection
