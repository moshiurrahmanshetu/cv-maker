@extends('layouts.app')

@section('title', 'Document Builder: ' . $cv->title)

@section('content')
<style>
    /* Flexible Builder Workspace Layout */
    .builder-split-workspace {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        width: 100%;
    }

    #builderNavSidebar {
        width: 220px;
        min-width: 220px;
        max-width: 220px;
        flex: 0 0 220px;
        transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.2s cubic-bezier(0.4, 0, 0.2, 1), max-width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: sticky;
        top: 80px;
        z-index: 10;
    }

    #builderNavSidebar.sidebar-collapsed {
        width: 58px;
        min-width: 58px;
        max-width: 58px;
        flex: 0 0 58px;
    }

    #builderNavSidebar.sidebar-collapsed .sidebar-header-title {
        display: none !important;
    }

    #builderNavSidebar.sidebar-collapsed .sidebar-header {
        justify-content: center !important;
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
    }

    #builderNavSidebar.sidebar-collapsed .sidebar-link {
        justify-content: center !important;
        padding: 0.6rem 0.25rem !important;
        text-align: center;
    }

    #builderNavSidebar.sidebar-collapsed .sidebar-link-text,
    #builderNavSidebar.sidebar-collapsed .sidebar-badge,
    #builderNavSidebar.sidebar-collapsed .sidebar-check {
        display: none !important;
    }

    #builderNavSidebar.sidebar-collapsed .sidebar-link i {
        font-size: 1.15rem;
        margin: 0 !important;
    }

    .builder-form-content {
        flex: 1 1 0%;
        min-width: 0;
        transition: all 0.2s ease;
    }

    /* Repeatable Sections UX Elements */
    .repeatable-card {
        transition: all 0.2s ease;
        border: 1px solid var(--saas-border, #e2e8f0);
        background: #ffffff;
    }

    .repeatable-card:hover {
        border-color: #cbd5e1;
    }

    .repeatable-card.is-new-entry {
        border-left: 3px solid #0284c7 !important;
    }

    .sticky-action-bar {
        position: sticky;
        bottom: 0;
        z-index: 9;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
        margin: 1.5rem -1.5rem -1.5rem -1.5rem;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.04);
    }
</style>

<div class="builder-workspace-container">
    <!-- Top Workspace Header -->
    <div class="card card-saas mb-3 p-3 shadow-sm border">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <a href="{{ route('cvs.index') }}" class="btn btn-sm btn-outline-secondary" title="Back to Documents">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-dark text-white" style="font-size: 0.72rem;">
                            <i class="bi {{ $cv->documentType?->icon ?? 'bi-file-earmark-text' }} me-1"></i>
                            {{ $cv->documentType?->name ?? 'Career Document' }}
                        </span>
                        @if($cv->isPublished())
                            <span class="badge-saas-published">Published</span>
                        @else
                            <span class="badge-saas-draft">Draft</span>
                        @endif
                        <span class="small text-muted" id="completionScoreBadge">({{ $cv->completion_percentage }}% complete)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="documentTitleInput" class="form-control form-control-sm fw-bold border-0 bg-transparent px-1 fs-5 text-dark" 
                               value="{{ $cv->title }}" style="max-width: 320px; box-shadow: none;" placeholder="Document Title">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Autosave Status Indicator -->
                <div id="autosaveIndicator" class="small text-muted d-flex align-items-center gap-1 px-2 py-1 rounded bg-light border">
                    <i class="bi bi-cloud-check text-success" id="autosaveIcon"></i>
                    <span id="autosaveText">All changes saved</span>
                </div>

                <!-- Template Switcher Button -->
                <button type="button" class="btn btn-saas-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#templateSwitcherModal">
                    <i class="bi bi-layout-text-window-reverse me-1 text-primary"></i>
                    <span class="d-none d-sm-inline">Template:</span> 
                    <strong>{{ $templateModel?->name ?? 'Standard' }}</strong>
                </button>

                <!-- Full Document Preview Link -->
                <a href="{{ route('cvs.show', $cv) }}" class="btn btn-saas-secondary btn-sm" target="_blank" title="Full Page Preview">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>

                <!-- Quick Status Toggle -->
                <form action="{{ route('cvs.toggle-status', $cv) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $cv->isPublished() ? 'btn-outline-secondary' : 'btn-saas-success' }}">
                        <i class="bi {{ $cv->isPublished() ? 'bi-arrow-counterclockwise' : 'bi-check2-circle' }} me-1"></i>
                        {{ $cv->isPublished() ? 'Revert to Draft' : 'Publish' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile & Tablet Switcher: [ Edit Content ] | [ Live Preview ] -->
        <div class="d-lg-none mt-3 pt-3 border-top">
            <div class="btn-group w-100" role="group">
                <button type="button" class="btn btn-sm {{ !request()->has('view_preview') ? 'btn-dark' : 'btn-outline-dark' }}" id="mobileTabEdit" onclick="switchMobileView('edit')">
                    <i class="bi bi-pencil-square me-1"></i> Edit Form
                </button>
                <button type="button" class="btn btn-sm {{ request()->has('view_preview') ? 'btn-dark' : 'btn-outline-dark' }}" id="mobileTabPreview" onclick="switchMobileView('preview')">
                    <i class="bi bi-eye me-1"></i> Live Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Main Workspace: Adaptive Responsive Layout on Desktop -->
    <div class="row g-3">
        <!-- LEFT COLUMN: Builder Navigation & Active Section Form -->
        <div class="col-lg-7" id="builderFormCol">
            <!-- Mobile Section Dropdown (< lg) -->
            <div class="d-lg-none card card-saas mb-3 p-3">
                <label class="form-label small fw-bold text-muted mb-2">Switch Section</label>
                <select class="form-select form-select-sm" onchange="window.location.href=this.value">
                    @foreach($checklist as $key => $sec)
                        <option value="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" {{ $activeSection === $key ? 'selected' : '' }}>
                            {{ $sec['label'] }} {{ $sec['count'] > 0 ? "({$sec['count']})" : '' }} {{ $sec['is_complete'] ? '✓' : '' }}
                        </option>
                    @endforeach
                    <option value="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'customization']) }}" {{ $activeSection === 'customization' ? 'selected' : '' }}>
                        🎨 Design & Customization
                    </option>
                </select>
            </div>

            <!-- Desktop Collapsible Split Workspace -->
            <div class="builder-split-workspace d-none d-lg-flex">
                <!-- Section Sidebar Navigation (Desktop) -->
                <div class="card card-saas shadow-sm" id="builderNavSidebar">
                    <div class="card-header bg-white border-bottom py-2 px-2 d-flex align-items-center justify-content-between sidebar-header">
                        <span class="small fw-bold text-uppercase text-muted sidebar-header-title text-truncate" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                            {{ $isLetter ? 'Letter Sections' : 'CV Sections' }}
                        </span>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 text-muted" id="sidebarToggleBtn" onclick="toggleSidebarCollapse()" title="Collapse Sidebar" aria-label="Toggle CV Sections Sidebar">
                            <i class="bi bi-chevron-left" id="sidebarToggleIcon"></i>
                        </button>
                    </div>
                    <div class="list-group list-group-flush p-1">
                        @foreach($checklist as $key => $sec)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" 
                               class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mb-1 border-0 sidebar-link {{ $activeSection === $key ? 'bg-dark text-white fw-semibold' : 'text-secondary' }}"
                               title="{{ $sec['label'] }}"
                               data-bs-toggle="tooltip"
                               data-bs-placement="right"
                               style="transition: all 0.15s ease; font-size: 0.84rem;">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <i class="bi {{ $sec['icon'] }} {{ $activeSection === $key ? 'text-white' : 'text-muted' }}"></i>
                                    <span class="text-truncate sidebar-link-text">{{ $sec['label'] }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-1 sidebar-badge">
                                    @if($sec['count'] > 0 && !in_array($key, ['personal-info', 'summary', 'letter-details', 'letter-content', 'letter-closing']))
                                        <span class="badge {{ $activeSection === $key ? 'bg-light text-dark' : 'bg-surface-muted text-muted' }}" style="font-size: 0.68rem;">
                                            {{ $sec['count'] }}
                                        </span>
                                    @endif
                                    @if($sec['is_complete'])
                                        <i class="bi bi-check-circle-fill sidebar-check {{ $activeSection === $key ? 'text-white' : 'text-success' }}" style="font-size: 0.75rem;"></i>
                                    @endif
                                </div>
                            </a>
                        @endforeach

                        <!-- Design Customization Nav Item -->
                        <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'customization']) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mt-2 border-top sidebar-link {{ $activeSection === 'customization' ? 'bg-dark text-white fw-semibold' : 'text-primary' }}"
                           title="Design & Colors"
                           data-bs-toggle="tooltip"
                           data-bs-placement="right"
                           style="transition: all 0.15s ease; font-size: 0.84rem;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-palette {{ $activeSection === 'customization' ? 'text-white' : 'text-primary' }}"></i>
                                <span class="sidebar-link-text">Design & Colors</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary sidebar-badge" style="font-size: 0.65rem;">Styles</span>
                        </a>
                    </div>
                </div>

                <!-- Active Form Workspace -->
                <div class="builder-form-content" id="builderFormContent">
                    @include('cvs.builder.sections.' . $activeSection)
                </div>
            </div>

            <!-- Mobile Active Form Workspace (< lg) -->
            <div class="d-lg-none">
                @include('cvs.builder.sections.' . $activeSection)
            </div>
        </div>

        <!-- RIGHT COLUMN: Real Live Document Preview -->
        <div class="col-lg-5" id="builderPreviewCol">
            <div class="card card-saas sticky-top shadow-sm border" style="top: 80px;">
                <!-- Preview Toolbar -->
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle text-dark border small" style="font-size: 0.7rem;">LIVE PREVIEW</span>
                        <span class="small text-muted font-monospace" style="font-size: 0.75rem;">{{ $templateModel?->name }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="setPreviewZoom(0.8)" title="Zoom Out (80%)">
                            <span class="small fw-semibold">80%</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="setPreviewZoom(1.0)" title="Actual Size (100%)">
                            <span class="small fw-semibold">100%</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="refreshLivePreview()" title="Refresh Renderer">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Live Preview Viewport -->
                <div class="card-body p-2 bg-light overflow-auto" style="max-height: calc(100vh - 150px); min-height: 580px;">
                    <div id="previewZoomWrapper" style="transform-origin: top center; transition: transform 0.2s ease;">
                        <div id="liveDocumentRenderContainer" class="bg-white shadow border rounded-1 mx-auto" style="max-width: 800px; overflow: hidden;">
                            @include($templateView, ['cvData' => $cvData, 'cv' => $cv])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Switcher Modal (Filtered to Compatible Templates) -->
<div class="modal fade" id="templateSwitcherModal" tabindex="-1" aria-labelledby="templateSwitcherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="templateSwitcherModalLabel">
                        <i class="bi bi-layout-text-window-reverse text-primary me-2"></i> Compatible Templates
                    </h5>
                    <p class="text-muted small mb-0 mt-1">
                        Showing templates compatible with <strong>{{ $cv->documentType?->name ?? 'Standard CV' }}</strong>. Content is 100% preserved when switching.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    @foreach($compatibleTemplates as $tmpl)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border {{ $tmpl->id === $cv->template_id ? 'border-primary border-2 shadow-sm' : 'border-subtle' }} rounded-3 overflow-hidden bg-white" style="transition: all 0.2s ease;">
                                <div class="position-relative bg-light border-bottom text-center p-2" style="height: 180px;">
                                    <img src="{{ $tmpl->preview_image_url }}" alt="{{ $tmpl->name }}" class="img-fluid rounded object-fit-contain w-100 h-100">
                                    @if($tmpl->is_premium)
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark shadow-sm">
                                            <i class="bi bi-star-fill me-1"></i> Premium
                                        </span>
                                    @else
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-success text-white shadow-sm">Free</span>
                                    @endif
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="fw-bold text-dark mb-1">{{ $tmpl->name }}</div>
                                        <div class="small text-muted line-clamp-2" style="font-size: 0.78rem;">
                                            {{ $tmpl->description }}
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-2 border-top">
                                        @if($tmpl->id === $cv->template_id)
                                            <button type="button" class="btn btn-sm btn-primary w-100 disabled" disabled>
                                                <i class="bi bi-check-circle-fill me-1"></i> Current Template
                                            </button>
                                        @else
                                            <form action="{{ route('cvs.switch-template', $cv) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="template_id" value="{{ $tmpl->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-dark w-100">
                                                    Switch to this Template
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.ai-assistant-modal')
@endsection

@push('scripts')
<script>
    const CV_ID = {{ $cv->id }};
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const AUTOSAVE_URL = '{{ route("cvs.builder.autosave", $cv) }}';
    const PREVIEW_RENDER_URL = '{{ route("cvs.builder.render-preview", $cv) }}';

    let autosaveTimeout = null;
    let isSaving = false;

    // Mobile View Toggle
    function switchMobileView(mode) {
        const formCol = document.getElementById('builderFormCol');
        const previewCol = document.getElementById('builderPreviewCol');
        const btnEdit = document.getElementById('mobileTabEdit');
        const btnPreview = document.getElementById('mobileTabPreview');

        if (mode === 'preview') {
            formCol.classList.add('d-none');
            previewCol.classList.remove('d-none');
            btnEdit.className = 'btn btn-sm btn-outline-dark';
            btnPreview.className = 'btn btn-sm btn-dark';
            refreshLivePreview();
        } else {
            formCol.classList.remove('d-none');
            previewCol.classList.add('d-none');
            btnEdit.className = 'btn btn-sm btn-dark';
            btnPreview.className = 'btn btn-sm btn-outline-dark';
        }
    }

    // Zoom Controls
    function setPreviewZoom(scale) {
        const wrapper = document.getElementById('previewZoomWrapper');
        if (wrapper) {
            wrapper.style.transform = `scale(${scale})`;
            wrapper.style.transformOrigin = 'top center';
        }
    }

    // Status Indicator
    function updateSaveStatus(state, message) {
        const indicator = document.getElementById('autosaveIndicator');
        const icon = document.getElementById('autosaveIcon');
        const text = document.getElementById('autosaveText');

        if (state === 'saving') {
            icon.className = 'bi bi-arrow-repeat text-primary spinner-border spinner-border-sm';
            text.innerText = 'Saving changes...';
            indicator.className = 'small text-muted d-flex align-items-center gap-1 px-2 py-1 rounded bg-primary-subtle border border-primary-subtle';
        } else if (state === 'saved') {
            icon.className = 'bi bi-cloud-check-fill text-success';
            text.innerText = message || 'All changes saved';
            indicator.className = 'small text-muted d-flex align-items-center gap-1 px-2 py-1 rounded bg-light border';
        } else if (state === 'error') {
            icon.className = 'bi bi-exclamation-triangle-fill text-danger';
            text.innerText = 'Save failed';
            indicator.className = 'small text-danger d-flex align-items-center gap-1 px-2 py-1 rounded bg-danger-subtle border border-danger-subtle';
        }
    }

    // Debounced Autosave Engine
    function triggerAutosave(payload) {
        updateSaveStatus('saving');
        clearTimeout(autosaveTimeout);

        autosaveTimeout = setTimeout(() => {
            fetch(AUTOSAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateSaveStatus('saved', `Saved at ${data.saved_at}`);
                    if (data.completion_percentage) {
                        const scoreEl = document.getElementById('completionScoreBadge');
                        if (scoreEl) scoreEl.innerText = `(${data.completion_percentage}% complete)`;
                    }
                    refreshLivePreview();
                } else {
                    updateSaveStatus('error');
                }
            })
            .catch(() => {
                updateSaveStatus('error');
            });
        }, 800);
    }

    // Refresh live rendered preview container
    function refreshLivePreview() {
        fetch(PREVIEW_RENDER_URL, {
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.html) {
                const container = document.getElementById('liveDocumentRenderContainer');
                if (container) {
                    container.innerHTML = data.html;
                }
            }
        })
        .catch(err => console.error('Preview refresh failed', err));
    }

    // Live DOM Bindings & Change Listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Document Title Instant Edit
        const titleInput = document.getElementById('documentTitleInput');
        if (titleInput) {
            titleInput.addEventListener('input', (e) => {
                triggerAutosave({ title: e.target.value });
            });
        }

        // Listen on all form input changes across sections
        document.querySelectorAll('form').forEach(form => {
            if (form.id === 'customizationForm') return; // Handled separately
            form.querySelectorAll('input, textarea, select').forEach(input => {
                if (input.type === 'file' || input.type === 'hidden') return;
                input.addEventListener('input', () => {
                    // Extract form field data
                    const formData = new FormData(form);
                    const obj = {};
                    formData.forEach((val, key) => {
                        if (key !== '_token' && key !== '_method') {
                            obj[key] = val;
                        }
                    });

                    // Route based on section
                    const sec = '{{ $activeSection }}';
                    if (sec === 'personal-info') {
                        triggerAutosave({ personal_info: obj });
                    } else if (sec === 'summary') {
                        triggerAutosave({ summary: obj.summary || '' });
                    } else if (sec.startsWith('letter-')) {
                        triggerAutosave({ letter_details: obj });
                    }
                });
            });
        });
    });

    // Customization Real-time style updater
    function triggerLiveStyleUpdate() {
        const form = document.getElementById('customizationForm');
        if (!form) return;

        const accentColor = document.getElementById('accentColorInput')?.value || '#1e293b';
        const hexLabel = document.getElementById('accentColorHex');
        if (hexLabel) hexLabel.innerText = accentColor;

        const fontFamily = document.getElementById('fontFamilySelect')?.value || 'Inter';
        const fontSample = document.getElementById('fontPreviewSample');
        if (fontSample) fontSample.style.fontFamily = fontFamily;

        const formData = new FormData(form);
        const settings = {};
        formData.forEach((val, key) => {
            if (key !== '_token' && key !== '_method') {
                settings[key] = val;
            }
        });

        triggerAutosave({ settings: settings });
    }

    // ==========================================
    // Phase 5: AI Career Document Assistant Client
    // ==========================================
    const AI_ENDPOINTS = {
        summary: '{{ route("cvs.builder.ai.summary", $cv) }}',
        objective: '{{ route("cvs.builder.ai.objective", $cv) }}',
        experience: '{{ route("cvs.builder.ai.experience", $cv) }}',
        project: '{{ route("cvs.builder.ai.project", $cv) }}',
        skills: '{{ route("cvs.builder.ai.skills", $cv) }}',
        skills_append: '{{ route("cvs.builder.ai.skills.append", $cv) }}',
        improve: '{{ route("cvs.builder.ai.improve", $cv) }}',
        cover_letter: '{{ route("cvs.builder.ai.cover-letter", $cv) }}',
        motivation_letter: '{{ route("cvs.builder.ai.motivation-letter", $cv) }}'
    };

    let currentAiFeature = 'summary';
    let currentAiOptions = {};
    let currentAiResponseData = null;
    let currentAiVariants = {};
    let activeAiVariantKey = 'professional';

    function normalizeAiFeatureKey(feature) {
        const map = {
            'summary': 'summary',
            'profile_summary': 'summary',
            'objective': 'objective',
            'career_objective': 'objective',
            'experience': 'experience',
            'experience_rewrite': 'experience',
            'experience-rewrite': 'experience',
            'experience-bullets': 'experience',
            'project': 'project',
            'project_rewrite': 'project',
            'project-rewrite': 'project',
            'project-bullets': 'project',
            'skills': 'skills',
            'skill_suggestions': 'skills',
            'skills_suggestion': 'skills',
            'skill-suggestions': 'skills',
            'improve': 'improve',
            'content_improve': 'improve',
            'improve-content': 'improve',
            'cover_letter': 'cover_letter',
            'cover-letter': 'cover_letter',
            'motivation_letter': 'motivation_letter',
            'motivation-letter': 'motivation_letter'
        };
        return map[feature] || 'summary';
    }

    function setAiModalState(state, errorMessage = '') {
        const inputState = document.getElementById('aiStateInput');
        const genState = document.getElementById('aiStateGenerating');
        const resultsState = document.getElementById('aiStateResults');
        const errState = document.getElementById('aiStateError');

        if (inputState) inputState.classList.toggle('d-none', state !== 'input');
        if (genState) genState.classList.toggle('d-none', state !== 'generating');
        if (resultsState) resultsState.classList.toggle('d-none', state !== 'results');
        if (errState) {
            errState.classList.toggle('d-none', state !== 'error');
            if (errorMessage) {
                const msgEl = document.getElementById('aiErrorMessage');
                if (msgEl) msgEl.innerText = errorMessage;
            }
        }
    }

    function openAiAssistant(feature, options = {}) {
        const normalized = normalizeAiFeatureKey(feature);
        currentAiFeature = normalized;
        currentAiOptions = options;
        currentAiResponseData = null;
        currentAiVariants = {};

        const titleEl = document.getElementById('aiAssistantModalLabel');
        const subEl = document.getElementById('aiAssistantSubtitle');
        const guideTitle = document.getElementById('aiContextGuideTitle');
        const guideText = document.getElementById('aiContextGuideText');
        const container = document.getElementById('aiDynamicFieldsContainer');

        let title = 'AI Career Assistant';
        let sub = 'Tailored career document enhancement';
        let guideT = 'Customize AI Context';
        let guideDesc = 'Provide details to guide the AI generation. Document history will automatically be referenced.';
        let fieldsHtml = '';

        if (normalized === 'summary') {
            title = 'AI Profile Summary Generator';
            sub = 'Generate 3 high-impact summary variants for your target career role.';
            guideT = 'Target Role & Experience';
            guideDesc = 'Specify your target role and years of experience to tailor the summary tone and keywords.';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Target Position / Title</label>
                        <input type="text" name="target_position" class="form-control form-control-sm" value="${options.target_position || '{{ $cv->personalInfo?->job_title ?? $cv->title }}'}" placeholder="e.g. Senior Software Architect">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Years of Experience</label>
                        <input type="text" name="years_of_experience" class="form-control form-control-sm" placeholder="e.g. 7+ years">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Industry / Specialization</label>
                        <input type="text" name="industry" class="form-control form-control-sm" placeholder="e.g. Cloud Infrastructure / SaaS">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Key Skills to Highlight</label>
                        <input type="text" name="key_skills" class="form-control form-control-sm" placeholder="e.g. Laravel, AWS, Team Leadership">
                    </div>
                </div>
            `;
        } else if (normalized === 'objective') {
            title = 'AI Career Objective Generator';
            sub = 'Craft forward-looking career objective statements.';
            guideT = 'Career Trajectory';
            guideDesc = 'Define your target aspiration and core competencies.';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Target Position / Aspiring Role</label>
                        <input type="text" name="target_position" class="form-control form-control-sm" value="${options.target_position || '{{ $cv->personalInfo?->job_title ?? $cv->title }}'}" placeholder="e.g. Lead Product Engineer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Key Skills / Focus Areas</label>
                        <input type="text" name="key_skills" class="form-control form-control-sm" placeholder="e.g. Scalable Systems, Full-Stack Architecture">
                    </div>
                </div>
            `;
        } else if (normalized === 'experience') {
            title = 'AI Work Experience Enhancement';
            sub = 'Generate quantified accomplishment bullets or polish job descriptions.';
            guideT = 'Position & Raw Contributions';
            guideDesc = 'Enter your role details and any raw notes to convert into professional bullet points.';
            const mode = options.mode || 'bullets';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Job Title / Role</label>
                        <input type="text" name="position" class="form-control form-control-sm" value="${(options.position || '').replace(/"/g, '&quot;')}" placeholder="e.g. Senior Software Engineer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Company / Employer</label>
                        <input type="text" name="company" class="form-control form-control-sm" value="${(options.company || '').replace(/"/g, '&quot;')}" placeholder="e.g. Stripe, Acme Corp">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Generation Mode</label>
                        <select name="mode" class="form-select form-select-sm">
                            <option value="bullets" ${mode === 'bullets' ? 'selected' : ''}>Quantified Action-Oriented Bullet Points</option>
                            <option value="improve" ${mode === 'improve' ? 'selected' : ''}>Improve & Polish Existing Phrasing</option>
                            <option value="professional" ${mode === 'professional' ? 'selected' : ''}>Executive & Formal Tone</option>
                            <option value="concise" ${mode === 'concise' ? 'selected' : ''}>Concise & Crisp</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Raw Notes or Existing Draft (Optional)</label>
                        <textarea name="draft" class="form-control form-control-sm font-monospace" rows="3" placeholder="e.g. led migration to microservices, improved latency by 35%, managed 5 devs">${options.draft || options.current_text || ''}</textarea>
                    </div>
                </div>
            `;
        } else if (normalized === 'project') {
            title = 'AI Project Description & Bullets';
            sub = 'Highlight technical scope, architectural achievements, and project results.';
            const mode = options.mode || 'describe';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Project Name</label>
                        <input type="text" name="project_name" class="form-control form-control-sm" value="${(options.project_name || '').replace(/"/g, '&quot;')}" placeholder="e.g. Real-time Analytics Engine">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Technologies & Stack</label>
                        <input type="text" name="technologies" class="form-control form-control-sm" value="${(options.technologies || '').replace(/"/g, '&quot;')}" placeholder="e.g. Laravel, Redis, MySQL, Docker">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Generation Mode</label>
                        <select name="mode" class="form-select form-select-sm">
                            <option value="describe" ${mode === 'describe' ? 'selected' : ''}>Full Project Description</option>
                            <option value="bullets" ${mode === 'bullets' ? 'selected' : ''}>Technical Highlight Bullets</option>
                            <option value="concise" ${mode === 'concise' ? 'selected' : ''}>Concise Summary</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Draft / Scope Notes (Optional)</label>
                        <textarea name="draft" class="form-control form-control-sm font-monospace" rows="3" placeholder="Brief outline of architecture, challenges solved, or results...">${options.draft || ''}</textarea>
                    </div>
                </div>
            `;
        } else if (normalized === 'skills') {
            title = 'AI Skill Suggestions';
            sub = 'Discover hard and soft skills tailored for your target role and document context.';
            guideT = 'Target Role & Categorization';
            guideDesc = 'Specify your target role or leave blank to infer automatically from your CV.';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Target Position / Field</label>
                        <input type="text" name="target_position" class="form-control form-control-sm" value="{{ $cv->personalInfo?->job_title ?? $cv->title }}" placeholder="e.g. Full-Stack Developer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Skill Category Focus (Optional)</label>
                        <input type="text" name="category" class="form-control form-control-sm" placeholder="e.g. Backend, Cloud, Management">
                    </div>
                </div>
            `;
        } else if (normalized === 'improve') {
            title = 'AI Content & ATS Improver';
            sub = 'Refine phrasing, elevate vocabulary, and optimize keyword density.';
            guideT = 'Text Enhancement Settings';
            guideDesc = 'Select an optimization style and review your text before generating.';
            const tone = options.tone || 'professional';
            const initialText = options.text || (options.target_input_id && document.getElementById(options.target_input_id) ? document.getElementById(options.target_input_id).value : '');
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Optimization Focus / Tone</label>
                        <select name="tone" class="form-select form-select-sm">
                            <option value="professional" ${tone === 'professional' ? 'selected' : ''}>Professional & Polished</option>
                            <option value="ats" ${tone === 'ats' ? 'selected' : ''}>ATS-Friendly & Keyword Dense</option>
                            <option value="concise" ${tone === 'concise' ? 'selected' : ''}>Crisp & Concise</option>
                            <option value="impactful" ${tone === 'impactful' ? 'selected' : ''}>Action-Verb & Impact Focused</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Content to Improve <span class="text-danger">*</span></label>
                        <textarea name="text" class="form-control form-control-sm font-monospace" rows="5" required placeholder="Paste or type text to improve...">${initialText}</textarea>
                    </div>
                </div>
            `;
        } else if (normalized === 'cover_letter') {
            title = 'AI Cover Letter Generator';
            sub = 'Draft a structured, persuasive cover letter tailored to your target job.';
            guideT = 'Target Job & Company Details';
            guideDesc = 'Provide the role and company you are applying for to generate tailored motivation and qualifications.';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Target Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" class="form-control form-control-sm" value="{{ $cv->personalInfo?->job_title ?? $cv->title }}" placeholder="e.g. Senior Software Engineer" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Company / Employer Name</label>
                        <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Acme Corp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Recipient Name / Title</label>
                        <input type="text" name="recipient_name" class="form-control form-control-sm" placeholder="e.g. Hiring Committee or Dr. Vance">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Key Strengths / Skills</label>
                        <input type="text" name="key_skills" class="form-control form-control-sm" placeholder="e.g. Distributed Systems, Team Leadership">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Specific Notes or Job Posting Excerpt (Optional)</label>
                        <textarea name="additional_notes" class="form-control form-control-sm font-monospace" rows="3" placeholder="Paste key requirements or company mission points to align with..."></textarea>
                    </div>
                </div>
            `;
        } else if (normalized === 'motivation_letter') {
            title = 'AI Motivation Letter Generator';
            sub = 'Draft a persuasive motivation letter highlighting academic & career aspirations.';
            guideT = 'Academic Program / Target Institution';
            guideDesc = 'Enter target program details and your academic/research goals.';
            fieldsHtml = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Target Program / Position <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" class="form-control form-control-sm" placeholder="e.g. M.Sc. in Computer Science" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">University / Institution Name</label>
                        <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Technical University of Munich">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Academic Goals & Research Focus</label>
                        <textarea name="goals" class="form-control form-control-sm font-monospace" rows="3" placeholder="Outline your research interests, thesis goals, or long-term vision..."></textarea>
                    </div>
                </div>
            `;
        }

        if (titleEl) titleEl.innerText = title;
        if (subEl) subEl.innerText = sub;
        if (guideTitle) guideTitle.innerText = guideT;
        if (guideText) guideText.innerText = guideDesc;
        if (container) container.innerHTML = fieldsHtml;

        setAiModalState('input');

        const modalEl = document.getElementById('aiAssistantModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Submit AI Generation
    function submitAiGeneration() {
        const form = document.getElementById('aiPromptForm');
        if (!form) return;

        const formData = new FormData(form);
        const payload = {};
        formData.forEach((val, key) => {
            payload[key] = val;
        });

        // Validation for required fields
        if (currentAiFeature === 'improve' && (!payload.text || !payload.text.trim())) {
            alert('Please enter or paste the text you would like to improve.');
            return;
        }
        if ((currentAiFeature === 'cover_letter' || currentAiFeature === 'motivation_letter') && (!payload.job_title || !payload.job_title.trim())) {
            alert('Please enter a target job title or program name.');
            return;
        }

        setAiModalState('generating');

        const endpoint = AI_ENDPOINTS[currentAiFeature] || AI_ENDPOINTS.summary;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data: data })))
        .then(({ ok, status, data }) => {
            if (!ok || !data.success) {
                setAiModalState('error', data.message || 'Generation failed. Please try again.');
                return;
            }

            renderAiResults(data);
        })
        .catch(err => {
            console.error('AI Request Error:', err);
            setAiModalState('error', 'Network error or service unavailable. Your data is safe.');
        });
    }

    // Render Generation Results in Modal
    function renderAiResults(response) {
        currentAiResponseData = response.data || {};
        setAiModalState('results');

        const variantsContainer = document.getElementById('aiVariantsTabsContainer');
        const variantsNav = document.getElementById('aiVariantsNav');
        const textarea = document.getElementById('aiResultEditableTextarea');
        const structuredLetter = document.getElementById('aiStructuredLetterContainer');
        const skillsGrid = document.getElementById('aiSkillsGridContainer');
        const textareaCard = textarea?.closest('.card');

        // Reset visibility
        if (variantsContainer) variantsContainer.classList.add('d-none');
        if (structuredLetter) structuredLetter.classList.add('d-none');
        if (skillsGrid) skillsGrid.classList.add('d-none');
        if (textareaCard) textareaCard.classList.remove('d-none');

        // 1. Multi-Variant Results (Summary / Objective)
        if (currentAiResponseData.variants && typeof currentAiResponseData.variants === 'object') {
            currentAiVariants = currentAiResponseData.variants;
            const keys = Object.keys(currentAiVariants);
            if (keys.length > 0) {
                if (variantsContainer && variantsNav) {
                    variantsContainer.classList.remove('d-none');
                    let navHtml = '';
                    keys.forEach((key, idx) => {
                        const label = key.charAt(0).toUpperCase() + key.slice(1);
                        navHtml += `
                            <li class="nav-item">
                                <button type="button" class="nav-link ${idx === 0 ? 'active' : ''} py-1 px-3 small fw-semibold" onclick="selectAiVariant('${key}')">
                                    ${label}
                                </button>
                            </li>
                        `;
                    });
                    variantsNav.innerHTML = navHtml;
                }
                activeAiVariantKey = keys[0];
                if (textarea) textarea.value = currentAiVariants[keys[0]];
            }
        } 
        // 2. Skill Suggestions
        else if (currentAiFeature === 'skills') {
            if (textareaCard) textareaCard.classList.add('d-none');
            if (skillsGrid) {
                skillsGrid.classList.remove('d-none');
                renderSkillsCheckboxes(currentAiResponseData);
            }
        } 
        // 3. Cover / Motivation Letter
        else if (currentAiFeature === 'cover_letter' || currentAiFeature === 'motivation_letter') {
            if (structuredLetter) {
                structuredLetter.classList.remove('d-none');
                const salutationEl = document.getElementById('aiLetterSalutation');
                const openingEl = document.getElementById('aiLetterOpening');
                const bodyEl = document.getElementById('aiLetterBody');
                const ctaEl = document.getElementById('aiLetterCallToAction');
                const closingEl = document.getElementById('aiLetterClosing');
                const sigEl = document.getElementById('aiLetterSignature');

                if (salutationEl) salutationEl.value = currentAiResponseData.salutation || '';
                if (openingEl) openingEl.value = currentAiResponseData.opening || '';
                if (bodyEl) bodyEl.value = currentAiResponseData.body || '';
                if (ctaEl) ctaEl.value = currentAiResponseData.call_to_action || '';
                if (closingEl) closingEl.value = currentAiResponseData.closing || '';
                if (sigEl) sigEl.value = currentAiResponseData.signature || '{{ Auth::user()->name }}';
            }
            if (textarea) {
                textarea.value = currentAiResponseData.full_text || currentAiResponseData.text || currentAiResponseData.content || '';
            }
        } 
        // 4. Standard Text / Bullets / Improved Content
        else {
            let content = '';
            if (Array.isArray(currentAiResponseData.bullets)) {
                content = currentAiResponseData.bullets.join('\n');
            } else if (currentAiResponseData.bullets && typeof currentAiResponseData.bullets === 'string') {
                content = currentAiResponseData.bullets;
            } else {
                content = currentAiResponseData.text || currentAiResponseData.content || '';
            }
            if (textarea) textarea.value = content;
        }
    }

    function selectAiVariant(key) {
        if (!currentAiVariants[key]) return;
        activeAiVariantKey = key;
        const textarea = document.getElementById('aiResultEditableTextarea');
        if (textarea) textarea.value = currentAiVariants[key];

        const navBtns = document.querySelectorAll('#aiVariantsNav .nav-link');
        navBtns.forEach(btn => {
            btn.classList.toggle('active', btn.innerText.toLowerCase() === key.toLowerCase());
        });
    }

    function renderSkillsCheckboxes(data) {
        const list = document.getElementById('aiSkillsCheckboxesList');
        if (!list) return;

        let items = [];
        if (Array.isArray(data.skills)) {
            items = data.skills;
        } else if (data.skills && typeof data.skills === 'object') {
            // Grouped by categories
            Object.keys(data.skills).forEach(cat => {
                const sub = data.skills[cat];
                if (Array.isArray(sub)) {
                    sub.forEach(name => items.push({ name: name, category: cat }));
                }
            });
        }

        if (items.length === 0) {
            list.innerHTML = '<div class="col-12 text-center text-muted py-2">No skills generated.</div>';
            return;
        }

        let html = '';
        items.forEach((item, idx) => {
            const name = typeof item === 'string' ? item : item.name;
            const cat = typeof item === 'object' && item.category ? item.category : '';
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="form-check p-2 bg-light border rounded d-flex align-items-center gap-2">
                        <input class="form-check-input ms-0 ai-skill-checkbox" type="checkbox" value="${name.replace(/"/g, '&quot;')}" id="ai_skill_${idx}" checked>
                        <label class="form-check-label small fw-semibold text-dark text-truncate" for="ai_skill_${idx}" title="${name}">
                            ${name} ${cat ? `<span class="badge bg-white text-muted border ms-1" style="font-size: 0.65rem;">${cat}</span>` : ''}
                        </label>
                    </div>
                </div>
            `;
        });
        list.innerHTML = html;
    }

    // Toggle Select All Skills
    function toggleSelectAllSkills() {
        const checkboxes = document.querySelectorAll('.ai-skill-checkbox');
        const anyUnchecked = Array.from(checkboxes).some(c => !c.checked);
        checkboxes.forEach(c => c.checked = anyUnchecked);
        const btn = document.getElementById('aiSelectAllSkillsBtn');
        if (btn) btn.innerText = anyUnchecked ? 'Deselect All' : 'Select All';
    }

    // Apply Result to Document Form & Trigger Autosave + Live Preview
    function applyAiResultToDocument() {
        const modalEl = document.getElementById('aiAssistantModal');
        const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

        // 1. Skill Suggestions Application (Appends to DB)
        if (currentAiFeature === 'skills') {
            const checked = Array.from(document.querySelectorAll('.ai-skill-checkbox:checked')).map(c => c.value);
            if (checked.length === 0) {
                alert('Please select at least one skill to append.');
                return;
            }

            const applyBtn = document.getElementById('aiApplyResultBtn');
            if (applyBtn) applyBtn.disabled = true;

            fetch(AI_ENDPOINTS.skills_append, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ skills: checked })
            })
            .then(res => res.json())
            .then(data => {
                if (applyBtn) applyBtn.disabled = false;
                if (data.success) {
                    if (modalInstance) modalInstance.hide();
                    refreshLivePreview();
                    if ('{{ $activeSection }}' === 'skills') {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to append skills.');
                }
            })
            .catch(() => {
                if (applyBtn) applyBtn.disabled = false;
                alert('Network error while appending skills.');
            });
            return;
        }

        // 2. Cover / Motivation Letter Application
        if (currentAiFeature === 'cover_letter' || currentAiFeature === 'motivation_letter') {
            const salutation = document.getElementById('aiLetterSalutation')?.value;
            const opening = document.getElementById('aiLetterOpening')?.value;
            const body = document.getElementById('aiLetterBody')?.value;
            const cta = document.getElementById('aiLetterCallToAction')?.value;

            const formSalutation = document.querySelector('input[name="salutation"]');
            const formOpening = document.querySelector('textarea[name="opening"]');
            const formBody = document.querySelector('textarea[name="body"]');
            const formCta = document.querySelector('textarea[name="call_to_action"]');

            if (formSalutation && salutation) formSalutation.value = salutation;
            if (formOpening && opening) formOpening.value = opening;
            if (formBody && body) formBody.value = body;
            if (formCta && cta) formCta.value = cta;

            // Trigger debounced autosave
            triggerAutosave({
                letter_details: {
                    salutation: formSalutation ? formSalutation.value : salutation,
                    opening: formOpening ? formOpening.value : opening,
                    body: formBody ? formBody.value : body,
                    call_to_action: formCta ? formCta.value : cta
                }
            });

            if (modalInstance) modalInstance.hide();
            refreshLivePreview();
            return;
        }

        // 3. Text Area / Field Injection (Summary, Experience, Projects, Improve)
        const textarea = document.getElementById('aiResultEditableTextarea');
        const finalText = textarea ? textarea.value : '';

        const targetId = currentAiOptions.target_input_id || currentAiOptions.targetField;
        let targetEl = targetId ? document.getElementById(targetId) : null;

        if (!targetEl && currentAiFeature === 'summary') {
            targetEl = document.getElementById('summaryInput');
        }

        if (targetEl) {
            targetEl.value = finalText;
            targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            targetEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (currentAiFeature === 'summary' || currentAiFeature === 'objective') {
            triggerAutosave({ summary: finalText });
            if (typeof updateCharCount === 'function') updateCharCount();
        }

        if (modalInstance) modalInstance.hide();
        refreshLivePreview();
    }

    // Modal Event Bindings
    document.addEventListener('DOMContentLoaded', () => {
        const generateBtn = document.getElementById('aiSubmitGenerateBtn');
        if (generateBtn) generateBtn.addEventListener('click', submitAiGeneration);

        const retryBtn = document.getElementById('aiRetryBtn');
        if (retryBtn) retryBtn.addEventListener('click', submitAiGeneration);

        const regenBtn = document.getElementById('aiRegenerateBtn');
        if (regenBtn) regenBtn.addEventListener('click', () => setAiModalState('input'));

        const applyBtn = document.getElementById('aiApplyResultBtn');
        if (applyBtn) applyBtn.addEventListener('click', applyAiResultToDocument);

        const selectAllSkillsBtn = document.getElementById('aiSelectAllSkillsBtn');
        if (selectAllSkillsBtn) selectAllSkillsBtn.addEventListener('click', toggleSelectAllSkills);

        // Initialize Sidebar & Repeatable UI
        initSidebarCollapse();
        initRepeatableForms();
        initBootstrapTooltips();
    });

    // ==========================================
    // Collapsible CV Sections Sidebar Engine
    // ==========================================
    const SIDEBAR_STORAGE_KEY = 'cvmaker_builder_sidebar_collapsed';

    function initSidebarCollapse() {
        const savedState = localStorage.getItem(SIDEBAR_STORAGE_KEY);
        const isCollapsed = savedState === 'true';
        applySidebarState(isCollapsed, false);
    }

    function toggleSidebarCollapse() {
        const sidebar = document.getElementById('builderNavSidebar');
        if (!sidebar) return;
        const willCollapse = !sidebar.classList.contains('sidebar-collapsed');
        applySidebarState(willCollapse, true);
    }

    function applySidebarState(collapsed, saveToStorage = true) {
        const sidebar = document.getElementById('builderNavSidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const toggleIcon = document.getElementById('sidebarToggleIcon');

        if (!sidebar) return;

        if (collapsed) {
            sidebar.classList.add('sidebar-collapsed');
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-right';
            if (toggleBtn) {
                toggleBtn.setAttribute('title', 'Expand Sidebar');
                toggleBtn.setAttribute('aria-label', 'Expand Sidebar');
            }
            if (saveToStorage) localStorage.setItem(SIDEBAR_STORAGE_KEY, 'true');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-left';
            if (toggleBtn) {
                toggleBtn.setAttribute('title', 'Collapse Sidebar');
                toggleBtn.setAttribute('aria-label', 'Collapse Sidebar');
            }
            if (saveToStorage) localStorage.setItem(SIDEBAR_STORAGE_KEY, 'false');
        }

        // Re-initialize tooltips for updated sidebar links
        setTimeout(initBootstrapTooltips, 250);
    }

    function initBootstrapTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                const existing = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (existing) existing.dispose();
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    // ==========================================
    // Repeatable Sections Client Engine
    // ==========================================
    let isRepeatableDirty = false;
    let isSubmitting = false;

    function markRepeatableDirty() {
        isRepeatableDirty = true;
    }

    function initRepeatableForms() {
        document.querySelectorAll('form[data-repeatable-form]').forEach(form => {
            form.addEventListener('submit', () => {
                isSubmitting = true;
                isRepeatableDirty = false;
            });

            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('input', () => {
                    markRepeatableDirty();
                });
            });
        });

        // Initialize counters & reorder buttons across containers
        document.querySelectorAll('.repeatable-entries-container').forEach(container => {
            updateRepeatableNumbers(container.id);
        });

        window.addEventListener('beforeunload', (e) => {
            if (isRepeatableDirty && !isSubmitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    function addRepeatableEntry(containerId, templateId) {
        const container = document.getElementById(containerId);
        const template = document.getElementById(templateId);
        if (!container || !template) return;

        // Hide empty placeholder message if visible
        const emptyState = container.querySelector('.repeatable-empty-state');
        if (emptyState) emptyState.classList.add('d-none');

        const tempKey = 'temp_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        let html = template.innerHTML.replace(/__INDEX__/g, tempKey);

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.trim();
        const newCard = tempDiv.firstElementChild;
        if (!newCard) return;

        newCard.classList.add('is-new-entry');
        container.appendChild(newCard);

        updateRepeatableNumbers(containerId);
        markRepeatableDirty();

        // Bind input listeners for live dirty tracking & autosave
        newCard.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', markRepeatableDirty);
        });

        // Scroll to and focus first input
        newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const firstInput = newCard.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 150);
        }
    }

    function removeRepeatableEntry(btn, confirmMsg = '') {
        const card = btn.closest('.repeatable-card');
        if (!card) return;

        if (confirmMsg && !confirm(confirmMsg)) {
            return;
        }

        const form = card.closest('form');
        const idInput = card.querySelector('input[name$="[id]"]');

        // If existing saved record, append hidden input to track deletion on save
        if (idInput && idInput.value && !idInput.value.startsWith('temp_')) {
            if (form) {
                const hiddenDel = document.createElement('input');
                hiddenDel.type = 'hidden';
                hiddenDel.name = 'deleted_ids[]';
                hiddenDel.value = idInput.value;
                form.appendChild(hiddenDel);
            }
        }

        const container = card.closest('.repeatable-entries-container');
        card.remove();

        if (container) {
            updateRepeatableNumbers(container.id);
            // If no cards left, reveal empty state
            const remainingCards = container.querySelectorAll('.repeatable-card');
            if (remainingCards.length === 0) {
                const emptyState = container.querySelector('.repeatable-empty-state');
                if (emptyState) emptyState.classList.remove('d-none');
            }
        }

        markRepeatableDirty();
    }

    function moveRepeatableEntry(btn, direction) {
        const card = btn.closest('.repeatable-card');
        if (!card) return;

        const container = card.closest('.repeatable-entries-container');
        if (!container) return;

        if (direction === 'up' && card.previousElementSibling && card.previousElementSibling.classList.contains('repeatable-card')) {
            container.insertBefore(card, card.previousElementSibling);
        } else if (direction === 'down' && card.nextElementSibling && card.nextElementSibling.classList.contains('repeatable-card')) {
            container.insertBefore(card.nextElementSibling, card);
        }

        updateRepeatableNumbers(container.id);
        markRepeatableDirty();
    }

    function updateRepeatableNumbers(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const cards = container.querySelectorAll('.repeatable-card');
        cards.forEach((card, index) => {
            const numBadge = card.querySelector('.entry-number-badge');
            if (numBadge) numBadge.innerText = `#${index + 1}`;

            const sortOrderInput = card.querySelector('input[name$="[sort_order]"]');
            if (sortOrderInput) sortOrderInput.value = index + 1;

            const upBtn = card.querySelector('.btn-move-up');
            if (upBtn) upBtn.disabled = (index === 0);

            const downBtn = card.querySelector('.btn-move-down');
            if (downBtn) downBtn.disabled = (index === cards.length - 1);
        });
    }
</script>
@endpush
