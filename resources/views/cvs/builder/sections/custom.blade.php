<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-plus-square me-2 text-muted"></i> Custom Sections
            </h2>
            <p class="text-muted small mb-0 mt-1">Create user-defined custom sections such as Volunteer Work, Publications, Patents, Speaking Engagements, or Hobbies.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'custom']) }}" method="POST" id="customBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="customEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->customSections->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-plus-square text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No custom section entries added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add custom sections tailored to your background.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->customSections as $index => $item)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $item->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="badge bg-light text-dark border px-2 py-0 small fw-semibold">
                                    {{ $item->section_title }}
                                </span>
                                @if($item->title)
                                    <span class="small fw-bold text-dark entry-title-preview">{{ $item->title }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove custom entry: {{ $item->title ?? $item->section_title }}?')" title="Remove Entry">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                            <input type="hidden" name="items[{{ $item->id }}][sort_order]" value="{{ $item->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Section Heading / Category <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $item->id }}][section_title]" class="form-control form-control-sm" value="{{ old("items.{$item->id}.section_title", $item->section_title) }}" placeholder="e.g. Volunteer Work, Publications, Patents" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Entry Title / Headline</label>
                                    <input type="text" name="items[{{ $item->id }}][title]" class="form-control form-control-sm" value="{{ old("items.{$item->id}.title", $item->title) }}" placeholder="e.g. Open Source Contributor, Co-Author">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Organization / Subtitle</label>
                                    <input type="text" name="items[{{ $item->id }}][subtitle]" class="form-control form-control-sm" value="{{ old("items.{$item->id}.subtitle", $item->subtitle) }}" placeholder="e.g. Red Cross, ACM Journal, Tech Summit">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Date / Period</label>
                                    <input type="text" name="items[{{ $item->id }}][date_period]" class="form-control form-control-sm" value="{{ old("items.{$item->id}.date_period", $item->date_period) }}" placeholder="e.g. 2022 - Present or Oct 2023">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Detailed Description / Content</label>
                                    <textarea name="items[{{ $item->id }}][content]" class="form-control form-control-sm" rows="3" placeholder="Key responsibilities, publication citations, details, or accomplishments...">{{ old("items.{$item->id}.content", $item->content) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('customEntriesContainer', 'customEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Custom Entry
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Custom Entries
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Custom Entry -->
<template id="customEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Custom Entry</span>
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
                    <label class="form-label small fw-semibold">Section Heading / Category <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][section_title]" class="form-control form-control-sm" placeholder="e.g. Volunteer Work, Publications, Patents" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Entry Title / Headline</label>
                    <input type="text" name="items[__INDEX__][title]" class="form-control form-control-sm" placeholder="e.g. Open Source Contributor, Co-Author">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Organization / Subtitle</label>
                    <input type="text" name="items[__INDEX__][subtitle]" class="form-control form-control-sm" placeholder="e.g. Red Cross, ACM Journal, Tech Summit">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Date / Period</label>
                    <input type="text" name="items[__INDEX__][date_period]" class="form-control form-control-sm" placeholder="e.g. 2022 - Present or Oct 2023">
                </div>

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Detailed Description / Content</label>
                    <textarea name="items[__INDEX__][content]" class="form-control form-control-sm" rows="3" placeholder="Key responsibilities, publication citations, details, or accomplishments..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
