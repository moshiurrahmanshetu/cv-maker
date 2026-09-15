<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-card-text me-2 text-muted"></i> Professional Profile Summary
            </h2>
            <p class="text-muted small mb-0 mt-1">A concise 2-4 sentence executive summary highlighting your career impact and core strengths.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.summary', $cv) }}" id="summaryForm">
            @csrf

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 fw-bold">Executive Summary</label>
                    <span class="small text-muted" id="charCountDisplay">
                        <span id="charCount">0</span> / 3000 chars (Recommended: 200 - 500)
                    </span>
                </div>

                <textarea name="summary" id="summaryInput" class="form-control @error('summary') is-invalid @enderror" rows="6" placeholder="Example: Dedicated Full Stack Engineer with 8+ years of experience leading cross-functional engineering teams, architecting microservices, and optimizing relational databases for high-concurrency SaaS platforms...">{{ old('summary', $cv->summary) }}</textarea>
                
                @error('summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Writing Tips Callout -->
            <div class="p-3 border rounded-2 bg-surface-subtle mb-4">
                <div class="small fw-bold text-dark mb-1"><i class="bi bi-lightbulb me-1"></i> Pro Tip:</div>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Lead with your primary title, mention years of experience, highlight 2-3 standout accomplishments with metrics, and list your top technical competencies.
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('summaryInput').value = ''; updateCharCount();">
                    <i class="bi bi-eraser me-1"></i> Clear Text
                </button>
                <button type="submit" class="btn-saas-primary px-4">
                    <i class="bi bi-floppy me-1"></i> Save Summary
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateCharCount() {
        const input = document.getElementById('summaryInput');
        const counter = document.getElementById('charCount');
        if (input && counter) {
            counter.textContent = input.value.length;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('summaryInput');
        if (input) {
            input.addEventListener('input', updateCharCount);
            updateCharCount();
        }
    });
</script>
@endpush
