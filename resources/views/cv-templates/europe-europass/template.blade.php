{{-- Template 5: Europe Europass Style --}}
<div class="template-europe-europass" style="
    font-family: {{ $cvData['fontFamily'] ?? "'Segoe UI', Roboto, Helvetica, Arial, sans-serif" }};
    color: #1f2937;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.7' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.9rem') }};
    padding: 24px 28px;
">
    <style>
        .template-europe-europass .europass-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 18px;
            border-bottom: 2px solid {{ $cvData['accentColor'] ?? '#0e4194' }};
            margin-bottom: 20px;
        }
        .template-europe-europass .europass-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.3rem' : '2.0rem') }};
            font-weight: 700;
            color: {{ $cvData['accentColor'] ?? '#0e4194' }};
            margin-bottom: 2px;
            letter-spacing: -0.01em;
        }
        .template-europe-europass .europass-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #4b5563;
        }
        .template-europe-europass .europass-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 16px 24px;
            margin-bottom: {{ $cvData['sectionSpacing'] === 'compact' ? '14px' : ($cvData['sectionSpacing'] === 'spacious' ? '24px' : '18px') }};
        }
        .template-europe-europass .europass-label-col {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: {{ $cvData['accentColor'] ?? '#0e4194' }};
            border-right: 2px solid #e5e7eb;
            padding-right: 12px;
            text-align: right;
        }
        .template-europe-europass .europass-content-col {
            padding-left: 4px;
        }
        .template-europe-europass .europass-item {
            margin-bottom: 14px;
        }
        .template-europe-europass .europass-item-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
        }
        .template-europe-europass .europass-item-sub {
            font-size: 0.88rem;
            color: #4b5563;
            margin-bottom: 4px;
        }
        .template-europe-europass .europass-date-badge {
            display: inline-block;
            background-color: #f3f4f6;
            color: #374151;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
        }
        .template-europe-europass .europass-skill-pill {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-right: 4px;
            margin-bottom: 6px;
        }
        .template-europe-europass .europass-lang-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            margin-right: 8px;
            margin-bottom: 6px;
        }
    </style>

    <!-- Europass Header -->
    <div class="europass-header">
        @if(!empty($cvData['photoUrl']) && $cvData['photoSize'] !== 'hidden')
            <div>
                <img src="{{ $cvData['photoUrl'] }}" alt="{{ $cvData['fullName'] }}" class="rounded shadow-sm object-fit-cover" style="
                    width: {{ $cvData['photoSize'] === 'small' ? '70px' : ($cvData['photoSize'] === 'large' ? '110px' : '90px') }};
                    height: {{ $cvData['photoSize'] === 'small' ? '70px' : ($cvData['photoSize'] === 'large' ? '110px' : '90px') }};
                    border: 2px solid {{ $cvData['accentColor'] ?? '#0e4194' }};
                ">
            </div>
        @endif
        <div class="flex-grow-1">
            <h1 class="europass-name">{{ $cvData['fullName'] }}</h1>
            <div class="europass-title">{{ $cvData['jobTitle'] }}</div>
            <div class="d-flex flex-wrap gap-3 mt-2 text-muted" style="font-size: 0.84rem;">
                @if(!empty($cvData['email']))
                    <span><i class="bi bi-envelope me-1"></i>{{ $cvData['email'] }}</span>
                @endif
                @if(!empty($cvData['phone']))
                    <span><i class="bi bi-telephone me-1"></i>{{ $cvData['phone'] }}</span>
                @endif
                @if(!empty($cvData['location']))
                    <span><i class="bi bi-geo-alt me-1"></i>{{ $cvData['location'] }}</span>
                @endif
                @if(!empty($cvData['linkedin']))
                    <span><i class="bi bi-linkedin me-1"></i>{{ $cvData['linkedin'] }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary -->
    @if($cvData['hasSummary'])
        <div class="europass-grid">
            <div class="europass-label-col">Profile Summary</div>
            <div class="europass-content-col" style="white-space: pre-line;">{{ $cvData['summary'] }}</div>
        </div>
    @endif

    <!-- Work Experience -->
    @if($cvData['hasExperiences'])
        <div class="europass-grid">
            <div class="europass-label-col">Work Experience</div>
            <div class="europass-content-col">
                @foreach($cvData['experiences'] as $exp)
                    <div class="europass-item">
                        <span class="europass-date-badge">
                            {{ $exp->start_date }} – {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                        </span>
                        <div class="europass-item-title">{{ $exp->job_title }}</div>
                        <div class="europass-item-sub">
                            {{ $exp->employer }}{{ (!empty($exp->city) || !empty($exp->country)) ? ', ' . implode(', ', array_filter([$exp->city, $exp->country])) : '' }}
                        </div>
                        @if(!empty($exp->description))
                            <div style="font-size: 0.86rem; color: #374151; white-space: pre-line;">{{ $exp->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Education -->
    @if($cvData['hasEducations'])
        <div class="europass-grid">
            <div class="europass-label-col">Education & Training</div>
            <div class="europass-content-col">
                @foreach($cvData['educations'] as $edu)
                    <div class="europass-item">
                        <span class="europass-date-badge">
                            {{ $edu->start_date }} – {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                        </span>
                        <div class="europass-item-title">{{ $edu->degree }}{{ !empty($edu->field_of_study) ? ' in ' . $edu->field_of_study : '' }}</div>
                        <div class="europass-item-sub">
                            {{ $edu->institution }}{{ (!empty($edu->city) || !empty($edu->country)) ? ', ' . implode(', ', array_filter([$edu->city, $edu->country])) : '' }}
                            @if(!empty($edu->grade_or_gpa))
                                &bull; Grade: {{ $edu->grade_or_gpa }}
                            @endif
                        </div>
                        @if(!empty($edu->description))
                            <div style="font-size: 0.86rem; color: #374151; white-space: pre-line;">{{ $edu->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Skills -->
    @if($cvData['hasSkills'])
        <div class="europass-grid">
            <div class="europass-label-col">Personal Skills</div>
            <div class="europass-content-col">
                @foreach($cvData['skillsByCategory'] as $categoryName => $skills)
                    <div class="mb-2">
                        <strong class="d-block small text-muted mb-1">{{ $categoryName }}</strong>
                        @foreach($skills as $skill)
                            <span class="europass-skill-pill">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Languages (European CEFR levels style) -->
    @if($cvData['hasLanguages'])
        <div class="europass-grid">
            <div class="europass-label-col">Languages</div>
            <div class="europass-content-col">
                @foreach($cvData['languages'] as $lang)
                    <div class="europass-lang-badge">
                        <span class="fw-bold">{{ $lang->language }}</span>
                        <span class="badge bg-secondary text-white">{{ $lang->proficiency }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Projects / Publications -->
    @if($cvData['hasProjects'])
        <div class="europass-grid">
            <div class="europass-label-col">Projects</div>
            <div class="europass-content-col">
                @foreach($cvData['projects'] as $proj)
                    <div class="europass-item">
                        <div class="europass-item-title">{{ $proj->title }}{{ !empty($proj->role) ? ' (' . $proj->role . ')' : '' }}</div>
                        @if(!empty($proj->technologies))
                            <div class="small text-muted mb-1">{{ $proj->technologies }}</div>
                        @endif
                        @if(!empty($proj->description))
                            <div style="font-size: 0.86rem; color: #374151; white-space: pre-line;">{{ $proj->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Certifications -->
    @if($cvData['hasCertifications'])
        <div class="europass-grid">
            <div class="europass-label-col">Certifications</div>
            <div class="europass-content-col">
                @foreach($cvData['certifications'] as $cert)
                    <div class="europass-item">
                        <div class="europass-item-title">{{ $cert->name }}</div>
                        <div class="small text-muted">{{ $cert->issuing_organization }}{{ !empty($cert->issue_date) ? ' &bull; ' . $cert->issue_date : '' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
