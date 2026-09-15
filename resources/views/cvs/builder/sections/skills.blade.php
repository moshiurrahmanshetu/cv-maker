<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-tools me-2 text-muted"></i> Skills & Competencies
            </h2>
            <p class="text-muted small mb-0 mt-1">Add technical, leadership, or specialized skills with flexible rating & level representation.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-dark" onclick="openAiAssistant('skills_suggestion', {})">
                <i class="bi bi-stars me-1 text-primary"></i> Suggest Skills with AI
            </button>
            @if(!$editItem)
                <a href="#skillFormCard" class="btn-saas-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Add Skill
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Existing Records List -->
        @if($cv->skills->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-tools text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No skills added yet</div>
                <p class="small text-muted mb-0">Use the form below to add your primary skills.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-2 mb-4">
                @foreach($cv->skills as $index => $skill)
                    <div class="border rounded-2 p-3 bg-white d-flex align-items-center justify-content-between gap-2 {{ ($editItem && $editItem->id === $skill->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                            <div>
                                <span class="fw-bold text-dark">{{ $skill->name }}</span>
                                @if($skill->category)
                                    <span class="badge bg-light text-muted border ms-1" style="font-size: 0.72rem;">{{ $skill->category }}</span>
                                @endif
                            </div>
                            <span class="badge-saas-published py-0 px-2" style="font-size: 0.72rem;">
                                {{ $skill->level }}
                            </span>
                            <div class="d-none d-sm-flex align-items-center gap-2" style="width: 120px;">
                                <div class="progress-saas flex-grow-1">
                                    <div class="progress-saas-bar" style="width: {{ $skill->rating }}%;"></div>
                                </div>
                                <span class="small text-muted font-monospace" style="font-size: 0.75rem;">{{ $skill->rating }}%</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <!-- Move Up -->
                            <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'skills']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $skill->id }}">
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                            </form>

                            <!-- Move Down -->
                            <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'skills']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $skill->id }}">
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                            </form>

                            <!-- Edit -->
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'skills', 'edit_id' => $skill->id]) }}#skillFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Skill">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <!-- Delete -->
                            <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'skills', 'id' => $skill->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove skill: {{ $skill->name }}?')" title="Delete Skill">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="skillFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Skill' : 'Add New Skill' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'skills']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'skills', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'skills']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Skill Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $editItem?->name) }}" placeholder="e.g. Laravel / PHP, Cloud Architecture, SQL" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Proficiency Level</label>
                            <select name="level" class="form-select form-select-sm" required>
                                <option value="Beginner" {{ old('level', $editItem?->level) === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="Intermediate" {{ old('level', $editItem?->level) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Advanced" {{ old('level', $editItem?->level ?? 'Advanced') === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                <option value="Expert" {{ old('level', $editItem?->level) === 'Expert' ? 'selected' : '' }}>Expert</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Proficiency Score / Rating (1-100%)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" name="rating" id="skillRatingRange" min="10" max="100" step="5" class="form-range flex-grow-1" value="{{ old('rating', $editItem?->rating ?? 80) }}" oninput="document.getElementById('ratingValue').textContent = this.value + '%'">
                                <span class="small fw-bold text-dark font-monospace" id="ratingValue" style="width: 45px;">{{ old('rating', $editItem?->rating ?? 80) }}%</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Category / Group (Optional)</label>
                            <input type="text" name="category" class="form-control form-control-sm" value="{{ old('category', $editItem?->category) }}" placeholder="e.g. Backend, Frontend, DevOps, Management">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'skills']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Skill' : 'Save Skill' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
