{{-- Template 1: Classic Executive --}}
<div class="template-classic-executive" style="
    font-family: {{ $cvData['fontFamily'] ?? "'Times New Roman', Times, 'Georgia', serif" }};
    color: #1e293b;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.75' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.92rem') }};
">
    <style>
        .template-classic-executive .classic-sans {
            font-family: {{ $cvData['fontFamily'] ?? '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif' }};
        }
        .template-classic-executive .header-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.7rem' : ($cvData['headingScale'] === 'large' ? '2.4rem' : '2.1rem') }};
            font-weight: 700;
            letter-spacing: 0.04em;
            color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            text-transform: uppercase;
        }
        .template-classic-executive .header-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .template-classic-executive .classic-section-heading {
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            border-bottom: 1.5px solid {{ $cvData['accentColor'] ?? '#334155' }};
            padding-bottom: 4px;
            margin-bottom: 14px;
            margin-top: {{ $cvData['sectionSpacing'] === 'compact' ? '12px' : ($cvData['sectionSpacing'] === 'spacious' ? '24px' : '18px') }};
        }

        .template-classic-executive .classic-section-heading:first-child {
            margin-top: 0;
        }
        .template-classic-executive .classic-entry-title {
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }
        .template-classic-executive .classic-entry-sub {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
        }
        .template-classic-executive .classic-meta-date {
            font-size: 0.82rem;
            color: #64748b;
            font-style: italic;
        }
        .template-classic-executive .classic-body-text {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 0.88rem;
            line-height: 1.55;
            color: #334155;
        }
        .template-classic-executive .classic-skill-tag {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 0.8rem;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 3px 9px;
            border-radius: 3px;
            color: #1e293b;
            display: inline-block;
        }
        .template-classic-executive .classic-ref-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
        }
    </style>

    <!-- Executive Centered Header -->
    <div class="text-center pb-3 mb-3 border-bottom" style="border-bottom: 2px solid #0f172a !important;">
        @if(!empty($cvData['photoUrl']))
            <div class="mb-3">
                <img src="{{ $cvData['photoUrl'] }}" alt="{{ $cvData['fullName'] }}" class="rounded-circle border shadow-sm object-fit-cover" style="width: 90px; height: 90px; border-color: #cbd5e1 !important;">
            </div>
        @endif

        <h1 class="header-name mb-1">{{ $cvData['fullName'] }}</h1>
        <div class="header-title mb-2 classic-sans">{{ $cvData['jobTitle'] }}</div>

        <!-- Contact details -->
        <div class="d-flex flex-wrap justify-content-center gap-3 classic-sans" style="font-size: 0.82rem; color: #475569;">
            @if(!empty($cvData['email']))
                <div><i class="bi bi-envelope me-1"></i>{{ $cvData['email'] }}</div>
            @endif
            @if(!empty($cvData['phone']))
                <div><i class="bi bi-telephone me-1"></i>{{ $cvData['phone'] }}</div>
            @endif
            @if(!empty($cvData['location']))
                <div><i class="bi bi-geo-alt me-1"></i>{{ $cvData['location'] }}</div>
            @endif
            @if(!empty($cvData['website']))
                <div><i class="bi bi-globe me-1"></i>{{ $cvData['website'] }}</div>
            @endif
            @if(!empty($cvData['linkedin']))
                <div><i class="bi bi-linkedin me-1"></i>{{ $cvData['linkedin'] }}</div>
            @endif
            @if(!empty($cvData['github']))
                <div><i class="bi bi-github me-1"></i>{{ $cvData['github'] }}</div>
            @endif
            @if(!empty($cvData['otherUrl']))
                <div><i class="bi bi-link-45deg me-1"></i>{{ $cvData['otherUrl'] }}</div>
            @endif
        </div>
    </div>

    <!-- 1. Executive Summary -->
    @if($cvData['hasSummary'])
        <div class="mb-3">
            <div class="classic-section-heading">Executive Summary</div>
            <p class="classic-body-text mb-0" style="white-space: pre-line; text-align: justify;">
                {{ $cvData['summary'] }}
            </p>
        </div>
    @endif

    <!-- 2. Work Experience -->
    @if($cvData['hasExperiences'])
        <div class="mb-3">
            <div class="classic-section-heading">Professional Experience</div>
            <div class="d-flex flex-column gap-3">
                @foreach($cvData['experiences'] as $exp)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="classic-entry-title">{{ $exp->job_title }}</span>
                            <span class="classic-meta-date classic-sans">
                                {{ $exp->start_date }} &ndash; {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                            </span>
                        </div>
                        <div class="classic-entry-sub classic-sans mb-1">
                            {{ $exp->employer }}{{ $exp->city ? ', ' . $exp->city : '' }}{{ $exp->country ? ' (' . $exp->country . ')' : '' }}
                        </div>
                        @if($exp->description)
                            <div class="classic-body-text" style="white-space: pre-line;">{{ $exp->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. Education -->
    @if($cvData['hasEducations'])
        <div class="mb-3">
            <div class="classic-section-heading">Education & Academic Background</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cvData['educations'] as $edu)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="classic-entry-title">{{ $edu->degree }}</span>
                            <span class="classic-meta-date classic-sans">
                                {{ $edu->start_date }} &ndash; {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                            </span>
                        </div>
                        <div class="classic-entry-sub classic-sans">
                            {{ $edu->institution }}{{ $edu->field_of_study ? ' &bull; ' . $edu->field_of_study : '' }}{{ $edu->city ? ' (' . $edu->city . ')' : '' }}
                        </div>
                        @if($edu->grade_or_gpa)
                            <div class="classic-sans small text-muted">{{ $edu->grade_or_gpa }}</div>
                        @endif
                        @if($edu->description)
                            <div class="classic-body-text mt-1" style="white-space: pre-line;">{{ $edu->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 4. Key Projects -->
    @if($cvData['hasProjects'])
        <div class="mb-3">
            <div class="classic-section-heading">Key Projects & Initiatives</div>
            <div class="d-flex flex-column gap-2">
                @foreach($cvData['projects'] as $proj)
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="classic-entry-title">{{ $proj->title }}</span>
                            @if($proj->start_date)
                                <span class="classic-meta-date classic-sans">{{ $proj->start_date }} {{ $proj->end_date ? '&ndash; ' . $proj->end_date : '' }}</span>
                            @endif
                        </div>
                        @if($proj->role)
                            <div class="classic-entry-sub classic-sans">{{ $proj->role }}</div>
                        @endif
                        @if($proj->technologies)
                            <div class="classic-sans small text-muted mb-1"><strong>Technologies:</strong> {{ $proj->technologies }}</div>
                        @endif
                        @if($proj->description)
                            <div class="classic-body-text" style="white-space: pre-line;">{{ $proj->description }}</div>
                        @endif
                        @if($proj->project_url)
                            <div class="classic-sans small mt-1">
                                <a href="{{ $proj->project_url }}" target="_blank" class="text-decoration-none text-muted">
                                    <i class="bi bi-link-45deg"></i> {{ $proj->project_url }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 5. Core Skills -->
    @if($cvData['hasSkills'])
        <div class="mb-3">
            <div class="classic-section-heading">Core Competencies & Skills</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($cvData['skills'] as $skill)
                    <div class="classic-skill-tag">
                        <strong>{{ $skill->name }}</strong>
                        @if(!empty($skill->level))
                            <span class="text-muted ms-1" style="font-size: 0.75rem;">({{ $skill->level }})</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 6. Certifications & Awards -->
    @if($cvData['hasCertifications'] || $cvData['hasAwards'])
        <div class="row g-3 mb-3">
            @if($cvData['hasCertifications'])
                <div class="{{ $cvData['hasAwards'] ? 'col-md-6' : 'col-12' }}">
                    <div class="classic-section-heading">Certifications & Credentials</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['certifications'] as $cert)
                            <div>
                                <div class="classic-entry-title" style="font-size: 0.9rem;">{{ $cert->name }}</div>
                                <div class="classic-sans small text-muted">
                                    {{ $cert->issuing_organization }}{{ $cert->issue_date ? ' &bull; ' . $cert->issue_date : '' }}
                                </div>
                                @if($cert->description)
                                    <div class="classic-body-text small mt-1">{{ $cert->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($cvData['hasAwards'])
                <div class="{{ $cvData['hasCertifications'] ? 'col-md-6' : 'col-12' }}">
                    <div class="classic-section-heading">Honors & Awards</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['awards'] as $award)
                            <div>
                                <div class="classic-entry-title" style="font-size: 0.9rem;">{{ $award->title }}</div>
                                <div class="classic-sans small text-muted">
                                    {{ $award->issuer }}{{ $award->issue_date ? ' &bull; ' . $award->issue_date : '' }}
                                </div>
                                @if($award->description)
                                    <div class="classic-body-text small mt-1">{{ $award->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- 7. Languages -->
    @if($cvData['hasLanguages'])
        <div class="mb-3">
            <div class="classic-section-heading">Languages</div>
            <div class="d-flex flex-wrap gap-4 classic-sans" style="font-size: 0.85rem;">
                @foreach($cvData['languages'] as $lang)
                    <div>
                        <strong>{{ $lang->language }}</strong>: <span class="text-muted">{{ $lang->proficiency }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 8. Custom Sections -->
    @if($cvData['hasCustomSections'])
        @foreach($cvData['customSectionsGrouped'] as $sectionTitle => $entries)
            <div class="mb-3">
                <div class="classic-section-heading">{{ $sectionTitle }}</div>
                <div class="d-flex flex-column gap-2">
                    @foreach($entries as $entry)
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline">
                                @if($entry->title)
                                    <span class="classic-entry-title">{{ $entry->title }}</span>
                                @endif
                                @if($entry->date_period)
                                    <span class="classic-meta-date classic-sans">{{ $entry->date_period }}</span>
                                @endif
                            </div>
                            @if($entry->subtitle)
                                <div class="classic-entry-sub classic-sans">{{ $entry->subtitle }}</div>
                            @endif
                            @if($entry->content)
                                <div class="classic-body-text mt-1" style="white-space: pre-line;">{{ $entry->content }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <!-- 9. References -->
    @if($cvData['hasReferences'])
        <div class="mb-3">
            <div class="classic-section-heading">Professional References</div>
            <div class="row g-2 classic-sans">
                @foreach($cvData['references'] as $ref)
                    <div class="col-md-6">
                        <div class="classic-ref-box">
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $ref->full_name }}</div>
                            <div class="text-secondary small">{{ $ref->job_title }}{{ $ref->company ? ' at ' . $ref->company : '' }}</div>
                            @if($ref->email)
                                <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div>
                            @endif
                            @if($ref->phone)
                                <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $ref->phone }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
