<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sheet->title }} - EPAS-E</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #198754;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #198754;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .meta-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .meta-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            background: #198754;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .content {
            font-size: 14px;
            padding: 0 15px;
        }
        .content p {
            margin-bottom: 10px;
        }
        .topic {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .topic h4 {
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .question-list {
            list-style: none;
            padding: 0;
        }
        .question-item {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .options {
            list-style-type: upper-alpha;
            margin-left: 20px;
            margin-top: 5px;
        }
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .card-header {
            background: #e9ecef;
            padding: 10px 15px;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
        }
        .card-body {
            padding: 15px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $sheet->title }}</h1>
        <div class="subtitle">
            @if($sheet->module)
            {{ $sheet->module->module_name }} | {{ $sheet->module->module_number }}
            @endif
        </div>
    </div>

    <div class="meta-info">
        <p><strong>Information Sheet:</strong> {{ $sheet->title }}</p>
        @if($sheet->module)
        <p><strong>Module:</strong> {{ $sheet->module->module_name }}</p>
        @endif
        <p><strong>Export Date:</strong> {{ $exportDate }}</p>
    </div>

    @if($sheet->learning_objective)
    <div class="section">
        <div class="section-title">Learning Objective</div>
        <div class="content">
            {!! nl2br(e($sheet->learning_objective)) !!}
        </div>
    </div>
    @endif

    @if($sheet->introduction)
    <div class="section">
        <div class="section-title">Introduction</div>
        <div class="content">
            {!! nl2br(e($sheet->introduction)) !!}
        </div>
    </div>
    @endif

    @if($sheet->topics->count() > 0)
    <div class="section">
        <div class="section-title">Topics</div>
        @foreach($sheet->topics->sortBy('order') as $topic)
        <div class="topic">
            <h4>{{ $topic->title }}</h4>
            <div class="content">{!! $topic->content !!}</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($sheet->selfChecks->count() > 0)
    <div class="section">
        <div class="section-title">Self-Check Questions</div>
        @foreach($sheet->selfChecks as $selfCheck)
        <div class="card">
            <div class="card-header">{{ $selfCheck->title }}</div>
            <div class="card-body">
                @if($selfCheck->instructions)
                <p><em>{{ $selfCheck->instructions }}</em></p>
                @endif
                <ul class="question-list">
                    @foreach($selfCheck->questions as $index => $question)
                    <li class="question-item">
                        <strong>{{ $index + 1 }}. {{ $question->question }}</strong>
                        @if($question->question_type === 'multiple_choice' && $question->options)
                        <ul class="options">
                            @foreach($question->options as $option)
                            <li>{{ $option }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($sheet->taskSheets->count() > 0)
    <div class="section">
        <div class="section-title">Task Sheets</div>
        @foreach($sheet->taskSheets as $taskSheet)
        <div class="card">
            <div class="card-header">{{ $taskSheet->title }}</div>
            <div class="card-body">
                @if($taskSheet->description)
                <p>{{ $taskSheet->description }}</p>
                @endif
                @if($taskSheet->instructions)
                <div class="content">
                    <strong>Instructions:</strong><br>
                    {!! nl2br(e($taskSheet->instructions)) !!}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($sheet->jobSheets->count() > 0)
    <div class="section">
        <div class="section-title">Job Sheets</div>
        @foreach($sheet->jobSheets as $jobSheet)
        <div class="card">
            <div class="card-header">{{ $jobSheet->title }}</div>
            <div class="card-body">
                @if($jobSheet->description)
                <p>{{ $jobSheet->description }}</p>
                @endif
                @if($jobSheet->tools_materials)
                <div class="content">
                    <strong>Tools & Materials:</strong><br>
                    {!! nl2br(e($jobSheet->tools_materials)) !!}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($sheet->homeworks->count() > 0)
    <div class="section">
        <div class="section-title">Homework Assignments</div>
        @foreach($sheet->homeworks as $homework)
        <div class="card">
            <div class="card-header">{{ $homework->title }}</div>
            <div class="card-body">
                @if($homework->description)
                <p>{{ $homework->description }}</p>
                @endif
                @if($homework->instructions)
                <div class="content">
                    {!! nl2br(e($homework->instructions)) !!}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Generated from EPAS-E Learning Management System</p>
        <p>{{ $exportDate }} | For offline study use only</p>
    </div>
</body>
</html>
