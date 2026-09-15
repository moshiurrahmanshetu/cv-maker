<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-plus-square me-2 text-muted"></i> Custom Sections
            </h2>
            <p class="text-muted small mb-0 mt-1">Create user-defined custom sections such as Volunteer Work, Publications, Patents, Speaking Engagements, or Hobbies.</p>
        </div>
        @if(!$editItem)
            <a href="#customFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Custom Entry
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->customSections->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-plus-square text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No custom section entries added yet</div>
                <p class="small text-muted mb-0">Use the form below to add custom sections tailored to your background.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->customSections as $index => $item)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $item->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                            <div>
                                <span class="badge bg-light text-dark border px-2 py-1 small fw-semibold mb-1">
                                    {{ $item->section_title }}
                                </span>
                                @if($item->title)
                                    <h3 class="h6 fw-bold mb-0 text-dark mt-1">{{ $item->title }}</h3>
                                @endif
                                @if($item->subtitle)
                                    <div class="small fw-semibold text-secondary">{{ $item->subtitle }}</div>
                                @endif
                                @if($item->date_period)
                                    <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                        {{ $item->date_period }}
                                    </div>
                                @endif
                            </div>

                            <!-- Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'custom']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'custom']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'custom', 'edit_id' => $item->id]) }}#customFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Entry">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'custom', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove custom entry: {{ $item->title ?? $item->section_title }}?')" title="Delete Entry">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($item->content)
                            <p class="small text-secondary mb-0 mt-2 pt-2 border-top" style="line-height: 1.5; white-space: pre-line;">{{ $item->content }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="customFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Custom Section Entry' : 'Add New Custom Entry' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'custom']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'custom', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'custom']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Section Heading / Category <span class="text-danger">*</span></label>
                            <input type="text" name="section_title" class="form-control form-control-sm" value="{{ old('section_title', $editItem?->section_title) }}" placeholder="e.g. Volunteer Work, Publications, Patents, Speaking" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Entry Title / Headline</label>
                            <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $editItem?->title) }}" placeholder="e.g. Open Source Contributor, Co-Author">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Organization / Subtitle</label>
                            <input type="text" name="subtitle" class="form-control form-control-sm" value="{{ old('subtitle', $editItem?->subtitle) }}" placeholder="e.g. Red Cross, ACM Journal, Tech Summit">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Date / Period</label>
                            <input type="text" name="date_period" class="form-control form-control-sm" value="{{ old('date_period', $editItem?->date_period) }}" placeholder="e.g. 2022 - Present or Oct 2023">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small">Detailed Description / Content</label>
                            <textarea name="content" class="form-control form-control-sm" rows="3" placeholder="Key responsibilities, publication citations, details, or accomplishments...">{{ old('content', $editItem?->content) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'custom']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
