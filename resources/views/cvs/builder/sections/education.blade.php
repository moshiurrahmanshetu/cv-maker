<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-mortarboard me-2 text-muted"></i> Education
            </h2>
            <p class="text-muted small mb-0 mt-1">List your academic degrees, diplomas, and relevant coursework.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'education']) }}" method="POST" id="educationBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="educationEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->educations->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-mortarboard text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No education records added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add your academic degrees and studies.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->educations as $index => $edu)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $edu->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $edu->degree }}</span>
                                <span class="small text-muted">&bull; {{ $edu->institution }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove education: {{ $edu->degree }} at {{ $edu->institution }}?')" title="Remove Education">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $edu->id }}][id]" value="{{ $edu->id }}">
                            <input type="hidden" name="items[{{ $edu->id }}][sort_order]" value="{{ $edu->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Institution / University <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $edu->id }}][institution]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.institution", $edu->institution) }}" placeholder="e.g. Stanford University" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Degree <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $edu->id }}][degree]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.degree", $edu->degree) }}" placeholder="e.g. Master of Science" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Field of Study</label>
                                    <input type="text" name="items[{{ $edu->id }}][field_of_study]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.field_of_study", $edu->field_of_study) }}" placeholder="e.g. Computer Science & AI">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Result / GPA (Optional)</label>
                                    <input type="text" name="items[{{ $edu->id }}][grade_or_gpa]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.grade_or_gpa", $edu->grade_or_gpa) }}" placeholder="e.g. 3.9 GPA or First Class Honours">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">City</label>
                                    <input type="text" name="items[{{ $edu->id }}][city]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.city", $edu->city) }}" placeholder="e.g. Stanford, CA">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Country</label>
                                    <input type="text" name="items[{{ $edu->id }}][country]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.country", $edu->country) }}" placeholder="e.g. United States">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Start Date</label>
                                    <input type="text" name="items[{{ $edu->id }}][start_date]" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.start_date", $edu->start_date) }}" placeholder="e.g. 2018-09">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">End Date</label>
                                    <input type="text" name="items[{{ $edu->id }}][end_date]" id="eduEndDateInput_{{ $edu->id }}" class="form-control form-control-sm" value="{{ old("items.{$edu->id}.end_date", $edu->end_date) }}" placeholder="e.g. 2020-06" {{ $edu->is_current ? 'disabled' : '' }}>
                                    
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="items[{{ $edu->id }}][is_current]" value="1" id="eduIsCurrent_{{ $edu->id }}" {{ old("items.{$edu->id}.is_current", $edu->is_current) ? 'checked' : '' }} onchange="document.getElementById('eduEndDateInput_{{ $edu->id }}').disabled = this.checked;">
                                        <label class="form-check-label small text-secondary" for="eduIsCurrent_{{ $edu->id }}">
                                            I am currently studying here
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Honors, Research, or Notes</label>
                                    <textarea name="items[{{ $edu->id }}][description]" class="form-control form-control-sm" rows="3" placeholder="Notable honors, thesis title, relevant coursework, or extracurriculars...">{{ old("items.{$edu->id}.description", $edu->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('educationEntriesContainer', 'eduEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Education
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Education Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Education Entry -->
<template id="eduEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Education Record</span>
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
                    <label class="form-label small fw-semibold">Institution / University <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][institution]" class="form-control form-control-sm" placeholder="e.g. Stanford University" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Degree <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][degree]" class="form-control form-control-sm" placeholder="e.g. Master of Science" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Field of Study</label>
                    <input type="text" name="items[__INDEX__][field_of_study]" class="form-control form-control-sm" placeholder="e.g. Computer Science & AI">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Result / GPA (Optional)</label>
                    <input type="text" name="items[__INDEX__][grade_or_gpa]" class="form-control form-control-sm" placeholder="e.g. 3.9 GPA or First Class Honours">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">City</label>
                    <input type="text" name="items[__INDEX__][city]" class="form-control form-control-sm" placeholder="e.g. Stanford, CA">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Country</label>
                    <input type="text" name="items[__INDEX__][country]" class="form-control form-control-sm" placeholder="e.g. United States">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <input type="text" name="items[__INDEX__][start_date]" class="form-control form-control-sm" placeholder="e.g. 2018-09">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">End Date</label>
                    <input type="text" name="items[__INDEX__][end_date]" id="eduEndDateInput___INDEX__" class="form-control form-control-sm" placeholder="e.g. 2020-06">
                    
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="items[__INDEX__][is_current]" value="1" id="eduIsCurrent___INDEX__" onchange="document.getElementById('eduEndDateInput___INDEX__').disabled = this.checked;">
                        <label class="form-check-label small text-secondary" for="eduIsCurrent___INDEX__">
                            I am currently studying here
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Honors, Research, or Notes</label>
                    <textarea name="items[__INDEX__][description]" class="form-control form-control-sm" rows="3" placeholder="Notable honors, thesis title, relevant coursework, or extracurriculars..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
