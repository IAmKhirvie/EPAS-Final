@extends('layouts.app')

@section('title', 'Test Results - ' . $test->title)

@section('content')
<div class="container py-4">
    {{-- Results Header --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1">{{ $test->title }}</h4>
                    <p class="text-muted mb-0">{{ $module->module_title }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Module
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Score Card --}}
    <div class="card shadow-sm border-0 mb-4 {{ $submission->passed ? 'border-success' : 'border-danger' }}" style="border-width: 2px !important;">
        <div class="card-body text-center py-5">
            <div class="mb-3">
                @if($submission->passed)
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                @else
                    <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                @endif
            </div>

            <h2 class="mb-2">
                <span class="{{ $submission->passed ? 'text-success' : 'text-danger' }}">
                    {{ number_format($submission->percentage, 1) }}%
                </span>
            </h2>

            <p class="lead mb-3">
                @if($submission->passed)
                    <span class="badge bg-success fs-5">PASSED</span>
                @else
                    <span class="badge bg-danger fs-5">FAILED</span>
                @endif
            </p>

            <div class="row justify-content-center">
                <div class="col-auto">
                    <div class="text-muted">
                        <strong>Score:</strong> {{ $submission->score }} / {{ $submission->total_points }} points
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-muted">
                        <strong>Grade:</strong> {{ $submission->grade_letter }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-muted">
                        <strong>Time:</strong> {{ $submission->formatted_time_taken }}
                    </div>
                </div>
                <div class="col-auto">
                    <div class="text-muted">
                        <strong>Passing:</strong> {{ $test->passing_score }}%
                    </div>
                </div>
            </div>

            @if($canRetake && !$submission->passed)
                <div class="mt-4">
                    <a href="{{ route('courses.modules.competency-tests.show', [$course, $module, $test]) }}" class="btn btn-primary">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Part Scores --}}
    @if(count($questionsByPart) > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Score by Part</h5>
            </div>
            <div class="card-body">
                @foreach($questionsByPart as $partKey => $partData)
                    @php
                        $partPercentage = $partData['total'] > 0 ? ($partData['score'] / $partData['total']) * 100 : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $partData['part']['name'] ?? 'Part ' . ($loop->index + 1) }}</span>
                            <span>{{ $partData['score'] }} / {{ $partData['total'] }} ({{ number_format($partPercentage, 1) }}%)</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $partPercentage >= $test->passing_score ? 'bg-success' : 'bg-danger' }}"
                                 style="width: {{ $partPercentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Question Review --}}
    @if($showAnswers)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Question Review</h5>
            </div>
            <div class="card-body">
                @php $questionNumber = 1; @endphp
                @foreach($questions as $question)
                    @php
                        $detail = $gradingDetails[$question->id] ?? null;
                        $isCorrect = $detail['is_correct'] ?? false;
                        $isPartial = $detail['is_partial'] ?? false;
                        $userAnswer = $detail['user_answer'] ?? null;
                    @endphp
                    <div class="question-review mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $isCorrect ? 'bg-success' : ($isPartial ? 'bg-warning' : 'bg-danger') }} me-2">
                                    {{ $questionNumber }}
                                </span>
                                @if($isCorrect)
                                    <i class="fas fa-check text-success"></i>
                                @elseif($isPartial)
                                    <i class="fas fa-minus text-warning"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                            </div>
                            <span class="badge bg-light text-dark">
                                {{ $detail['points_earned'] ?? 0 }} / {{ $question->points }} pts
                            </span>
                        </div>

                        <p class="mb-2"><strong>{{ $question->question_text }}</strong></p>

                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Your Answer:</small>
                                <p class="mb-0 {{ $isCorrect ? 'text-success' : 'text-danger' }}">
                                    @if(is_array($userAnswer))
                                        {{ implode(', ', $userAnswer) }}
                                    @elseif($userAnswer !== null && $userAnswer !== '')
                                        {{ $userAnswer }}
                                    @else
                                        <em class="text-muted">No answer</em>
                                    @endif
                                </p>
                            </div>
                            @if(!$isCorrect)
                                <div class="col-md-6">
                                    <small class="text-muted">Correct Answer:</small>
                                    <p class="mb-0 text-success">{{ $question->correct_answer }}</p>
                                </div>
                            @endif
                        </div>

                        @if($question->explanation)
                            <div class="mt-2 p-2 bg-light rounded">
                                <small class="text-muted"><i class="fas fa-lightbulb me-1"></i> {{ $question->explanation }}</small>
                            </div>
                        @endif
                    </div>
                    @php $questionNumber++; @endphp
                @endforeach
            </div>
        </div>
    @endif

    {{-- Attempt History --}}
    @if($history->count() > 1)
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Attempt History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Attempt</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $attempt)
                                <tr class="{{ $attempt->id === $submission->id ? 'table-active' : '' }}">
                                    <td>{{ $attempt->attempt_number }}</td>
                                    <td>{{ $attempt->score }} / {{ $attempt->total_points }}</td>
                                    <td>{{ number_format($attempt->percentage, 1) }}%</td>
                                    <td>
                                        @if($attempt->passed)
                                            <span class="badge bg-success">Passed</span>
                                        @elseif($attempt->status === 'timed_out')
                                            <span class="badge bg-warning">Timed Out</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ $attempt->formatted_time_taken }}</td>
                                    <td>{{ $attempt->completed_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
