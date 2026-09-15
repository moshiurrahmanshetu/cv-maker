<div class="card card-saas">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="bi bi-card-text me-2 text-muted"></i> Professional Profile Summary & Objective
            </h2>
            <p class="text-muted small mb-0 mt-1">An executive summary highlighting your career impact, key specializations, and professional trajectory.</p>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-outline-dark" onclick="openAiAssistant('profile_summary', { target_input_id: 'summaryInput', current_text: document.getElementById('summaryInput').value })">
                <i class="bi bi-stars me-1 text-primary"></i> Generate with AI
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-saas-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-magic me-1"></i> More AI
                </button>
                <ul class="dropdown-menu dropdown-menu-saas dropdown-menu-end">
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-saas" onclick="openAiAssistant('career_objective', { target_input_id: 'summaryInput' })">
                            <i class="bi bi-bullseye me-2"></i> Career Objective
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-saas" onclick="openAiAssistant('content_improve', { target_input_id: 'summaryInput', text: document.getElementById('summaryInput').value, tone: 'professional' })">
                            <i class="bi bi-spellcheck me-2"></i> Improve & Polish Writing
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item dropdown-item-saas" onclick="openAiAssistant('content_improve', { target_input_id: 'summaryInput', text: document.getElementById('summaryInput').value, tone: 'ats' })">
                            <i class="bi bi-check2-all me-2"></i> ATS-Friendly Keywords
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('cvs.builder.summary', $cv) }}" id="summaryForm">
            @csrf

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 fw-bold">Executive Summary / Objective</label>
                    <span class="small text-muted" id="charCountDisplay">
                        <span id="charCount">0</span> / 3000 chars (Recommended: 200 - 500)
                    </span>
                </div>

                <textarea name="summary" id="summaryInput" class="form-control @error('summary') is-invalid @enderror" rows="6" placeholder="Example: Dedicated Software Architect with 8+ years of experience leading cross-functional engineering teams, architecting distributed microservices, and optimizing relational databases for high-concurrency cloud applications...">{{ old('summary', $cv->summary) }}</textarea>
                
                @error('summary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Writing Tips Callout -->
            <div class="p-3 border rounded-2 bg-surface-subtle mb-4">
                <div class="small fw-bold text-dark mb-1"><i class="bi bi-lightbulb me-1"></i> Pro Tip:</div>
                <p class="small text-muted mb-0" style="line-height: 1.5;">
                    Lead with your primary title, state total years in the field, highlight 2-3 standout accomplishments with quantifiable results, and feature your core technical stack.
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('summaryInput').value = ''; updateCharCount(); triggerAutosave({ summary: '' });">
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
