@extends('layouts.admin')

@section('title', 'Admin Inspect: ' . $cv->title)
@section('page-title', 'Resume Inspection')

@section('content')
<div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 d-print-none">
    <div>
        <a href="{{ route('admin.cvs.index') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to All CVs
        </a>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h4 fw-bold mb-0 text-dark">{{ $cv->title }}</h1>
            @if($cv->isPublished())
                <span class="badge-saas-published">Published</span>
            @else
                <span class="badge-saas-draft">Draft</span>
            @endif
            <span class="badge bg-light text-secondary border">
                <i class="bi bi-palette me-1"></i> {{ $templateModel?->name ?? 'Standard Template' }}
            </span>
        </div>
        <div class="small text-muted mt-1">
            Owner: <strong>{{ $cv->user?->name }}</strong> ({{ $cv->user?->email }}) &bull; Created: {{ $cv->created_at->format('M d, Y') }}
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-saas-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
    </div>
</div>

<!-- Preview Document Paper Container (Dynamic Template Render) -->
<div class="cv-preview-paper mb-5 shadow-sm mx-auto" style="max-width: 860px; background: #ffffff; padding: 40px; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 1000px;">
    @include($templateView, ['cvData' => $cvData, 'cv' => $cv])
</div>
@endsection
