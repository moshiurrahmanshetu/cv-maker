@extends('layouts.app')

@section('title', 'Create New Career Document - CV Maker')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="mb-4">
            <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to My Documents
            </a>
            <h1 class="h3 fw-bold mt-2 mb-1">Create Career Document</h1>
            <p class="text-muted small mb-0">Select your document type, pick a compatible layout, and customize your details. Everything is safely auto-saved as a draft.</p>
        </div>

        <form method="POST" action="{{ route('cvs.store') }}" id="createDocumentForm">
            @csrf

            <!-- Hidden Inputs -->
            <input type="hidden" name="document_type_id" id="selectedDocTypeId" value="{{ old('document_type_id', $selectedType?->id) }}">
            <input type="hidden" name="template_id" id="selectedTemplateId" value="{{ old('template_id', $selectedTemplate?->id) }}">
            <input type="hidden" name="template_key" id="selectedTemplateKey" value="{{ old('template_key', $selectedTemplate?->key) }}">

            <!-- Step 1: Select Document Type -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark">Step 1: Choose Document Type</h2>
                        <span class="small text-muted">Select the specific format tailored for your career application.</span>
                    </div>
                    <span class="badge-saas-published">7 Available Formats</span>
                </div>
                <div class="card-body p-4 bg-surface-subtle">
                    <div class="row g-3">
                        @foreach($documentTypes as $docType)
                            @php
                                $isTypeSelected = (old('document_type_id', $selectedType?->id) == $docType->id);
                                $isLetter = $docType->isLetterBased();
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 doc-type-choice-card border-2 cursor-pointer {{ $isTypeSelected ? 'border-dark shadow-sm bg-white' : 'border bg-white' }}"
                                     data-type-id="{{ $docType->id }}"
                                     data-type-slug="{{ $docType->slug }}"
                                     data-is-letter="{{ $isLetter ? '1' : '0' }}"
                                     style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="p-2 rounded bg-light text-dark">
                                                        <i class="{{ $docType->icon ?? 'bi bi-file-earmark-text' }} fs-5"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark small">{{ $docType->name }}</span>
                                                </div>
                                                <div class="type-check-badge {{ $isTypeSelected ? '' : 'd-none' }}">
                                                    <i class="bi bi-check-circle-fill text-dark fs-5"></i>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                                                {{ $docType->description ?? 'Professional tailored career document layout.' }}
                                            </p>
                                        </div>
                                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                                            <span class="badge {{ $isLetter ? 'badge-saas-draft' : 'badge-saas-published' }}" style="font-size: 0.7rem;">
                                                {{ $isLetter ? 'Letter Format' : 'Resume/CV' }}
                                            </span>
                                            <span class="small fw-semibold text-muted type-select-label" style="font-size: 0.75rem;">
                                                {{ $isTypeSelected ? 'Selected' : 'Select' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Step 2: Choose Compatible Template -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark">Step 2: Choose Layout & Template</h2>
                        <span class="small text-muted">Showing templates designed and optimized for the chosen document type.</span>
                    </div>
                    <span class="badge-saas-published" id="templateCountBadge">{{ count($allTemplates) }} Available</span>
                </div>
                <div class="card-body p-4 bg-surface-subtle">
                    <div class="row g-3" id="templatesContainer">
                        @foreach($allTemplates as $template)
                            @php
                                $isTemplateSelected = (old('template_id', $selectedTemplate?->id) == $template->id);
                                $compatibleTypeIds = $template->documentTypes->pluck('id')->toArray();
                            @endphp
                            <div class="col-md-4 template-item-col" 
                                 data-template-id="{{ $template->id }}"
                                 data-template-key="{{ $template->key }}"
                                 data-compatible-types="{{ json_encode($compatibleTypeIds) }}">
                                <div class="card h-100 template-choice-card border-2 cursor-pointer {{ $isTemplateSelected ? 'border-dark shadow' : 'border' }}"
                                     data-template-id="{{ $template->id }}"
                                     data-template-key="{{ $template->key }}"
                                     style="cursor: pointer; transition: all 0.2s ease;">
                                    
                                    <!-- Template Thumbnail -->
                                    <div class="bg-white p-2 text-center border-bottom position-relative" style="height: 190px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <img src="{{ $template->preview_image_url }}" alt="{{ $template->name }}" class="img-fluid rounded" style="max-height: 175px; object-fit: contain;">
                                        
                                        <!-- Selected checkmark indicator -->
                                        <div class="position-absolute top-0 end-0 m-2 selected-template-badge {{ $isTemplateSelected ? '' : 'd-none' }}">
                                            <span class="badge bg-dark text-white rounded-pill px-2 py-1 shadow-sm">
                                                <i class="bi bi-check2-circle me-1"></i> Selected
                                            </span>
                                        </div>

                                        <div class="position-absolute top-0 start-0 m-2">
                                            @if($template->is_premium)
                                                <span class="badge-saas-draft">Premium</span>
                                            @else
                                                <span class="badge-saas-published">Free</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Card Content -->
                                    <div class="card-body p-3 bg-white d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $template->name }}</div>
                                            <div class="text-muted" style="font-size: 0.76rem;">{{ $template->category->name }}</div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <span class="btn btn-sm template-select-btn {{ $isTemplateSelected ? 'btn-dark' : 'btn-outline-secondary' }}" style="font-size: 0.75rem;">
                                                {{ $isTemplateSelected ? 'Selected' : 'Select' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Step 3: Title & Initial Details -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h2 class="h6 fw-bold mb-0 text-dark">Step 3: Document Details</h2>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Product Designer CV (2026)" required autofocus>
                        <div class="form-text text-muted">A private title to organize this document in your workspace.</div>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" id="jobTitleFieldGroup">
                        <label for="job_title" class="form-label fw-semibold" id="jobTitleLabel">Target Job Headline / Role (Optional)</label>
                        <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Principal Software Architect, Marketing Director">
                        <div class="form-text text-muted" id="jobTitleHelp">The target headline that appears prominently on your document.</div>
                        @error('job_title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 d-none" id="companyFieldGroup">
                        <label for="company_or_institution" class="form-label fw-semibold">Target Company or University / Institution (Optional)</label>
                        <input type="text" class="form-control @error('company_or_institution') is-invalid @enderror" id="company_or_institution" name="company_or_institution" value="{{ old('company_or_institution') }}" placeholder="e.g. Acme Corp / Stanford University">
                        <div class="form-text text-muted">The organization or institution you are writing this letter to.</div>
                        @error('company_or_institution')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="p-3 bg-surface-subtle border rounded-2 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold small text-dark">
                            <i class="bi bi-shield-check text-muted"></i>
                            <span>Zero Data Loss Policy</span>
                        </div>
                        <p class="small text-muted mb-0">
                            Your career details and contact information are safely shared across documents. You can switch templates or modify document settings anytime in the builder.
                        </p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('cvs.index') }}" class="btn-saas-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-saas-primary px-4">
                            <i class="bi bi-arrow-right me-1"></i> Create Document & Open Builder
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeCards = document.querySelectorAll('.doc-type-choice-card');
        const templateCards = document.querySelectorAll('.template-choice-card');
        const templateCols = document.querySelectorAll('.template-item-col');
        
        const docTypeIdInput = document.getElementById('selectedDocTypeId');
        const templateIdInput = document.getElementById('selectedTemplateId');
        const templateKeyInput = document.getElementById('selectedTemplateKey');
        
        const jobTitleLabel = document.getElementById('jobTitleLabel');
        const jobTitleHelp = document.getElementById('jobTitleHelp');
        const companyFieldGroup = document.getElementById('companyFieldGroup');
        const templateCountBadge = document.getElementById('templateCountBadge');

        function filterTemplates(selectedTypeId) {
            let visibleCount = 0;
            let currentSelectedStillVisible = false;

            templateCols.forEach(col => {
                const rawCompat = col.getAttribute('data-compatible-types');
                let compatTypes = [];
                try {
                    compatTypes = JSON.parse(rawCompat) || [];
                } catch (e) {
                    compatTypes = [];
                }

                // If no type restriction, or type is in compatible list
                const isCompatible = compatTypes.length === 0 || compatTypes.includes(parseInt(selectedTypeId));

                if (isCompatible) {
                    col.style.display = 'block';
                    visibleCount++;
                    if (col.getAttribute('data-template-id') === templateIdInput.value) {
                        currentSelectedStillVisible = true;
                    }
                } else {
                    col.style.display = 'none';
                }
            });

            if (templateCountBadge) {
                templateCountBadge.textContent = visibleCount + ' Compatible';
            }

            // If currently selected template is hidden, auto-select the first visible one
            if (!currentSelectedStillVisible) {
                for (let col of templateCols) {
                    if (col.style.display !== 'none') {
                        const card = col.querySelector('.template-choice-card');
                        if (card) {
                            selectTemplate(card);
                            break;
                        }
                    }
                }
            }
        }

        function selectDocType(card) {
            const typeId = card.getAttribute('data-type-id');
            const typeSlug = card.getAttribute('data-type-slug');
            const isLetter = card.getAttribute('data-is-letter') === '1';

            docTypeIdInput.value = typeId;

            typeCards.forEach(c => {
                c.classList.remove('border-dark', 'shadow-sm');
                c.classList.add('border');
                const badge = c.querySelector('.type-check-badge');
                if (badge) badge.classList.add('d-none');
                const lbl = c.querySelector('.type-select-label');
                if (lbl) lbl.textContent = 'Select';
            });

            card.classList.add('border-dark', 'shadow-sm');
            card.classList.remove('border');
            const badge = card.querySelector('.type-check-badge');
            if (badge) badge.classList.remove('d-none');
            const lbl = card.querySelector('.type-select-label');
            if (lbl) lbl.textContent = 'Selected';

            // Update letter-specific form fields
            if (isLetter) {
                if (companyFieldGroup) companyFieldGroup.classList.remove('d-none');
                if (jobTitleLabel) jobTitleLabel.textContent = 'Target Position / Program Applied For (Optional)';
                if (jobTitleHelp) jobTitleHelp.textContent = 'e.g. Lead Software Engineer or MSc in Computer Science';
            } else {
                if (companyFieldGroup) companyFieldGroup.classList.add('d-none');
                if (jobTitleLabel) jobTitleLabel.textContent = 'Target Job Headline / Role (Optional)';
                if (jobTitleHelp) jobTitleHelp.textContent = 'The target headline that appears prominently on your document.';
            }

            filterTemplates(typeId);
        }

        function selectTemplate(card) {
            const templateId = card.getAttribute('data-template-id');
            const templateKey = card.getAttribute('data-template-key');

            templateIdInput.value = templateId;
            templateKeyInput.value = templateKey;

            templateCards.forEach(c => {
                c.classList.remove('border-dark', 'shadow');
                c.classList.add('border');
                const badge = c.querySelector('.selected-template-badge');
                if (badge) badge.classList.add('d-none');
                const btn = c.querySelector('.template-select-btn');
                if (btn) {
                    btn.className = 'btn btn-sm btn-outline-secondary template-select-btn';
                    btn.textContent = 'Select';
                }
            });

            card.classList.add('border-dark', 'shadow');
            card.classList.remove('border');
            const badge = card.querySelector('.selected-template-badge');
            if (badge) badge.classList.remove('d-none');
            const btn = card.querySelector('.template-select-btn');
            if (btn) {
                btn.className = 'btn btn-sm btn-dark template-select-btn';
                btn.textContent = 'Selected';
            }
        }

        typeCards.forEach(card => {
            card.addEventListener('click', function () {
                selectDocType(this);
            });
        });

        templateCards.forEach(card => {
            card.addEventListener('click', function () {
                selectTemplate(this);
            });
        });

        // Initial filter on page load
        if (docTypeIdInput.value) {
            filterTemplates(docTypeIdInput.value);
        }
    });
</script>
@endpush
@endsection
