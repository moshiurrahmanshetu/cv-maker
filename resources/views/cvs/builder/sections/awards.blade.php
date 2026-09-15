<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-trophy me-2 text-muted"></i> Honors & Awards
            </h2>
            <p class="text-muted small mb-0 mt-1">Add recognitions, competition awards, academic honors, or hackathon wins.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'awards']) }}" method="POST" id="awardsBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="awardsEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->awards->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-trophy text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No honors or awards added yet</div>
                    <p class="small text-muted mb-0">Click the button below to record your achievements and recognitions.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->awards as $index => $award)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $award->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $award->title }}</span>
                                @if($award->issuer)
                                    <span class="small text-muted">&bull; {{ $award->issuer }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove award: {{ $award->title }}?')" title="Remove Award">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $award->id }}][id]" value="{{ $award->id }}">
                            <input type="hidden" name="items[{{ $award->id }}][sort_order]" value="{{ $award->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Award / Honor Title <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $award->id }}][title]" class="form-control form-control-sm" value="{{ old("items.{$award->id}.title", $award->title) }}" placeholder="e.g. 1st Place National Hackathon, Outstanding Innovator" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Issuing Organization / Host</label>
                                    <input type="text" name="items[{{ $award->id }}][issuer]" class="form-control form-control-sm" value="{{ old("items.{$award->id}.issuer", $award->issuer) }}" placeholder="e.g. IEEE, TechCrunch, University">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Date Received</label>
                                    <input type="text" name="items[{{ $award->id }}][issue_date]" class="form-control form-control-sm" value="{{ old("items.{$award->id}.issue_date", $award->issue_date) }}" placeholder="e.g. 2023-10">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Description / Significance</label>
                                    <textarea name="items[{{ $award->id }}][description]" class="form-control form-control-sm" rows="3" placeholder="Explain the context, project, or distinction behind this award...">{{ old("items.{$award->id}.description", $award->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('awardsEntriesContainer', 'awardEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Award
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Award Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Award Entry -->
<template id="awardEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Award</span>
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
                    <label class="form-label small fw-semibold">Award / Honor Title <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][title]" class="form-control form-control-sm" placeholder="e.g. 1st Place National Hackathon, Outstanding Innovator" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Issuing Organization / Host</label>
                    <input type="text" name="items[__INDEX__][issuer]" class="form-control form-control-sm" placeholder="e.g. IEEE, TechCrunch, University">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Date Received</label>
                    <input type="text" name="items[__INDEX__][issue_date]" class="form-control form-control-sm" placeholder="e.g. 2023-10">
                </div>

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Description / Significance</label>
                    <textarea name="items[__INDEX__][description]" class="form-control form-control-sm" rows="3" placeholder="Explain the context, project, or distinction behind this award..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
