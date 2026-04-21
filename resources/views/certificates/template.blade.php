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

        html,
        body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .certificate {
            width: 100%;
            height: 100%;
            padding: 20px 30px;
            border: 12px solid #0c3a2d;
            position: relative;
            background: #fff;
            box-sizing: border-box;
        }

        .certificate::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            border: 2px solid #6d9773;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-top: 5px;
        }

        .logo {
            font-size: 18px;
            font-weight: bold;
            color: #0c3a2d;
            margin-bottom: 5px;
        }

        .title {
            font-size: 32px;
            color: #0c3a2d;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 15px;
        }

        .content {
            text-align: center;
            margin: 15px 0;
        }

        .presented-to {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .recipient-name {
            font-size: 30px;
            color: #0c3a2d;
            font-style: italic;
            border-bottom: 2px solid #ffb902;
            display: inline-block;
            padding: 0 30px 8px;
            margin-bottom: 12px;
        }

        .description {
            font-size: 12px;
            color: #475569;
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .course-name {
            font-size: 20px;
            color: #0c3a2d;
            font-weight: bold;
            margin: 12px 0;
        }

        .footer {
            position: absolute;
            bottom: 25px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-block {
            text-align: center;
            width: 150px;
        }

        .signature-line {
            border-top: 1px solid #0c3a2d;
            padding-top: 5px;
            font-size: 10px;
            color: #64748b;
        }

        .date-block {
            text-align: center;
        }

        .seal {
            width: 65px;
            height: 65px;
            border: 3px solid #0c3a2d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #0c3a2d;
            text-align: center;
            font-weight: bold;
            margin: 0 auto 5px;
            background: #e8f5e9;
        }

        .date {
            font-size: 11px;
            color: #475569;
        }

        .certificate-number {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="header">
            <div class="logo">EPAS-E Learning Management System</div>
            <div class="title">Certificate of Completion</div>
            <div class="subtitle">Electronic Products Assembly and Servicing</div>
        </div>

        <div class="content">
            <div class="presented-to">This is to certify that</div>
            <div class="recipient-name">{{ $user->full_name }}</div>
            <div class="description">
                has successfully completed all the requirements and demonstrated competency in
            </div>
            <div class="course-name">{{ $course->course_name }}</div>
            <div class="description">
                as prescribed by the EPAS-E Learning Management System curriculum.
            </div>
        </div>

        <div class="footer">
            <div class="signature-block">
                <div class="signature-line">Administrator</div>
            </div>

            <div class="date-block">
                <div class="seal">
                    OFFICIAL<br>SEAL
                </div>
                <div class="date">{{ $issue_date }}</div>
                <div class="certificate-number">Certificate No: {{ $certificate_number }}</div>
            </div>

            <div class="signature-block">
                <div class="signature-line">Instructor</div>
            </div>
        </div>
    </div>
</body>

</html>