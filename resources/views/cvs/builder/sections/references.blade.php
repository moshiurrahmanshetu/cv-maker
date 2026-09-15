<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-people me-2 text-muted"></i> Professional References
            </h2>
            <p class="text-muted small mb-0 mt-1">Add colleagues, managers, or mentors. You can hide references from public output without deleting them.</p>
        </div>
        @if(!$editItem)
            <a href="#referenceFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Reference
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->references->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-people text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No references added yet</div>
                <p class="small text-muted mb-0">Use the form below to add professional contacts.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->references as $index => $ref)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ $ref->is_hidden ? 'opacity-75 bg-surface-subtle' : '' }} {{ ($editItem && $editItem->id === $ref->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="h6 fw-bold mb-0 text-dark">{{ $ref->full_name }}</h3>
                                    @if($ref->is_hidden)
                                        <span class="badge bg-secondary text-white py-0 px-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-eye-slash me-1"></i> Hidden from Output
                                        </span>
                                    @else
                                        <span class="badge-saas-published py-0 px-2" style="font-size: 0.7rem;">
                                            <i class="bi bi-eye me-1"></i> Visible
                                        </span>
                                    @endif
                                </div>
                                <div class="small fw-semibold text-secondary mt-1">
                                    {{ $ref->job_title }}{{ $ref->company ? ' &bull; ' . $ref->company : '' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    @if($ref->email)
                                        <span class="me-2"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</span>
                                    @endif
                                    @if($ref->phone)
                                        <span class="me-2"><i class="bi bi-telephone me-1"></i>{{ $ref->phone }}</span>
                                    @endif
                                    @if($ref->relationship)
                                        <span class="fst-italic">({{ $ref->relationship }})</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Visibility Toggle Button -->
                                <form action="{{ route('cvs.builder.references.toggle-visibility', ['cv' => $cv, 'id' => $ref->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="{{ $ref->is_hidden ? 'Make Visible' : 'Hide from Output' }}">
                                        <i class="bi {{ $ref->is_hidden ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                                        <span class="d-none d-sm-inline ms-1" style="font-size: 0.78rem;">{{ $ref->is_hidden ? 'Show' : 'Hide' }}</span>
                                    </button>
                                </form>

                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'references']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $ref->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'references']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $ref->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'references', 'edit_id' => $ref->id]) }}#referenceFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Reference">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'references', 'id' => $ref->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove reference: {{ $ref->full_name }}?')" title="Delete Reference">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="referenceFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Reference' : 'Add New Reference' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'references']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'references', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'references']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Reference Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control form-control-sm" value="{{ old('full_name', $editItem?->full_name) }}" placeholder="e.g. Dr. Sarah Jenkins" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Position / Job Title</label>
                            <input type="text" name="job_title" class="form-control form-control-sm" value="{{ old('job_title', $editItem?->job_title) }}" placeholder="e.g. VP of Engineering">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Company / Institution</label>
                            <input type="text" name="company" class="form-control form-control-sm" value="{{ old('company', $editItem?->company) }}" placeholder="e.g. Cloud Matrix Labs">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $editItem?->email) }}" placeholder="sarah.jenkins@example.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Phone Number</label>
                            <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $editItem?->phone) }}" placeholder="+1 (555) 456-7890">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Professional Relationship</label>
                            <input type="text" name="relationship" class="form-control form-control-sm" value="{{ old('relationship', $editItem?->relationship) }}" placeholder="e.g. Direct Manager, Research Advisor">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_hidden" value="1" id="refIsHidden" {{ old('is_hidden', $editItem?->is_hidden) ? 'checked' : '' }}>
                                <label class="form-check-label small text-secondary" for="refIsHidden">
                                    Hide this reference from rendered CV (data is preserved for future use)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'references']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Reference' : 'Save Reference' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
