<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-person me-2 text-muted"></i> Personal Information
            </h2>
            <p class="text-muted small mb-0 mt-1">Provide your primary contact channels and headline information.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.personal-info', $cv) }}" enctype="multipart/form-data">
            @csrf

            <!-- Photo Upload Area -->
            <div class="mb-4 p-3 border rounded-2 bg-surface-subtle d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                <div class="position-relative flex-shrink-0">
                    @if($cv->personalInfo?->photo_url)
                        <img src="{{ $cv->personalInfo->photo_url }}" alt="Profile Photo" class="rounded-circle border object-fit-cover shadow-sm" style="width: 72px; height: 72px;">
                    @else
                        <div class="rounded-circle border bg-white text-muted d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; font-size: 1.75rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <label class="form-label fw-bold mb-1">Profile Photo</label>
                    <input type="file" name="photo" class="form-control form-control-sm @error('photo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp">
                    <div class="form-text text-muted small">PNG, JPG, or WEBP up to 2MB. Optional.</div>
                    @error('photo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if($cv->personalInfo?->photo_path)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                            <label class="form-check-label small text-danger" for="removePhotoCheck">
                                <i class="bi bi-trash me-1"></i> Remove current profile photo
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $cv->personalInfo?->full_name) }}" placeholder="e.g. Alex Morgan">
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Professional Title / Headline</label>
                    <input type="text" name="job_title" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title', $cv->personalInfo?->job_title) }}" placeholder="e.g. Senior Software Architect">
                    @error('job_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $cv->personalInfo?->email) }}" placeholder="alex@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $cv->personalInfo?->phone) }}" placeholder="+1 (555) 000-0000">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $cv->personalInfo?->address) }}" placeholder="e.g. 123 Innovation Blvd, Suite 400">
                </div>

                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $cv->personalInfo?->city) }}" placeholder="San Francisco">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $cv->personalInfo?->country) }}" placeholder="United States">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $cv->personalInfo?->postal_code) }}" placeholder="94107">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Website / Portfolio URL</label>
                    <input type="text" name="website" class="form-control" value="{{ old('website', $cv->personalInfo?->website) }}" placeholder="https://alexmorgan.dev">
                </div>

                <div class="col-md-6">
                    <label class="form-label">LinkedIn Profile URL</label>
                    <input type="text" name="linkedin" class="form-control" value="{{ old('linkedin', $cv->personalInfo?->linkedin) }}" placeholder="https://linkedin.com/in/alexmorgan">
                </div>

                <div class="col-md-6">
                    <label class="form-label">GitHub URL</label>
                    <input type="text" name="github" class="form-control" value="{{ old('github', $cv->personalInfo?->github) }}" placeholder="https://github.com/alexmorgan">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Other Social / Portfolio URL</label>
                    <input type="text" name="other_url" class="form-control" value="{{ old('other_url', $cv->personalInfo?->other_url) }}" placeholder="https://dribbble.com/alex or https://x.com/alex">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top">
                <span class="small text-muted"><i class="bi bi-shield-check me-1"></i> Changes save only to this section</span>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-floppy me-1"></i> Save Personal Info
                </button>
            </div>
        </form>
    </div>
</div>
