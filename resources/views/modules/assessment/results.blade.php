@extends('layouts.app')

@section('title', 'Assessment Results - ' . $module->module_title . ' - EPAS-E')

@push('styles')
<style>
.results-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Results Header */
.results-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

.results-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2.5rem;
}

.results-icon.passed {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.results-icon.failed {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.results-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.results-title.passed {
    color: #10b981;
}

.results-title.failed {
    color: #ef4444;
}

.results-subtitle {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

/* Score Card */
.score-card {
    background: var(--background);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.score-item {
    text-align: center;
}

.score-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
}

.score-value.percentage {
    color: var(--primary);
}

.score-label {
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Grade Badge */
.grade-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.grade-badge.A { background: #10b981; color: white; }
.grade-badge.B { background: #6d9773; color: white; }
.grade-badge.C { background: #f59e0b; color: white; }
.grade-badge.D { background: #f97316; color: white; }
.grade-badge.F { background: #ef4444; color: white; }

/* Results Actions */
.results-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Question Review */
.review-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.review-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.review-body {
    padding: 1.5rem;
}

/* Question Review Card */
.review-card {
    border: 1px solid var(--border);
    border-radius: calc(var(--border-radius) / 2);
    margin-bottom: 1rem;
    overflow: hidden;
}

.review-card:last-child {
    margin-bottom: 0;
}

.review-card-header {
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.review-card-header.correct {
    background: rgba(16, 185, 129, 0.1);
    border-bottom: 1px solid rgba(16, 185, 129, 0.2);
}

.review-card-header.incorrect {
    background: rgba(239, 68, 68, 0.1);
    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
}

.review-card-header.partial {
    background: rgba(245, 158, 11, 0.1);
    border-bottom: 1px solid rgba(245, 158, 11, 0.2);
}

.review-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
}

.review-status.correct { color: #10b981; }
.review-status.incorrect { color: #ef4444; }
.review-status.partial { color: #f59e0b; }

.review-points {
    font-weight: 600;
    color: var(--text-secondary);
}

.review-card-body {
    padding: 1rem;
}

.review-question {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.review-answers {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.review-answer {
    padding: 0.5rem 0.75rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

.review-answer.user-answer {
    background: rgba(239, 68, 68, 0.1);
    border-left: 3px solid #ef4444;
}

.review-answer.user-answer.correct {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid #10b981;
}

.review-answer.correct-answer {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid #10b981;
}

.review-answer-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

/* History Section */
.history-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.history-table {
    width: 100%;
}

.history-table th,
.history-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.history-table th {
    background: var(--background);
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.history-table tr:last-child td {
    border-bottom: none;
}

.history-table tr.current {
    background: rgba(var(--primary-rgb), 0.05);
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.passed {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.status-badge.failed {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

/* Responsive */
@media (max-width: 768px) {
    .score-card {
        grid-template-columns: repeat(2, 1fr);
    }

    .results-actions {
        flex-direction: column;
    }

    .results-actions .btn {
        width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="results-container">
        {{-- Results Header --}}
        <div class="results-header">
            <div class="results-icon {{ $submission->passed ? 'passed' : 'failed' }}">
                <i class="fas {{ $submission->passed ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
            </div>
            <h1 class="results-title {{ $submission->passed ? 'passed' : 'failed' }}">
                {{ $submission->passed ? 'Congratulations!' : 'Assessment Not Passed' }}
            </h1>
            <p class="results-subtitle">
                {{ $module->module_number }}: {{ $module->module_title }}
            </p>

            <span class="grade-badge {{ $submission->grade_letter }}">Grade: {{ $submission->grade_letter }}</span>

            {{-- Score Card --}}
            <div class="score-card">
                <div class="score-item">
                    <div class="score-value percentage">{{ number_format($submission->percentage, 1) }}%</div>
                    <div class="score-label">Score</div>
                </div>
                <div class="score-item">
                    <div class="score-value">{{ $submission->score }}/{{ $submission->total_points }}</div>
                    <div class="score-label">Points</div>
                </div>
                <div class="score-item">
                    <div class="score-value">{{ $module->assessment_passing_score }}%</div>
                    <div class="score-label">Passing</div>
                </div>
                <div class="score-item">
                    <div class="score-value">{{ $submission->formatted_time_taken }}</div>
                    <div class="score-label">Time Taken</div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="results-actions">
                <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Module
                </a>
                @if($canRetake)
                <a href="{{ route('courses.modules.assessment.show', [$course, $module]) }}" class="btn btn-primary">
                    <i class="fas fa-redo me-1"></i>Retake Assessment
                </a>
                @endif
                @if($submission->passed)
                <a href="{{ route('courses.show', $course) }}" class="btn btn-success">
                    <i class="fas fa-graduation-cap me-1"></i>Continue Course
                </a>
                @endif
            </div>
        </div>

        {{-- Question Review (if showing answers is enabled) --}}
        @if($showAnswers)
        <div class="review-section">
            <div class="review-header">
                <h3><i class="fas fa-list-check text-primary"></i> Question Review</h3>
                <span class="text-muted">{{ $gradingDetails->where('is_correct', true)->count() }}/{{ $questions->count() }} Correct</span>
            </div>
            <div class="review-body">
                @foreach($questions as $index => $item)
                @php
                    $question = $item['question'];
                    $detail = $gradingDetails->get($question->id);
                    $isCorrect = $detail['is_correct'] ?? false;
                    $isPartial = $detail['is_partial'] ?? false;
                    $status = $isCorrect ? 'correct' : ($isPartial ? 'partial' : 'incorrect');
                @endphp
                <div class="review-card">
                    <div class="review-card-header {{ $status }}">
                        <span class="review-status {{ $status }}">
                            @if($isCorrect)
                                <i class="fas fa-check-circle"></i> Correct
                            @elseif($isPartial)
                                <i class="fas fa-minus-circle"></i> Partial Credit
                            @else
                                <i class="fas fa-times-circle"></i> Incorrect
                            @endif
                        </span>
                        <span class="review-points">
                            {{ $detail['points_earned'] ?? 0 }}/{{ $detail['points_possible'] ?? 1 }} points
                        </span>
                    </div>
                    <div class="review-card-body">
                        <div class="review-question">
                            Q{{ $index + 1 }}. {{ $question->question_text }}
                        </div>
                        <div class="review-answers">
                            <div class="review-answer user-answer {{ $isCorrect ? 'correct' : '' }}">
                                <div class="review-answer-label">Your Answer</div>
                                @if(is_array($detail['user_answer'] ?? null))
                                    {{ implode(', ', $detail['user_answer']) }}
                                @else
                                    {{ $detail['user_answer'] ?? 'No answer' }}
                                @endif
                            </div>
                            @if(!$isCorrect)
                            <div class="review-answer correct-answer">
                                <div class="review-answer-label">Correct Answer</div>
                                {{ $detail['correct_answer'] ?? '-' }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="review-section">
            <div class="review-header">
                <h3><i class="fas fa-eye-slash text-muted"></i> Question Review</h3>
            </div>
            <div class="review-body text-center text-muted py-4">
                <i class="fas fa-lock fa-2x mb-2"></i>
                <p>Correct answers are not shown for this assessment.</p>
            </div>
        </div>
        @endif

        {{-- Attempt History --}}
        @if($history->count() > 1)
        <div class="history-section">
            <div class="review-header">
                <h3><i class="fas fa-history text-primary"></i> Attempt History</h3>
            </div>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Attempt</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Time Taken</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $attempt)
                    <tr class="{{ $attempt->id === $submission->id ? 'current' : '' }}">
                        <td>#{{ $attempt->attempt_number }}</td>
                        <td>{{ number_format($attempt->percentage, 1) }}%</td>
                        <td>
                            <span class="status-badge {{ $attempt->passed ? 'passed' : 'failed' }}">
                                {{ $attempt->passed ? 'Passed' : 'Failed' }}
                            </span>
                        </td>
                        <td>{{ $attempt->formatted_time_taken }}</td>
                        <td>{{ $attempt->completed_at?->format('M d, Y H:i') ?? '-' }}</td>
                        <td>
                            @if($attempt->id !== $submission->id)
                            <a href="{{ route('courses.modules.assessment.results', [$course, $module, $attempt]) }}"
                               class="btn btn-outline-secondary btn-sm">
                                View
                            </a>
                            @else
                            <span class="text-muted">Current</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
