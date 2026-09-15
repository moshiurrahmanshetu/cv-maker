<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-briefcase me-2 text-muted"></i> Work Experience
            </h2>
            <p class="text-muted small mb-0 mt-1">Add your professional positions in reverse chronological order.</p>
        </div>
        @if(!$editItem)
            <a href="#experienceFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Position
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        <!-- Existing Records List -->
        @if($cv->experiences->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-briefcase text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No work experiences added yet</div>
                <p class="small text-muted mb-0">Use the form below to add your first work history record.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->experiences as $index => $exp)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $exp->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1 text-dark">{{ $exp->job_title }}</h3>
                                <div class="small fw-semibold text-secondary">
                                    {{ $exp->employer }}{{ $exp->city ? ' &bull; ' . $exp->city : '' }}{{ $exp->country ? ', ' . $exp->country : '' }}
                                </div>
                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                    {{ $exp->start_date }} &ndash; {{ $exp->is_current ? 'Present (Current)' : ($exp->end_date ?? 'Present') }}
                                </div>
                            </div>

                            <!-- Reorder & Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'experience']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $exp->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'experience']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $exp->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'experience', 'edit_id' => $exp->id]) }}#experienceFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Position">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete Trigger -->
                                <button type="button" class="btn btn-sm btn-saas-danger-outline py-0 px-2" data-bs-toggle="modal" data-bs-target="#deleteExpModal{{ $exp->id }}" title="Delete Position">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        @if($exp->description)
                            <p class="small text-secondary mb-0 mt-2 pt-2 border-top" style="white-space: pre-line; line-height: 1.5;">{{ $exp->description }}</p>
                        @endif
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteExpModal{{ $exp->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content card-saas">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold fs-6">Delete Work Experience</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body py-3">
                                    Are you sure you want to remove <strong>{{ $exp->job_title }}</strong> at <strong>{{ $exp->employer }}</strong>?
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'experience', 'id' => $exp->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="experienceFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Work Experience Record' : 'Add New Work Experience' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'experience']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'experience', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'experience']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Job Title <span class="text-danger">*</span></label>
                            <input type="text" name="job_title" class="form-control form-control-sm" value="{{ old('job_title', $editItem?->job_title) }}" placeholder="e.g. Senior Backend Engineer" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Employer / Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="employer" class="form-control form-control-sm" value="{{ old('employer', $editItem?->employer) }}" placeholder="e.g. Acme Corp" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">City</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $editItem?->city) }}" placeholder="e.g. San Francisco">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Country</label>
                            <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $editItem?->country) }}" placeholder="e.g. United States">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Start Date</label>
                            <input type="text" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', $editItem?->start_date) }}" placeholder="e.g. 2021-03">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">End Date</label>
                            <input type="text" name="end_date" id="expEndDateInput" class="form-control form-control-sm" value="{{ old('end_date', $editItem?->end_date) }}" placeholder="e.g. 2023-12" {{ ($editItem && $editItem->is_current) ? 'disabled' : '' }}>
                            
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" value="1" id="expIsCurrent" {{ old('is_current', $editItem?->is_current) ? 'checked' : '' }} onchange="document.getElementById('expEndDateInput').disabled = this.checked;">
                                <label class="form-check-label small text-secondary" for="expIsCurrent">
                                    I currently work here
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small">Responsibilities & Impact</label>
                            <textarea name="description" class="form-control form-control-sm" rows="4" placeholder="Describe your key contributions, technologies used, and measurable results...">{{ old('description', $editItem?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'experience']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Position' : 'Save Position' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
