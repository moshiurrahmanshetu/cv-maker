{{-- Template 9: Academic Motivation Letter --}}
<div class="template-motivation-academic" style="
    font-family: {{ $cvData['fontFamily'] ?? "'Merriweather', 'Georgia', serif" }};
    color: #1e293b;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.5' : ($cvData['lineSpacing'] === 'relaxed' ? '1.9' : '1.75') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.86rem' : ($cvData['fontSizeScale'] === 'large' ? '1.04rem' : '0.95rem') }};
    padding: 36px 40px;
    min-height: 800px;
">
    <style>
        .template-motivation-academic .acad-top-rule {
            height: 4px;
            background-color: {{ $cvData['accentColor'] ?? '#8c1d40' }};
            margin-bottom: 24px;
        }
        .template-motivation-academic .acad-sender-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.5rem' : ($cvData['headingScale'] === 'large' ? '2.2rem' : '1.85rem') }};
            font-weight: 700;
            color: {{ $cvData['accentColor'] ?? '#8c1d40' }};
            margin-bottom: 2px;
        }
        .template-motivation-academic .acad-sender-meta {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.84rem;
            color: #64748b;
            margin-bottom: 24px;
        }
        .template-motivation-academic .acad-institution-box {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.9rem;
            color: #334155;
            margin-bottom: 24px;
            padding-left: 14px;
            border-left: 2px solid {{ $cvData['accentColor'] ?? '#8c1d40' }};
        }
        .template-motivation-academic .acad-subject {
            font-weight: 700;
            font-size: 1.02rem;
            color: {{ $cvData['accentColor'] ?? '#8c1d40' }};
            text-align: center;
            margin: 24px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .template-motivation-academic .acad-paragraph {
            margin-bottom: 18px;
            text-align: justify;
            white-space: pre-line;
            color: #334155;
        }
    </style>

    <div class="acad-top-rule"></div>

    <!-- Sender Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="acad-sender-name">{{ $cvData['fullName'] }}</div>
            <div class="acad-sender-meta">
                @if(!empty($cvData['email'])) <span>{{ $cvData['email'] }}</span> &bull; @endif
                @if(!empty($cvData['phone'])) <span>{{ $cvData['phone'] }}</span> &bull; @endif
                @if(!empty($cvData['location'])) <span>{{ $cvData['location'] }}</span> @endif
            </div>
        </div>
        <div class="small text-muted font-monospace">{{ $cvData['letterDate'] ?: date('F j, Y') }}</div>
    </div>

    <!-- Target Institution / Committee -->
    <div class="acad-institution-box">
        @if(!empty($cvData['recipientName']))
            <strong>{{ $cvData['recipientName'] }}</strong><br>
        @endif
        @if(!empty($cvData['recipientTitle']))
            <span>{{ $cvData['recipientTitle'] }}</span><br>
        @endif
        @if(!empty($cvData['companyName']))
            <span><strong>{{ $cvData['companyName'] }}</strong></span><br>
        @endif
        @if(!empty($cvData['companyAddress']))
            <span>{!! nl2br(e($cvData['companyAddress'])) !!}</span>
        @endif
    </div>

    <!-- Subject / Statement of Purpose -->
    @if(!empty($cvData['subject']))
        <div class="acad-subject">
            {{ $cvData['subject'] }}
        </div>
    @endif

    <!-- Salutation -->
    <div class="fw-bold text-dark mb-3">
        {{ $cvData['salutation'] ?: 'Dear Members of the Admissions Committee,' }}
    </div>

    <!-- Motivation Opening -->
    @if(!empty($cvData['opening']))
        <div class="acad-paragraph">
            {{ $cvData['opening'] }}
        </div>
    @endif

    <!-- Core Motivation & Background -->
    @if(!empty($cvData['body']))
        <div class="acad-paragraph">
            {{ $cvData['body'] }}
        </div>
    @endif

    <!-- Academic Goals & Value Proposition -->
    @if(!empty($cvData['callToAction']))
        <div class="acad-paragraph">
            {{ $cvData['callToAction'] }}
        </div>
    @endif

    <!-- Formal Sign-Off -->
    <div class="mt-4 pt-3">
        <div>{{ $cvData['closing'] ?: 'Respectfully submitted,' }}</div>
        <div class="fw-bold text-dark fs-5 mt-4">{{ $cvData['senderSignature'] ?: $cvData['fullName'] }}</div>
    </div>
</div>
