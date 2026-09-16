<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $safeTitle ?? 'Career Document' }}</title>
    <style>
        @page {
            size: {{ config('pdf.paper_size', 'a4') }} {{ config('pdf.orientation', 'portrait') }};
            margin-top: {{ config('pdf.margins.top', 10) }}mm;
            margin-right: {{ config('pdf.margins.right', 12) }}mm;
            margin-bottom: {{ config('pdf.margins.bottom', 10) }}mm;
            margin-left: {{ config('pdf.margins.left', 12) }}mm;
        }

        /* Reset & Base Print Typography */
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            background-color: #ffffff;
            color: #1e293b;
            font-family: {{ $fontFamilyFallback ?? 'DejaVu Sans, Helvetica, Arial, sans-serif' }};
            font-size: 12px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Multi-page and Page Break Rules */
        .page-break-avoid, 
        .experience-entry, 
        .education-entry, 
        .project-entry, 
        .award-entry, 
        .ref-box, 
        .cert-entry,
        .classic-ref-box,
        .modern-skill-bar-container,
        .technical-skill-card {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        h1, h2, h3, h4, h5, h6,
        .section-heading,
        .classic-section-heading,
        .modern-section-heading,
        .ats-heading,
        .sidebar-section-title,
        .technical-heading,
        .australia-heading,
        .europass-heading,
        .letter-subject,
        .letter-salutation {
            page-break-after: avoid;
            break-after: avoid;
            page-break-inside: avoid;
        }

        /* DomPDF Grid & 2-Column Layout Engine */
        .row {
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        .row::after {
            content: "";
            display: table;
            clear: both;
        }
        .col-md-3 { width: 25%; float: left; }
        .col-md-4 { width: 33.333333%; float: left; }
        .col-md-5 { width: 41.666667%; float: left; }
        .col-md-6 { width: 50%; float: left; }
        .col-md-7 { width: 58.333333%; float: left; }
        .col-md-8 { width: 66.666667%; float: left; }
        .col-md-9 { width: 75%; float: left; }
        .col-12, .col-md-12 { width: 100%; float: left; }
        
        .g-0 > [class*="col-"] { padding-left: 0; padding-right: 0; }
        .g-3 > [class*="col-"] { padding: 4px; }
        .g-4 > [class*="col-"] { padding: 6px; }

        /* Typography & Utilities */
        .text-center { text-align: center; }
        .text-end, .text-right { text-align: right; }
        .text-start, .text-left { text-align: left; }
        .text-uppercase { text-transform: uppercase; }
        .fw-bold { font-weight: bold; }
        .fw-semibold { font-weight: 600; }
        .fst-italic { font-style: italic; }
        
        .text-muted, .text-secondary { color: #64748b !important; }
        .text-dark { color: #0f172a !important; }
        .text-primary { color: {{ $cvData['accentColor'] ?? '#0f172a' }} !important; }

        /* Borders & Radii */
        .border { border: 1px solid #e2e8f0; }
        .border-top { border-top: 1px solid #e2e8f0; }
        .border-bottom { border-bottom: 1px solid #e2e8f0; }
        .border-start { border-left: 1px solid #e2e8f0; }
        .border-end { border-right: 1px solid #e2e8f0; }
        .rounded-circle { border-radius: 50%; }
        .rounded-2 { border-radius: 4px; }
        .rounded-3 { border-radius: 6px; }

        /* Spacing Helpers */
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-3 { padding-top: 12px; padding-bottom: 12px; }
        .px-2 { padding-left: 8px; padding-right: 8px; }
        .px-3 { padding-left: 12px; padding-right: 12px; }
        .p-3 { padding: 12px; }
        .p-4 { padding: 16px; }

        /* Badges & Tags */
        .badge, .badge-saas-published {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 600;
            border-radius: 3px;
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        /* Icon Handling for Print */
        @if(empty($cvData['showIcons']))
            i.bi { display: none !important; }
        @else
            i.bi {
                display: inline-block;
                font-style: normal;
                margin-right: 2px;
            }
        @endif

        /* Hide browser-only interactive UI */
        .no-print, .btn, .btn-saas-primary, .btn-saas-secondary, .dropdown, button, .modal {
            display: none !important;
        }

        /* Safe Table Formatting */
        table {
            border-collapse: collapse;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="pdf-document-container">
        @include($templateView, ['cvData' => $cvData, 'cv' => $cv])
    </div>
</body>
</html>
