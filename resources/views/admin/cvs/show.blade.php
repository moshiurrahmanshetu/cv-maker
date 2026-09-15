@extends('layouts.admin')

@section('title', 'Admin Inspect: ' . $cv->title)
@section('page-title', 'Resume Inspection')

@section('content')
<div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
        <a href="{{ route('admin.cvs.index') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to All CVs
        </a>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h4 fw-bold mb-0">{{ $cv->title }}</h1>
            @if($cv->isPublished())
                <span class="badge-saas-published">Published</span>
            @else
                <span class="badge-saas-draft">Draft</span>
            @endif
        </div>
        <div class="small text-muted mt-1">
            Owner: <strong>{{ $cv->user?->name }}</strong> ({{ $cv->user?->email }}) &bull; Created: {{ $cv->created_at->format('M d, Y') }}
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-saas-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<!-- Preview Document Paper Container -->
<div class="cv-preview-paper mb-5 bg-white">
    <!-- Header / Contact Info -->
    <div class="border-bottom pb-4 mb-4 text-center">
        <h2 class="display-6 fw-bold mb-1" style="color: var(--cv-dark);">
            {{ $cv->personalInfo?->full_name ?? 'Name Not Provided' }}
        </h2>
        <div class="h5 fw-semibold text-secondary mb-3">
            {{ $cv->personalInfo?->job_title ?? 'Title Not Provided' }}
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3 small text-muted">
            @if($cv->personalInfo?->email)
                <div><i class="bi bi-envelope me-1"></i> {{ $cv->personalInfo->email }}</div>
            @endif
            @if($cv->personalInfo?->phone)
                <div><i class="bi bi-telephone me-1"></i> {{ $cv->personalInfo->phone }}</div>
            @endif
            @if($cv->personalInfo?->city || $cv->personalInfo?->country)
                <div><i class="bi bi-geo-alt me-1"></i> {{ implode(', ', array_filter([$cv->personalInfo->city, $cv->personalInfo->country])) }}</div>
            @endif
            @if($cv->personalInfo?->website)
                <div><i class="bi bi-globe me-1"></i> {{ $cv->personalInfo->website }}</div>
            @endif
            @if($cv->personalInfo?->linkedin)
                <div><i class="bi bi-linkedin me-1"></i> {{ $cv->personalInfo->linkedin }}</div>
            @endif
        </div>
    </div>

    <!-- Professional Summary -->
    @if($cv->summary)
        <div class="mb-4">
            <div class="cv-section-heading">Professional Summary</div>
            <p class="text-secondary mb-0" style="line-height: 1.6; font-size: 0.95rem;">
                {{ $cv->summary }}
            </p>
        </div>
    @endif

    <!-- Work Experience -->
    @if($cv->experiences->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Work Experience</div>
            <div class="d-flex flex-column gap-3">
                @foreach($cv->experiences as $exp)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $exp->job_title }}</h3>
                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                {{ $exp->start_date }} &ndash; {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                            </span>
                        </div>
                        <div class="small fw-semibold text-secondary mb-1">
                            {{ $exp->employer }}{{ $exp->city ? ' | ' . $exp->city : '' }}
                        </div>
                        @if($exp->description)
                            <p class="small text-secondary mb-0" style="white-space: pre-line;">{{ $exp->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Education -->
    @if($cv->educations->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Education</div>
            <div class="d-flex flex-column gap-3">
                @foreach($cv->educations as $edu)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $edu->degree }}</h3>
                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                {{ $edu->start_date }} &ndash; {{ $edu->end_date ?? 'Present' }}
                            </span>
                        </div>
                        <div class="small fw-semibold text-secondary">
                            {{ $edu->institution }}{{ $edu->city ? ' | ' . $edu->city : '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Skills -->
    @if($cv->skills->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Core Skills</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($cv->skills as $skill)
                    <span class="badge bg-light text-dark border px-3 py-2 fw-medium" style="font-size: 0.85rem;">
                        {{ $skill->name }}
                        @if($skill->level)
                            <span class="text-muted ms-1 small">({{ $skill->level }})</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
