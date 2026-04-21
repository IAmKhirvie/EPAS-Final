@extends('layouts.app')

@section('title', 'My Analytics - EPAS-E')

@push('styles')
<style>
.analytics-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}
.analytics-header h2 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
}
.analytics-header p {
    margin: 0;
    opacity: 0.9;
}

.grade-ring-container {
    display: flex;
    align-items: center;
    gap: 2rem;
}
.grade-ring {
    position: relative;
    width: 140px;
    height: 140px;
}
.grade-ring canvas {
    width: 100%;
    height: 100%;
}
.grade-ring-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.grade-ring-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}
.grade-ring-label {
    font-size: 0.75rem;
    opacity: 0.9;
}

.grade-details {
    flex: 1;
}
.grade-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.grade-badge.competent { background: rgba(255,255,255,0.2); }
.grade-badge.not-competent { background: rgba(255,0,0,0.3); }

.stat-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e9ecef;
}
.stat-card-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #333;
}
.stat-card-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.stat-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
}
.stat-card-icon.blue { background: #fff8e1; color: #ffb902; }
.stat-card-icon.green { background: #e8f5e9; color: #388e3c; }
.stat-card-icon.purple { background: #f3e5f5; color: #7b1fa2; }
.stat-card-icon.orange { background: #fff3e0; color: #f57c00; }

.analytics-section {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.analytics-section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.analytics-section-title i {
    color: #667eea;
}

.breakdown-chart-container {
    height: 300px;
    position: relative;
}

.strengths-list, .weaknesses-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.strengths-list li, .weaknesses-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 8px;
}
.strengths-list li {
    background: #e8f5e9;
}
.weaknesses-list li {
    background: #fff3e0;
}
.strength-label, .weakness-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.strength-label i { color: #388e3c; }
.weakness-label i { color: #f57c00; }
.strength-score, .weakness-score {
    font-weight: 600;
}
.improvement-badge {
    font-size: 0.75rem;
    background: #ffb74d;
    color: #fff;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    margin-left: 0.5rem;
}

.module-table {
    width: 100%;
}
.module-table th {
    text-align: left;
    padding: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
    border-bottom: 2px solid #e9ecef;
}
.module-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}
.module-table tr:hover {
    background: #f8f9fa;
}
.grade-pill {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.grade-pill.outstanding { background: #c8e6c9; color: #2e7d32; }
.grade-pill.very-satisfactory { background: #fff8e1; color: #bb8954; }
.grade-pill.satisfactory { background: #fff9c4; color: #f57f17; }
.grade-pill.fairly-satisfactory { background: #ffe0b2; color: #ef6c00; }
.grade-pill.dnm { background: #ffcdd2; color: #c62828; }
.grade-pill.no-grade { background: #e0e0e0; color: #757575; }

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>
@endpush

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'My Analytics'],
    ]" />

    {{-- Analytics Header with Overall Grade --}}
    <div class="analytics-header">
        <div class="grade-ring-container">
            <div class="grade-ring">
                <canvas id="gradeRingChart"></canvas>
                <div class="grade-ring-center">
                    <div class="grade-ring-value">{{ $analytics['overall_average'] }}%</div>
                    <div class="grade-ring-label">Overall</div>
                </div>
            </div>
            <div class="grade-details">
                <h2>Performance Overview</h2>
                <div class="grade-badge {{ $analytics['overall_grade']['is_competent'] ? 'competent' : 'not-competent' }}">
                    {{ $analytics['overall_grade']['descriptor'] }} ({{ $analytics['overall_grade']['code'] }})
                </div>
                <p>{{ $analytics['overall_grade']['competency_status'] }}</p>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-card-value">{{ $analytics['completed_modules'] }}/{{ $analytics['total_modules'] }}</div>
            <div class="stat-card-label">Modules Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-card-value">{{ $analytics['progress_percentage'] }}%</div>
            <div class="stat-card-label">Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon purple">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-card-value">{{ $analytics['component_averages']['self_checks'] }}%</div>
            <div class="stat-card-label">Quiz Average</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon orange">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-card-value">{{ $analytics['component_averages']['homeworks'] }}%</div>
            <div class="stat-card-label">Homework Average</div>
        </div>
    </div>

    <div class="row">
        {{-- Grade Breakdown Chart --}}
        <div class="col-lg-8 mb-4">
            <div class="analytics-section">
                <div class="analytics-section-title">
                    <i class="fas fa-chart-bar"></i> Grade Breakdown by Category
                </div>
                <div class="breakdown-chart-container">
                    <canvas id="breakdownChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Strengths & Weaknesses --}}
        <div class="col-lg-4 mb-4">
            <div class="analytics-section mb-3">
                <div class="analytics-section-title">
                    <i class="fas fa-star"></i> Your Strengths
                </div>
                @if(count($analytics['strengths']) > 0)
                <ul class="strengths-list">
                    @foreach($analytics['strengths'] as $strength)
                    <li>
                        <span class="strength-label">
                            <i class="fas fa-check-circle"></i>
                            {{ $strength['label'] }}
                        </span>
                        <span class="strength-score">{{ $strength['score'] }}%</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted small">Complete more assessments to see your strengths.</p>
                @endif
            </div>

            <div class="analytics-section">
                <div class="analytics-section-title">
                    <i class="fas fa-arrow-up"></i> Areas to Improve
                </div>
                @if(count($analytics['weaknesses']) > 0)
                <ul class="weaknesses-list">
                    @foreach($analytics['weaknesses'] as $weakness)
                    <li>
                        <span class="weakness-label">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $weakness['label'] }}
                        </span>
                        <span class="weakness-score">
                            {{ $weakness['score'] }}%
                            <span class="improvement-badge">+{{ number_format($weakness['improvement_needed'], 1) }}% needed</span>
                        </span>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted small">Great job! Keep up the excellent work.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Module-by-Module Breakdown --}}
    <div class="analytics-section">
        <div class="analytics-section-title">
            <i class="fas fa-list"></i> Module Performance
        </div>
        @if(count($analytics['module_breakdown']) > 0)
        <div class="table-responsive">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Course</th>
                        <th>Quizzes</th>
                        <th>Homework</th>
                        <th>Tasks</th>
                        <th>Jobs</th>
                        <th>Overall</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analytics['module_breakdown'] as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['module']->module_number }}</strong>
                            <br><small class="text-muted">{{ Str::limit($item['module']->module_title, 30) }}</small>
                        </td>
                        <td><small>{{ Str::limit($item['course']->course_name, 20) }}</small></td>
                        <td>{{ $item['grade']['components']['self_checks']['percentage'] ?? 0 }}%</td>
                        <td>{{ $item['grade']['components']['homeworks']['percentage'] ?? 0 }}%</td>
                        <td>{{ $item['grade']['components']['task_sheets']['percentage'] ?? 0 }}%</td>
                        <td>{{ $item['grade']['components']['job_sheets']['percentage'] ?? 0 }}%</td>
                        <td><strong>{{ $item['grade']['percentage'] }}%</strong></td>
                        <td>
                            @php
                                $gradeClass = match($item['grade']['grade']['code'] ?? 'NG') {
                                    'O' => 'outstanding',
                                    'VS' => 'very-satisfactory',
                                    'S' => 'satisfactory',
                                    'FS' => 'fairly-satisfactory',
                                    'DNM' => 'dnm',
                                    default => 'no-grade'
                                };
                            @endphp
                            <span class="grade-pill {{ $gradeClass }}">{{ $item['grade']['grade']['code'] ?? 'NG' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-chart-line"></i>
            <p>No module data available yet. Start completing assessments to see your performance.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grade Ring Chart
    const gradeRingCtx = document.getElementById('gradeRingChart').getContext('2d');
    const overallGrade = {{ $analytics['overall_average'] }};

    new Chart(gradeRingCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [overallGrade, 100 - overallGrade],
                backgroundColor: ['rgba(255,255,255,0.9)', 'rgba(255,255,255,0.2)'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '75%',
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    // Breakdown Chart
    const breakdownCtx = document.getElementById('breakdownChart').getContext('2d');
    new Chart(breakdownCtx, {
        type: 'bar',
        data: {
            labels: ['Quizzes', 'Homework', 'Task Sheets', 'Job Sheets'],
            datasets: [{
                label: 'Your Score',
                data: [
                    {{ $analytics['component_averages']['self_checks'] }},
                    {{ $analytics['component_averages']['homeworks'] }},
                    {{ $analytics['component_averages']['task_sheets'] }},
                    {{ $analytics['component_averages']['job_sheets'] }}
                ],
                backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c'],
                borderRadius: 8,
                barThickness: 50,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) { return value + '%'; }
                    },
                    grid: { color: '#e9ecef' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
@endpush
