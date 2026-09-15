@extends('layouts.app')

@section('title', $cv->title . ' - Preview')

@section('content')
<div class="mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 d-print-none">
    <div>
        <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to My CVs
        </a>
        <div class="d-flex align-items-center gap-2 mt-1">
            <h1 class="h3 fw-bold mb-0">{{ $cv->title }}</h1>
            @if($cv->isPublished())
                <span class="badge-saas-published">Published</span>
            @else
                <span class="badge-saas-draft">Draft</span>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-saas-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
        <a href="{{ route('cvs.edit', $cv) }}" class="btn-saas-primary">
            <i class="bi bi-pencil"></i> Edit CV
        </a>
    </div>
</div>

<!-- CV Document Paper Container -->
<div class="cv-preview-paper mb-5">
    <!-- Header / Contact Info -->
    <div class="border-bottom pb-4 mb-4 text-center">
        <h2 class="display-6 fw-bold mb-1" style="color: var(--cv-dark);">
            {{ $cv->personalInfo?->full_name ?? 'Your Name' }}
        </h2>
        <div class="h5 fw-semibold text-secondary mb-3">
            {{ $cv->personalInfo?->job_title ?? 'Target Role / Title' }}
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
            @if($cv->personalInfo?->github)
                <div><i class="bi bi-github me-1"></i> {{ $cv->personalInfo->github }}</div>
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
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $exp->job_title }}</h3>
                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                {{ $exp->start_date }} &ndash; {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                            </span>
                        </div>
                        <div class="small fw-semibold text-secondary mb-2">
                            {{ $exp->employer }}{{ $exp->city ? ' | ' . $exp->city : '' }}{{ $exp->country ? ', ' . $exp->country : '' }}
                        </div>
                        @if($exp->description)
                            <p class="small text-secondary mb-0" style="line-height: 1.5; white-space: pre-line;">{{ $exp->description }}</p>
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
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $edu->degree }}</h3>
                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                {{ $edu->start_date }} &ndash; {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                            </span>
                        </div>
                        <div class="small fw-semibold text-secondary mb-1">
                            {{ $edu->institution }}{{ $edu->city ? ' | ' . $edu->city : '' }}
                        </div>
                        @if($edu->grade_or_gpa)
                            <div class="small text-muted">{{ $edu->grade_or_gpa }}</div>
                        @endif
                        @if($edu->description)
                            <p class="small text-secondary mb-0 mt-1">{{ $edu->description }}</p>
                        @endif
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

    <!-- Languages -->
    @if($cv->languages->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Languages</div>
            <div class="d-flex flex-wrap gap-3">
                @foreach($cv->languages as $lang)
                    <div class="small">
                        <strong>{{ $lang->language }}</strong>: <span class="text-muted">{{ $lang->proficiency }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Projects -->
    @if($cv->projects->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Key Projects</div>
            <div class="d-flex flex-column gap-3">
                @foreach($cv->projects as $proj)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $proj->title }}</h3>
                            @if($proj->start_date)
                                <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                    {{ $proj->start_date }} {{ $proj->end_date ? '&ndash; ' . $proj->end_date : '' }}
                                </span>
                            @endif
                        </div>
                        @if($proj->project_url)
                            <a href="{{ $proj->project_url }}" target="_blank" class="small text-decoration-none text-muted d-inline-block mb-1">
                                <i class="bi bi-link-45deg"></i> {{ $proj->project_url }}
                            </a>
                        @endif
                        @if($proj->description)
                            <p class="small text-secondary mb-0">{{ $proj->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Certifications -->
    @if($cv->certifications->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Certifications</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cv->certifications as $cert)
                    <div class="small">
                        <strong>{{ $cert->name }}</strong> &ndash; <span class="text-secondary">{{ $cert->issuing_organization }}</span>
                        @if($cert->issue_date)
                            <span class="text-muted">({{ $cert->issue_date }})</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- References -->
    @if($cv->references->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">References</div>
            <div class="row g-3">
                @foreach($cv->references as $ref)
                    <div class="col-md-6">
                        <div class="p-2 border rounded-1 bg-surface-subtle">
                            <div class="fw-bold small">{{ $ref->full_name }}</div>
                            <div class="text-secondary small">{{ $ref->job_title }} at {{ $ref->company }}</div>
                            @if($ref->email)
                                <div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
