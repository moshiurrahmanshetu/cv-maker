@extends('layouts.app')

@section('title', 'Edit CV: ' . $cv->title)

@section('content')
<form method="POST" action="{{ route('cvs.update', $cv) }}" id="cvEditForm">
    @csrf
    @method('PUT')

    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('cvs.index') }}" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> My CVs
                </a>
                <span class="text-muted">/</span>
                @if($cv->isPublished())
                    <span class="badge-saas-published">Published</span>
                @else
                    <span class="badge-saas-draft">Draft</span>
                @endif
                <span class="small text-muted">({{ $cv->completion_percentage }}% complete)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" name="title" class="form-control form-control-lg fw-bold border-0 px-0 bg-transparent" value="{{ old('title', $cv->title) }}" placeholder="CV Title" required style="font-size: 1.5rem; max-width: 500px;">
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('cvs.show', $cv) }}" class="btn-saas-secondary" target="_blank">
                <i class="bi bi-eye"></i> Preview
            </a>

            <!-- Save as Draft button -->
            <button type="submit" name="action" value="save_draft" class="btn-saas-secondary">
                <i class="bi bi-floppy"></i> Save Draft
            </button>

            <!-- Publish / Ready button -->
            <button type="submit" name="action" value="publish" class="btn-saas-success">
                <i class="bi bi-check-circle"></i> {{ $cv->isPublished() ? 'Update & Keep Published' : 'Publish / Mark Ready' }}
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Sections -->
        <div class="col-lg-8">

            <!-- 1. Personal Information Section -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-person me-2 text-muted"></i> 1. Personal & Contact Information
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="personal_info[full_name]" class="form-control" value="{{ old('personal_info.full_name', $cv->personalInfo?->full_name) }}" placeholder="e.g. Alex Morgan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Title / Headline</label>
                            <input type="text" name="personal_info[job_title]" class="form-control" value="{{ old('personal_info.job_title', $cv->personalInfo?->job_title) }}" placeholder="e.g. Senior Software Engineer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="personal_info[email]" class="form-control" value="{{ old('personal_info.email', $cv->personalInfo?->email) }}" placeholder="alex@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="personal_info[phone]" class="form-control" value="{{ old('personal_info.phone', $cv->personalInfo?->phone) }}" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="personal_info[address]" class="form-control" value="{{ old('personal_info.address', $cv->personalInfo?->address) }}" placeholder="e.g. 123 Innovation Way">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="personal_info[city]" class="form-control" value="{{ old('personal_info.city', $cv->personalInfo?->city) }}" placeholder="San Francisco">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="personal_info[country]" class="form-control" value="{{ old('personal_info.country', $cv->personalInfo?->country) }}" placeholder="United States">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="personal_info[postal_code]" class="form-control" value="{{ old('personal_info.postal_code', $cv->personalInfo?->postal_code) }}" placeholder="94107">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Website / Portfolio</label>
                            <input type="url" name="personal_info[website]" class="form-control" value="{{ old('personal_info.website', $cv->personalInfo?->website) }}" placeholder="https://mywebsite.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="text" name="personal_info[linkedin]" class="form-control" value="{{ old('personal_info.linkedin', $cv->personalInfo?->linkedin) }}" placeholder="https://linkedin.com/in/username">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GitHub / Other URL</label>
                            <input type="text" name="personal_info[github]" class="form-control" value="{{ old('personal_info.github', $cv->personalInfo?->github) }}" placeholder="https://github.com/username">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Profile Summary -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-card-text me-2 text-muted"></i> 2. Professional Summary
                    </h2>
                </div>
                <div class="card-body p-4">
                    <label class="form-label">Career Summary / Bio</label>
                    <textarea name="summary" class="form-control" rows="4" placeholder="Briefly describe your experience, career highlights, and core technical strengths...">{{ old('summary', $cv->summary) }}</textarea>
                </div>
            </div>

            <!-- 3. Work Experience Section -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-briefcase me-2 text-muted"></i> 3. Work Experience
                    </h2>
                    <button type="button" class="btn btn-sm btn-saas-secondary" onclick="addExperienceRow()">
                        <i class="bi bi-plus-lg"></i> Add Position
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="experienceContainer">
                        @forelse($cv->experiences as $index => $exp)
                            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted">Position #<span class="item-number">{{ $index + 1 }}</span></span>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Job Title</label>
                                        <input type="text" name="experiences[{{ $index }}][job_title]" class="form-control form-control-sm" value="{{ $exp->job_title }}" placeholder="e.g. Lead Developer">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Employer / Company</label>
                                        <input type="text" name="experiences[{{ $index }}][employer]" class="form-control form-control-sm" value="{{ $exp->employer }}" placeholder="e.g. Acme Corp">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">City / Location</label>
                                        <input type="text" name="experiences[{{ $index }}][city]" class="form-control form-control-sm" value="{{ $exp->city }}" placeholder="e.g. New York">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Date</label>
                                        <input type="text" name="experiences[{{ $index }}][start_date]" class="form-control form-control-sm" value="{{ $exp->start_date }}" placeholder="e.g. 2021-03">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Date</label>
                                        <input type="text" name="experiences[{{ $index }}][end_date]" class="form-control form-control-sm" value="{{ $exp->end_date }}" placeholder="e.g. Present or 2023-12">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Responsibilities & Achievements</label>
                                    <textarea name="experiences[{{ $index }}][description]" class="form-control form-control-sm" rows="3" placeholder="Key achievements, technologies used, responsibilities...">{{ $exp->description }}</textarea>
                                </div>
                            </div>
                        @empty
                            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted">Position #<span class="item-number">1</span></span>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Job Title</label>
                                        <input type="text" name="experiences[0][job_title]" class="form-control form-control-sm" placeholder="e.g. Software Engineer">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Employer / Company</label>
                                        <input type="text" name="experiences[0][employer]" class="form-control form-control-sm" placeholder="e.g. Acme Corp">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">City / Location</label>
                                        <input type="text" name="experiences[0][city]" class="form-control form-control-sm" placeholder="e.g. San Francisco">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Date</label>
                                        <input type="text" name="experiences[0][start_date]" class="form-control form-control-sm" placeholder="e.g. 2022-01">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Date</label>
                                        <input type="text" name="experiences[0][end_date]" class="form-control form-control-sm" placeholder="e.g. Present">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Responsibilities & Achievements</label>
                                    <textarea name="experiences[0][description]" class="form-control form-control-sm" rows="3" placeholder="Key responsibilities and accomplishments..."></textarea>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 4. Education Section -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-mortarboard me-2 text-muted"></i> 4. Education
                    </h2>
                    <button type="button" class="btn btn-sm btn-saas-secondary" onclick="addEducationRow()">
                        <i class="bi bi-plus-lg"></i> Add Education
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="educationContainer">
                        @forelse($cv->educations as $index => $edu)
                            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted">Education #<span class="item-number">{{ $index + 1 }}</span></span>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Institution / University</label>
                                        <input type="text" name="educations[{{ $index }}][institution]" class="form-control form-control-sm" value="{{ $edu->institution }}" placeholder="e.g. UC Berkeley">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Degree & Field of Study</label>
                                        <input type="text" name="educations[{{ $index }}][degree]" class="form-control form-control-sm" value="{{ $edu->degree }}" placeholder="e.g. B.S. in Computer Science">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">City / Country</label>
                                        <input type="text" name="educations[{{ $index }}][city]" class="form-control form-control-sm" value="{{ $edu->city }}" placeholder="e.g. Berkeley, USA">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Date</label>
                                        <input type="text" name="educations[{{ $index }}][start_date]" class="form-control form-control-sm" value="{{ $edu->start_date }}" placeholder="e.g. 2018-09">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Date</label>
                                        <input type="text" name="educations[{{ $index }}][end_date]" class="form-control form-control-sm" value="{{ $edu->end_date }}" placeholder="e.g. 2022-05">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted">Education #<span class="item-number">1</span></span>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Institution / University</label>
                                        <input type="text" name="educations[0][institution]" class="form-control form-control-sm" placeholder="e.g. University Name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Degree & Field of Study</label>
                                        <input type="text" name="educations[0][degree]" class="form-control form-control-sm" placeholder="e.g. Bachelor of Science">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">City / Country</label>
                                        <input type="text" name="educations[0][city]" class="form-control form-control-sm" placeholder="e.g. Boston, USA">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Date</label>
                                        <input type="text" name="educations[0][start_date]" class="form-control form-control-sm" placeholder="e.g. 2016-09">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Date</label>
                                        <input type="text" name="educations[0][end_date]" class="form-control form-control-sm" placeholder="e.g. 2020-05">
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- 5. Skills & Languages Section -->
            <div class="row g-4 mb-4">
                <!-- Skills -->
                <div class="col-md-6">
                    <div class="card card-saas h-100">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <h2 class="h6 fw-bold mb-0">
                                <i class="bi bi-tools me-2 text-muted"></i> 5. Skills
                            </h2>
                            <button type="button" class="btn btn-sm btn-saas-secondary" onclick="addSkillRow()">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        <div class="card-body p-4" id="skillContainer">
                            @forelse($cv->skills as $index => $skill)
                                <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="{{ $index }}">
                                    <input type="text" name="skills[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $skill->name }}" placeholder="e.g. PHP / Laravel">
                                    <select name="skills[{{ $index }}][level]" class="form-select form-select-sm" style="max-width: 130px;">
                                        <option value="Beginner" {{ $skill->level === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="Intermediate" {{ $skill->level === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Advanced" {{ $skill->level === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                        <option value="Expert" {{ $skill->level === 'Expert' ? 'selected' : '' }}>Expert</option>
                                    </select>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="0">
                                    <input type="text" name="skills[0][name]" class="form-control form-control-sm" placeholder="e.g. JavaScript">
                                    <select name="skills[0][level]" class="form-select form-select-sm" style="max-width: 130px;">
                                        <option value="Advanced">Advanced</option>
                                        <option value="Expert">Expert</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Beginner">Beginner</option>
                                    </select>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Languages -->
                <div class="col-md-6">
                    <div class="card card-saas h-100">
                        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <h2 class="h6 fw-bold mb-0">
                                <i class="bi bi-translate me-2 text-muted"></i> 6. Languages
                            </h2>
                            <button type="button" class="btn btn-sm btn-saas-secondary" onclick="addLanguageRow()">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        <div class="card-body p-4" id="languageContainer">
                            @forelse($cv->languages as $index => $lang)
                                <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="{{ $index }}">
                                    <input type="text" name="languages[{{ $index }}][language]" class="form-control form-control-sm" value="{{ $lang->language }}" placeholder="e.g. English">
                                    <select name="languages[{{ $index }}][proficiency]" class="form-select form-select-sm" style="max-width: 130px;">
                                        <option value="Native" {{ $lang->proficiency === 'Native' ? 'selected' : '' }}>Native</option>
                                        <option value="Fluent" {{ $lang->proficiency === 'Fluent' ? 'selected' : '' }}>Fluent</option>
                                        <option value="Professional" {{ $lang->proficiency === 'Professional' ? 'selected' : '' }}>Professional</option>
                                        <option value="Basic" {{ $lang->proficiency === 'Basic' ? 'selected' : '' }}>Basic</option>
                                    </select>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @empty
                                <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="0">
                                    <input type="text" name="languages[0][language]" class="form-control form-control-sm" placeholder="e.g. English">
                                    <select name="languages[0][proficiency]" class="form-select form-select-sm" style="max-width: 130px;">
                                        <option value="Native">Native</option>
                                        <option value="Fluent">Fluent</option>
                                        <option value="Professional">Professional</option>
                                        <option value="Basic">Basic</option>
                                    </select>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Projects & Certifications -->
            <div class="card card-saas mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-folder-check me-2 text-muted"></i> 7. Projects & Certifications
                    </h2>
                    <button type="button" class="btn btn-sm btn-saas-secondary" onclick="addProjectRow()">
                        <i class="bi bi-plus-lg"></i> Add Project
                    </button>
                </div>
                <div class="card-body p-4">
                    <div id="projectContainer">
                        @forelse($cv->projects as $index => $proj)
                            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-muted">Project #<span class="item-number">{{ $index + 1 }}</span></span>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Project Title</label>
                                        <input type="text" name="projects[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $proj->title }}" placeholder="e.g. Open Source CLI Tool">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Role / URL</label>
                                        <input type="text" name="projects[{{ $index }}][project_url]" class="form-control form-control-sm" value="{{ $proj->project_url }}" placeholder="https://github.com/...">
                                    </div>
                                </div>
                                <textarea name="projects[{{ $index }}][description]" class="form-control form-control-sm" rows="2" placeholder="Key details and technologies...">{{ $proj->description }}</textarea>
                            </div>
                        @empty
                            <p class="small text-muted mb-0">No projects added yet. Click "+ Add Project" to highlight key portfolio works.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar Summary / Settings Column -->
        <div class="col-lg-4">
            <div class="card card-saas sticky-top" style="top: 84px;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-sliders me-1"></i> CV Settings
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" {{ $cv->isDraft() ? 'selected' : '' }}>Draft (In Progress)</option>
                            <option value="published" {{ $cv->isPublished() ? 'selected' : '' }}>Published (Ready)</option>
                        </select>
                        <div class="form-text text-muted">Drafts are private and allow incomplete entries.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template Preset</label>
                        <select name="template_key" class="form-select">
                            <option value="classic" {{ $cv->template_key === 'classic' ? 'selected' : '' }}>Classic Executive</option>
                            <option value="modern" {{ $cv->template_key === 'modern' ? 'selected' : '' }}>Modern Clean</option>
                            <option value="technical" {{ $cv->template_key === 'technical' ? 'selected' : '' }}>Technical Minimal</option>
                        </select>
                    </div>

                    <div class="p-3 bg-surface-subtle border rounded-2 mb-4">
                        <div class="small fw-semibold mb-1">Completion Score: {{ $cv->completion_percentage }}%</div>
                        <div class="progress-saas mb-2">
                            <div class="progress-saas-bar" style="width: {{ $cv->completion_percentage }}%;"></div>
                        </div>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">
                            Keep adding experiences, education, and skills to maximize your score.
                        </p>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="save_draft" class="btn-saas-secondary w-100 justify-content-center">
                            <i class="bi bi-floppy"></i> Save Draft
                        </button>
                        <button type="submit" name="action" value="publish" class="btn-saas-success w-100 justify-content-center">
                            <i class="bi bi-check2-circle"></i> {{ $cv->isPublished() ? 'Save & Update' : 'Publish / Mark Ready' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function removeRepeaterItem(btn) {
        const item = btn.closest('.repeater-item');
        if (item) {
            item.remove();
        }
    }

    function addExperienceRow() {
        const container = document.getElementById('experienceContainer');
        const index = container.querySelectorAll('.repeater-item').length + Date.now();
        const html = `
            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-bold text-muted">New Position</span>
                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small">Job Title</label>
                        <input type="text" name="experiences[${index}][job_title]" class="form-control form-control-sm" placeholder="e.g. Senior Engineer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Employer / Company</label>
                        <input type="text" name="experiences[${index}][employer]" class="form-control form-control-sm" placeholder="e.g. Company Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">City / Location</label>
                        <input type="text" name="experiences[${index}][city]" class="form-control form-control-sm" placeholder="e.g. Austin, TX">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Start Date</label>
                        <input type="text" name="experiences[${index}][start_date]" class="form-control form-control-sm" placeholder="e.g. 2021-01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">End Date</label>
                        <input type="text" name="experiences[${index}][end_date]" class="form-control form-control-sm" placeholder="e.g. Present">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Responsibilities & Achievements</label>
                    <textarea name="experiences[${index}][description]" class="form-control form-control-sm" rows="3" placeholder="Key responsibilities and achievements..."></textarea>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function addEducationRow() {
        const container = document.getElementById('educationContainer');
        const index = container.querySelectorAll('.repeater-item').length + Date.now();
        const html = `
            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="small fw-bold text-muted">New Education</span>
                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small">Institution / University</label>
                        <input type="text" name="educations[${index}][institution]" class="form-control form-control-sm" placeholder="e.g. University Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Degree & Field of Study</label>
                        <input type="text" name="educations[${index}][degree]" class="form-control form-control-sm" placeholder="e.g. B.S. in Computer Science">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">City / Country</label>
                        <input type="text" name="educations[${index}][city]" class="form-control form-control-sm" placeholder="e.g. Boston, USA">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Start Date</label>
                        <input type="text" name="educations[${index}][start_date]" class="form-control form-control-sm" placeholder="e.g. 2018-09">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">End Date</label>
                        <input type="text" name="educations[${index}][end_date]" class="form-control form-control-sm" placeholder="e.g. 2022-05">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function addSkillRow() {
        const container = document.getElementById('skillContainer');
        const index = container.querySelectorAll('.repeater-item').length + Date.now();
        const html = `
            <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="${index}">
                <input type="text" name="skills[${index}][name]" class="form-control form-control-sm" placeholder="e.g. Docker">
                <select name="skills[${index}][level]" class="form-select form-select-sm" style="max-width: 130px;">
                    <option value="Advanced">Advanced</option>
                    <option value="Expert">Expert</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Beginner">Beginner</option>
                </select>
                <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function addLanguageRow() {
        const container = document.getElementById('languageContainer');
        const index = container.querySelectorAll('.repeater-item').length + Date.now();
        const html = `
            <div class="repeater-item d-flex align-items-center gap-2 mb-2" data-index="${index}">
                <input type="text" name="languages[${index}][language]" class="form-control form-control-sm" placeholder="e.g. Spanish">
                <select name="languages[${index}][proficiency]" class="form-select form-select-sm" style="max-width: 130px;">
                    <option value="Native">Native</option>
                    <option value="Fluent">Fluent</option>
                    <option value="Professional">Professional</option>
                    <option value="Basic">Basic</option>
                </select>
                <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function addProjectRow() {
        const container = document.getElementById('projectContainer');
        const index = container.querySelectorAll('.repeater-item').length + Date.now();
        const html = `
            <div class="repeater-item border p-3 rounded-2 mb-3 bg-white" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-muted">New Project</span>
                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent" onclick="removeRepeaterItem(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small">Project Title</label>
                        <input type="text" name="projects[${index}][title]" class="form-control form-control-sm" placeholder="e.g. E-Commerce API">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Role / URL</label>
                        <input type="text" name="projects[${index}][project_url]" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                </div>
                <textarea name="projects[${index}][description]" class="form-control form-control-sm" rows="2" placeholder="Key details and technologies..."></textarea>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endpush
@endsection
