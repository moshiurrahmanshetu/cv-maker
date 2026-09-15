{{-- Template 2: Modern Minimal (2-Column Sidebar) --}}
<div class="template-modern-minimal" style="
    font-family: {{ $cvData['fontFamily'] ?? '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif' }};
    color: #1e293b;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.75' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.9rem') }};
">
    <style>
        .template-modern-minimal .modern-sidebar {
            background-color: #f8fafc;
            border-right: 1px solid #e2e8f0;
            padding: 24px 20px;
        }
        .template-modern-minimal .modern-main {
            padding: 24px 24px;
        }
        .template-modern-minimal .modern-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.7rem' : ($cvData['headingScale'] === 'large' ? '2.5rem' : '2.2rem') }};
            font-weight: 800;
            color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .template-modern-minimal .modern-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.02em;
        }

        .template-modern-minimal .modern-section-heading {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            margin-top: 20px;
        }
        .template-modern-minimal .modern-section-heading:first-child {
            margin-top: 0;
        }
        .template-modern-minimal .modern-section-heading::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #e2e8f0;
        }
        .template-modern-minimal .sidebar-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 10px;
            margin-top: 18px;
        }
        .template-modern-minimal .sidebar-section-title:first-child {
            margin-top: 0;
        }
        .template-modern-minimal .modern-exp-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }
        .template-modern-minimal .modern-exp-company {
            font-size: 0.88rem;
            font-weight: 600;
            color: #475569;
        }
        .template-modern-minimal .modern-date-badge {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }
        .template-modern-minimal .modern-skill-bar-container {
            margin-bottom: 8px;
        }
        .template-modern-minimal .modern-skill-bar-bg {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 3px;
        }
        .template-modern-minimal .modern-skill-bar-fill {
            height: 100%;
            background: #334155;
            border-radius: 3px;
        }
        .template-modern-minimal .modern-contact-item {
            font-size: 0.82rem;
            color: #475569;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
            word-break: break-word;
        }
    </style>

    <div class="row g-0">
        <!-- Left Sidebar (35% width) -->
        <div class="col-md-4 modern-sidebar">
            <!-- Profile Photo -->
            @if(!empty($cvData['photoUrl']))
                <div class="text-center mb-4">
                    <img src="{{ $cvData['photoUrl'] }}" alt="{{ $cvData['fullName'] }}" class="rounded-circle border shadow-sm object-fit-cover" style="width: 105px; height: 105px; border-color: #cbd5e1 !important;">
                </div>
            @endif

            <!-- Contact Information -->
            <div class="sidebar-section-title">Contact</div>
            <div class="mb-3">
                @if(!empty($cvData['email']))
                    <div class="modern-contact-item">
                        <i class="bi bi-envelope text-secondary"></i>
                        <span>{{ $cvData['email'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['phone']))
                    <div class="modern-contact-item">
                        <i class="bi bi-telephone text-secondary"></i>
                        <span>{{ $cvData['phone'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['location']))
                    <div class="modern-contact-item">
                        <i class="bi bi-geo-alt text-secondary"></i>
                        <span>{{ $cvData['location'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['website']))
                    <div class="modern-contact-item">
                        <i class="bi bi-globe text-secondary"></i>
                        <span>{{ $cvData['website'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['linkedin']))
                    <div class="modern-contact-item">
                        <i class="bi bi-linkedin text-secondary"></i>
                        <span>{{ $cvData['linkedin'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['github']))
                    <div class="modern-contact-item">
                        <i class="bi bi-github text-secondary"></i>
                        <span>{{ $cvData['github'] }}</span>
                    </div>
                @endif
                @if(!empty($cvData['otherUrl']))
                    <div class="modern-contact-item">
                        <i class="bi bi-link-45deg text-secondary"></i>
                        <span>{{ $cvData['otherUrl'] }}</span>
                    </div>
                @endif
            </div>

            <!-- Skills with Rating / Progress Indicators -->
            @if($cvData['hasSkills'])
                <div class="sidebar-section-title">Skills & Competencies</div>
                <div class="mb-3">
                    @foreach($cvData['skills'] as $skill)
                        <div class="modern-skill-bar-container">
                            <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                <span class="fw-semibold text-dark">{{ $skill->name }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $skill->level ?? ($skill->rating ? $skill->rating . '%' : '') }}</span>
                            </div>
                            <div class="modern-skill-bar-bg">
                                <div class="modern-skill-bar-fill" style="width: {{ $skill->rating ?? ($skill->level === 'Expert' ? 95 : ($skill->level === 'Advanced' ? 80 : 65)) }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Languages -->
            @if($cvData['hasLanguages'])
                <div class="sidebar-section-title">Languages</div>
                <div class="mb-3">
                    @foreach($cvData['languages'] as $lang)
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.82rem;">
                            <span class="fw-medium text-dark">{{ $lang->language }}</span>
                            <span class="text-muted small">{{ $lang->proficiency }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Certifications in Sidebar -->
            @if($cvData['hasCertifications'])
                <div class="sidebar-section-title">Certifications</div>
                <div class="mb-3">
                    @foreach($cvData['certifications'] as $cert)
                        <div class="mb-2" style="font-size: 0.82rem;">
                            <div class="fw-semibold text-dark">{{ $cert->name }}</div>
                            <div class="text-muted small">{{ $cert->issuing_organization }}{{ $cert->issue_date ? ' (' . $cert->issue_date . ')' : '' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- References in Sidebar -->
            @if($cvData['hasReferences'])
                <div class="sidebar-section-title">References</div>
                <div class="mb-2">
                    @foreach($cvData['references'] as $ref)
                        <div class="mb-2 p-2 bg-white border rounded" style="font-size: 0.8rem;">
                            <div class="fw-bold text-dark">{{ $ref->full_name }}</div>
                            <div class="text-secondary small">{{ $ref->job_title }}{{ $ref->company ? ' &bull; ' . $ref->company : '' }}</div>
                            @if($ref->email)
                                <div class="text-muted small mt-1"><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Main Column (65% width) -->
        <div class="col-md-8 modern-main">
            <!-- Header -->
            <div class="mb-3 pb-3 border-bottom">
                <h1 class="modern-name mb-1">{{ $cvData['fullName'] }}</h1>
                <div class="modern-title">{{ $cvData['jobTitle'] }}</div>
            </div>

            <!-- Summary -->
            @if($cvData['hasSummary'])
                <div class="mb-3">
                    <div class="modern-section-heading">Professional Profile</div>
                    <p class="text-secondary mb-0" style="line-height: 1.6; font-size: 0.9rem; white-space: pre-line;">
                        {{ $cvData['summary'] }}
                    </p>
                </div>
            @endif

            <!-- Work Experience -->
            @if($cvData['hasExperiences'])
                <div class="mb-3">
                    <div class="modern-section-heading">Work Experience</div>
                    <div class="d-flex flex-column gap-3">
                        @foreach($cvData['experiences'] as $exp)
                            <div>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="modern-exp-title">{{ $exp->job_title }}</span>
                                    <span class="modern-date-badge">
                                        {{ $exp->start_date }} &ndash; {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                                    </span>
                                </div>
                                <div class="modern-exp-company mb-1">
                                    {{ $exp->employer }}{{ $exp->city ? ' &bull; ' . $exp->city : '' }}{{ $exp->country ? ', ' . $exp->country : '' }}
                                </div>
                                @if($exp->description)
                                    <p class="text-secondary mb-0 small" style="line-height: 1.5; white-space: pre-line;">
                                        {{ $exp->description }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Education -->
            @if($cvData['hasEducations'])
                <div class="mb-3">
                    <div class="modern-section-heading">Education</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['educations'] as $edu)
                            <div>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="modern-exp-title">{{ $edu->degree }}</span>
                                    <span class="modern-date-badge">
                                        {{ $edu->start_date }} &ndash; {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                                    </span>
                                </div>
                                <div class="modern-exp-company">
                                    {{ $edu->institution }}{{ $edu->field_of_study ? ' &bull; ' . $edu->field_of_study : '' }}{{ $edu->city ? ' (' . $edu->city . ')' : '' }}
                                </div>
                                @if($edu->grade_or_gpa)
                                    <div class="small text-muted">{{ $edu->grade_or_gpa }}</div>
                                @endif
                                @if($edu->description)
                                    <p class="text-secondary mb-0 small mt-1" style="line-height: 1.5; white-space: pre-line;">{{ $edu->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Key Projects -->
            @if($cvData['hasProjects'])
                <div class="mb-3">
                    <div class="modern-section-heading">Key Projects</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['projects'] as $proj)
                            <div class="p-2 border rounded bg-white">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $proj->title }}</span>
                                    @if($proj->start_date)
                                        <span class="modern-date-badge">{{ $proj->start_date }} {{ $proj->end_date ? '&ndash; ' . $proj->end_date : '' }}</span>
                                    @endif
                                </div>
                                @if($proj->role)
                                    <div class="small fw-semibold text-secondary">{{ $proj->role }}</div>
                                @endif
                                @if($proj->technologies)
                                    <div class="small text-muted mb-1"><strong>Stack:</strong> {{ $proj->technologies }}</div>
                                @endif
                                @if($proj->description)
                                    <p class="text-secondary small mb-1" style="white-space: pre-line;">{{ $proj->description }}</p>
                                @endif
                                @if($proj->project_url)
                                    <a href="{{ $proj->project_url }}" target="_blank" class="small text-decoration-none text-muted">
                                        <i class="bi bi-link-45deg"></i> {{ $proj->project_url }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Awards -->
            @if($cvData['hasAwards'])
                <div class="mb-3">
                    <div class="modern-section-heading">Awards & Achievements</div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($cvData['awards'] as $award)
                            <div>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $award->title }}</span>
                                    @if($award->issue_date)
                                        <span class="modern-date-badge">{{ $award->issue_date }}</span>
                                    @endif
                                </div>
                                @if($award->issuer)
                                    <div class="small text-secondary">{{ $award->issuer }}</div>
                                @endif
                                @if($award->description)
                                    <p class="small text-secondary mb-0 mt-1">{{ $award->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Custom Sections -->
            @if($cvData['hasCustomSections'])
                @foreach($cvData['customSectionsGrouped'] as $sectionTitle => $entries)
                    <div class="mb-3">
                        <div class="modern-section-heading">{{ $sectionTitle }}</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach($entries as $entry)
                                <div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        @if($entry->title)
                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $entry->title }}</span>
                                        @endif
                                        @if($entry->date_period)
                                            <span class="modern-date-badge">{{ $entry->date_period }}</span>
                                        @endif
                                    </div>
                                    @if($entry->subtitle)
                                        <div class="small text-secondary">{{ $entry->subtitle }}</div>
                                    @endif
                                    @if($entry->content)
                                        <p class="small text-secondary mb-0 mt-1" style="white-space: pre-line;">{{ $entry->content }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
