<!-- AI Career Assistant Modal -->
<div class="modal fade" id="aiAssistantModal" tabindex="-1" aria-labelledby="aiAssistantModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border card-saas shadow">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-3 px-4 bg-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded bg-dark text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-stars fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold fs-6 mb-0 text-dark" id="aiAssistantModalLabel">AI Career Assistant</h5>
                        <div class="text-muted" style="font-size: 0.72rem;" id="aiAssistantSubtitle">Smart document content enhancement</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="modal" aria-label="Close" id="aiModalCloseBtn"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-surface-subtle" id="aiModalBody">
                
                <!-- 1. CONTEXT INPUT STATE -->
                <div id="aiStateInput">
                    <div class="p-3 bg-white border rounded mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span class="fw-semibold small text-dark" id="aiContextGuideTitle">Customize AI Context</span>
                        </div>
                        <p class="small text-muted mb-0" id="aiContextGuideText">
                            Provide optional details to help the AI generate relevant, accurate career content. Existing document data will automatically be referenced.
                        </p>
                    </div>

                    <form id="aiPromptForm" onsubmit="return false;">
                        <!-- Dynamic fields will be populated based on feature -->
                        <div id="aiDynamicFieldsContainer"></div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-3">
                            <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="button" class="btn-saas-primary px-4" id="aiSubmitGenerateBtn">
                                <i class="bi bi-stars me-1"></i> Generate with AI
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. GENERATING / LOADING STATE -->
                <div id="aiStateGenerating" class="text-center py-5 d-none">
                    <div class="spinner-border text-dark mb-3" style="width: 2.8rem; height: 2.8rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" id="aiGeneratingTitle">Generating tailored career content...</h6>
                    <p class="text-muted small mb-0" id="aiGeneratingSubtitle">Synthesizing role context, verified experience, and professional phrasing.</p>
                </div>

                <!-- 3. RESULTS & VARIANTS STATE -->
                <div id="aiStateResults" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" id="aiResultsBadge">
                            AI Generated Suggestions
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="aiRegenerateBtn" style="font-size: 0.75rem;">
                            <i class="bi bi-arrow-repeat me-1"></i> Regenerate
                        </button>
                    </div>

                    <!-- Variants Tabs Container (for Summary & Objectives) -->
                    <div id="aiVariantsTabsContainer" class="d-none mb-3">
                        <ul class="nav nav-pills nav-fill bg-white p-1 rounded border" id="aiVariantsNav"></ul>
                    </div>

                    <!-- Editable Content Area -->
                    <div class="card card-saas border mb-3">
                        <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-dark" id="aiResultActiveLabel">Suggested Content</span>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">Editable Preview</span>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <textarea class="form-control font-monospace" id="aiResultEditableTextarea" rows="6" style="font-size: 0.88rem; line-height: 1.5; resize: vertical;"></textarea>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-pencil me-1"></i> You can edit or refine this text directly before applying it to your document.
                            </div>
                        </div>
                    </div>

                    <!-- Structured Letter Details Preview (for Cover/Motivation Letters) -->
                    <div id="aiStructuredLetterContainer" class="d-none mb-3">
                        <div class="p-3 bg-white border rounded">
                            <div class="mb-2">
                                <label class="small fw-bold text-muted">Salutation</label>
                                <input type="text" class="form-control form-control-sm" id="aiLetterSalutation">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted">Opening Paragraph</label>
                                <textarea class="form-control form-control-sm" id="aiLetterOpening" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted">Body & Core Qualifications</label>
                                <textarea class="form-control form-control-sm" id="aiLetterBody" rows="4"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted">Call to Action</label>
                                <textarea class="form-control form-control-sm" id="aiLetterCallToAction" rows="2"></textarea>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted">Closing Sign-off</label>
                                    <input type="text" class="form-control form-control-sm" id="aiLetterClosing">
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted">Signer Name</label>
                                    <input type="text" class="form-control form-control-sm" id="aiLetterSignature">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Checkbox Selection Grid (for Skill Suggestions) -->
                    <div id="aiSkillsGridContainer" class="d-none mb-3">
                        <div class="p-3 bg-white border rounded">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-dark">Select Skills to Append to Document:</span>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small text-dark" id="aiSelectAllSkillsBtn">
                                    Select All
                                </button>
                            </div>
                            <div class="row g-2" id="aiSkillsCheckboxesList"></div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn-saas-primary px-4" id="aiApplyResultBtn">
                            <i class="bi bi-check2-circle me-1"></i> Apply to Document
                        </button>
                    </div>
                </div>

                <!-- 4. FAILED / ERROR STATE -->
                <div id="aiStateError" class="text-center py-4 d-none">
                    <div class="stat-widget-icon mx-auto mb-3 bg-danger-subtle text-danger" style="width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">AI Generation Unavailable</h6>
                    <p class="text-muted small mb-3" id="aiErrorMessage">Unable to complete the generation request. Your document data is safe and unchanged.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn-saas-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn-saas-primary" id="aiRetryBtn">
                            <i class="bi bi-arrow-repeat me-1"></i> Try Again
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
