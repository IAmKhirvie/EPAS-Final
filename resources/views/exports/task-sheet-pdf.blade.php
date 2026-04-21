<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $taskSheet->title }} - Task Sheet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            border-bottom: 3px solid #ffc107;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        .header .badge { background: #ffc107; color: #333; padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        .meta-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .meta-info p { margin: 5px 0; font-size: 14px; }
        .section { margin-bottom: 30px; }
        .section-title { background: #ffc107; color: #333; padding: 10px 15px; font-size: 16px; margin-bottom: 15px; border-radius: 4px; }
        .content { font-size: 14px; padding: 0 15px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <span class="badge">Task Sheet</span>
        <h1>{{ $taskSheet->title }}</h1>
    </div>

    <div class="meta-info">
        @if($taskSheet->informationSheet)
        <p><strong>Information Sheet:</strong> {{ $taskSheet->informationSheet->title }}</p>
        @if($taskSheet->informationSheet->module)
        <p><strong>Module:</strong> {{ $taskSheet->informationSheet->module->module_name }}</p>
        @endif
        @endif
        <p><strong>Export Date:</strong> {{ $exportDate }}</p>
    </div>

    @if($taskSheet->description)
    <div class="section">
        <div class="section-title">Description</div>
        <div class="content">{!! nl2br(e($taskSheet->description)) !!}</div>
    </div>
    @endif

    @if($taskSheet->instructions)
    <div class="section">
        <div class="section-title">Instructions</div>
        <div class="content">{!! nl2br(e($taskSheet->instructions)) !!}</div>
    </div>
    @endif

    <div class="footer">
        <p>Generated from EPAS-E Learning Management System</p>
        <p>{{ $exportDate }} | For offline study use only</p>
    </div>
</body>
</html>
