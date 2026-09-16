@extends('layouts.app')

@section('title', 'ATS Analysis - ' . $cv->title)

@section('content')
<div class="container py-4">
    <!-- Top Control Bar -->
    <div class="mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 bg-white p-3 p-md-4 rounded-3 border shadow-sm">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> My Documents
                </a>
                <span class="text-muted small">/</span>
                <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']) }}" class="small text-muted text-decoration-none">
                    Builder
                </a>
                <span class="text-muted small">/</span>
                <span class="small fw-semibold text-dark">ATS Scanner</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h4 fw-bold mb-0 text-dark">{{ $cv->title }}</h1>
                <span class="badge bg-light text-secondary border">{{ $cv->documentType?->name ?? 'Standard CV' }}</span>
                @if($cv->isPublished())
                    <span class="badge-saas-published">Published</span>
                @else
                    <span class="badge-saas-draft">Draft</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Re-analyze Trigger -->
            <form action="{{ route('cvs.ats.analyze', $cv) }}" method="POST" class="d-inline">
                @csrf
                @if(!empty($analysis?->job_description))
                    <input type="hidden" name="job_description" value="{{ $analysis->job_description }}">
                @endif
                <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-clockwise"></i> Re-Scan Document
                </button>
            </form>

            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']) }}" class="btn btn-saas-secondary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Edit in Builder
            </a>

            <a href="{{ route('cvs.show', $cv) }}" class="btn btn-saas-primary btn-sm">
                <i class="bi bi-eye me-1"></i> View Document
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="row g-4">
        <!-- Left Column: Overall Score & Category Breakdown -->
        <div class="col-lg-4">
            <!-- Overall Score Card -->
            <div class="card border shadow-sm rounded-3 mb-4 text-center p-4 bg-white">
                <h6 class="text-uppercase text-muted fw-bold small mb-3 tracking-wide">Overall ATS Score</h6>
                
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px; border-radius: 50%; background: #f8fafc; border: 8px solid {{ $analysis->getColorHex() }};">
                        <div class="text-center">
                            <span class="display-5 fw-bold text-dark">{{ $analysis->overall_score }}</span>
                            <span class="small text-muted d-block" style="margin-top: -6px;">/ 100</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="badge px-3 py-2 fs-6 rounded-pill" style="background-color: {{ $analysis->getColorHex() }}15; color: {{ $analysis->getColorHex() }}; border: 1px solid {{ $analysis->getColorHex() }}40;">
                        Grade {{ $analysis->getGrade() }} &bull; 
                        @if($analysis->overall_score >= 85)
                            ATS Ready
                        @elseif($analysis->overall_score >= 70)
                            Good Alignment
                        @elseif($analysis->overall_score >= 50)
                            Needs Improvement
                        @else
                            Action Required
                        @endif
                    </span>
                </div>

                <p class="small text-muted mb-0">
                    Scored against ATS parsing standards, keyword density, section architecture, and formatting compliance.
                </p>
            </div>

            <!-- 5-Category Breakdown Card -->
            <div class="card border shadow-sm rounded-3 mb-4 p-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-steps text-primary"></i> Category Scoring
                </h6>

                <!-- 1. Structure -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold text-secondary">Structure & Formatting</span>
                        <span class="fw-bold text-dark">{{ $analysis->structure_score }} / 20</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($analysis->structure_score / 20) * 100 }}%"></div>
                    </div>
                </div>

                <!-- 2. Contact Info -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold text-secondary">Contact Completeness</span>
                        <span class="fw-bold text-dark">{{ $analysis->contact_score ?? 15 }} / 15</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        @php
                            $contactPct = (($analysis->contact_score ?? 15) / 15) * 100;
                        @endphp
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $contactPct }}%"></div>
                    </div>
                </div>

                <!-- 3. Content Impact -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold text-secondary">Content Impact & Verbs</span>
                        <span class="fw-bold text-dark">{{ $analysis->content_score }} / 25</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($analysis->content_score / 25) * 100 }}%"></div>
                    </div>
                </div>

                <!-- 4. Skills Density -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold text-secondary">Skills & Keywords</span>
                        <span class="fw-bold text-dark">{{ $analysis->skills_score }} / 20</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($analysis->skills_score / 20) * 100 }}%"></div>
                    </div>
                </div>

                <!-- 5. Completeness & Length -->
                <div class="mb-1">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-semibold text-secondary">Length & Completeness</span>
                        <span class="fw-bold text-dark">{{ $analysis->completeness_score }} / 20</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ ($analysis->completeness_score / 20) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Quantitative Stats Card -->
            <div class="card border shadow-sm rounded-3 p-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-speedometer2 text-secondary"></i> Document Metrics
                </h6>

                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Word Count</div>
                            <div class="fw-bold text-dark fs-6">{{ $analysis->metrics['word_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Reading Time</div>
                            <div class="fw-bold text-dark fs-6">{{ $analysis->metrics['reading_time_minutes'] ?? 1 }} min</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Action Verbs</div>
                            <div class="fw-bold text-success fs-6">{{ $analysis->metrics['action_verbs_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Impact Metrics</div>
                            <div class="fw-bold text-primary fs-6">{{ $analysis->metrics['metrics_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Total Skills</div>
                            <div class="fw-bold text-dark fs-6">{{ $analysis->metrics['skills_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-2 bg-light">
                            <div class="small text-muted">Passive Words</div>
                            <div class="fw-bold {{ ($analysis->metrics['passive_phrases_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' }} fs-6">
                                {{ $analysis->metrics['passive_phrases_count'] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Job Matcher, Strengths, Issues & Actionable Suggestions -->
        <div class="col-lg-8">
            <!-- Job Description Matcher Card -->
            <div class="card border shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-briefcase-fill small"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Job Description Matcher</h6>
                            <span class="small text-muted">Match your document against real job postings</span>
                        </div>
                    </div>

                    @if(!empty($analysis->job_description))
                        <form action="{{ route('cvs.ats.clear-job', $cv) }}" method="POST" class="d-inline" onsubmit="return confirm('Clear target job description?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Clear Target Job
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if(!empty($analysis->job_match_score !== null))
                        <!-- Job Match Score Banner -->
                        <div class="p-3 mb-4 rounded-3 border bg-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <span class="small text-muted text-uppercase fw-semibold d-block">Target Job Alignment</span>
                                <h4 class="fw-bold mb-0 text-dark">
                                    {{ $analysis->job_match_score }}% Keyword Match
                                </h4>
                            </div>
                            <div class="flex-grow-1 mx-md-4" style="max-width: 300px;">
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $jobMatchColor = $analysis->job_match_score >= 75 ? '#10b981' : ($analysis->job_match_score >= 50 ? '#f59e0b' : '#ef4444');
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $analysis->job_match_score }}%; background-color: {{ $jobMatchColor }};"></div>
                                </div>
                            </div>
                            <div>
                                <span class="badge px-3 py-2" style="background-color: {{ $jobMatchColor }}20; color: {{ $jobMatchColor }}; border: 1px solid {{ $jobMatchColor }}40;">
                                    {{ count($analysis->matched_keywords ?? []) }} Matched &bull; {{ count($analysis->missing_keywords ?? []) }} Missing
                                </span>
                            </div>
                        </div>

                        <!-- Matched Keywords -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i> Matched Keywords ({{ count($analysis->matched_keywords ?? []) }})
                            </h6>
                            @if(!empty($analysis->matched_keywords))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($analysis->matched_keywords as $item)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-2 fw-medium">
                                            <i class="bi bi-check2"></i> {{ $item['keyword'] }}
                                            <small class="text-muted ms-1">({{ implode(', ', $item['found_in'] ?? []) }})</small>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="small text-muted mb-0">No direct keywords matched with the target job posting.</p>
                            @endif
                        </div>

                        <!-- Missing Keywords & Placement Recommendations -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-warning"></i> Missing Keywords & Placement Hints
                            </h6>
                            @if(!empty($analysis->missing_keywords))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr class="small text-muted">
                                                <th style="width: 25%;">Missing Keyword</th>
                                                <th style="width: 15%;">Priority</th>
                                                <th>Contextual Recommendation</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($analysis->missing_keywords as $item)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $item['keyword'] }}</td>
                                                    <td>
                                                        @if($item['priority'] === 'High')
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">High Priority</span>
                                                        @else
                                                            <span class="badge bg-light text-secondary border">Medium</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-secondary">{{ $item['placement_hint'] ?? 'Consider adding if relevant.' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-3 bg-success-subtle text-success rounded-2 small">
                                    <i class="bi bi-check-circle me-1"></i> Outstanding! All identified core competencies from the job description are present in your document.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Match Form (Paste or Update JD) -->
                    <form action="{{ route('cvs.ats.match-job', $cv) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="job_description" class="form-label small fw-semibold text-secondary">
                                {{ !empty($analysis->job_description) ? 'Update Target Job Description:' : 'Paste Target Job Description (Text or Requirements):' }}
                            </label>
                            <textarea 
                                name="job_description" 
                                id="job_description" 
                                rows="4" 
                                class="form-control form-control-sm @error('job_description') is-invalid @enderror" 
                                placeholder="Paste the job requirements, responsibilities, or complete job posting here (e.g. Senior PHP / Laravel Developer with MySQL, Docker, REST APIs, and Agile experience)..."
                                required>{{ old('job_description', $analysis->job_description ?? '') }}</textarea>
                            @error('job_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">
                                Our parser extracts core technologies, soft skills, and domain requirements, then compares them against your document's sections.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-saas-primary btn-sm">
                                <i class="bi bi-search me-1"></i> {{ !empty($analysis->job_description) ? 'Re-Analyze Match' : 'Analyze Job Description Match' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Strengths Card -->
            <div class="card border shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill text-success"></i> Document Strengths ({{ count($analysis->strengths ?? []) }})
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if(!empty($analysis->strengths))
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            @foreach($analysis->strengths as $strength)
                                <li class="d-flex align-items-start gap-2 small text-dark">
                                    <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                    <span>{{ $strength }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="small text-muted mb-0">Add more content and structured sections to build your strengths list.</p>
                    @endif
                </div>
            </div>

            <!-- Issues & Deficiencies Card -->
            <div class="card border shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-octagon-fill text-danger"></i> Improvement Areas ({{ count($analysis->issues ?? []) }})
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if(!empty($analysis->issues))
                        <div class="d-flex flex-column gap-3">
                            @foreach($analysis->issues as $issue)
                                <div class="p-3 rounded-2 border d-flex align-items-start gap-3 {{ $issue['severity'] === 'High' ? 'bg-danger-subtle border-danger-subtle' : ($issue['severity'] === 'Medium' ? 'bg-warning-subtle border-warning-subtle' : 'bg-light border') }}">
                                    @if($issue['severity'] === 'High')
                                        <i class="bi bi-x-circle-fill text-danger fs-5 mt-1"></i>
                                    @elseif($issue['severity'] === 'Medium')
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 mt-1"></i>
                                    @else
                                        <i class="bi bi-info-circle-fill text-secondary fs-5 mt-1"></i>
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ $issue['severity'] === 'High' ? 'bg-danger text-white' : ($issue['severity'] === 'Medium' ? 'bg-warning text-dark' : 'bg-secondary text-white') }}">
                                                {{ $issue['severity'] }} Priority
                                            </span>
                                            <span class="small fw-semibold text-muted">{{ $issue['category'] ?? 'General' }}</span>
                                        </div>
                                        <div class="small text-dark fw-medium">{{ $issue['message'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 bg-success-subtle text-success rounded-2 small">
                            <i class="bi bi-check-circle me-1"></i> No major ATS compatibility issues detected!
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actionable Recommendations Card -->
            <div class="card border shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning"></i> Actionable Next Steps
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if(!empty($analysis->suggestions))
                        <ol class="mb-0 ps-3 d-flex flex-column gap-2 small text-secondary">
                            @foreach($analysis->suggestions as $suggestion)
                                <li class="text-dark">{{ $suggestion }}</li>
                            @endforeach
                        </ol>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => 'personal-info']) }}" class="btn btn-saas-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Apply Improvements in Builder
                            </a>
                        </div>
                    @else
                        <p class="small text-muted mb-0">Your document is in great shape.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
