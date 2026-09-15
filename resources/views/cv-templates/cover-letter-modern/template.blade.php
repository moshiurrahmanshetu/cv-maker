{{-- Template 8: Modern Minimalist Cover Letter --}}
<div class="template-cover-letter-modern" style="
    font-family: {{ $cvData['fontFamily'] ?? "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" }};
    color: #1f2937;
    background: #ffffff;
    line-height: {{ $cvData['lineSpacing'] === 'compact' ? '1.45' : ($cvData['lineSpacing'] === 'relaxed' ? '1.8' : '1.65') }};
    font-size: {{ $cvData['fontSizeScale'] === 'small' ? '0.86rem' : ($cvData['fontSizeScale'] === 'large' ? '1.02rem' : '0.94rem') }};
    padding: 32px 36px;
    min-height: 800px;
">
    <style>
        .template-cover-letter-modern .modern-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .template-cover-letter-modern .modern-sender-name {
            font-size: {{ $cvData['headingScale'] === 'compact' ? '1.6rem' : ($cvData['headingScale'] === 'large' ? '2.4rem' : '2.0rem') }};
            font-weight: 800;
            color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            letter-spacing: -0.02em;
            margin-bottom: 2px;
        }
        .template-cover-letter-modern .modern-sender-title {
            font-size: 1.0rem;
            font-weight: 600;
            color: #64748b;
        }
        .template-cover-letter-modern .modern-sender-contacts {
            text-align: right;
            font-size: 0.84rem;
            color: #475569;
            line-height: 1.5;
        }
        .template-cover-letter-modern .modern-recipient-card {
            background-color: #f8fafc;
            border-left: 3px solid {{ $cvData['accentColor'] ?? '#0f172a' }};
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 24px;
            font-size: 0.9rem;
        }
        .template-cover-letter-modern .modern-subject-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: {{ $cvData['accentColor'] ?? '#0f172a' }};
            font-weight: 700;
            font-size: 0.92rem;
            padding: 6px 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .template-cover-letter-modern .modern-paragraph {
            margin-bottom: 16px;
            color: #334155;
            white-space: pre-line;
        }
    </style>

    <!-- Modern Header -->
    <div class="modern-header">
        <div>
            <div class="modern-sender-name">{{ $cvData['fullName'] }}</div>
            @if(!empty($cvData['jobTitle']))
                <div class="modern-sender-title">{{ $cvData['jobTitle'] }}</div>
            @endif
        </div>
        <div class="modern-sender-contacts">
            @if(!empty($cvData['email']))
                <div>{{ $cvData['email'] }}</div>
            @endif
            @if(!empty($cvData['phone']))
                <div>{{ $cvData['phone'] }}</div>
            @endif
            @if(!empty($cvData['location']))
                <div>{{ $cvData['location'] }}</div>
            @endif
            @if(!empty($cvData['linkedin']))
                <div>{{ $cvData['linkedin'] }}</div>
            @endif
        </div>
    </div>

    <!-- Date & Recipient Details -->
    <div class="d-flex justify-content-between align-items-baseline mb-2">
        <div class="small fw-semibold text-muted">{{ $cvData['letterDate'] ?: date('F j, Y') }}</div>
    </div>

    <div class="modern-recipient-card">
        @if(!empty($cvData['recipientName']))
            <strong class="text-dark">{{ $cvData['recipientName'] }}</strong><br>
        @endif
        @if(!empty($cvData['recipientTitle']))
            <span class="text-secondary">{{ $cvData['recipientTitle'] }}</span><br>
        @endif
        @if(!empty($cvData['companyName']))
            <span class="fw-semibold">{{ $cvData['companyName'] }}</span><br>
        @endif
        @if(!empty($cvData['companyAddress']))
            <span class="text-muted">{!! nl2br(e($cvData['companyAddress'])) !!}</span>
        @endif
    </div>

    <!-- Subject -->
    @if(!empty($cvData['subject']))
        <div class="modern-subject-badge">
            <i class="bi bi-bookmark-fill me-1"></i> {{ $cvData['subject'] }}
        </div>
    @endif

    <!-- Salutation -->
    <div class="fw-bold text-dark mb-3">
        {{ $cvData['salutation'] ?: 'Dear Hiring Team,' }}
    </div>

    <!-- Opening -->
    @if(!empty($cvData['opening']))
        <div class="modern-paragraph">
            {{ $cvData['opening'] }}
        </div>
    @endif

    <!-- Main Body -->
    @if(!empty($cvData['body']))
        <div class="modern-paragraph">
            {{ $cvData['body'] }}
        </div>
    @endif

    <!-- Call to Action -->
    @if(!empty($cvData['callToAction']))
        <div class="modern-paragraph">
            {{ $cvData['callToAction'] }}
        </div>
    @endif

    <!-- Sign-Off -->
    <div class="mt-4 pt-2">
        <div class="text-muted">{{ $cvData['closing'] ?: 'Best regards,' }}</div>
        <div class="fw-bold text-dark fs-5 mt-4">{{ $cvData['senderSignature'] ?: $cvData['fullName'] }}</div>
    </div>
</div>
