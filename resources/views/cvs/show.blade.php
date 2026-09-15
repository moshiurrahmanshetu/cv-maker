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
            <span class="small text-muted">({{ $cv->completion_percentage }}% complete)</span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-saas-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
        <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']) }}" class="btn-saas-primary">
            <i class="bi bi-pencil-square"></i> Open CV Builder
        </a>
    </div>
</div>

<!-- CV Document Paper Container -->
<div class="cv-preview-paper mb-5">
    <!-- Header / Contact Info & Profile Photo -->
    <div class="border-bottom pb-4 mb-4 text-center">
        @if($cv->personalInfo?->photo_url)
            <div class="mb-3">
                <img src="{{ $cv->personalInfo->photo_url }}" alt="Profile Photo" class="rounded-circle border object-fit-cover shadow-sm" style="width: 96px; height: 96px;">
            </div>
        @endif

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
            @if($cv->personalInfo?->other_url)
                <div><i class="bi bi-link-45deg me-1"></i> {{ $cv->personalInfo->other_url }}</div>
            @endif
        </div>
    </div>

    <!-- 1. Professional Summary -->
    @if($cv->summary)
        <div class="mb-4">
            <div class="cv-section-heading">Professional Summary</div>
            <p class="text-secondary mb-0" style="line-height: 1.6; font-size: 0.95rem; white-space: pre-line;">
                {{ $cv->summary }}
            </p>
        </div>
    @endif

    <!-- 2. Work Experience -->
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

    <!-- 3. Education -->
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
                            {{ $edu->institution }}{{ $edu->field_of_study ? ' &bull; ' . $edu->field_of_study : '' }}{{ $edu->city ? ' | ' . $edu->city : '' }}
                        </div>
                        @if($edu->grade_or_gpa)
                            <div class="small text-muted mb-1">{{ $edu->grade_or_gpa }}</div>
                        @endif
                        @if($edu->description)
                            <p class="small text-secondary mb-0" style="line-height: 1.5; white-space: pre-line;">{{ $edu->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 4. Skills -->
    @if($cv->skills->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Core Skills & Competencies</div>
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

    <!-- 5. Languages -->
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

    <!-- 6. Projects -->
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
                        @if($proj->role)
                            <div class="small fw-semibold text-secondary">{{ $proj->role }}</div>
                        @endif
                        @if($proj->technologies)
                            <div class="small text-muted mb-1"><strong>Tech:</strong> {{ $proj->technologies }}</div>
                        @endif
                        @if($proj->description)
                            <p class="small text-secondary mb-1" style="line-height: 1.5; white-space: pre-line;">{{ $proj->description }}</p>
                        @endif
                        @if($proj->project_url)
                            <a href="{{ $proj->project_url }}" target="_blank" class="small text-decoration-none text-muted d-inline-block">
                                <i class="bi bi-link-45deg"></i> {{ $proj->project_url }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 7. Certifications -->
    @if($cv->certifications->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Certifications & Licenses</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cv->certifications as $cert)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <div class="small">
                                <strong>{{ $cert->name }}</strong> &ndash; <span class="text-secondary">{{ $cert->issuing_organization }}</span>
                                @if($cert->issue_date)
                                    <span class="text-muted">({{ $cert->issue_date }})</span>
                                @endif
                            </div>
                            @if($cert->credential_url)
                                <a href="{{ $cert->credential_url }}" target="_blank" class="small text-muted text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                        </div>
                        @if($cert->description)
                            <p class="small text-muted mb-0 mt-1">{{ $cert->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 8. Awards -->
    @if($cv->awards->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">Honors & Awards</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cv->awards as $award)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $award->title }}</h3>
                            @if($award->issue_date)
                                <span class="small text-muted font-monospace" style="font-size: 0.8rem;">{{ $award->issue_date }}</span>
                            @endif
                        </div>
                        @if($award->issuer)
                            <div class="small text-secondary">{{ $award->issuer }}</div>
                        @endif
                        @if($award->description)
                            <p class="small text-secondary mb-0 mt-1">{{ $award->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 9. Custom Sections -->
    @if($cv->customSections->isNotEmpty())
        @php
            $groupedCustom = $cv->customSections->groupBy('section_title');
        @endphp
        @foreach($groupedCustom as $sectionTitle => $entries)
            <div class="mb-4">
                <div class="cv-section-heading">{{ $sectionTitle }}</div>
                <div class="d-flex flex-column gap-2">
                    @foreach($entries as $entry)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                @if($entry->title)
                                    <h3 class="h6 fw-bold mb-0 text-dark">{{ $entry->title }}</h3>
                                @endif
                                @if($entry->date_period)
                                    <span class="small text-muted font-monospace" style="font-size: 0.8rem;">{{ $entry->date_period }}</span>
                                @endif
                            </div>
                            @if($entry->subtitle)
                                <div class="small text-secondary">{{ $entry->subtitle }}</div>
                            @endif
                            @if($entry->content)
                                <p class="small text-secondary mb-0 mt-1" style="white-space: pre-line;">{{ $entry->content }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <!-- 10. References (Visible entries only) -->
    @php
        $visibleReferences = $cv->references->where('is_hidden', false);
    @endphp
    @if($visibleReferences->isNotEmpty())
        <div class="mb-4">
            <div class="cv-section-heading">References</div>
            <div class="row g-3">
                @foreach($visibleReferences as $ref)
                    <div class="col-md-6">
                        <div class="p-3 border rounded-2 bg-surface-subtle">
                            <div class="fw-bold small text-dark">{{ $ref->full_name }}</div>
                            <div class="text-secondary small">{{ $ref->job_title }}{{ $ref->company ? ' at ' . $ref->company : '' }}</div>
                            @if($ref->email)
                                <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div>
                            @endif
                            @if($ref->phone)
                                <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $ref->phone }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
