<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-trophy me-2 text-muted"></i> Honors & Awards
            </h2>
            <p class="text-muted small mb-0 mt-1">Add recognitions, competition awards, academic honors, or hackathon wins.</p>
        </div>
        @if(!$editItem)
            <a href="#awardFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Award
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->awards->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-trophy text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No honors or awards added yet</div>
                <p class="small text-muted mb-0">Use the form below to record your achievements and recognitions.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->awards as $index => $award)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $award->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1 text-dark">{{ $award->title }}</h3>
                                @if($award->issuer)
                                    <div class="small fw-semibold text-secondary">{{ $award->issuer }}</div>
                                @endif
                                @if($award->issue_date)
                                    <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                        Date: {{ $award->issue_date }}
                                    </div>
                                @endif
                            </div>

                            <!-- Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'awards']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $award->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'awards']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $award->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'awards', 'edit_id' => $award->id]) }}#awardFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Award">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'awards', 'id' => $award->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove award: {{ $award->title }}?')" title="Delete Award">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($award->description)
                            <p class="small text-secondary mb-0 mt-2 pt-2 border-top" style="line-height: 1.5;">{{ $award->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="awardFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Award' : 'Add New Award' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'awards']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'awards', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'awards']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Award / Honor Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $editItem?->title) }}" placeholder="e.g. 1st Place National Hackathon, Outstanding Innovator" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Issuing Organization / Host</label>
                            <input type="text" name="issuer" class="form-control form-control-sm" value="{{ old('issuer', $editItem?->issuer) }}" placeholder="e.g. IEEE, TechCrunch, University">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Date Received</label>
                            <input type="text" name="issue_date" class="form-control form-control-sm" value="{{ old('issue_date', $editItem?->issue_date) }}" placeholder="e.g. 2023-10">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small">Description / Significance</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Explain the context, project, or distinction behind this award...">{{ old('description', $editItem?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'awards']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Award' : 'Save Award' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
