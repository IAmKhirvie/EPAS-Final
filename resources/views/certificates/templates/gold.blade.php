<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: white;
        }

        .certificate {
            width: 100%;
            height: 100%;
            padding: 15mm 20mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
            position: relative;
        }

        .header, .footer {
            flex-shrink: 0;
        }

        .content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Keep your decorative borders – but they must use absolute positioning */
        .certificate::before {
            content: '';
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 2px solid #c7a252;  /* change color to match your design */
            pointer-events: none;
        }

        /* All other existing styles (fonts, colors, etc.) can stay as they were */

        .ornament { position: absolute; font-size: 24px; color: #b45309; }
        .ornament-tl { top: 18px; left: 22px; }
        .ornament-tr { top: 18px; right: 22px; }
        .ornament-bl { bottom: 18px; left: 22px; }
        .ornament-br { bottom: 18px; right: 22px; }

        .header { text-align: center; margin-bottom: 12px; padding-top: 10px; }
        .logo { font-size: 14px; font-weight: bold; color: #92400e; margin-bottom: 6px; letter-spacing: 3px; text-transform: uppercase; }
        .title { font-size: 38px; color: #92400e; font-weight: bold; font-family: 'Times New Roman', serif; letter-spacing: 4px; margin-bottom: 4px; text-shadow: 1px 1px 2px rgba(180, 83, 9, 0.2); }
        .subtitle { font-size: 14px; color: #a16207; letter-spacing: 6px; text-transform: uppercase; }

        .content { text-align: center; margin: 15px 0; }
        .presented-to { font-size: 11px; color: #a16207; margin-bottom: 6px; font-style: italic; }
        .recipient-name { font-size: 34px; color: #78350f; font-family: 'Brush Script MT', cursive; display: inline-block; padding: 0 25px 4px; margin-bottom: 10px; border-bottom: 2px solid #d97706; }
        .description { font-size: 11px; color: #78350f; max-width: 420px; margin: 0 auto; line-height: 1.5; }
        .course-name { font-size: 20px; color: #92400e; font-weight: bold; margin: 12px 0; padding: 8px 25px; display: inline-block; border: 2px solid #d97706; background: rgba(255,255,255,0.5); border-radius: 3px; }

        .footer {
            position: absolute;
            bottom: 25px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-block { text-align: center; width: 140px; }
        .signature-line { border-top: 2px solid #b45309; padding-top: 6px; font-size: 10px; color: #78350f; font-weight: 600; }
        .center-block { text-align: center; }
        .seal { width: 70px; height: 70px; border: 4px solid #b45309; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #92400e; text-align: center; font-weight: bold; margin: 0 auto 6px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); box-shadow: 0 0 10px rgba(180, 83, 9, 0.3); }
        .date { font-size: 12px; color: #78350f; font-weight: 600; }
        .certificate-number { font-size: 8px; color: #a16207; margin-top: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="ornament ornament-tl">❧</div>
        <div class="ornament ornament-tr">❧</div>
        <div class="ornament ornament-bl">❧</div>
        <div class="ornament ornament-br">❧</div>
        <div class="header">
            <div class="logo">{{ $config['organization'] ?? 'EPAS-E Learning Management System' }}</div>
            <div class="title">Certificate</div>
            <div class="subtitle">of Excellence</div>
        </div>
        <div class="content">
            <div class="presented-to">This is to certify that</div>
            <div class="recipient-name">{{ $user->full_name }}</div>
            <div class="description">has demonstrated outstanding achievement and successfully completed all requirements in</div>
            <div class="course-name">{{ $course->course_name }}</div>
            <div class="description">with distinction and commitment to excellence.</div>
        </div>
        <div class="footer">
            <div class="signature-block">
                <div class="signature-line">{{ $config['signatory_left_title'] ?? 'Administrator' }}</div>
            </div>
            <div class="center-block">
                <div class="seal">★<br>EXCELLENCE<br>AWARD</div>
                <div class="date">{{ $issue_date }}</div>
                <div class="certificate-number">Certificate No: {{ $certificate_number }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line">{{ $config['signatory_right_title'] ?? 'Instructor' }}</div>
            </div>
        </div>
    </div>
</body>
</html>