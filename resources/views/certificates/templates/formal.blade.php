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

        .header { text-align: center; padding-top: 15px; margin-bottom: 10px; }
        .republic { font-size: 10px; color: #4a5568; letter-spacing: 2px; margin-bottom: 4px; }
        .dept { font-size: 12px; color: #2d3748; font-weight: bold; letter-spacing: 1px; margin-bottom: 2px; }
        .school { font-size: 16px; color: #1a365d; font-weight: bold; margin-bottom: 10px; }
        .title { font-size: 28px; color: #1a365d; font-weight: bold; text-transform: uppercase; letter-spacing: 4px; border-bottom: 2px solid #2c5282; border-top: 2px solid #2c5282; padding: 8px 0; margin: 0 80px; }

        .content { text-align: center; margin: 15px 0; }
        .presented-to { font-size: 11px; color: #4a5568; margin-bottom: 6px; font-style: italic; }
        .recipient-name { font-size: 28px; color: #1a365d; font-weight: bold; font-family: 'Brush Script MT', cursive; margin-bottom: 10px; }
        .description { font-size: 11px; color: #2d3748; max-width: 400px; margin: 0 auto 6px; line-height: 1.5; text-align: justify; text-align-last: center; }
        .course-name { font-size: 16px; color: #1a365d; font-weight: bold; margin: 10px 0; text-transform: uppercase; letter-spacing: 1px; }
        .given-text { font-size: 10px; color: #4a5568; margin-top: 12px; }

        .footer { position: absolute; bottom: 25px; left: 40px; right: 40px; }
        .signatures { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .signature-block { text-align: center; width: 150px; }
        .signature-name { font-size: 12px; color: #1a365d; font-weight: bold; border-top: 1px solid #1a365d; padding-top: 4px; }
        .signature-title { font-size: 9px; color: #4a5568; }
        .center-seal { text-align: center; }
        .seal-circle { width: 55px; height: 55px; border: 2px solid #1a365d; border-radius: 50%; margin: 0 auto 4px; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #1a365d; text-align: center; font-weight: bold; line-height: 1.2; }
        .meta-row { display: flex; justify-content: space-between; font-size: 8px; color: #718096; margin-top: 10px; padding-top: 8px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="inner-border"></div>
        <div class="inner-border-2"></div>
        <div class="header">
            <div class="republic">Republic of the Philippines</div>
            <div class="dept">Department of Education</div>
            <div class="school">{{ $config['organization'] ?? 'EPAS-E Learning Management System' }}</div>
            <div class="title">Certificate of Completion</div>
        </div>
        <div class="content">
            <div class="presented-to">This is to certify that</div>
            <div class="recipient-name">{{ $user->full_name }}</div>
            <div class="description">
                has satisfactorily completed the prescribed course requirements and has demonstrated the necessary competencies in
            </div>
            <div class="course-name">{{ $course->course_name }}</div>
            <div class="description">
                as offered by this institution in accordance with the standards set by the Department of Education.
            </div>
            <div class="given-text">Given this {{ $issue_date }} at {{ $config['location'] ?? 'Philippines' }}.</div>
        </div>
        <div class="footer">
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-name">{{ $config['signatory_left_name'] ?? '_______________' }}</div>
                    <div class="signature-title">{{ $config['signatory_left_title'] ?? 'School Administrator' }}</div>
                </div>
                <div class="center-seal">
                    <div class="seal-circle">OFFICIAL<br>SCHOOL<br>SEAL</div>
                </div>
                <div class="signature-block">
                    <div class="signature-name">{{ $config['signatory_right_name'] ?? '_______________' }}</div>
                    <div class="signature-title">{{ $config['signatory_right_title'] ?? 'Course Instructor' }}</div>
                </div>
            </div>
            <div class="meta-row">
                <span>Certificate No: {{ $certificate_number }}</span>
                <span>Verify at: {{ config('app.url') }}/verify</span>
            </div>
        </div>
    </div>
</body>
</html>