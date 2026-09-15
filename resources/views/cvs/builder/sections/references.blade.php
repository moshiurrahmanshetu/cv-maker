<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-people me-2 text-muted"></i> Professional References
            </h2>
            <p class="text-muted small mb-0 mt-1">Add colleagues, managers, or mentors. You can hide references from public output without deleting them.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'references']) }}" method="POST" id="referencesBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="referencesEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->references->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-people text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No references added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add professional contacts.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->references as $index => $ref)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $ref->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $ref->full_name }}</span>
                                @if($ref->job_title || $ref->company)
                                    <span class="small text-muted">&bull; {{ $ref->job_title }}{{ $ref->company ? ' at ' . $ref->company : '' }}</span>
                                @endif
                                @if($ref->is_hidden)
                                    <span class="badge bg-secondary text-white py-0 px-2" style="font-size: 0.65rem;">
                                        <i class="bi bi-eye-slash me-1"></i> Hidden
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove reference: {{ $ref->full_name }}?')" title="Remove Reference">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $ref->id }}][id]" value="{{ $ref->id }}">
                            <input type="hidden" name="items[{{ $ref->id }}][sort_order]" value="{{ $ref->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Reference Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $ref->id }}][full_name]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.full_name", $ref->full_name) }}" placeholder="e.g. Dr. Sarah Jenkins" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Position / Job Title</label>
                                    <input type="text" name="items[{{ $ref->id }}][job_title]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.job_title", $ref->job_title) }}" placeholder="e.g. VP of Engineering">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Company / Institution</label>
                                    <input type="text" name="items[{{ $ref->id }}][company]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.company", $ref->company) }}" placeholder="e.g. Cloud Matrix Labs">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email Address</label>
                                    <input type="email" name="items[{{ $ref->id }}][email]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.email", $ref->email) }}" placeholder="sarah.jenkins@example.com">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Phone Number</label>
                                    <input type="text" name="items[{{ $ref->id }}][phone]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.phone", $ref->phone) }}" placeholder="+1 (555) 456-7890">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Professional Relationship</label>
                                    <input type="text" name="items[{{ $ref->id }}][relationship]" class="form-control form-control-sm" value="{{ old("items.{$ref->id}.relationship", $ref->relationship) }}" placeholder="e.g. Direct Manager, Research Advisor">
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="items[{{ $ref->id }}][is_hidden]" value="1" id="refIsHidden_{{ $ref->id }}" {{ old("items.{$ref->id}.is_hidden", $ref->is_hidden) ? 'checked' : '' }}>
                                        <label class="form-check-label small text-secondary" for="refIsHidden_{{ $ref->id }}">
                                            Hide this reference from rendered CV (data is preserved for future use)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('referencesEntriesContainer', 'refEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Reference
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Reference Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Reference Entry -->
<template id="refEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Reference</span>
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
                    <label class="form-label small fw-semibold">Reference Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][full_name]" class="form-control form-control-sm" placeholder="e.g. Dr. Sarah Jenkins" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Position / Job Title</label>
                    <input type="text" name="items[__INDEX__][job_title]" class="form-control form-control-sm" placeholder="e.g. VP of Engineering">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Company / Institution</label>
                    <input type="text" name="items[__INDEX__][company]" class="form-control form-control-sm" placeholder="e.g. Cloud Matrix Labs">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="items[__INDEX__][email]" class="form-control form-control-sm" placeholder="sarah.jenkins@example.com">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Phone Number</label>
                    <input type="text" name="items[__INDEX__][phone]" class="form-control form-control-sm" placeholder="+1 (555) 456-7890">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Professional Relationship</label>
                    <input type="text" name="items[__INDEX__][relationship]" class="form-control form-control-sm" placeholder="e.g. Direct Manager, Research Advisor">
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="items[__INDEX__][is_hidden]" value="1" id="refIsHidden___INDEX__">
                        <label class="form-check-label small text-secondary" for="refIsHidden___INDEX__">
                            Hide this reference from rendered CV (data is preserved for future use)
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
