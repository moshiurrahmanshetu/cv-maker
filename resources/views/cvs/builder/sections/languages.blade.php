<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-translate me-2 text-muted"></i> Languages
            </h2>
            <p class="text-muted small mb-0 mt-1">Add languages you speak along with your proficiency level.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'languages']) }}" method="POST" id="languagesBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="languagesEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->languages->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-translate text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No languages added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add your spoken languages.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->languages as $index => $lang)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $lang->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $lang->language }}</span>
                                <span class="badge-saas-published py-0 px-2" style="font-size: 0.72rem;">{{ $lang->proficiency }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove language: {{ $lang->language }}?')" title="Remove Language">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $lang->id }}][id]" value="{{ $lang->id }}">
                            <input type="hidden" name="items[{{ $lang->id }}][sort_order]" value="{{ $lang->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Language <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $lang->id }}][language]" class="form-control form-control-sm" value="{{ old("items.{$lang->id}.language", $lang->language) }}" placeholder="e.g. English, German, Spanish" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Proficiency Level</label>
                                    <select name="items[{{ $lang->id }}][proficiency]" class="form-select form-select-sm" required>
                                        <option value="Native" {{ old("items.{$lang->id}.proficiency", $lang->proficiency) === 'Native' ? 'selected' : '' }}>Native / Bilingual</option>
                                        <option value="Fluent" {{ old("items.{$lang->id}.proficiency", $lang->proficiency ?? 'Fluent') === 'Fluent' ? 'selected' : '' }}>Fluent / Full Professional</option>
                                        <option value="Professional" {{ old("items.{$lang->id}.proficiency", $lang->proficiency) === 'Professional' ? 'selected' : '' }}>Professional Working</option>
                                        <option value="Intermediate" {{ old("items.{$lang->id}.proficiency", $lang->proficiency) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Basic" {{ old("items.{$lang->id}.proficiency", $lang->proficiency) === 'Basic' ? 'selected' : '' }}>Basic / Elementary</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('languagesEntriesContainer', 'langEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Language
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Language Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Language Entry -->
<template id="langEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Language</span>
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
                    <label class="form-label small fw-semibold">Language <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][language]" class="form-control form-control-sm" placeholder="e.g. English, German, Spanish" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Proficiency Level</label>
                    <select name="items[__INDEX__][proficiency]" class="form-select form-select-sm" required>
                        <option value="Native">Native / Bilingual</option>
                        <option value="Fluent" selected>Fluent / Full Professional</option>
                        <option value="Professional">Professional Working</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Basic">Basic / Elementary</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>
