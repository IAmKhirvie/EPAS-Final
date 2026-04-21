<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jobSheet->title }} - Job Sheet</title>
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
            border-bottom: 3px solid #0dcaf0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 { color: #333; font-size: 24px; margin-bottom: 10px; }
        .header .badge { background: #0dcaf0; color: #333; padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        .meta-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .meta-info p { margin: 5px 0; font-size: 14px; }
        .section { margin-bottom: 30px; }
        .section-title { background: #0dcaf0; color: #333; padding: 10px 15px; font-size: 16px; margin-bottom: 15px; border-radius: 4px; }
        .content { font-size: 14px; padding: 0 15px; }
        .step { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .step-number { background: #0dcaf0; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <span class="badge">Job Sheet</span>
        <h1>{{ $jobSheet->title }}</h1>
    </div>

    <div class="meta-info">
        @if($jobSheet->informationSheet)
        <p><strong>Information Sheet:</strong> {{ $jobSheet->informationSheet->title }}</p>
        @if($jobSheet->informationSheet->module)
        <p><strong>Module:</strong> {{ $jobSheet->informationSheet->module->module_name }}</p>
        @endif
        @endif
        <p><strong>Export Date:</strong> {{ $exportDate }}</p>
    </div>

    @if($jobSheet->description)
    <div class="section">
        <div class="section-title">Description</div>
        <div class="content">{!! nl2br(e($jobSheet->description)) !!}</div>
    </div>
    @endif

    @if($jobSheet->tools_materials)
    <div class="section">
        <div class="section-title">Tools & Materials</div>
        <div class="content">{!! nl2br(e($jobSheet->tools_materials)) !!}</div>
    </div>
    @endif

    @if($jobSheet->steps && count($jobSheet->steps) > 0)
    <div class="section">
        <div class="section-title">Procedure Steps</div>
        @foreach($jobSheet->steps as $index => $step)
        <div class="step">
            <span class="step-number">{{ $index + 1 }}</span>
            <strong>{{ $step['title'] ?? 'Step ' . ($index + 1) }}</strong>
            @if(isset($step['description']))
            <p style="margin-top: 10px; margin-left: 40px;">{{ $step['description'] }}</p>
            @endif
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
