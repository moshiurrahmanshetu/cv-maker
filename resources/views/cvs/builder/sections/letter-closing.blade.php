@php
    $isMotivation = $cv->documentType?->slug === 'motivation-letter';
@endphp

<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-pen me-2 text-muted"></i> Sign-off & Closing
            </h2>
            <p class="text-muted small mb-0 mt-1">Configure your formal valediction, signature name, and sign-off tone.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.letter-details', $cv) }}">
            @csrf
            <input type="hidden" name="section" value="letter-closing">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Formal Valediction / Closing</label>
                    <input type="text" name="closing" class="form-control @error('closing') is-invalid @enderror" 
                           value="{{ old('closing', $cv->letterDetail?->closing ?: ($isMotivation ? 'Respectfully submitted,' : 'Sincerely,')) }}" 
                           placeholder="{{ $isMotivation ? 'e.g. Respectfully submitted,' : 'e.g. Sincerely, or Best regards,' }}">
                    <div class="form-text small text-muted">Formal closing before signature.</div>
                    @error('closing')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Typed Signature / Full Name</label>
                    <input type="text" name="sender_signature" class="form-control @error('sender_signature') is-invalid @enderror" 
                           value="{{ old('sender_signature', $cv->letterDetail?->sender_signature ?: ($cv->personalInfo?->full_name ?? Auth::user()->name)) }}" 
                           placeholder="e.g. Alex Morgan">
                    <div class="form-text small text-muted">Appears below your closing phrase.</div>
                    @error('sender_signature')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top">
                <span class="small text-muted"><i class="bi bi-shield-check me-1"></i> Changes update live preview immediately</span>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-floppy me-1"></i> Save Sign-off
                </button>
            </div>
        </form>
    </div>
</div>
