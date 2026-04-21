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

        .accent-bar { position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%); }
        .accent-bar-bottom { position: absolute; bottom: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #a855f7 0%, #8b5cf6 50%, #6366f1 100%); }

        .header { margin-bottom: 20px; }
        .logo { font-size: 10px; font-weight: 600; color: #6366f1; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 20px; }
        .title { font-size: 44px; color: #1f2937; font-weight: 300; letter-spacing: -1px; line-height: 1.1; }
        .title span { font-weight: 700; background: linear-gradient(90deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .content { margin: 20px 0; }
        .recipient-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .recipient-name { font-size: 32px; color: #1f2937; font-weight: 600; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 3px solid #6366f1; display: inline-block; }
        .description { font-size: 12px; color: #6b7280; line-height: 1.6; max-width: 450px; }
        .course-block { margin: 20px 0; padding: 15px 25px; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-radius: 8px; display: inline-block; }
        .course-label { font-size: 8px; color: #7c3aed; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .course-name { font-size: 18px; color: #4c1d95; font-weight: 600; }

        .footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-block { }
        .signature-line { width: 120px; border-top: 2px solid #d1d5db; padding-top: 8px; }
        .signature-title { font-size: 9px; color: #6b7280; font-weight: 600; }
        .meta-block { text-align: right; }
        .date { font-size: 11px; color: #374151; font-weight: 500; }
        .certificate-number { font-size: 8px; color: #9ca3af; margin-top: 4px; font-family: monospace; }
        .qr-placeholder { width: 50px; height: 50px; border: 1px solid #e5e7eb; border-radius: 4px; margin-left: auto; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="accent-bar"></div>
        <div class="accent-bar-bottom"></div>
        <div class="header">
            <div class="logo">{{ $config['organization'] ?? 'EPAS-E Learning System' }}</div>
            <div class="title">Certificate of<br><span>Completion</span></div>
        </div>
        <div class="content">
            <div class="recipient-label">Awarded to</div>
            <div class="recipient-name">{{ $user->full_name }}</div>
            <div class="description">For successfully completing all modules and demonstrating proficiency in the following program:</div>
            <div class="course-block">
                <div class="course-label">Program</div>
                <div class="course-name">{{ $course->course_name }}</div>
            </div>
        </div>
        <div class="footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-title">{{ $config['signatory_left_title'] ?? 'Program Director' }}</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-title">{{ $config['signatory_right_title'] ?? 'Instructor' }}</div>
            </div>
            <div class="meta-block">
                <div class="qr-placeholder">QR</div>
                <div class="date">{{ $issue_date }}</div>
                <div class="certificate-number">{{ $certificate_number }}</div>
            </div>
        </div>
    </div>
</body>
</html>