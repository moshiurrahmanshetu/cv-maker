<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-briefcase me-2 text-muted"></i> Work Experience
            </h2>
            <p class="text-muted small mb-0 mt-1">Add your professional positions in reverse chronological order.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'experience']) }}" method="POST" id="experienceBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="experienceEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->experiences->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-briefcase text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No work experiences added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add your first work history record.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->experiences as $index => $exp)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $exp->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $exp->job_title }}</span>
                                <span class="small text-muted">&bull; {{ $exp->employer }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove position: {{ $exp->job_title }} at {{ $exp->employer }}?')" title="Remove Position">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $exp->id }}][id]" value="{{ $exp->id }}">
                            <input type="hidden" name="items[{{ $exp->id }}][sort_order]" value="{{ $exp->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Job Title <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $exp->id }}][job_title]" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.job_title", $exp->job_title) }}" placeholder="e.g. Senior Backend Engineer" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Employer / Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $exp->id }}][employer]" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.employer", $exp->employer) }}" placeholder="e.g. Acme Corp" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">City</label>
                                    <input type="text" name="items[{{ $exp->id }}][city]" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.city", $exp->city) }}" placeholder="e.g. San Francisco">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Country</label>
                                    <input type="text" name="items[{{ $exp->id }}][country]" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.country", $exp->country) }}" placeholder="e.g. United States">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Start Date</label>
                                    <input type="text" name="items[{{ $exp->id }}][start_date]" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.start_date", $exp->start_date) }}" placeholder="e.g. 2021-03">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">End Date</label>
                                    <input type="text" name="items[{{ $exp->id }}][end_date]" id="expEndDateInput_{{ $exp->id }}" class="form-control form-control-sm" value="{{ old("items.{$exp->id}.end_date", $exp->end_date) }}" placeholder="e.g. 2023-12" {{ $exp->is_current ? 'disabled' : '' }}>
                                    
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="items[{{ $exp->id }}][is_current]" value="1" id="expIsCurrent_{{ $exp->id }}" {{ old("items.{$exp->id}.is_current", $exp->is_current) ? 'checked' : '' }} onchange="document.getElementById('expEndDateInput_{{ $exp->id }}').disabled = this.checked;">
                                        <label class="form-check-label small text-secondary" for="expIsCurrent_{{ $exp->id }}">
                                            I currently work here
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small mb-0 fw-semibold">Responsibilities & Impact</label>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-dark py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                                <i class="bi bi-stars me-1 text-primary"></i> AI Assistant
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'bullets')">
                                                        <i class="bi bi-list-task me-2"></i> Generate Bullet Points
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'improve')">
                                                        <i class="bi bi-spellcheck me-2"></i> Improve & Polish Writing
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'professional')">
                                                        <i class="bi bi-briefcase me-2"></i> Make Professional & Formal
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'concise')">
                                                        <i class="bi bi-text-paragraph me-2"></i> Make Concise
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <textarea name="items[{{ $exp->id }}][description]" class="form-control form-control-sm exp-desc-input" rows="4" placeholder="Describe your key contributions, technologies used, and measurable results...">{{ old("items.{$exp->id}.description", $exp->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('experienceEntriesContainer', 'expEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Experience
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Experience Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Experience Entry -->
<template id="expEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Work Experience</span>
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
                    <label class="form-label small fw-semibold">Job Title <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][job_title]" class="form-control form-control-sm" placeholder="e.g. Senior Backend Engineer" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Employer / Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][employer]" class="form-control form-control-sm" placeholder="e.g. Acme Corp" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">City</label>
                    <input type="text" name="items[__INDEX__][city]" class="form-control form-control-sm" placeholder="e.g. San Francisco">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Country</label>
                    <input type="text" name="items[__INDEX__][country]" class="form-control form-control-sm" placeholder="e.g. United States">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="text" name="items[__INDEX__][start_date]" class="form-control form-control-sm" placeholder="e.g. 2021-03">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="text" name="items[__INDEX__][end_date]" id="expEndDateInput___INDEX__" class="form-control form-control-sm" placeholder="e.g. 2023-12">
                    
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="items[__INDEX__][is_current]" value="1" id="expIsCurrent___INDEX__" onchange="document.getElementById('expEndDateInput___INDEX__').disabled = this.checked;">
                        <label class="form-check-label small text-secondary" for="expIsCurrent___INDEX__">
                            I currently work here
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label small mb-0 fw-semibold">Responsibilities & Impact</label>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-dark py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                <i class="bi bi-stars me-1 text-primary"></i> AI Assistant
                            </button>
                            <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end shadow-sm">
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'bullets')">
                                        <i class="bi bi-list-task me-2"></i> Generate Bullet Points
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'improve')">
                                        <i class="bi bi-spellcheck me-2"></i> Improve & Polish Writing
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'professional')">
                                        <i class="bi bi-briefcase me-2"></i> Make Professional & Formal
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerCardExpAi(this, 'concise')">
                                        <i class="bi bi-text-paragraph me-2"></i> Make Concise
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <textarea name="items[__INDEX__][description]" class="form-control form-control-sm exp-desc-input" rows="4" placeholder="Describe your key contributions, technologies used, and measurable results..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    function triggerCardExpAi(btn, mode) {
        const card = btn.closest('.repeatable-card');
        if (!card) return;

        const posInput = card.querySelector('input[name$="[job_title]"]');
        const empInput = card.querySelector('input[name$="[employer]"]');
        const descInput = card.querySelector('.exp-desc-input');

        // Assign a temporary unique ID to the textarea if needed
        if (!descInput.id) {
            descInput.id = 'expDesc_' + Date.now();
        }

        openAiAssistant('experience_rewrite', {
            target_input_id: descInput.id,
            position: posInput ? posInput.value : '',
            company: empInput ? empInput.value : '',
            draft: descInput ? descInput.value : '',
            mode: mode
        });
    }
</script>
@endpush
