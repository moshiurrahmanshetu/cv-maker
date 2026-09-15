{{-- Template 4: ATS Clean Standard --}}
<div class="template-ats-clean" style="
    font-family: {{ $cvData['fontFamily'] ?? 'Arial, Helvetica, sans-serif' }};
    color: #111827;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.7' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.92rem') }};
    padding: 24px 28px;
">
    <style>
        .template-ats-clean h1.ats-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.2rem' : '1.9rem') }};
            font-weight: 700;
            color: #111827;
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }
        .template-ats-clean .ats-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #374151;
            text-align: center;
            margin-bottom: 8px;
        }
        .template-ats-clean .ats-contact-line {
            text-align: center;
            font-size: 0.86rem;
            color: #4b5563;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #d1d5db;
        }
        .template-ats-clean .ats-heading {
            font-size: 1.05rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: {{ $cvData['accentColor'] ?? '#111827' }};
            border-bottom: 1px solid #9ca3af;
            padding-bottom: 3px;
            margin-top: {{ $cvData['sectionSpacing'] === 'compact' ? '14px' : ($cvData['sectionSpacing'] === 'spacious' ? '24px' : '18px') }};
            margin-bottom: 10px;
        }
        .template-ats-clean .ats-item-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-weight: 700;
            color: #111827;
            font-size: 0.95rem;
        }
        .template-ats-clean .ats-item-sub {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            color: #374151;
            font-size: 0.88rem;
            margin-bottom: 4px;
        }
        .template-ats-clean .ats-date {
            font-size: 0.85rem;
            color: #4b5563;
            font-weight: normal;
        }
        .template-ats-clean .ats-bullet-text {
            color: #374151;
            font-size: 0.88rem;
            white-space: pre-line;
            margin-bottom: 12px;
        }
    </style>

    <!-- Top ATS Header -->
    <div class="ats-header">
        <h1 class="ats-name">{{ $cvData['fullName'] }}</h1>
        <div class="ats-title">{{ $cvData['jobTitle'] }}</div>
        
        <div class="ats-contact-line">
            @php
                $contactItems = [];
                if (!empty($cvData['email'])) $contactItems[] = $cvData['email'];
                if (!empty($cvData['phone'])) $contactItems[] = $cvData['phone'];
                if (!empty($cvData['location'])) $contactItems[] = $cvData['location'];
                if (!empty($cvData['linkedin'])) $contactItems[] = $cvData['linkedin'];
                if (!empty($cvData['website'])) $contactItems[] = $cvData['website'];
            @endphp
            {{ implode(' | ', $contactItems) }}
        </div>
    </div>

    <!-- Summary -->
    @if($cvData['hasSummary'])
        <div class="ats-section">
            <h2 class="ats-heading">Professional Summary</h2>
            <div class="ats-bullet-text">{{ $cvData['summary'] }}</div>
        </div>
    @endif

    <!-- Experience -->
    @if($cvData['hasExperiences'])
        <div class="ats-section">
            <h2 class="ats-heading">Work Experience</h2>
            @foreach($cvData['experiences'] as $exp)
                <div class="ats-item">
                    <div class="ats-item-header">
                        <span>{{ $exp->job_title }}</span>
                        <span class="ats-date">
                            {{ $exp->start_date }} – {{ $exp->is_current ? 'Present' : ($exp->end_date ?? 'Present') }}
                        </span>
                    </div>
                    <div class="ats-item-sub">
                        <span>{{ $exp->employer }}{{ (!empty($exp->city) || !empty($exp->country)) ? ' – ' . implode(', ', array_filter([$exp->city, $exp->country])) : '' }}</span>
                    </div>
                    @if(!empty($exp->description))
                        <div class="ats-bullet-text">{{ $exp->description }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Education -->
    @if($cvData['hasEducations'])
        <div class="ats-section">
            <h2 class="ats-heading">Education</h2>
            @foreach($cvData['educations'] as $edu)
                <div class="ats-item">
                    <div class="ats-item-header">
                        <span>{{ $edu->degree }}{{ !empty($edu->field_of_study) ? ' in ' . $edu->field_of_study : '' }}</span>
                        <span class="ats-date">
                            {{ $edu->start_date }} – {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}
                        </span>
                    </div>
                    <div class="ats-item-sub">
                        <span>{{ $edu->institution }}{{ (!empty($edu->city) || !empty($edu->country)) ? ', ' . implode(', ', array_filter([$edu->city, $edu->country])) : '' }}</span>
                        @if(!empty($edu->grade_or_gpa))
                            <span>GPA: {{ $edu->grade_or_gpa }}</span>
                        @endif
                    </div>
                    @if(!empty($edu->description))
                        <div class="ats-bullet-text">{{ $edu->description }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Skills -->
    @if($cvData['hasSkills'])
        <div class="ats-section">
            <h2 class="ats-heading">Technical & Professional Skills</h2>
            <div class="ats-bullet-text">
                @foreach($cvData['skillsByCategory'] as $categoryName => $skills)
                    <strong>{{ $categoryName }}:</strong> {{ $skills->pluck('name')->implode(', ') }}<br>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Certifications -->
    @if($cvData['hasCertifications'])
        <div class="ats-section">
            <h2 class="ats-heading">Certifications</h2>
            @foreach($cvData['certifications'] as $cert)
                <div class="ats-item">
                    <div class="ats-item-header">
                        <span>{{ $cert->name }}</span>
                        @if(!empty($cert->issue_date))
                            <span class="ats-date">{{ $cert->issue_date }}</span>
                        @endif
                    </div>
                    <div class="ats-item-sub">
                        <span>{{ $cert->issuing_organization }}</span>
                        @if(!empty($cert->credential_id))
                            <span>ID: {{ $cert->credential_id }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Projects -->
    @if($cvData['hasProjects'])
        <div class="ats-section">
            <h2 class="ats-heading">Key Projects</h2>
            @foreach($cvData['projects'] as $proj)
                <div class="ats-item">
                    <div class="ats-item-header">
                        <span>{{ $proj->title }}{{ !empty($proj->role) ? ' (' . $proj->role . ')' : '' }}</span>
                        @if(!empty($proj->start_date))
                            <span class="ats-date">{{ $proj->start_date }} – {{ $proj->end_date ?? 'Present' }}</span>
                        @endif
                    </div>
                    @if(!empty($proj->technologies))
                        <div class="ats-item-sub">
                            <span><strong>Technologies:</strong> {{ $proj->technologies }}</span>
                        </div>
                    @endif
                    @if(!empty($proj->description))
                        <div class="ats-bullet-text">{{ $proj->description }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Languages -->
    @if($cvData['hasLanguages'])
        <div class="ats-section">
            <h2 class="ats-heading">Languages</h2>
            <div class="ats-bullet-text">
                {{ $cvData['languages']->map(fn($l) => "{$l->language} ({$l->proficiency})")->implode(', ') }}
            </div>
        </div>
    @endif

    <!-- Awards -->
    @if($cvData['hasAwards'])
        <div class="ats-section">
            <h2 class="ats-heading">Honors & Awards</h2>
            @foreach($cvData['awards'] as $award)
                <div class="ats-item">
                    <div class="ats-item-header">
                        <span>{{ $award->title }}</span>
                        @if(!empty($award->issue_date))
                            <span class="ats-date">{{ $award->issue_date }}</span>
                        @endif
                    </div>
                    <div class="ats-item-sub">
                        <span>{{ $award->issuer }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- References -->
    @if($cvData['hasReferences'])
        <div class="ats-section">
            <h2 class="ats-heading">References</h2>
            <div class="ats-bullet-text">
                @foreach($cvData['references'] as $ref)
                    <strong>{{ $ref->full_name }}</strong> – {{ $ref->job_title }} at {{ $ref->company }} ({{ $ref->email ?? $ref->phone }})<br>
                @endforeach
            </div>
        </div>
    @endif
</div>
