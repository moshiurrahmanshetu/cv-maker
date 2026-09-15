@extends('layouts.app')

@section('title', $cv->title . ' - Live Preview')

@section('content')
<!-- Top Control Bar (Hidden when printing) -->
<div class="mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 d-print-none bg-white p-3 p-md-4 rounded-3 border shadow-sm">
    <div>
        <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to My CVs
        </a>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
            <h1 class="h4 fw-bold mb-0 text-dark">{{ $cv->title }}</h1>
            @if($cv->isPublished())
                <span class="badge-saas-published">Published</span>
            @else
                <span class="badge-saas-draft">Draft</span>
            @endif
            <span class="small text-muted">({{ $cv->completion_percentage }}% complete)</span>
        </div>
    </div>

    <!-- Template Switcher & Actions -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Template Switcher Dropdown Form -->
        <form action="{{ route('cvs.switch-template', $cv) }}" method="POST" class="d-inline-flex align-items-center gap-1" id="templateSwitchForm">
            @csrf
            <label for="templateSwitcherSelect" class="small fw-semibold text-secondary me-1 d-none d-sm-inline">Template:</label>
            <div class="input-group input-group-sm" style="min-width: 210px;">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <i class="bi bi-palette"></i>
                </span>
                <select name="template_id" id="templateSwitcherSelect" class="form-select form-select-sm border-start-0 fw-semibold" onchange="document.getElementById('templateSwitchForm').submit();">
                    @foreach($activeTemplates as $tmpl)
                        <option value="{{ $tmpl->id }}" {{ ($cv->template_id == $tmpl->id || ($cv->template_key == $tmpl->key && !$cv->template_id)) ? 'selected' : '' }}>
                            {{ $tmpl->name }} ({{ $tmpl->category->name }}){{ $tmpl->is_premium ? ' ★' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <button type="button" class="btn-saas-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>

        <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']) }}" class="btn-saas-primary btn-sm">
            <i class="bi bi-pencil-square"></i> Open Builder
        </a>
    </div>
</div>

<!-- Live CV Document Container (A4 oriented) -->
<div class="cv-preview-paper mb-5 shadow-sm mx-auto" style="max-width: 860px; background: #ffffff; padding: 40px; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 1050px;">
    @include($templateView, ['cvData' => $cvData, 'cv' => $cv])
</div>
@endsection
