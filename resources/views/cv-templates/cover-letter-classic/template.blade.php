{{-- Template 7: Executive Cover Letter (Classic) --}}
<div class="template-cover-letter-classic" style="
    font-family: {{ $cvData['fontFamily'] ?? "'Georgia', 'Times New Roman', serif" }};
    color: #1f2937;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.5' : ($cvData['lineSpacing'] === 'relaxed' ? '1.85' : '1.7') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.88rem' : ($cvData['fontSizeScale'] === 'large' ? '1.05rem' : '0.96rem') }};
    padding: 32px 36px;
    min-height: 800px;
">
    <style>
        .template-cover-letter-classic .letter-head {
            border-bottom: 2px solid {{ $cvData['accentColor'] ?? '#1e293b' }};
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .template-cover-letter-classic .letter-sender-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.4rem' : '2.0rem') }};
            font-weight: 700;
            color: {{ $cvData['accentColor'] ?? '#1e293b' }};
            letter-spacing: -0.01em;
            margin-bottom: 4px;
        }
        .template-cover-letter-classic .letter-sender-title {
            font-size: 1.02rem;
            color: #4b5563;
            font-style: italic;
            margin-bottom: 8px;
        }
        .template-cover-letter-classic .letter-sender-meta {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.84rem;
            color: #6b7280;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .template-cover-letter-classic .letter-date-line {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.9rem;
            color: #374151;
            margin-bottom: 20px;
        }
        .template-cover-letter-classic .letter-recipient-block {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.92rem;
            color: #1f2937;
            line-height: 1.45;
            margin-bottom: 24px;
        }
        .template-cover-letter-classic .letter-subject {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            font-size: 0.98rem;
            color: {{ $cvData['accentColor'] ?? '#1e293b' }};
            margin-bottom: 20px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .template-cover-letter-classic .letter-salutation {
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }
        .template-cover-letter-classic .letter-body-paragraph {
            margin-bottom: 16px;
            text-align: justify;
            white-space: pre-line;
        }
        .template-cover-letter-classic .letter-closing-block {
            margin-top: 28px;
        }
        .template-cover-letter-classic .letter-signature-name {
            font-weight: 700;
            color: #111827;
            font-size: 1.05rem;
            margin-top: 36px;
        }
    </style>

    <!-- Formal Letterhead -->
    <div class="letter-head">
        <div class="letter-sender-name">{{ $cvData['fullName'] }}</div>
        @if(!empty($cvData['jobTitle']))
            <div class="letter-sender-title">{{ $cvData['jobTitle'] }}</div>
        @endif
        <div class="letter-sender-meta">
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

    <!-- Letter Date -->
    <div class="letter-date-line">
        {{ $cvData['letterDate'] ?: date('F j, Y') }}
    </div>

    <!-- Recipient Block -->
    <div class="letter-recipient-block">
        @if(!empty($cvData['recipientName']))
            <strong>{{ $cvData['recipientName'] }}</strong><br>
        @endif
        @if(!empty($cvData['recipientTitle']))
            <span>{{ $cvData['recipientTitle'] }}</span><br>
        @endif
        @if(!empty($cvData['companyName']))
            <span>{{ $cvData['companyName'] }}</span><br>
        @endif
        @if(!empty($cvData['companyAddress']))
            <span>{!! nl2br(e($cvData['companyAddress'])) !!}</span>
        @endif
    </div>

    <!-- Subject Line -->
    @if(!empty($cvData['subject']))
        <div class="letter-subject">
            RE: {{ $cvData['subject'] }}
        </div>
    @endif

    <!-- Salutation -->
    <div class="letter-salutation">
        {{ $cvData['salutation'] ?: 'Dear Hiring Manager,' }}
    </div>

    <!-- Opening Paragraph -->
    @if(!empty($cvData['opening']))
        <div class="letter-body-paragraph">
            {{ $cvData['opening'] }}
        </div>
    @endif

    <!-- Main Body Paragraphs -->
    @if(!empty($cvData['body']))
        <div class="letter-body-paragraph">
            {{ $cvData['body'] }}
        </div>
    @endif

    <!-- Call to Action / Closing statement -->
    @if(!empty($cvData['callToAction']))
        <div class="letter-body-paragraph">
            {{ $cvData['callToAction'] }}
        </div>
    @endif

    <!-- Formal Sign-Off & Signature -->
    <div class="letter-closing-block">
        <div>{{ $cvData['closing'] ?: 'Sincerely,' }}</div>
        <div class="letter-signature-name">{{ $cvData['senderSignature'] ?: $cvData['fullName'] }}</div>
    </div>
</div>
