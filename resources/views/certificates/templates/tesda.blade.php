<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion - TESDA NCII</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { margin: 0; padding: 0; }

        body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Georgia, serif;
            overflow: hidden;
        }

        .page {
            position: relative;
            width: 297mm;
            height: 210mm;
        }

        /* ── Borders ── */
        .border-outer {
            position: absolute;
            top: 5mm; left: 5mm; right: 5mm; bottom: 5mm;
            border: 3px solid #003366;
        }
        .border-inner {
            position: absolute;
            top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
            border: 1.5px solid #c8a951;
        }

        /* ── Corner accents ── */
        .c { position: absolute; width: 22px; height: 22px; border-color: #c8a951; border-style: solid; }
        .c-tl { top: 10mm; left: 10mm; border-width: 3px 0 0 3px; }
        .c-tr { top: 10mm; right: 10mm; border-width: 3px 3px 0 0; }
        .c-bl { bottom: 10mm; left: 10mm; border-width: 0 0 3px 3px; }
        .c-br { bottom: 10mm; right: 10mm; border-width: 0 3px 3px 0; }

        /* ── Header - absolute positioned at top ── */
        .header {
            position: absolute;
            top: 16mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
        }
        .republic {
            font-size: 9pt;
            letter-spacing: 2px;
            color: #003366;
            text-transform: uppercase;
        }
        .tesda-name {
            font-size: 12pt;
            font-weight: bold;
            color: #003366;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 1mm;
        }
        .tesda-sub {
            font-size: 7pt;
            color: #666;
            letter-spacing: 0.5px;
            margin-top: 1mm;
        }
        .gold-line {
            width: 160px;
            border: none;
            border-top: 2px solid #c8a951;
            margin: 5mm auto 3mm auto;
        }
        .cert-title {
            font-size: 22pt;
            font-weight: bold;
            color: #003366;
            text-transform: uppercase;
            letter-spacing: 5px;
        }
        .cert-type {
            font-size: 12pt;
            font-weight: bold;
            color: #c8a951;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 1mm;
        }

        /* ── Body - centered vertically ── */
        .body {
            position: absolute;
            top: 78mm;
            left: 40mm;
            right: 40mm;
            text-align: center;
        }
        .preamble {
            font-size: 10pt;
            color: #444;
        }
        .recipient-name {
            font-size: 24pt;
            font-weight: bold;
            color: #003366;
            padding-bottom: 2mm;
            border-bottom: 2px solid #c8a951;
            display: inline-block;
            margin-top: 3mm;
        }
        .qual-label {
            font-size: 10pt;
            color: #555;
            margin-top: 4mm;
        }
        .qual-box {
            font-size: 14pt;
            font-weight: bold;
            color: #003366;
            background: #f0e8d4;
            border: 1px solid #c8a951;
            padding: 3px 25px;
            display: inline-block;
            margin-top: 3mm;
        }
        .nc-level {
            font-size: 11pt;
            font-weight: bold;
            color: #c8a951;
            letter-spacing: 3px;
            margin-top: 2mm;
        }
        .institution {
            font-size: 9pt;
            color: #444;
            line-height: 1.5;
            margin-top: 3mm;
        }
        .institution strong {
            color: #003366;
        }

        /* ── Footer - absolute positioned at bottom ── */
        .footer {
            position: absolute;
            bottom: 14mm;
            left: 20mm;
            right: 20mm;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            border: none;
            padding: 0;
            vertical-align: bottom;
        }
        .sig-cell {
            width: 35%;
            text-align: center;
        }
        .seal-cell {
            width: 30%;
            text-align: center;
        }
        .sign-line {
            border-top: 1.5px solid #003366;
            width: 140px;
            margin: 0 auto 2px auto;
        }
        .sign-title {
            font-size: 7.5pt;
            color: #003366;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .seal-circle {
            width: 50px;
            height: 50px;
            border: 2px solid #003366;
            border-radius: 50%;
            margin: 0 auto 3mm auto;
            text-align: center;
            line-height: 50px;
            background: #f8f4eb;
        }
        .seal-circle-inner {
            display: inline-block;
            vertical-align: middle;
            line-height: 1.2;
            font-size: 5.5pt;
            color: #003366;
            font-weight: bold;
            text-transform: uppercase;
        }
        .issue-date {
            font-size: 9pt;
            color: #003366;
            font-weight: bold;
        }
        .cert-number {
            font-size: 7pt;
            color: #888;
            font-family: 'Courier New', monospace;
            margin-top: 1mm;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="border-outer"></div>
    <div class="border-inner"></div>
    <div class="c c-tl"></div>
    <div class="c c-tr"></div>
    <div class="c c-bl"></div>
    <div class="c c-br"></div>

    <!-- Header -->
    <div class="header">
        <div class="republic">Republic of the Philippines</div>
        <div class="tesda-name">Technical Education and Skills Development Authority</div>
        <div class="tesda-sub">Providing Direction, Policies, Programs and Standards towards Quality Technical Education and Skills Development</div>
        <hr class="gold-line">
        <div class="cert-title">Certificate of Completion</div>
        <div class="cert-type">National Certificate II</div>
    </div>

    <!-- Body -->
    <div class="body">
        <div class="preamble">This is to certify that</div>
        <div class="recipient-name">{{ $user->full_name }}</div>
        <div class="qual-label">has successfully completed the requirements for the qualification of</div>
        <div class="qual-box">{{ $course->course_name }}</div>
        <div class="nc-level">NC II</div>
        <div class="institution">
            as prescribed by the Technical Education and Skills Development Authority<br>
            Training Institution: <strong>{{ $config['institution'] ?? 'IETI College of Technology - Marikina' }}</strong>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="sig-cell">
                    <div class="sign-line"></div>
                    <div class="sign-title">{{ $config['signatory_left_title'] ?? 'School Administrator' }}</div>
                </td>
                <td class="seal-cell">
                    <div class="seal-circle">
                        <span class="seal-circle-inner">TESDA<br>OFFICIAL<br>SEAL</span>
                    </div>
                    <div class="issue-date">{{ $issue_date }}</div>
                    <div class="cert-number">{{ $certificate_number }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sign-line"></div>
                    <div class="sign-title">{{ $config['signatory_right_title'] ?? 'Lead Instructor / Trainer' }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
