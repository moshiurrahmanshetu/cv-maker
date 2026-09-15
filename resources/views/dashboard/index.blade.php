@extends('layouts.app')

@section('title', 'Dashboard - Career Document Maker')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Welcome, {{ $user->name }}</h1>
        <p class="text-muted small mb-0">Manage your CVs, Resumes, Cover Letters, and Motivation Letters.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('cvs.create') }}" class="btn-saas-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Document
        </a>
    </div>
</div>

<!-- Quick Stats Row (Real user metrics) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Total Documents</div>
                <div class="stat-widget-number">{{ $totalCvs }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Published / Ready</div>
                <div class="stat-widget-number">{{ $publishedCvs }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Incomplete Drafts</div>
                <div class="stat-widget-number">{{ $draftCvs }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Documents Section -->
<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="fw-bold">
            <i class="bi bi-clock-history me-1"></i> Recent Career Documents
        </div>
        <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none fw-semibold">
            View All ({{ $totalCvs }}) <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-4">
        @if($recentCvs->isEmpty())
            <div class="text-center py-5">
                <div class="stat-widget-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <h3 class="h5 fw-bold mb-1">No documents created yet</h3>
                <p class="text-muted small mb-4">Start by creating your first CV, Resume, or Cover Letter draft.</p>
                <a href="{{ route('cvs.create') }}" class="btn-saas-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create First Document
                </a>
            </div>
        @else
            <div class="row g-3">
                @foreach($recentCvs as $cv)
                    @php
                        $isLetter = $cv->isLetter();
                        $docType = $cv->documentType;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-saas h-100 p-3 d-flex flex-column justify-content-between border">
                            <div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <span class="badge {{ $isLetter ? 'badge-saas-draft' : 'badge-saas-published' }} d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;">
                                        <i class="{{ $docType?->icon ?? 'bi bi-file-earmark-text' }}"></i>
                                        {{ $docType?->name ?? 'Document' }}
                                    </span>
                                    @if($cv->isPublished())
                                        <span class="badge-saas-published flex-shrink-0">Published</span>
                                    @else
                                        <span class="badge-saas-draft flex-shrink-0">Draft</span>
                                    @endif
                                </div>

                                <h2 class="h6 fw-bold mb-1 text-truncate" title="{{ $cv->title }}">
                                    {{ $cv->title }}
                                </h2>

                                <p class="small text-muted mb-3 text-truncate">
                                    @if($isLetter)
                                        {{ $cv->letterDetail?->recipient_company ?? ($cv->letterDetail?->recipient_name ? 'To: '.$cv->letterDetail->recipient_name : 'No recipient specified') }}
                                    @else
                                        {{ $cv->personalInfo?->job_title ?? 'No target title specified' }}
                                    @endif
                                </p>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 0.78rem;">
                                        <span>Completion</span>
                                        <span class="fw-semibold">{{ $cv->completion_percentage }}%</span>
                                    </div>
                                    <div class="progress-saas">
                                        <div class="progress-saas-bar" style="width: {{ $cv->completion_percentage }}%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                <span class="small text-muted" style="font-size: 0.78rem;">
                                    Updated {{ $cv->updated_at->diffForHumans() }}
                                </span>
                                <div class="d-flex align-items-center gap-1">
                                    <a href="{{ route('cvs.show', $cv) }}" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Preview Document">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('cvs.edit', $cv) }}" class="btn btn-sm btn-saas-primary py-1 px-2" title="{{ $cv->isDraft() ? 'Continue Draft' : 'Edit Document' }}">
                                        <i class="bi {{ $cv->isDraft() ? 'bi-pencil-square' : 'bi-pencil' }}"></i>
                                        <span class="d-none d-sm-inline ms-1">{{ $cv->isDraft() ? 'Continue Draft' : 'Edit' }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
