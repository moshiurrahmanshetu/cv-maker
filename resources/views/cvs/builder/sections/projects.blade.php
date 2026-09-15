<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-folder-check me-2 text-muted"></i> Projects & Portfolio
            </h2>
            <p class="text-muted small mb-0 mt-1">Showcase significant open source, client, academic, or personal projects.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'projects']) }}" method="POST" id="projectsBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="projectsEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->projects->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-folder text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No projects added yet</div>
                    <p class="small text-muted mb-0">Click the button below to showcase your standout projects.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->projects as $index => $proj)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $proj->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $proj->title }}</span>
                                @if($proj->role)
                                    <span class="small text-muted">&bull; {{ $proj->role }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove project: {{ $proj->title }}?')" title="Remove Project">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $proj->id }}][id]" value="{{ $proj->id }}">
                            <input type="hidden" name="items[{{ $proj->id }}][sort_order]" value="{{ $proj->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Project Name / Title <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $proj->id }}][title]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.title", $proj->title) }}" placeholder="e.g. Distributed Analytics Engine" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Role (Optional)</label>
                                    <input type="text" name="items[{{ $proj->id }}][role]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.role", $proj->role) }}" placeholder="e.g. Lead Developer, Creator, UI Designer">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Technologies Used</label>
                                    <input type="text" name="items[{{ $proj->id }}][technologies]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.technologies", $proj->technologies) }}" placeholder="e.g. Laravel, MySQL, Redis, Docker, Bootstrap">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Project / Repository URL</label>
                                    <input type="text" name="items[{{ $proj->id }}][project_url]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.project_url", $proj->project_url) }}" placeholder="https://github.com/username/project">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Start Date</label>
                                    <input type="text" name="items[{{ $proj->id }}][start_date]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.start_date", $proj->start_date) }}" placeholder="e.g. 2022-01">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">End Date / Status</label>
                                    <input type="text" name="items[{{ $proj->id }}][end_date]" class="form-control form-control-sm" value="{{ old("items.{$proj->id}.end_date", $proj->end_date) }}" placeholder="e.g. 2023-04 or Ongoing">
                                </div>

                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small mb-0 fw-semibold">Project Description & Results</label>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-dark py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                                <i class="bi bi-stars me-1 text-primary"></i> AI Assistant
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'describe')">
                                                        <i class="bi bi-card-text me-2"></i> Generate Description
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'bullets')">
                                                        <i class="bi bi-list-task me-2"></i> Generate Technical Bullets
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'concise')">
                                                        <i class="bi bi-text-paragraph me-2"></i> Make Concise
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <textarea name="items[{{ $proj->id }}][description]" class="form-control form-control-sm proj-desc-input" rows="4" placeholder="Briefly describe the purpose of the project, technical challenges solved, and key features...">{{ old("items.{$proj->id}.description", $proj->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('projectsEntriesContainer', 'projEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Project
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Project Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Project Entry -->
<template id="projEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Project</span>
                <span class="badge bg-info-subtle text-info border" style="font-size: 0.65rem;">Unsaved</span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down">
                    <i class="bi bi-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this)" title="Remove Entry">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>

        <div class="card-body p-3">
            <input type="hidden" name="items[__INDEX__][id]" value="__INDEX__">
            <input type="hidden" name="items[__INDEX__][sort_order]" value="">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Project Name / Title <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][title]" class="form-control form-control-sm" placeholder="e.g. Distributed Analytics Engine" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Role (Optional)</label>
                    <input type="text" name="items[__INDEX__][role]" class="form-control form-control-sm" placeholder="e.g. Lead Developer, Creator, UI Designer">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Technologies Used</label>
                    <input type="text" name="items[__INDEX__][technologies]" class="form-control form-control-sm" placeholder="e.g. Laravel, MySQL, Redis, Docker, Bootstrap">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Project / Repository URL</label>
                    <input type="text" name="items[__INDEX__][project_url]" class="form-control form-control-sm" placeholder="https://github.com/username/project">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="text" name="items[__INDEX__][start_date]" class="form-control form-control-sm" placeholder="e.g. 2022-01">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">End Date / Status</label>
                    <input type="text" name="items[__INDEX__][end_date]" class="form-control form-control-sm" placeholder="e.g. 2023-04 or Ongoing">
                </div>

                <div class="col-md-12">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small mb-0 fw-semibold">Project Description & Results</label>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-dark py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                <i class="bi bi-stars me-1 text-primary"></i> AI Assistant
                            </button>
                            <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end shadow-sm">
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'describe')">
                                        <i class="bi bi-card-text me-2"></i> Generate Description
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'bullets')">
                                        <i class="bi bi-list-task me-2"></i> Generate Technical Bullets
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardProjAi(this, 'concise')">
                                        <i class="bi bi-text-paragraph me-2"></i> Make Concise
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <textarea name="items[__INDEX__][description]" class="form-control form-control-sm proj-desc-input" rows="4" placeholder="Briefly describe the purpose of the project, technical challenges solved, and key features..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    function triggerCardProjAi(btn, mode) {
        const card = btn.closest('.repeatable-card');
        if (!card) return;

        const titleInput = card.querySelector('input[name$="[title]"]');
        const techInput = card.querySelector('input[name$="[technologies]"]');
        const descInput = card.querySelector('.proj-desc-input');

        if (!descInput.id) {
            descInput.id = 'projDesc_' + Date.now();
        }

        openAiAssistant('project_rewrite', {
            target_input_id: descInput.id,
            project_name: titleInput ? titleInput.value : '',
            technologies: techInput ? techInput.value : '',
            draft: descInput ? descInput.value : '',
            mode: mode
        });
    }
</script>
@endpush
