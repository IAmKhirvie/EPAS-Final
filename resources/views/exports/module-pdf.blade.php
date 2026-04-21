<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $module->module_name }} - EPAS-E</title>
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
            border-bottom: 3px solid #ffb902;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0c3a2d;
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
        .meta-info strong {
            color: #ffb902;
        }
        .section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #ffb902;
            color: white;
            padding: 10px 15px;
            font-size: 18px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .subsection {
            margin-bottom: 25px;
            padding-left: 15px;
            border-left: 3px solid #dee2e6;
        }
        .subsection-title {
            color: #ffb902;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .content {
            font-size: 14px;
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
        .question-item strong {
            display: block;
            margin-bottom: 5px;
        }
        .options {
            list-style-type: upper-alpha;
            margin-left: 20px;
            margin-top: 5px;
        }
        .sheet-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        .sheet-card .card-header {
            background: #e9ecef;
            padding: 10px 15px;
            font-weight: bold;
        }
        .sheet-card .card-body {
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
        .page-break {
            page-break-before: always;
        }
        @media print {
            body {
                padding: 0;
            }
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $module->module_name }}</h1>
        <div class="subtitle">{{ $module->qualification_title }}</div>
    </div>

    <div class="meta-info">
        <p><strong>Module Number:</strong> {{ $module->module_number }}</p>
        <p><strong>Module Title:</strong> {{ $module->module_title }}</p>
        <p><strong>Unit of Competency:</strong> {{ $module->unit_of_competency }}</p>
        <p><strong>Export Date:</strong> {{ $exportDate }}</p>
    </div>

    @if($module->introduction)
    <div class="section">
        <div class="section-title">Introduction</div>
        <div class="content">
            {!! nl2br(e($module->introduction)) !!}
        </div>
    </div>
    @endif

    @if($module->learning_outcomes)
    <div class="section">
        <div class="section-title">Learning Outcomes</div>
        <div class="content">
            {!! nl2br(e($module->learning_outcomes)) !!}
        </div>
    </div>
    @endif

    @foreach($module->informationSheets as $sheet)
    <div class="section page-break">
        <div class="section-title">Information Sheet {{ $sheet->sheet_number }}: {{ $sheet->title }}</div>

        @if($sheet->learning_objective)
        <div class="subsection">
            <div class="subsection-title">Learning Objective</div>
            <div class="content">{!! nl2br(e($sheet->learning_objective)) !!}</div>
        </div>
        @endif

        @if($sheet->introduction)
        <div class="subsection">
            <div class="subsection-title">Introduction</div>
            <div class="content">{!! nl2br(e($sheet->introduction)) !!}</div>
        </div>
        @endif

        @if($sheet->topics->count() > 0)
        <div class="subsection">
            <div class="subsection-title">Topics</div>
            @foreach($sheet->topics->sortBy('order') as $topic)
            <div class="topic">
                <h4>{{ $topic->title }}</h4>
                @if($topic->content)
                <div class="content">{!! $topic->content !!}</div>
                @endif
                @if($topic->document_content)
                <div class="content">{!! app(\App\Services\ContentSanitizationService::class)->stripWordBloat($topic->document_content) !!}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($sheet->selfChecks->count() > 0)
        <div class="subsection">
            <div class="subsection-title">Self-Check Questions</div>
            @foreach($sheet->selfChecks as $selfCheck)
            <div class="sheet-card">
                <div class="card-header">{{ $selfCheck->title }}</div>
                <div class="card-body">
                    @if($selfCheck->instructions)
                    <p><em>{{ $selfCheck->instructions }}</em></p>
                    @endif
                    <ul class="question-list">
                        @foreach($selfCheck->questions->sortBy('order') as $index => $question)
                        <li class="question-item">
                            <strong>{{ $index + 1 }}. {{ $question->question_text }}</strong>
                            @if(in_array($question->question_type, ['multiple_choice', 'image_choice']) && $question->options)
                            <ul class="options">
                                @foreach($question->options as $option)
                                <li>{{ is_array($option) ? ($option['text'] ?? $option['label'] ?? '') : $option }}</li>
                                @endforeach
                            </ul>
                            @elseif($question->question_type === 'true_false')
                            <ul class="options">
                                <li>True</li>
                                <li>False</li>
                            </ul>
                            @elseif($question->question_type === 'identification' || $question->question_type === 'fill_blank')
                            <p style="margin-top:5px;color:#666"><em>Answer: ________________</em></p>
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
        <div class="subsection">
            <div class="subsection-title">Task Sheets</div>
            @foreach($sheet->taskSheets as $taskSheet)
            <div class="sheet-card">
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
        <div class="subsection">
            <div class="subsection-title">Job Sheets</div>
            @foreach($sheet->jobSheets as $jobSheet)
            <div class="sheet-card">
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
    </div>
    @endforeach

    <div class="footer">
        <p>Generated from EPAS-E Learning Management System</p>
        <p>{{ $exportDate }} | For offline study use only</p>
    </div>
</body>
</html>
