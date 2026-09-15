@php
    $isMotivation = $cv->documentType?->slug === 'motivation-letter';
@endphp

<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-file-earmark-richtext me-2 text-muted"></i>
                {{ $isMotivation ? 'Motivation, Background & Goals' : 'Letter Content & Body Paragraphs' }}
            </h2>
            <p class="text-muted small mb-0 mt-1">
                {{ $isMotivation ? 'Articulate your core motivation, relevant academic/industry preparation, and career ambitions.' : 'Draft your opening statement, body paragraphs highlighting key achievements, and compelling call-to-action.' }}
            </p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.letter-details', $cv) }}">
            @csrf
            <input type="hidden" name="section" value="letter-content">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Salutation / Greeting</label>
                    <input type="text" name="salutation" class="form-control @error('salutation') is-invalid @enderror" 
                           value="{{ old('salutation', $cv->letterDetail?->salutation ?: ($isMotivation ? 'Dear Members of the Admissions Committee,' : 'Dear Hiring Manager,')) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Dear Members of the Admissions Committee,' : 'e.g. Dear Dr. Vance, or Dear Hiring Team,' }}">
                    @error('salutation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Opening & Purpose Statement' : 'Opening Hook & Intent' }}
                    </label>
                    <textarea name="opening" rows="3" class="form-control @error('opening') is-invalid @enderror" 
                              placeholder="{{ $isMotivation ? 'State why you are applying to this program and summarize your core enthusiasm...' : 'State the position you are applying for and introduce your high-impact qualifications...' }}">{{ old('opening', $cv->letterDetail?->opening) }}</textarea>
                    <div class="form-text small text-muted">1 concise paragraph introducing your target role/program.</div>
                    @error('opening')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Core Motivation, Research Interests & Background' : 'Body Paragraphs (Evidence & Quantifiable Achievements)' }}
                    </label>
                    <textarea name="body" rows="7" class="form-control @error('body') is-invalid @enderror" 
                              placeholder="{{ $isMotivation ? 'Detail your technical/academic background, previous research/projects, and what draws you specifically to this faculty or curriculum...' : 'Highlight 1-3 key achievements, leadership experiences, and how your skills directly solve problems for the employer...' }}">{{ old('body', $cv->letterDetail?->body) }}</textarea>
                    <div class="form-text small text-muted">Use line breaks to separate paragraphs. Supports multiple paragraphs.</div>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        {{ $isMotivation ? 'Academic Goals & Value Proposition' : 'Closing Statement & Call to Action' }}
                    </label>
                    <textarea name="call_to_action" rows="2" class="form-control @error('call_to_action') is-invalid @enderror" 
                              placeholder="{{ $isMotivation ? 'Summarize how you will contribute to the university community and thank the committee for their review...' : 'Thank the reader for their consideration and request an interview or discussion...' }}">{{ old('call_to_action', $cv->letterDetail?->call_to_action) }}</textarea>
                    @error('call_to_action')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top">
                <span class="small text-muted"><i class="bi bi-shield-check me-1"></i> Changes update live preview immediately</span>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-floppy me-1"></i> Save Content
                </button>
            </div>
        </form>
    </div>
</div>
