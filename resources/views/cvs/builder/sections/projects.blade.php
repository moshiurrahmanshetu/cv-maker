<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-folder-check me-2 text-muted"></i> Projects & Portfolio
            </h2>
            <p class="text-muted small mb-0 mt-1">Showcase significant open source, client, academic, or personal projects.</p>
        </div>
        @if(!$editItem)
            <a href="#projectFormCard" class="btn-saas-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Project
            </a>
        @endif
    </div>
    <div class="card-body p-4">
        @if($cv->projects->isEmpty())
            <div class="text-center py-4 border rounded-2 bg-surface-subtle">
                <i class="bi bi-folder text-muted fs-3 mb-2 d-block"></i>
                <div class="small fw-semibold text-dark">No projects added yet</div>
                <p class="small text-muted mb-0">Use the form below to showcase your standout projects.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3 mb-4">
                @foreach($cv->projects as $index => $proj)
                    <div class="border rounded-2 p-3 bg-white d-flex flex-column justify-content-between {{ ($editItem && $editItem->id === $proj->id) ? 'border-dark shadow-sm bg-surface-subtle' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1 text-dark">{{ $proj->title }}</h3>
                                @if($proj->role)
                                    <div class="small fw-semibold text-secondary">{{ $proj->role }}</div>
                                @endif
                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                    {{ $proj->start_date ?? 'N/A' }} {{ $proj->end_date ? '&ndash; ' . $proj->end_date : '' }}
                                </div>
                            </div>

                            <!-- Action Controls -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Move Up -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'projects']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $proj->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Up" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                </form>

                                <!-- Move Down -->
                                <form action="{{ route('cvs.builder.items.reorder', ['cv' => $cv, 'section' => 'projects']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $proj->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                </form>

                                <!-- Edit -->
                                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'projects', 'edit_id' => $proj->id]) }}#projectFormCard" class="btn btn-sm btn-saas-secondary py-0 px-2" title="Edit Project">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Delete Trigger -->
                                <form action="{{ route('cvs.builder.items.destroy', ['cv' => $cv, 'section' => 'projects', 'id' => $proj->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-saas-danger-outline py-0 px-2" onclick="return confirm('Remove project: {{ $proj->title }}?')" title="Delete Project">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($proj->technologies)
                            <div class="mt-2">
                                <span class="small fw-semibold text-muted">Technologies:</span>
                                <span class="small text-secondary">{{ $proj->technologies }}</span>
                            </div>
                        @endif

                        @if($proj->description)
                            <p class="small text-secondary mb-0 mt-2 pt-2 border-top" style="line-height: 1.5;">{{ $proj->description }}</p>
                        @endif

                        @if($proj->project_url)
                            <div class="mt-2 pt-2 border-top">
                                <a href="{{ $proj->project_url }}" target="_blank" class="small text-muted text-decoration-none">
                                    <i class="bi bi-link-45deg"></i> {{ $proj->project_url }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add / Edit Form Card -->
        <div class="card card-saas border" id="projectFormCard">
            <div class="card-header bg-surface-subtle border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                <span class="small fw-bold text-dark">
                    <i class="bi {{ $editItem ? 'bi-pencil-square' : 'bi-plus-circle' }} me-1"></i>
                    {{ $editItem ? 'Edit Project' : 'Add New Project' }}
                </span>
                @if($editItem)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'projects']) }}" class="small text-muted text-decoration-none">
                        <i class="bi bi-x-circle me-1"></i> Cancel Edit
                    </a>
                @endif
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ $editItem ? route('cvs.builder.items.update', ['cv' => $cv, 'section' => 'projects', 'id' => $editItem->id]) : route('cvs.builder.items.store', ['cv' => $cv, 'section' => 'projects']) }}">
                    @csrf
                    @if($editItem)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Project Name / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $editItem?->title) }}" placeholder="e.g. Distributed Analytics Engine" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Role (Optional)</label>
                            <input type="text" name="role" class="form-control form-control-sm" value="{{ old('role', $editItem?->role) }}" placeholder="e.g. Lead Developer, Creator, UI Designer">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Technologies Used</label>
                            <input type="text" name="technologies" class="form-control form-control-sm" value="{{ old('technologies', $editItem?->technologies) }}" placeholder="e.g. Laravel, MySQL, Redis, Docker, Bootstrap">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Project / Repository URL</label>
                            <input type="text" name="project_url" class="form-control form-control-sm" value="{{ old('project_url', $editItem?->project_url) }}" placeholder="https://github.com/username/project">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Start Date</label>
                            <input type="text" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', $editItem?->start_date) }}" placeholder="e.g. 2022-01">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">End Date / Status</label>
                            <input type="text" name="end_date" class="form-control form-control-sm" value="{{ old('end_date', $editItem?->end_date) }}" placeholder="e.g. 2023-04 or Ongoing">
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small mb-0 fw-semibold">Project Description & Results</label>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-dark py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                        <i class="bi bi-stars me-1 text-primary"></i> AI Assistant
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end shadow-sm">
                                        <li>
                                            <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerProjAi('describe')">
                                                <i class="bi bi-card-text me-2"></i> Generate Description
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerProjAi('bullets')">
                                                <i class="bi bi-list-task me-2"></i> Generate Technical Bullets
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item dropdown-item-saas" onclick="triggerProjAi('concise')">
                                                <i class="bi bi-text-paragraph me-2"></i> Make Concise
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <textarea name="description" id="projDescriptionInput" class="form-control form-control-sm" rows="4" placeholder="Briefly describe the purpose of the project, technical challenges solved, and key features...">{{ old('description', $editItem?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                        @if($editItem)
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'projects']) }}" class="btn-saas-secondary btn-sm">Cancel</a>
                        @endif
                        <button type="submit" class="btn-saas-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> {{ $editItem ? 'Update Project' : 'Save Project' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function triggerProjAi(mode) {
        const form = document.querySelector('#projectFormCard form');
        const titleInput = form ? form.querySelector('input[name="title"]') : null;
        const techInput = form ? form.querySelector('input[name="technologies"]') : null;
        const descInput = document.getElementById('projDescriptionInput');

        openAiAssistant('project_rewrite', {
            target_input_id: 'projDescriptionInput',
            project_name: titleInput ? titleInput.value : '',
            technologies: techInput ? techInput.value : '',
            draft: descInput ? descInput.value : '',
            mode: mode
        });
    }
</script>
@endpush
