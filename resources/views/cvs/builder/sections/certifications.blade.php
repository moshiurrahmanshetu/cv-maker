<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-patch-check me-2 text-muted"></i> Certifications & Credentials
            </h2>
            <p class="text-muted small mb-0 mt-1">Add professional certificates, licenses, and verified accreditations.</p>
        </div>
    </div>

    <div class="card-body p-4">
        <form data-repeatable-form action="{{ route('cvs.builder.items.batch', ['cv' => $cv, 'section' => 'certifications']) }}" method="POST" id="certificationsBatchForm">
            @csrf

            <div class="repeatable-entries-container d-flex flex-column gap-3 mb-4" id="certificationsEntriesContainer">
                <!-- Empty State -->
                <div class="repeatable-empty-state text-center py-4 border rounded-2 bg-surface-subtle {{ $cv->certifications->isEmpty() ? '' : 'd-none' }}">
                    <i class="bi bi-patch-check text-muted fs-3 mb-2 d-block"></i>
                    <div class="small fw-semibold text-dark">No certifications added yet</div>
                    <p class="small text-muted mb-0">Click the button below to add certificates and credentials.</p>
                </div>

                <!-- Existing Saved Entries -->
                @foreach($cv->certifications as $index => $cert)
                    <div class="card repeatable-card rounded-2 shadow-none border" data-entry-id="{{ $cert->id }}">
                        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white entry-number-badge" style="font-size: 0.72rem;">#{{ $index + 1 }}</span>
                                <span class="small fw-bold text-dark entry-title-preview">{{ $cert->name }}</span>
                                @if($cert->issuing_organization)
                                    <span class="small text-muted">&bull; {{ $cert->issuing_organization }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-up" onclick="moveRepeatableEntry(this, 'up')" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-move-down" onclick="moveRepeatableEntry(this, 'down')" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeRepeatableEntry(this, 'Remove certification: {{ $cert->name }}?')" title="Remove Certification">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <input type="hidden" name="items[{{ $cert->id }}][id]" value="{{ $cert->id }}">
                            <input type="hidden" name="items[{{ $cert->id }}][sort_order]" value="{{ $cert->sort_order ?? ($index + 1) }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Certification Name <span class="text-danger">*</span></label>
                                    <input type="text" name="items[{{ $cert->id }}][name]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.name", $cert->name) }}" placeholder="e.g. AWS Certified Solutions Architect" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Issuing Organization</label>
                                    <input type="text" name="items[{{ $cert->id }}][issuing_organization]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.issuing_organization", $cert->issuing_organization) }}" placeholder="e.g. Amazon Web Services, Google">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Issue Date</label>
                                    <input type="text" name="items[{{ $cert->id }}][issue_date]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.issue_date", $cert->issue_date) }}" placeholder="e.g. 2022-08">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Expiration Date (Optional)</label>
                                    <input type="text" name="items[{{ $cert->id }}][expiration_date]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.expiration_date", $cert->expiration_date) }}" placeholder="e.g. 2025-08 or Never">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Credential ID</label>
                                    <input type="text" name="items[{{ $cert->id }}][credential_id]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.credential_id", $cert->credential_id) }}" placeholder="e.g. AWS-PSA-994821">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Credential Verification URL</label>
                                    <input type="text" name="items[{{ $cert->id }}][credential_url]" class="form-control form-control-sm" value="{{ old("items.{$cert->id}.credential_url", $cert->credential_url) }}" placeholder="https://aws.amazon.com/verify/...">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Description / Focus Area</label>
                                    <textarea name="items[{{ $cert->id }}][description]" class="form-control form-control-sm" rows="2" placeholder="Brief description of skills evaluated or specialized topics covered...">{{ old("items.{$cert->id}.description", $cert->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Action Area (Sticky Bar with Add More at bottom) -->
            <div class="sticky-action-bar">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addRepeatableEntry('certificationsEntriesContainer', 'certEntryTemplate')">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Certification
                </button>

                <button type="submit" class="btn-saas-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i> Save Certification Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Template for New Blank Certification Entry -->
<template id="certEntryTemplate">
    <div class="card repeatable-card rounded-2 shadow-none border is-new-entry" data-entry-id="__INDEX__">
        <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white entry-number-badge" style="font-size: 0.72rem;">#New</span>
                <span class="small fw-bold text-dark">New Certification</span>
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
                    <label class="form-label small fw-semibold">Certification Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[__INDEX__][name]" class="form-control form-control-sm" placeholder="e.g. AWS Certified Solutions Architect" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Issuing Organization</label>
                    <input type="text" name="items[__INDEX__][issuing_organization]" class="form-control form-control-sm" placeholder="e.g. Amazon Web Services, Google">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Issue Date</label>
                    <input type="text" name="items[__INDEX__][issue_date]" class="form-control form-control-sm" placeholder="e.g. 2022-08">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Expiration Date (Optional)</label>
                    <input type="text" name="items[__INDEX__][expiration_date]" class="form-control form-control-sm" placeholder="e.g. 2025-08 or Never">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Credential ID</label>
                    <input type="text" name="items[__INDEX__][credential_id]" class="form-control form-control-sm" placeholder="e.g. AWS-PSA-994821">
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Credential Verification URL</label>
                    <input type="text" name="items[__INDEX__][credential_url]" class="form-control form-control-sm" placeholder="https://aws.amazon.com/verify/...">
                </div>

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Description / Focus Area</label>
                    <textarea name="items[__INDEX__][description]" class="form-control form-control-sm" rows="2" placeholder="Brief description of skills evaluated or specialized topics covered..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
