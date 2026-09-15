<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-translate me-2 text-muted"></i> Languages
            </h2>
            <p class="text-muted small mb-0 mt-1">Add languages you speak along with your proficiency level.</p>
        </div>
        @if(!$editItem)
            <a href="#languageFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Language
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->languages->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-translate text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No languages added yet</div>
                <p class="small text-muted mb-0">Use the form below to add your spoken languages.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-2 mb-4">
                @foreach($cv->languages as $index => $lang)
                    <div class="border rounded-2 p-3 bg-white d-flex align-items-center justify-content-between gap-2 {{ ($editItem && $editItem->id === $lang->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-dark">{{ $lang->language }}</span>
                            <span class="badge-saas-published py-0 px-2" style="font-size: 0.72rem;">
                                {{ $lang->proficiency }}
                            </span>
                        </div>

                        <!-- Action Controls -->
                        <div class="d-flex align-items-center gap-1">
                            <!-- Move Up -->
                            <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'languages']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $lang->id }}">
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                            </form>

                            <!-- Move Down -->
                            <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'languages']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $lang->id }}">
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                            </form>

                            <!-- Edit -->
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'languages', 'edit_id' => $lang->id]) }}#languageFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Language">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Delete -->
                            <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'languages', 'id' => $lang->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove language: {{ $lang->language }}?')" title="Delete Language">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="languageFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Language' : 'Add New Language' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'languages']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'languages', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'languages']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Language <span class="text-danger">*</span></label>
                            <input type="text" name="language" class="form-control form-control-sm" value="{{ old('language', $editItem?->language) }}" placeholder="e.g. English, German, Spanish" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Proficiency Level</label>
                            <select name="proficiency" class="form-select form-select-sm" required>
                                <option value="Native" {{ old('proficiency', $editItem?->proficiency) === 'Native' ? 'selected' : '' }}>Native / Bilingual</option>
                                <option value="Fluent" {{ old('proficiency', $editItem?->proficiency ?? 'Fluent') === 'Fluent' ? 'selected' : '' }}>Fluent / Full Professional</option>
                                <option value="Professional" {{ old('proficiency', $editItem?->proficiency) === 'Professional' ? 'selected' : '' }}>Professional Working</option>
                                <option value="Intermediate" {{ old('proficiency', $editItem?->proficiency) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Basic" {{ old('proficiency', $editItem?->proficiency) === 'Basic' ? 'selected' : '' }}>Basic / Elementary</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'languages']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Language' : 'Save Language' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
