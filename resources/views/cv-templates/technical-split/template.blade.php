{{-- Template 3: Technical Split --}}
<div class="template-technical-split" style="
    font-family: {{ $cvData['fontFamily'] ?? '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' }};
    color: #1e293b;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.75' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.88rem') }};
">
    <style>
        .template-technical-split .tech-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        .template-technical-split .tech-header {
            background-color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            color: #f8fafc;
            padding: 24px 28px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .template-technical-split .tech-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.4rem' : '2.0rem') }};
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
        }
        .template-technical-split .tech-role-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .template-technical-split .tech-section-heading {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.06em;
            background-color: #f1f5f9;
            padding: 6px 12px;
            border-left: 3px solid #0f172a;
            border-radius: 0 4px 4px 0;
            margin-bottom: 12px;
            margin-top: 18px;
        }
        .template-technical-split .tech-section-heading:first-child {
            margin-top: 0;
        }
        .template-technical-split .tech-exp-card {
            border-left: 2px solid #cbd5e1;
            padding-left: 14px;
            position: relative;
            margin-bottom: 14px;
        }
        .template-technical-split .tech-exp-card::before {
            content: '';
            position: absolute;
            left: -5px;
            top: 4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #0f172a;
        }
        .template-technical-split .tech-chip {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.76rem;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .template-technical-split .tech-proj-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }
    </style>

    <!-- Top Technical Header Strip -->
    <div class="tech-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                @if(!empty($cvData['photoUrl']))
                    <img src="{{ $cvData['photoUrl'] }}" alt="{{ $cvData['fullName'] }}" class="rounded-circle border border-2 border-secondary object-fit-cover shadow-sm" style="width: 75px; height: 75px;">
                @endif
                <div>
                    <h1 class="tech-name mb-1">{{ $cvData['fullName'] }}</h1>
                    <div class="tech-role-badge tech-mono">{{ $cvData['jobTitle'] }}</div>
                </div>
            </div>

            <!-- Contact Grid in Header -->
            <div class="tech-mono" style="font-size: 0.78rem; line-height: 1.6; color: #cbd5e1;">
                @if(!empty($cvData['email']))
                    <div><i class="bi bi-envelope me-1"></i> {{ $cvData['email'] }}</div>
                @endif
                @if(!empty($cvData['phone']))
                    <div><i class="bi bi-telephone me-1"></i> {{ $cvData['phone'] }}</div>
                @endif
                @if(!empty($cvData['location']))
                    <div><i class="bi bi-geo-alt me-1"></i> {{ $cvData['location'] }}</div>
                @endif
                @if(!empty($cvData['github']))
                    <div><i class="bi bi-github me-1"></i> {{ $cvData['github'] }}</div>
                @endif
                @if(!empty($cvData['website']))
                    <div><i class="bi bi-globe me-1"></i> {{ $cvData['website'] }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary / Objective -->
    @if($cvData['hasSummary'])
        <div class="mb-3">
            <div class="tech-section-heading">// 01. EXECUTIVE SUMMARY</div>
            <p class="text-secondary mb-0" style="line-height: 1.55; white-space: pre-line;">
                {{ $cvData['summary'] }}
            </p>
        </div>
    @endif

    <!-- Technical Skills / Competencies -->
    @if($cvData['hasSkills'])
        <div class="mb-3">
            <div class="tech-section-heading">// 02. TECHNICAL SKILLS & STACK</div>
            <div class="row g-2">
                @foreach($cvData['skillsByCategory'] as $categoryName => $skills)
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white h-100">
                            <div class="tech-mono small fw-bold text-dark mb-1" style="font-size: 0.78rem; text-transform: uppercase;">
                                &gt; {{ $categoryName }}
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($skills as $skill)
                                    <span class="tech-chip">
                                        {{ $skill->name }}
                                        @if(!empty($skill->level))
                                            <span class="text-muted">({{ $skill->level }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Work Experience with Timeline -->
    @if($cvData['hasExperiences'])
        <div class="mb-3">
            <div class="tech-section-heading">// 03. PROFESSIONAL EXPERIENCE</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cvData['experiences'] as $exp)
                    <div class="tech-exp-card">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="fw-bold text-dark" style="font-size: 0.92rem;">{{ $exp->job_title }}</span>
                            <span class="tech-mono text-muted" style="font-size: 0.78rem;">
                                [{{ $exp->start_date }} &rarr; {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}]
                            </span>
                        </div>
                        <div class="text-secondary small fw-semibold mb-1">
                            {{ $exp->employer }}{{ $exp->city ? ' | ' . $exp->city : '' }}{{ $exp->country ? ', ' . $exp->country : '' }}
                        </div>
                        @if($exp->description)
                            <div class="text-secondary small" style="line-height: 1.5; white-space: pre-line;">{{ $exp->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Projects -->
    @if($cvData['hasProjects'])
        <div class="mb-3">
            <div class="tech-section-heading">// 04. KEY PROJECTS & SYSTEMS</div>
            <div class="row g-2">
                @foreach($cvData['projects'] as $proj)
                    <div class="col-md-6">
                        <div class="tech-proj-box h-100">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $proj->title }}</span>
                                @if($proj->start_date)
                                    <span class="tech-mono text-muted" style="font-size: 0.72rem;">{{ $proj->start_date }}</span>
                                @endif
                            </div>
                            @if($proj->role)
                                <div class="small text-secondary fw-medium">{{ $proj->role }}</div>
                            @endif
                            @if($proj->technologies)
                                <div class="tech-mono small text-dark my-1" style="font-size: 0.74rem;">
                                    <span class="text-muted">Stack:</span> {{ $proj->technologies }}
                                </div>
                            @endif
                            @if($proj->description)
                                <div class="small text-secondary mb-1" style="line-height: 1.4; white-space: pre-line;">{{ $proj->description }}</div>
                            @endif
                            @if($proj->project_url)
                                <a href="{{ $proj->project_url }}" target="_blank" class="tech-mono small text-muted text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right"></i> {{ $proj->project_url }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Education & Certifications Row -->
    @if($cvData['hasEducations'] || $cvData['hasCertifications'])
        <div class="row g-3 mb-3">
            @if($cvData['hasEducations'])
                <div class="{{ $cvData['hasCertifications'] ? 'col-md-6' : 'col-12' }}">
                    <div class="tech-section-heading">// 05. EDUCATION</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['educations'] as $edu)
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $edu->degree }}</div>
                                <div class="small text-secondary">
                                    {{ $edu->institution }}{{ $edu->field_of_study ? ' &bull; ' . $edu->field_of_study : '' }}
                                </div>
                                <div class="tech-mono text-muted" style="font-size: 0.75rem;">
                                    {{ $edu->start_date }} &ndash; {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                                    @if($edu->grade_or_gpa)
                                        | {{ $edu->grade_or_gpa }}
                                    @endif
                                </div>
                                @if($edu->description)
                                    <div class="small text-secondary mt-1" style="white-space: pre-line;">{{ $edu->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($cvData['hasCertifications'])
                <div class="{{ $cvData['hasEducations'] ? 'col-md-6' : 'col-12' }}">
                    <div class="tech-section-heading">// 06. CERTIFICATIONS</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['certifications'] as $cert)
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $cert->name }}</div>
                                <div class="small text-secondary">{{ $cert->issuing_organization }}</div>
                                @if($cert->issue_date || $cert->credential_id)
                                    <div class="tech-mono text-muted" style="font-size: 0.75rem;">
                                        {{ $cert->issue_date }}{{ $cert->credential_id ? ' | ID: ' . $cert->credential_id : '' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Languages & Awards Row -->
    @if($cvData['hasLanguages'] || $cvData['hasAwards'])
        <div class="row g-3 mb-3">
            @if($cvData['hasLanguages'])
                <div class="{{ $cvData['hasAwards'] ? 'col-md-6' : 'col-12' }}">
                    <div class="tech-section-heading">// 07. LANGUAGES</div>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($cvData['languages'] as $lang)
                            <div class="small">
                                <span class="fw-bold text-dark">{{ $lang->language }}</span>: <span class="text-muted">{{ $lang->proficiency }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($cvData['hasAwards'])
                <div class="{{ $cvData['hasLanguages'] ? 'col-md-6' : 'col-12' }}">
                    <div class="tech-section-heading">// 08. AWARDS</div>
                    <div class="d-flex flex-column gap-1">
                        @foreach($cvData['awards'] as $award)
                            <div>
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $award->title }}</span>
                                <span class="text-muted small">({{ $award->issuer }}{{ $award->issue_date ? ' &bull; ' . $award->issue_date : '' }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Custom Sections -->
    @if($cvData['hasCustomSections'])
        @foreach($cvData['customSectionsGrouped'] as $sectionTitle => $entries)
            <div class="mb-3">
                <div class="tech-section-heading">// {{ strtoupper($sectionTitle) }}</div>
                <div class="d-flex flex-column gap-2">
                    @foreach($entries as $entry)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                @if($entry->title)
                                    <span class="fw-bold text-dark">{{ $entry->title }}</span>
                                @endif
                                @if($entry->date_period)
                                    <span class="tech-mono text-muted" style="font-size: 0.78rem;">{{ $entry->date_period }}</span>
                                @endif
                            </div>
                            @if($entry->subtitle)
                                <div class="small text-secondary">{{ $entry->subtitle }}</div>
                            @endif
                            @if($entry->content)
                                <div class="small text-secondary mt-1" style="white-space: pre-line;">{{ $entry->content }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <!-- References -->
    @if($cvData['hasReferences'])
        <div class="mb-3">
            <div class="tech-section-heading">// 09. REFERENCES</div>
            <div class="row g-2">
                @foreach($cvData['references'] as $ref)
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-white">
                            <div class="fw-bold text-dark small">{{ $ref->full_name }}</div>
                            <div class="text-secondary small">{{ $ref->job_title }}{{ $ref->company ? ' @ ' . $ref->company : '' }}</div>
                            @if($ref->email)
                                <div class="tech-mono text-muted" style="font-size: 0.75rem;"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
