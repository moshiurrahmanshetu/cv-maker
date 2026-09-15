{{-- Template 6: Australia Compact --}}
<div class="template-australia-compact" style="
    font-family: {{ $cvData['fontFamily'] ?? "'Roboto', Helvetica, Arial, sans-serif" }};
    color: #1f2937;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.35' : ($cvData['lineSpacing'] === 'relaxed' ? '1.7' : '1.5') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.85rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.91rem') }};
    padding: 24px 28px;
">
    <style>
        .template-australia-compact .aus-header-band {
            background-color: {{ $cvData['accentColor'] ?? '#1b3b36' }};
            color: #ffffff;
            padding: 20px 24px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .template-australia-compact .aus-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.3rem' : '2.0rem') }};
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
            margin-bottom: 2px;
        }
        .template-australia-compact .aus-title {
            font-size: 1.05rem;
            font-weight: 500;
            color: #d1fae5;
            letter-spacing: 0.02em;
        }
        .template-australia-compact .aus-contact-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.82rem;
            color: #ecfdf5;
            margin-top: 10px;
        }
        .template-australia-compact .aus-heading {
            font-size: 0.95rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: {{ $cvData['accentColor'] ?? '#1b3b36' }};
            border-bottom: 2px solid {{ $cvData['accentColor'] ?? '#1b3b36' }};
            padding-bottom: 4px;
            margin-top: {{ $cvData['sectionSpacing'] === 'compact' ? '14px' : ($cvData['sectionSpacing'] === 'spacious' ? '24px' : '18px') }};
            margin-bottom: 12px;
        }
        .template-australia-compact .aus-entry-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
        }
        .template-australia-compact .aus-entry-org {
            font-weight: 600;
            font-size: 0.88rem;
            color: {{ $cvData['accentColor'] ?? '#1b3b36' }};
        }
        .template-australia-compact .aus-entry-date {
            font-size: 0.82rem;
            color: #6b7280;
            font-weight: 500;
        }
        .template-australia-compact .aus-skill-tag {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-block;
            margin-right: 4px;
            margin-bottom: 6px;
        }
        .template-australia-compact .aus-ref-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 3px solid {{ $cvData['accentColor'] ?? '#1b3b36' }};
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }
    </style>

    <!-- Australian Header Banner -->
    <div class="aus-header-band">
        <div>
            <h1 class="aus-name">{{ $cvData['fullName'] }}</h1>
            <div class="aus-title">{{ $cvData['jobTitle'] }}</div>
            <div class="aus-contact-strip">
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
        @if(!empty($cvData['photoUrl']) && $cvData['photoSize'] !== 'hidden')
            <img src="{{ $cvData['photoUrl'] }}" alt="{{ $cvData['fullName'] }}" class="rounded-circle shadow object-fit-cover border border-2 border-white" style="
                width: {{ $cvData['photoSize'] === 'small' ? '70px' : ($cvData['photoSize'] === 'large' ? '110px' : '90px') }};
                height: {{ $cvData['photoSize'] === 'small' ? '70px' : ($cvData['photoSize'] === 'large' ? '110px' : '90px') }};
            ">
        @endif
    </div>

    <!-- Executive Summary -->
    @if($cvData['hasSummary'])
        <div class="mb-3">
            <h2 class="aus-heading">Career Overview</h2>
            <div style="white-space: pre-line; color: #374151; font-size: 0.9rem;">{{ $cvData['summary'] }}</div>
        </div>
    @endif

    <!-- Key Skills -->
    @if($cvData['hasSkills'])
        <div class="mb-3">
            <h2 class="aus-heading">Key Competencies & Technical Skills</h2>
            <div>
                @foreach($cvData['skills'] as $skill)
                    <span class="aus-skill-tag">{{ $skill->name }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Employment History -->
    @if($cvData['hasExperiences'])
        <div class="mb-3">
            <h2 class="aus-heading">Employment History</h2>
            @foreach($cvData['experiences'] as $exp)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="aus-entry-title">{{ $exp->job_title }}</span>
                        <span class="aus-entry-date">{{ $exp->start_date }} – {{ $exp->is_current ? 'Current' : ($exp->end_date ?? 'Present') }}</span>
                    </div>
                    <div class="aus-entry-org mb-1">
                        {{ $exp->employer }}{{ (!empty($exp->city) || !empty($exp->country)) ? ' | ' . implode(', ', array_filter([$exp->city, $exp->country])) : '' }}
                    </div>
                    @if(!empty($exp->description))
                        <div style="font-size: 0.88rem; color: #374151; white-space: pre-line;">{{ $exp->description }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Education & Qualifications -->
    @if($cvData['hasEducations'])
        <div class="mb-3">
            <h2 class="aus-heading">Education & Qualifications</h2>
            @foreach($cvData['educations'] as $edu)
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="aus-entry-title">{{ $edu->degree }}{{ !empty($edu->field_of_study) ? ' in ' . $edu->field_of_study : '' }}</span>
                        <span class="aus-entry-date">{{ $edu->start_date }} – {{ $edu->is_current ? 'Present' : ($edu->end_date ?? 'Present') }}</span>
                    </div>
                    <div class="aus-entry-org">{{ $edu->institution }}{{ (!empty($edu->city) || !empty($edu->country)) ? ', ' . implode(', ', array_filter([$edu->city, $edu->country])) : '' }}</div>
                    @if(!empty($edu->description))
                        <div style="font-size: 0.86rem; color: #374151;">{{ $edu->description }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Referees -->
    @if($cvData['hasReferences'])
        <div class="mb-3">
            <h2 class="aus-heading">Professional Referees</h2>
            <div class="row g-2">
                @foreach($cvData['references'] as $ref)
                    <div class="col-md-6">
                        <div class="aus-ref-card">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $ref->full_name }}</div>
                            <div class="small text-muted">{{ $ref->job_title }} – {{ $ref->company }}</div>
                            <div class="small text-secondary mt-1">
                                @if(!empty($ref->email)) <div><i class="bi bi-envelope me-1"></i>{{ $ref->email }}</div> @endif
                                @if(!empty($ref->phone)) <div><i class="bi bi-telephone me-1"></i>{{ $ref->phone }}</div> @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
