<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-patch-check me-2 text-muted"></i> Certifications & Credentials
            </h2>
            <p class="text-muted small mb-0 mt-1">Add professional certificates, licenses, and verified accreditations.</p>
        </div>
        @if(!$editItem)
            <a href="#certFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Certification
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->certifications->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-patch-check text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No certifications added yet</div>
                <p class="small text-muted mb-0">Use the form below to add certificates and licenses.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->certifications as $index => $cert)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $cert->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1 text-dark">{{ $cert->name }}</h3>
                                <div class="small fw-semibold text-secondary">
                                    {{ $cert->issuing_organization }}
                                </div>
                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                    Issued: {{ $cert->issue_date ?? 'N/A' }}
                                    @if($cert->expiration_date)
                                        &bull; Expires: {{ $cert->expiration_date }}
                                    @endif
                                    @if($cert->credential_id)
                                        &bull; ID: {{ $cert->credential_id }}
                                    @endif
                                </div>
                            </div>

                            <!-- Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'certifications']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $cert->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'certifications']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $cert->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'certifications', 'edit_id' => $cert->id]) }}#certFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Certification">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete Trigger -->
                                <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'certifications', 'id' => $cert->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove certification: {{ $cert->name }}?')" title="Delete Certification">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($cert->credential_url)
                            <div class="mt-2 pt-2 border-top">
                                <a href="{{ $cert->credential_url }}" target="_blank" class="small text-muted text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Verify Credential
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="certFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Certification' : 'Add New Certification' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'certifications']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'certifications', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'certifications']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Certification Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $editItem?->name) }}" placeholder="e.g. AWS Certified Solutions Architect" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Issuing Organization</label>
                            <input type="text" name="issuing_organization" class="form-control form-control-sm" value="{{ old('issuing_organization', $editItem?->issuing_organization) }}" placeholder="e.g. Amazon Web Services, Google, Scrum Alliance">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Issue Date</label>
                            <input type="text" name="issue_date" class="form-control form-control-sm" value="{{ old('issue_date', $editItem?->issue_date) }}" placeholder="e.g. 2022-08">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Expiration Date (Optional)</label>
                            <input type="text" name="expiration_date" class="form-control form-control-sm" value="{{ old('expiration_date', $editItem?->expiration_date) }}" placeholder="e.g. 2025-08 or Never">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Credential ID</label>
                            <input type="text" name="credential_id" class="form-control form-control-sm" value="{{ old('credential_id', $editItem?->credential_id) }}" placeholder="e.g. AWS-PSA-994821">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Credential Verification URL</label>
                            <input type="text" name="credential_url" class="form-control form-control-sm" value="{{ old('credential_url', $editItem?->credential_url) }}" placeholder="https://aws.amazon.com/verify/...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small">Description / Focus Area</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Brief description of skills evaluated or specialized topics covered...">{{ old('description', $editItem?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'certifications']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Certification' : 'Save Certification' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
