<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #6d9773;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #6d9773;
            font-size: 22px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 10px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-left: 4px solid #6d9773;
            font-weight: bold;
            font-size: 13px;
            color: #333;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .stats-grid td {
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            background: #fff;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #6d9773;
        }
        .stat-value.success { color: #198754; }
        .stat-value.danger { color: #dc3545; }
        .stat-value.warning { color: #ffc107; }
        .stat-value.info { color: #0dcaf0; }
        .stat-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        table.data-table th, table.data-table td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        table.data-table th {
            background: #f8f9fa;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
        table.data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background: #d1e7dd; color: #0f5132; }
        .badge-danger { background: #f8d7da; color: #842029; }
        .badge-warning { background: #fff3cd; color: #664d03; }
        .badge-primary { background: #fff8e1; color: #bb8954; }
        .progress-bar {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            width: 80px;
        }
        .progress-fill {
            height: 100%;
            display: inline-block;
        }
        .progress-fill.success { background: #198754; }
        .progress-fill.danger { background: #dc3545; }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .two-column {
            width: 100%;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .two-column td:first-child {
            padding-left: 0;
        }
        .two-column td:last-child {
            padding-right: 0;
        }
        .list-item {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }
        .list-item:last-child {
            border-bottom: none;
        }
        .rank {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 10px;
            margin-right: 8px;
        }
        .rank-1 { background: #ffc107; color: #000; }
        .rank-2 { background: #adb5bd; color: #fff; }
        .rank-3 { background: #dc3545; color: #fff; }
        .rank-other { background: #e9ecef; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>EPAS-E Analytics Report</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>IETI College Marikina - Electronic Products Assembly and Servicing</p>
    </div>

    <!-- Overview Stats -->
    <div class="section">
        <div class="section-title">Overview</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-value">{{ $metrics['users']['total_students'] ?? 0 }}</div>
                    <div class="stat-label">Total Students</div>
                </td>
                <td>
                    <div class="stat-value success">{{ $metrics['modules']['overall_pass_rate'] ?? $metrics['performance']['pass_rate'] ?? 0 }}%</div>
                    <div class="stat-label">Pass Rate</div>
                </td>
                <td>
                    <div class="stat-value danger">{{ $metrics['modules']['overall_fail_rate'] ?? 0 }}%</div>
                    <div class="stat-label">Fail Rate</div>
                </td>
                <td>
                    <div class="stat-value info">{{ $metrics['courses']['total_modules'] ?? 0 }}</div>
                    <div class="stat-label">Total Modules</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Module Performance -->
    <div class="section">
        <div class="section-title">Module Performance</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Module</th>
                    <th style="text-align: center;">Attempts</th>
                    <th style="text-align: center;">Passed</th>
                    <th style="text-align: center;">Failed</th>
                    <th style="text-align: center;">Pass Rate</th>
                    <th style="text-align: center;">Avg Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($metrics['modules']['modules_list'] ?? [] as $module)
                <tr>
                    <td>
                        <strong>{{ $module['module_number'] }}</strong><br>
                        <small>{{ Str::limit($module['name'], 25) }}</small>
                    </td>
                    <td style="text-align: center;">{{ $module['total_attempts'] }}</td>
                    <td style="text-align: center;"><span class="badge badge-success">{{ $module['passed'] }}</span></td>
                    <td style="text-align: center;"><span class="badge badge-danger">{{ $module['failed'] }}</span></td>
                    <td style="text-align: center;">
                        <span class="badge {{ $module['pass_rate'] >= 70 ? 'badge-success' : ($module['pass_rate'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $module['pass_rate'] }}%
                        </span>
                    </td>
                    <td style="text-align: center;">{{ $module['average_score'] }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No module data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- User & Engagement Metrics -->
    <div class="section">
        <div class="section-title">User & Engagement Metrics</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-value">{{ $metrics['users']['total_instructors'] ?? 0 }}</div>
                    <div class="stat-label">Instructors</div>
                </td>
                <td>
                    <div class="stat-value success">{{ $metrics['users']['active_today'] ?? 0 }}</div>
                    <div class="stat-label">Active Today</div>
                </td>
                <td>
                    <div class="stat-value warning">{{ $metrics['users']['pending_approval'] ?? 0 }}</div>
                    <div class="stat-label">Pending Approval</div>
                </td>
                <td>
                    <div class="stat-value info">{{ $metrics['performance']['average_score'] ?? 0 }}%</div>
                    <div class="stat-label">Average Score</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Weekly Engagement -->
    <div class="section">
        <div class="section-title">Weekly Engagement</div>
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-value">{{ $metrics['engagement']['activities_completed_week'] ?? 0 }}</div>
                    <div class="stat-label">Activities Completed</div>
                </td>
                <td>
                    <div class="stat-value success">{{ $metrics['engagement']['homework_submissions_week'] ?? 0 }}</div>
                    <div class="stat-label">Homework Submissions</div>
                </td>
                <td>
                    <div class="stat-value info">{{ $metrics['engagement']['quiz_attempts_week'] ?? 0 }}</div>
                    <div class="stat-label">Quiz Attempts</div>
                </td>
                <td>
                    <div class="stat-value">{{ $metrics['modules']['total_attempts'] ?? 0 }}</div>
                    <div class="stat-label">Total Module Attempts</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Two Column: Top Performers & At-Risk -->
    <table class="two-column">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Top Performers</div>
                    @forelse($metrics['performance']['top_performers'] ?? [] as $index => $performer)
                    <div class="list-item">
                        <span class="rank {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-other')) }}">
                            {{ $index + 1 }}
                        </span>
                        {{ $performer->first_name }} {{ $performer->last_name }}
                        <span style="float: right;"><span class="badge badge-primary">{{ $performer->total_points ?? 0 }} pts</span></span>
                    </div>
                    @empty
                    <div class="list-item" style="text-align: center; color: #666;">No data available</div>
                    @endforelse
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title">Needs Attention</div>
                    @forelse($metrics['performance']['at_risk_students'] ?? [] as $student)
                    <div class="list-item">
                        {{ $student->first_name }} {{ $student->last_name }}
                        <br><small style="color: #dc3545;">
                            @if($student->last_login)
                                Last login: {{ $student->last_login->diffForHumans() }}
                            @else
                                Never logged in
                            @endif
                        </small>
                    </div>
                    @empty
                    <div class="list-item" style="text-align: center; color: #198754;">All students are active!</div>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        EPAS-E Learning Management System | IETI College Marikina | Analytics Report | Page 1
    </div>
</body>
</html>
