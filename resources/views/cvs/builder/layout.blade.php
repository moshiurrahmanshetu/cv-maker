@extends('layouts.app')

@section('title', 'Document Builder: ' . $cv->title)

@section('content')
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

    <!-- Main Workspace: Split-Screen on Desktop (Left ~58-60%, Right ~40-42%) -->
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

            <div class="row g-3">
                <!-- Section Sidebar Navigation (Desktop) -->
                <div class="col-md-4 d-none d-lg-block">
                    <div class="card card-saas sticky-top shadow-sm" style="top: 80px;">
                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <span class="small fw-bold text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                {{ $isLetter ? 'Letter Sections' : 'CV Sections' }}
                            </span>
                        </div>
                        <div class="list-group list-group-flush p-1">
                            @foreach($checklist as $key => $sec)
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" 
                                   class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mb-1 border-0 {{ $activeSection === $key ? 'bg-dark text-white fw-semibold' : 'text-secondary' }}"
                                   style="transition: all 0.15s ease; font-size: 0.84rem;">
                                    <div class="d-flex align-items-center gap-2 text-truncate">
                                        <i class="bi {{ $sec['icon'] }} {{ $activeSection === $key ? 'text-white' : 'text-muted' }}"></i>
                                        <span class="text-truncate">{{ $sec['label'] }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        @if($sec['count'] > 0 && !in_array($key, ['personal-info', 'summary', 'letter-details', 'letter-content', 'letter-closing']))
                                            <span class="badge {{ $activeSection === $key ? 'bg-light text-dark' : 'bg-surface-muted text-muted' }}" style="font-size: 0.68rem;">
                                                {{ $sec['count'] }}
                                            </span>
                                        @endif
                                        @if($sec['is_complete'])
                                            <i class="bi bi-check-circle-fill {{ $activeSection === $key ? 'text-white' : 'text-success' }}" style="font-size: 0.75rem;"></i>
                                        @endif
                                    </div>
                                </a>
                            @endforeach

                            <!-- Design Customization Nav Item -->
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'customization']) }}" 
                               class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mt-2 border-top {{ $activeSection === 'customization' ? 'bg-dark text-white fw-semibold' : 'text-primary' }}"
                               style="transition: all 0.15s ease; font-size: 0.84rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-palette {{ $activeSection === 'customization' ? 'text-white' : 'text-primary' }}"></i>
                                    <span>Design & Colors</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">Styles</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Form Workspace -->
                <div class="col-md-8 col-lg-8">
                    @include('cvs.builder.sections.' . $activeSection)
                </div>
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
</script>
@endpush
