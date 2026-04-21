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
            width: 100vw;
            height: 100vh;
            padding: 12mm 18mm;
            background: white;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        /* Decorative border */
        .certificate::before {
            content: '';
            position: absolute;
            top: 6mm;
            left: 6mm;
            right: 6mm;
            bottom: 6mm;
            border: 2px solid #c7a252;
            pointer-events: none;
        }

        /* Header */
        .header {
            text-align: center;
            flex-shrink: 0;
        }
        .org-name {
            font-size: 13px;
            letter-spacing: 3px;
            color: #5a4a2a;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .cert-title {
            font-size: 30px;
            font-weight: 700;
            color: #2c3e2f;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-family: 'Georgia', serif;
            margin-bottom: 4px;
        }
        .cert-sub {
            font-size: 11px;
            color: #7f8c8d;
            letter-spacing: 2px;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            padding-bottom: 4px;
        }

        /* Content – using gap for even spacing */
        .content {
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
        }
        .recipient {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e2f;
            font-family: 'Georgia', serif;
            border-bottom: 2px solid #c7a252;
            display: inline-block;
            padding: 0 25px 6px;
            margin: 0 auto;
        }
        .description {
            font-size: 11px;
            color: #4a5568;
            max-width: 80%;
            margin: 0 auto;
            line-height: 1.4;
        }
        .course {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e2f;
            background: #f5efe6;
            display: inline-block;
            padding: 5px 25px;
            margin: 0 auto;
            border-radius: 40px;
        }

        /* Footer – balanced signatures */
        .footer {
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
            width: 100%;
        }
        .signature {
            text-align: center;
            width: 140px;
        }
        .sign-line {
            width: 100%;
            border-top: 1.5px solid #2c3e2f;
            margin-bottom: 5px;
        }
        .sign-title {
            font-size: 10px;
            color: #5a4a2a;
            font-weight: 600;
        }
        .seal-area {
            text-align: center;
            flex-shrink: 0;
        }
        .seal {
            width: 55px;
            height: 55px;
            border: 2px solid #c7a252;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #c7a252;
            font-weight: bold;
            margin: 0 auto 5px;
            background: #fff9ef;
        }
        .date {
            font-size: 10px;
            color: #5a4a2a;
        }
        .cert-no {
            font-size: 9px;
            color: #4a5568;
            font-family: monospace;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <div class="org-name">{{ $config['organization'] ?? 'EPAS-E LEARNING MANAGEMENT SYSTEM' }}</div>
            <div class="cert-title">Certificate of Completion</div>
            <div class="cert-sub">Awarded to</div>
        </div>
        <div class="content">
            <div class="recipient">{{ $user->full_name }}</div>
            <div class="description">for successfully completing the prescribed course of study and demonstrating proficiency in</div>
            <div class="course">{{ $course->course_name }}</div>
            <div class="description">with all the rights and privileges pertaining thereto.</div>
        </div>
        <div class="footer">
            <div class="signature">
                <div class="sign-line"></div>
                <div class="sign-title">{{ $config['signatory_left_title'] ?? 'Program Director' }}</div>
            </div>
            <div class="seal-area">
                <div class="seal">OFFICIAL<br>SEAL</div>
                <div class="date">{{ $issue_date }}</div>
                <div class="cert-no">Certificate No: {{ $certificate_number }}</div>
            </div>
            <div class="signature">
                <div class="sign-line"></div>
                <div class="sign-title">{{ $config['signatory_right_title'] ?? 'Lead Instructor' }}</div>
            </div>
        </div>
    </div>
</body>
</html>