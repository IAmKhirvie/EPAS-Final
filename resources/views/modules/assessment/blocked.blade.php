@extends('layouts.app')

@section('title', 'Assessment Unavailable - ' . $module->module_title . ' - EPAS-E')

@push('styles')
<style>
.blocked-container {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    padding: 3rem 1.5rem;
}

.blocked-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.blocked-icon i {
    font-size: 3rem;
    color: #ef4444;
}

.blocked-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
}

.blocked-reason {
    color: var(--text-secondary);
    font-size: 1.05rem;
    margin-bottom: 2rem;
}

.blocked-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.blocked-card h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.blocked-card h4 i {
    color: var(--primary);
}

.pending-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pending-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--background);
    border-radius: calc(var(--border-radius) / 2);
    margin-bottom: 0.5rem;
}

.pending-item:last-child {
    margin-bottom: 0;
}

.pending-item i {
    color: #f59e0b;
}

.pending-item a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.pending-item a:hover {
    text-decoration: underline;
}

.progress-bar-container {
    background: var(--background);
    border-radius: 50px;
    height: 8px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-bar {
    height: 100%;
    background: var(--primary);
    border-radius: 50px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.attempts-info {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 1rem;
}

.attempt-stat {
    text-align: center;
}

.attempt-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.attempt-stat-label {
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="blocked-container">
        <div class="blocked-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="blocked-title">Assessment Unavailable</h1>
        <p class="blocked-reason">{{ $reason }}</p>

        {{-- Show details based on the reason --}}
        @if(isset($details['pending']) && count($details['pending']) > 0)
        <div class="blocked-card">
            <h4><i class="fas fa-tasks"></i> Activities to Complete</h4>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $details['percentage'] ?? 0 }}%"></div>
            </div>
            <p class="progress-text">
                {{ $details['completed_count'] ?? 0 }} of {{ $details['total'] ?? 0 }} activities completed
            </p>
            <ul class="pending-list">
                @foreach(array_slice($details['pending'], 0, 5) as $pending)
                <li class="pending-item">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $pending['self_check']->title ?? 'Self Check' }}</span>
                    <span class="text-muted">-</span>
                    <a href="{{ route('courses.modules.information-sheets.self-check', [$course, $module, $pending['sheet']]) }}">
                        Complete Now
                    </a>
                </li>
                @endforeach
                @if(count($details['pending']) > 5)
                <li class="pending-item text-muted">
                    <i class="fas fa-ellipsis-h"></i>
                    <span>And {{ count($details['pending']) - 5 }} more...</span>
                </li>
                @endif
            </ul>
        </div>
        @endif

        @if(isset($details['attempts_used']))
        <div class="blocked-card">
            <h4><i class="fas fa-redo"></i> Attempts Exhausted</h4>
            <div class="attempts-info">
                <div class="attempt-stat">
                    <div class="attempt-stat-value">{{ $details['attempts_used'] }}</div>
                    <div class="attempt-stat-label">Attempts Used</div>
                </div>
                <div class="attempt-stat">
                    <div class="attempt-stat-value">{{ $details['max_attempts'] }}</div>
                    <div class="attempt-stat-label">Max Allowed</div>
                </div>
            </div>
            <p class="text-muted">You have used all available attempts for this assessment.</p>
        </div>
        @endif

        @if(isset($details['best_attempt']))
        <div class="blocked-card">
            <h4><i class="fas fa-trophy"></i> Your Best Score</h4>
            <div class="attempts-info">
                <div class="attempt-stat">
                    <div class="attempt-stat-value text-success">{{ number_format($details['best_attempt']->percentage, 1) }}%</div>
                    <div class="attempt-stat-label">Best Score</div>
                </div>
                <div class="attempt-stat">
                    <div class="attempt-stat-value">{{ $details['best_attempt']->grade_letter }}</div>
                    <div class="attempt-stat-label">Grade</div>
                </div>
            </div>
            <a href="{{ route('courses.modules.assessment.results', [$course, $module, $details['best_attempt']]) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="fas fa-eye me-1"></i>View Results
            </a>
        </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Module
            </a>
        </div>
    </div>
</div>
@endsection
