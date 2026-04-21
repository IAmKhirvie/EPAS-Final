@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ dynamic_asset('css/pages/analytics.css') }}">
<style>
    .an-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; }
    .an-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem; }
    .an-full { margin-bottom: 0.75rem; }

    .an-card { background: var(--surface, #fff); border-radius: 16px; border: 1px solid #e8e8e8; overflow: hidden; }
    .an-card-head { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 1rem; border-bottom: 1px solid #f0f0f0; }
    .an-card-head h4 { font-size: 0.85rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.35rem; color: var(--text-primary); }
    .an-card-head h4 i { color: var(--primary, #0c3a2d); font-size: 0.8rem; }
    .an-card-head small { font-size: 0.72rem; color: var(--text-muted); }
    .an-card-body { padding: 1rem; }
    .an-card-body.no-pad { padding: 0; }

    /* Stat row */
    .an-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 0.75rem; }
    .an-stat { background: var(--surface, #fff); border-radius: 16px; border: 1px solid #e8e8e8; padding: 1rem 1.15rem; }
    .an-stat-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-bottom: 0.65rem; }
    .an-stat-val { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; }
    .an-stat-lbl { font-size: 0.68rem; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.15rem; }

    /* Leaderboard / At-risk rows */
    .an-row { display: flex; align-items: center; padding: 0.6rem 1rem; border-bottom: 1px solid #f5f5f5; gap: 0.65rem; }
    .an-row:last-child { border-bottom: none; }
    .an-row-rank { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0; }
    .an-row-info { flex: 1; min-width: 0; }
    .an-row-name { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); }
    .an-row-sub { font-size: 0.7rem; color: var(--text-muted); }
    .an-row-badge { font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 6px; flex-shrink: 0; }

    /* Progress bar */
    .an-bar { display: flex; height: 6px; border-radius: 3px; overflow: hidden; background: #f0f0f0; min-width: 80px; }

    /* Dark mode */
    .dark-mode .an-card { background: var(--card-bg); border-color: var(--border); }
    .dark-mode .an-card-head { border-bottom-color: var(--border); }
    .dark-mode .an-stat { background: var(--card-bg); border-color: var(--border); }
    .dark-mode .an-row { border-bottom-color: rgba(255,255,255,0.03); }
    .dark-mode .an-bar { background: var(--border); }

    @media (max-width: 768px) {
        .an-stats { grid-template-columns: 1fr 1fr; }
        .an-grid, .an-grid-3 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="page-header" style="margin-bottom:1rem;">
        <div class="page-header-left">
            <h1 style="font-size:1.4rem;"><i class="fas fa-chart-pie me-2"></i>Analytics</h1>
            <p>Module performance and student engagement</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('analytics.export.students') }}" class="btn btn-sm" style="border-radius:10px;border:1px solid #e0e0e0;font-weight:600;font-size:0.78rem;">
                <i class="fas fa-file-excel me-1" style="color:#198754"></i>Excel
            </a>
            <a href="{{ route('analytics.export.pdf') }}" class="btn btn-sm" style="border-radius:10px;border:1px solid #e0e0e0;font-weight:600;font-size:0.78rem;">
                <i class="fas fa-file-pdf me-1" style="color:#dc3545"></i>PDF
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="an-stats">
        <div class="an-stat">
            <div class="an-stat-icon" style="background:rgba(12,58,45,0.08);color:#0c3a2d"><i class="fas fa-user-graduate"></i></div>
            <div class="an-stat-val">{{ $metrics['users']['total_students'] ?? 0 }}</div>
            <div class="an-stat-lbl">Students</div>
        </div>
        <div class="an-stat">
            <div class="an-stat-icon" style="background:rgba(25,135,84,0.08);color:#198754"><i class="fas fa-check-circle"></i></div>
            <div class="an-stat-val">{{ $metrics['modules']['overall_pass_rate'] ?? $metrics['performance']['pass_rate'] ?? 0 }}%</div>
            <div class="an-stat-lbl">Pass Rate</div>
        </div>
        <div class="an-stat">
            <div class="an-stat-icon" style="background:rgba(220,53,69,0.08);color:#dc3545"><i class="fas fa-times-circle"></i></div>
            <div class="an-stat-val">{{ $metrics['modules']['overall_fail_rate'] ?? 0 }}%</div>
            <div class="an-stat-lbl">Fail Rate</div>
        </div>
        <div class="an-stat">
            <div class="an-stat-icon" style="background:rgba(13,110,253,0.08);color:#0d6efd"><i class="fas fa-book"></i></div>
            <div class="an-stat-val">{{ $metrics['courses']['total_modules'] ?? 0 }}</div>
            <div class="an-stat-lbl">Modules</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="an-grid">
        <div class="an-card">
            <div class="an-card-head">
                <h4><i class="fas fa-chart-bar"></i> Module Performance</h4>
                <small>Pass vs Fail by module</small>
            </div>
            <div class="an-card-body">
                <div style="position:relative;height:260px;"><canvas id="modulePerformanceChart"></canvas></div>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-head">
                <h4><i class="fas fa-chart-pie"></i> Overall Results</h4>
                <small>Total distribution</small>
            </div>
            <div class="an-card-body" style="display:flex;align-items:center;justify-content:center;min-height:260px;">
                <canvas id="overallResultsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Module Statistics Table --}}
    <div class="an-card an-full">
        <div class="an-card-head">
            <h4><i class="fas fa-table"></i> Module Statistics</h4>
        </div>
        <div class="an-card-body no-pad" style="overflow-x:auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th class="text-center">Attempts</th>
                        <th class="text-center">Passed</th>
                        <th class="text-center">Failed</th>
                        <th class="text-center">Pass Rate</th>
                        <th class="text-center">Avg Score</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['modules']['modules_list'] ?? [] as $module)
                    <tr>
                        <td>
                            <strong>{{ $module['module_number'] }}</strong>
                            <br><small style="color:var(--text-muted)">{{ Str::limit($module['name'], 30) }}</small>
                        </td>
                        <td class="text-center">{{ $module['total_attempts'] }}</td>
                        <td class="text-center"><span class="modern-badge success">{{ $module['passed'] }}</span></td>
                        <td class="text-center"><span class="modern-badge danger">{{ $module['failed'] }}</span></td>
                        <td class="text-center">
                            <span class="modern-badge {{ $module['pass_rate'] >= 70 ? 'success' : ($module['pass_rate'] >= 50 ? 'warning' : 'danger') }}">{{ $module['pass_rate'] }}%</span>
                        </td>
                        <td class="text-center">{{ $module['average_score'] }}%</td>
                        <td>
                            <div class="an-bar">
                                <div style="width:{{ $module['pass_rate'] }}%;background:#198754;"></div>
                                <div style="width:{{ $module['fail_rate'] }}%;background:#dc3545;"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="page-empty"><i class="fas fa-chart-bar"></i><p>No module data yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Leaderboard & At-Risk --}}
    <div class="an-grid">
        <div class="an-card">
            <div class="an-card-head">
                <h4><i class="fas fa-trophy" style="color:#f59e0b"></i> Leaderboard</h4>
            </div>
            <div class="an-card-body no-pad">
                @forelse($metrics['performance']['top_performers'] ?? [] as $index => $performer)
                <div class="an-row">
                    <div class="an-row-rank" style="@if($index === 0) background:rgba(245,158,11,0.12);color:#f59e0b; @elseif($index === 1) background:rgba(148,163,184,0.15);color:#64748b; @elseif($index === 2) background:rgba(180,83,9,0.12);color:#b45309; @else background:#f5f5f5;color:#999; @endif">
                        @if($index === 0)<i class="fas fa-crown"></i>@else {{ $index + 1 }} @endif
                    </div>
                    <div class="an-row-info">
                        <div class="an-row-name">{{ $performer->first_name }} {{ $performer->last_name }}</div>
                    </div>
                    <span class="an-row-badge" style="background:rgba(12,58,45,0.08);color:#0c3a2d">{{ $performer->total_points ?? 0 }} pts</span>
                </div>
                @empty
                <div class="page-empty"><i class="fas fa-trophy" style="color:#f59e0b"></i><p>No data yet</p></div>
                @endforelse
            </div>
        </div>

        <div class="an-card">
            <div class="an-card-head">
                <h4><i class="fas fa-exclamation-triangle" style="color:#dc3545"></i> Needs Attention</h4>
                <small>Inactive students</small>
            </div>
            <div class="an-card-body no-pad">
                @forelse($metrics['performance']['at_risk_students'] ?? [] as $student)
                <div class="an-row">
                    <div class="an-row-rank" style="background:rgba(220,53,69,0.08);color:#dc3545"><i class="fas fa-user"></i></div>
                    <div class="an-row-info">
                        <div class="an-row-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                        <div class="an-row-sub">{{ $student->email }}</div>
                    </div>
                    <span class="an-row-badge" style="background:rgba(220,53,69,0.08);color:#dc3545">
                        @if($student->last_login) {{ $student->last_login->diffForHumans() }} @else Never @endif
                    </span>
                </div>
                @empty
                <div class="page-empty"><i class="fas fa-check-circle" style="color:#198754"></i><p>All students active!</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Weekly Engagement --}}
    <div class="an-card an-full">
        <div class="an-card-head">
            <h4><i class="fas fa-calendar-week"></i> Weekly Engagement</h4>
        </div>
        <div class="an-card-body">
            <div class="an-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:1rem;">
                <div class="an-stat" style="padding:0.75rem 1rem;">
                    <div class="an-stat-icon" style="background:rgba(13,110,253,0.08);color:#0d6efd;width:30px;height:30px;font-size:0.78rem;margin-bottom:0.4rem;"><i class="fas fa-tasks"></i></div>
                    <div class="an-stat-val" style="font-size:1.2rem;">{{ $metrics['engagement']['activities_completed_week'] ?? 0 }}</div>
                    <div class="an-stat-lbl">Activities</div>
                </div>
                <div class="an-stat" style="padding:0.75rem 1rem;">
                    <div class="an-stat-icon" style="background:rgba(25,135,84,0.08);color:#198754;width:30px;height:30px;font-size:0.78rem;margin-bottom:0.4rem;"><i class="fas fa-book-open"></i></div>
                    <div class="an-stat-val" style="font-size:1.2rem;">{{ $metrics['engagement']['homework_submissions_week'] ?? 0 }}</div>
                    <div class="an-stat-lbl">Homework</div>
                </div>
                <div class="an-stat" style="padding:0.75rem 1rem;">
                    <div class="an-stat-icon" style="background:rgba(111,66,193,0.08);color:#6f42c1;width:30px;height:30px;font-size:0.78rem;margin-bottom:0.4rem;"><i class="fas fa-question-circle"></i></div>
                    <div class="an-stat-val" style="font-size:1.2rem;">{{ $metrics['engagement']['quiz_attempts_week'] ?? 0 }}</div>
                    <div class="an-stat-lbl">Quizzes</div>
                </div>
            </div>
            <div style="position:relative;height:220px;"><canvas id="dailyActivityChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartColors = {
        green: { bg: 'rgba(12, 58, 45, 0.7)', border: '#0c3a2d' },
        red: { bg: 'rgba(220, 53, 69, 0.6)', border: '#dc3545' },
        line: { bg: 'rgba(12, 58, 45, 0.08)', border: '#0c3a2d' },
    };
    const chartOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: { size: 11, family: 'Plus Jakarta Sans' }, usePointStyle: true, pointStyle: 'circle' } } },
    };

    // Module Performance
    const modCtx = document.getElementById('modulePerformanceChart');
    if (modCtx) {
        const modData = @json($metrics['modules']['modules_list'] ?? []);
        new Chart(modCtx, {
            type: 'bar',
            data: {
                labels: modData.map(m => m.module_number || m.name.substring(0, 15)),
                datasets: [
                    { label: 'Pass %', data: modData.map(m => m.pass_rate), backgroundColor: chartColors.green.bg, borderColor: chartColors.green.border, borderWidth: 1, borderRadius: 6 },
                    { label: 'Fail %', data: modData.map(m => m.fail_rate), backgroundColor: chartColors.red.bg, borderColor: chartColors.red.border, borderWidth: 1, borderRadius: 6 }
                ]
            },
            options: { ...chartOpts, scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
        });
    }

    // Overall Results
    const pieCtx = document.getElementById('overallResultsChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Passed', 'Failed'],
                datasets: [{ data: [{{ $metrics['modules']['total_passed'] ?? 0 }}, {{ $metrics['modules']['total_failed'] ?? 0 }}], backgroundColor: [chartColors.green.bg, chartColors.red.bg], borderColor: [chartColors.green.border, chartColors.red.border], borderWidth: 2 }]
            },
            options: { ...chartOpts, plugins: { ...chartOpts.plugins, legend: { position: 'bottom', labels: { font: { size: 11, family: 'Plus Jakarta Sans' }, usePointStyle: true } } } }
        });
    }

    // Daily Activity
    const dayCtx = document.getElementById('dailyActivityChart');
    if (dayCtx) {
        const dayData = @json($metrics['engagement']['daily_active_users'] ?? []);
        new Chart(dayCtx, {
            type: 'line',
            data: {
                labels: dayData.map(d => d.date),
                datasets: [{ label: 'Active Users', data: dayData.map(d => d.count), fill: true, backgroundColor: chartColors.line.bg, borderColor: chartColors.line.border, borderWidth: 2, tension: 0.4, pointRadius: 3, pointBackgroundColor: chartColors.line.border }]
            },
            options: { ...chartOpts, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } }
        });
    }
});
</script>
@endpush
