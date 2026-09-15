@extends('layouts.app')

@section('title', 'Create New CV - CV Maker')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="mb-4">
            <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to My CVs
            </a>
            <h1 class="h3 fw-bold mt-2 mb-1">Create New CV</h1>
            <p class="text-muted small mb-0">Choose your starting template and enter a title. Your CV will be initialized as a draft so you can fill sections at your own pace.</p>
        </div>

        <form method="POST" action="{{ route('cvs.store') }}" id="createCvForm">
            @csrf

            <!-- 1. Choose Template Section -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h6 fw-bold mb-0 text-dark">Step 1: Choose Your Starting Template</h2>
                        <span class="small text-muted">You can switch templates at any time without losing any data.</span>
                    </div>
                    <span class="badge-saas-published">Normalized Architecture</span>
                </div>
                <div class="card-body p-4 bg-surface-subtle">
                    <!-- Hidden input to store chosen template ID -->
                    <input type="hidden" name="template_id" id="selectedTemplateInput" value="{{ old('template_id', $selectedTemplate?->id) }}">
                    <input type="hidden" name="template_key" id="selectedTemplateKeyInput" value="{{ old('template_key', $selectedTemplate?->key) }}">

                    <div class="row g-3">
                        @foreach($templates as $template)
                            @php
                                $isSelected = (old('template_id', $selectedTemplate?->id) == $template->id);
                            @endphp
                            <div class="col-md-4">
                                <div class="card h-100 template-choice-card border-2 cursor-pointer {{ $isSelected ? 'border-dark shadow' : 'border' }}"
                                     data-template-id="{{ $template->id }}"
                                     data-template-key="{{ $template->key }}"
                                     style="cursor: pointer; transition: all 0.2s ease;">
                                    
                                    <!-- Template Thumbnail -->
                                    <div class="bg-white p-2 text-center border-bottom position-relative" style="height: 190px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <img src="{{ $template->preview_image_url }}" alt="{{ $template->name }}" class="img-fluid rounded" style="max-height: 175px; object-fit: contain;">
                                        
                                        <!-- Selected checkmark indicator -->
                                        <div class="position-absolute top-0 end-0 m-2 selected-check-badge {{ $isSelected ? '' : 'd-none' }}">
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
                                            <span class="btn btn-sm select-btn {{ $isSelected ? 'btn-dark' : 'btn-outline-secondary' }}" style="font-size: 0.75rem;">
                                                {{ $isSelected ? 'Selected' : 'Select' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 2. CV Details Card -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h2 class="h6 fw-bold mb-0 text-dark">Step 2: Resume Title & Target Role</h2>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <label for="title" class="form-label fw-semibold">CV / Resume Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Software Engineer Resume" required autofocus>
                        <div class="form-text text-muted">A private title to organize this resume in your dashboard.</div>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="job_title" class="form-label fw-semibold">Target Job Headline (Optional)</label>
                        <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Principal Software Architect, Marketing Director">
                        <div class="form-text text-muted">The primary role title that will appear at the top of your resume.</div>
                        @error('job_title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="p-3 bg-surface-subtle border rounded-2 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold small text-dark">
                            <i class="bi bi-shield-check text-muted"></i>
                            <span>Zero Data Loss Policy</span>
                        </div>
                        <p class="small text-muted mb-0">
                            Your career details are saved in a normalized format. You can switch to any other template at any time in the live previewer.
                        </p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('cvs.index') }}" class="btn-saas-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-saas-primary px-4">
                            <i class="bi bi-arrow-right"></i> Create Draft & Open Builder
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.template-choice-card');
        const inputId = document.getElementById('selectedTemplateInput');
        const inputKey = document.getElementById('selectedTemplateKeyInput');

        cards.forEach(card => {
            card.addEventListener('click', function () {
                const templateId = this.getAttribute('data-template-id');
                const templateKey = this.getAttribute('data-template-key');

                inputId.value = templateId;
                inputKey.value = templateKey;

                cards.forEach(c => {
                    c.classList.remove('border-dark', 'shadow');
                    c.classList.add('border');
                    const badge = c.querySelector('.selected-check-badge');
                    if (badge) badge.classList.add('d-none');
                    const btn = c.querySelector('.select-btn');
                    if (btn) {
                        btn.className = 'btn btn-sm btn-outline-secondary select-btn';
                        btn.textContent = 'Select';
                    }
                });

                this.classList.add('border-dark', 'shadow');
                this.classList.remove('border');
                const badge = this.querySelector('.selected-check-badge');
                if (badge) badge.classList.remove('d-none');
                const btn = this.querySelector('.select-btn');
                if (btn) {
                    btn.className = 'btn btn-sm btn-dark select-btn';
                    btn.textContent = 'Selected';
                }
            });
        });
    });
</script>
@endsection
