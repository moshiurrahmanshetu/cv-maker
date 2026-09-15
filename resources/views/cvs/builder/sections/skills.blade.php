<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-tools me-2 text-muted"></i> Skills & Competencies
            </h2>
            <p class="text-muted small mb-0 mt-1">Add technical, leadership, or specialized skills with rating & level representation.</p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-dark" onclick="openAiAssistant('skills_suggestion', {})">
            <i class="bi bi-stars me-1 text-primary"></i> Suggest Skills with AI
        </button>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'skills']) }}" method="POST" id="skillsBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="skillsEntriesContainer">
                <!-- Empty Placeholder State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->skills->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-tools text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No skills added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add your first skill.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->skills as $index => $skill)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $skill->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $skill->name }}</span>
                                @if($skill->category)
                                    <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">{{ $skill->category }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove skill: {{ $skill->name }}?')" title="Remove Skill">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $skill->id }}][id]" value="{{ $skill->id }}">
                            <input type="hidden" name="items[{{ $skill->id }}][sort_order]" value="{{ $skill->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Skill Name <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $skill->id }}][name]" class="form-control form-control-sm" value="{{ old("items.{$skill->id}.name", $skill->name) }}" placeholder="e.g. Laravel / PHP, Cloud Architecture, SQL" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Proficiency Level</label>
                                    <select name="items[{{ $skill->id }}][level]" class="form-select form-select-sm" required>
                                        <option value="Beginner" {{ old("items.{$skill->id}.level", $skill->level) === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="Intermediate" {{ old("items.{$skill->id}.level", $skill->level) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Advanced" {{ old("items.{$skill->id}.level", $skill->level ?? 'Advanced') === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                        <option value="Expert" {{ old("items.{$skill->id}.level", $skill->level) === 'Expert' ? 'selected' : '' }}>Expert</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Proficiency Rating (1-100%)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" name="items[{{ $skill->id }}][rating]" min="10" max="100" step="5" class="form-range flex-grow-1" value="{{ old("items.{$skill->id}.rating", $skill->rating ?? 80) }}" oninput="this.nextElementSibling.textContent = this.value + '%'">
                                        <span class="small fw-bold text-dark font-monospace" style="width: 45px;">{{ old("items.{$skill->id}.rating", $skill->rating ?? 80) }}%</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Category / Group (Optional)</label>
                                    <input type="text" name="items[{{ $skill->id }}][category]" class="form-control form-control-sm" value="{{ old("items.{$skill->id}.category", $skill->category) }}" placeholder="e.g. Backend, Frontend, DevOps">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('skillsEntriesContainer', 'skillEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Skill
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Skills Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Skill Entry -->
<template id="skillEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Skill</span>
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
                    <label class="form-label small fw-semibold">Skill Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][name]" class="form-control form-control-sm" placeholder="e.g. Laravel / PHP, Cloud Architecture, SQL" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Proficiency Level</label>
                    <select name="items[__INDEX__][level]" class="form-select form-select-sm" required>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced" selected>Advanced</option>
                        <option value="Expert">Expert</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Proficiency Rating (1-100%)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" name="items[__INDEX__][rating]" min="10" max="100" step="5" class="form-range flex-grow-1" value="80" oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <span class="small fw-bold text-dark font-monospace" style="width: 45px;">80%</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Category / Group (Optional)</label>
                    <input type="text" name="items[__INDEX__][category]" class="form-control form-control-sm" placeholder="e.g. Backend, Frontend, DevOps">
                </div>
            </div>
        </div>
    </div>
</template>
