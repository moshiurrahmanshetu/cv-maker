<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-mortarboard me-2 text-muted"></i> Education
            </h2>
            <p class="text-muted small mb-0 mt-1">List your academic degrees, diplomas, and relevant coursework.</p>
        </div>
        @if(!$editItem)
            <a href="#educationFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Education
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        <!-- Existing Records List -->
        @if($cv->educations->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-mortarboard text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No education records added yet</div>
                <p class="small text-muted mb-0">Use the form below to add your academic degrees and studies.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->educations as $index => $edu)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $edu->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1 text-dark">{{ $edu->degree }}</h3>
                                <div class="small fw-semibold text-secondary">
                                    {{ $edu->institution }}{{ $edu->field_of_study ? ' &bull; ' . $edu->field_of_study : '' }}
                                </div>
                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                    {{ $edu->start_date }} &ndash; {{ $edu->is_current ? 'Present (Enrolled)' : ($edu->end_date ?? 'Present') }}
                                    @if($edu->grade_or_gpa)
                                        &bull; {{ $edu->grade_or_gpa }}
                                    @endif
                                </div>
                            </div>

                            <!-- Reorder & Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'education']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $edu->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'education']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $edu->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'education', 'edit_id' => $edu->id]) }}#educationFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Education">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete Trigger -->
                                <button type="button" class="btn btn-sm btn-saas-danger-outline py-0 px-2" data-bs-toggle="modal" data-bs-target="#deleteEduModal{{ $edu->id }}" title="Delete Education">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        @if($edu->description)
                            <p class="small text-secondary mb-0 mt-2 pt-2 border-top" style="line-height: 1.5;">{{ $edu->description }}</p>
                        @endif
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteEduModal{{ $edu->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content card-saas">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold fs-6">Delete Education</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body py-3">
                                    Are you sure you want to remove <strong>{{ $edu->degree }}</strong> from <strong>{{ $edu->institution }}</strong>?
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'education', 'id' => $edu->id]) }}" method="POST">
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
        <div class="card card-saas border" id="educationFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Education Record' : 'Add New Education' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'education']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'education', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'education']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Institution / University <span class="text-danger">*</span></label>
                            <input type="text" name="institution" class="form-control form-control-sm" value="{{ old('institution', $editItem?->institution) }}" placeholder="e.g. Stanford University" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Degree <span class="text-danger">*</span></label>
                            <input type="text" name="degree" class="form-control form-control-sm" value="{{ old('degree', $editItem?->degree) }}" placeholder="e.g. Master of Science" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Field of Study</label>
                            <input type="text" name="field_of_study" class="form-control form-control-sm" value="{{ old('field_of_study', $editItem?->field_of_study) }}" placeholder="e.g. Computer Science & AI">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Result / GPA (Optional)</label>
                            <input type="text" name="grade_or_gpa" class="form-control form-control-sm" value="{{ old('grade_or_gpa', $editItem?->grade_or_gpa) }}" placeholder="e.g. 3.9 GPA or First Class Honours">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">City / Location</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $editItem?->city) }}" placeholder="e.g. Stanford, CA">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Country</label>
                            <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $editItem?->country) }}" placeholder="e.g. USA">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Start Date</label>
                            <input type="text" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', $editItem?->start_date) }}" placeholder="e.g. 2018-09">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">End Date</label>
                            <input type="text" name="end_date" id="eduEndDateInput" class="form-control form-control-sm" value="{{ old('end_date', $editItem?->end_date) }}" placeholder="e.g. 2020-06" {{ ($editItem && $editItem->is_current) ? 'disabled' : '' }}>
                            
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="is_current" value="1" id="eduIsCurrent" {{ old('is_current', $editItem?->is_current) ? 'checked' : '' }} onchange="document.getElementById('eduEndDateInput').disabled = this.checked;">
                                <label class="form-check-label small text-secondary" for="eduIsCurrent">
                                    I am currently studying here
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small">Honors, Research, or Notes</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Notable honors, thesis title, relevant coursework, or extracurriculars...">{{ old('description', $editItem?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'education']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Education' : 'Save Education' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
