@extends('layouts.app')

@section('title', 'My Documents - Career Document Maker')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">My Career Documents</h1>
        <p class="text-muted small mb-0">Manage all your CVs, Resumes, Cover Letters, and Motivation Letters in one unified workspace.</p>
    </div>
    <a href="{{ route('cvs.create') }}" class="btn-saas-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Document
    </a>
</div>

<!-- Document Type Quick-Filter Tabs -->
<div class="mb-4 overflow-auto pb-1">
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('cvs.index', array_merge(request()->except('document_type', 'page'))) }}" 
           class="btn btn-sm {{ !request('document_type') ? 'btn-dark' : 'btn-outline-secondary' }} text-nowrap rounded-pill px-3 py-1">
            <i class="bi bi-collection me-1"></i> All Documents
        </a>
        @foreach($documentTypes as $docType)
            @php
                $isActive = (request('document_type') === $docType->slug);
            @endphp
            <a href="{{ route('cvs.index', array_merge(request()->except('page'), ['document_type' => $docType->slug])) }}" 
               class="btn btn-sm {{ $isActive ? 'btn-dark' : 'btn-outline-secondary' }} text-nowrap rounded-pill px-3 py-1">
                <i class="{{ $docType->icon ?? 'bi bi-file-earmark' }} me-1"></i> {{ $docType->name }}
            </a>
        @endforeach
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('cvs.index') }}" class="row g-2 align-items-center">
        @if(request('document_type'))
            <input type="hidden" name="document_type" value="{{ request('document_type') }}">
        @endif
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, headline, or details..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Drafts Only</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published Only</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn-saas-secondary w-100 justify-content-center">
                Filter
            </button>
            @if(request('search') || request('status') || request('document_type'))
                <a href="{{ route('cvs.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Documents Grid -->
@if($cvs->isEmpty())
    <div class="card card-saas text-center py-5">
        <div class="stat-widget-icon mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="bi bi-journal-text"></i>
        </div>
        <h3 class="h5 fw-bold mb-1">No documents found</h3>
        <p class="text-muted small mb-4">
            @if(request('search') || request('status') || request('document_type'))
                No career documents match your current filter criteria.
            @else
                You haven't created any documents yet. Start crafting your professional resume or cover letter.
            @endif
        </p>
        <div>
            @if(request('search') || request('status') || request('document_type'))
                <a href="{{ route('cvs.index') }}" class="btn-saas-secondary me-2">Clear Filters</a>
            @endif
            <a href="{{ route('cvs.create') }}" class="btn-saas-primary">
                <i class="bi bi-plus-lg me-1"></i> Create New Document
            </a>
        </div>
    </div>
@else
    <div class="row g-3 mb-4">
        @foreach($cvs as $cv)
            @php
                $isLetter = $cv->isLetter();
                $docType = $cv->documentType;
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card card-saas h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header with Document Type Badge and Status -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="badge {{ $isLetter ? 'badge-saas-draft' : 'badge-saas-published' }} d-inline-flex align-items-center gap-1">
                                <i class="{{ $docType?->icon ?? 'bi bi-file-earmark-text' }}"></i>
                                {{ $docType?->name ?? 'Document' }}
                            </span>
                            @if($cv->isPublished())
                                <span class="badge-saas-published flex-shrink-0">Published</span>
                            @else
                                <span class="badge-saas-draft flex-shrink-0">Draft</span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h2 class="h6 fw-bold mb-1 text-truncate" title="{{ $cv->title }}">
                            {{ $cv->title }}
                        </h2>

                        <!-- Subtitle / Headline / Recipient -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <p class="small text-muted mb-0 text-truncate">
                                @if($isLetter)
                                    {{ $cv->letterDetail?->recipient_company ?? ($cv->letterDetail?->recipient_name ? 'To: '.$cv->letterDetail->recipient_name : 'No recipient specified') }}
                                @else
                                    {{ $cv->personalInfo?->job_title ?? 'No role headline specified' }}
                                @endif
                            </p>
                            @if($cv->template)
                                <span class="badge bg-light text-secondary border font-monospace flex-shrink-0" style="font-size: 0.68rem;">
                                    <i class="bi bi-palette me-1"></i>{{ $cv->template->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Completion Progress -->
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

                    <!-- Footer Actions -->
                    <div class="pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-muted" style="font-size: 0.78rem;">
                                Modified {{ $cv->updated_at->format('M d, Y') }}
                            </span>
                            
                            <!-- Toggle Status Action -->
                            <form action="{{ route('cvs.toggle-status', $cv) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none small text-secondary" style="font-size: 0.78rem;">
                                    {{ $cv->isDraft() ? 'Mark Ready' : 'Revert to Draft' }}
                                </button>
                            </form>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-1">
                            <div class="d-flex gap-1">
                                <a href="{{ route('cvs.show', $cv) }}" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Preview Document">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('cvs.pdf', $cv) }}" class="btn btn-sm btn-saas-secondary py-1 px-2 text-dark" title="Download PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="{{ route('cvs.edit', $cv) }}" class="btn btn-sm btn-saas-primary py-1 px-2" title="{{ $cv->isDraft() ? 'Continue Draft' : 'Edit Document' }}">
                                    <i class="bi {{ $cv->isDraft() ? 'bi-pencil-square' : 'bi-pencil' }}"></i>
                                    <span class="d-none d-sm-inline ms-1">{{ $cv->isDraft() ? 'Continue Draft' : 'Edit' }}</span>
                                </a>
                            </div>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-saas-secondary py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end">
                                    <li>
                                        <a href="{{ route('cvs.pdf', $cv) }}" class="dropdown-item dropdown-item-saas">
                                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('cvs.pdf.preview', $cv) }}" target="_blank" class="dropdown-item dropdown-item-saas">
                                            <i class="bi bi-file-pdf"></i> View PDF in Tab
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('cvs.duplicate', $cv) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item dropdown-item-saas">
                                                <i class="bi bi-copy"></i> Duplicate Document
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider-saas"></li>
                                    <li>
                                        <button type="button" class="dropdown-item dropdown-item-saas text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $cv->id }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal{{ $cv->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $cv->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border card-saas">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold fs-6" id="deleteModalLabel{{ $cv->id }}">Delete Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-4">
                            <p class="mb-1">Are you sure you want to permanently delete <strong>"{{ $cv->title }}"</strong>?</p>
                            <p class="small text-muted mb-0">This action will remove all structured sections and content. This cannot be undone.</p>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('cvs.destroy', $cv) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm px-3 py-2">
                                    <i class="bi bi-trash me-1"></i> Confirm Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $cvs->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
