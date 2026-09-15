@php
    $isMotivation = $cv->documentType?->slug === 'motivation-letter';
@endphp

<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-building me-2 text-muted"></i>
                {{ $isMotivation ? 'Target Institution & Committee' : 'Recipient & Organization' }}
            </h2>
            <p class="text-muted small mb-0 mt-1">
                {{ $isMotivation ? 'Specify the university, department, or admission committee details.' : 'Specify the hiring manager, organization name, and position details.' }}
            </p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.letter-details', $cv) }}">
            @csrf
            <input type="hidden" name="section" value="letter-details">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Recipient / Committee Name' : 'Recipient Full Name' }}
                    </label>
                    <input type="text" name="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" 
                           value="{{ old('recipient_name', $cv->letterDetail?->recipient_name) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Graduate Admissions Committee' : 'e.g. Dr. Elizabeth Vance or Hiring Manager' }}">
                    @error('recipient_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Department / Faculty Title' : 'Recipient Job Title' }}
                    </label>
                    <input type="text" name="recipient_title" class="form-control @error('recipient_title') is-invalid @enderror" 
                           value="{{ old('recipient_title', $cv->letterDetail?->recipient_title) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Department of Computer Science' : 'e.g. Director of Engineering Talent' }}">
                    @error('recipient_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'University / Organization Name' : 'Company / Organization Name' }}
                    </label>
                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                           value="{{ old('company_name', $cv->letterDetail?->company_name) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Stanford University' : 'e.g. Google LLC or Apex Cloud Solutions' }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Document Date</label>
                    <input type="text" name="letter_date" class="form-control @error('letter_date') is-invalid @enderror" 
                           value="{{ old('letter_date', $cv->letterDetail?->letter_date ?: date('F j, Y')) }}" 
                           placeholder="e.g. {{ date('F j, Y') }}">
                    @error('letter_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'University / Institution Address' : 'Company Address / Location' }}
                    </label>
                    <textarea name="company_address" rows="2" class="form-control @error('company_address') is-invalid @enderror" 
                              placeholder="e.g. 1600 Amphitheatre Parkway&#10;Mountain View, CA 94043">{{ old('company_address', $cv->letterDetail?->company_address) }}</textarea>
                    @error('company_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Subject / Program of Interest' : 'Subject / Position Applied For' }}
                    </label>
                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                           value="{{ old('subject', $cv->letterDetail?->subject) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Statement of Purpose – M.S. in Computer Science (AI Specialization)' : 'e.g. Application for Senior Cloud Architect (Req #8421)' }}">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top">
                <span class="small text-muted"><i class="bi bi-shield-check me-1"></i> Changes update live preview immediately</span>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-floppy me-1"></i> Save Details
                </button>
            </div>
        </form>
    </div>
</div>
