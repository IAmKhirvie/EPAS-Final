@extends('layouts.app')

@section('title', 'Final Assessment - ' . $module->module_title . ' - EPAS-E')

@push('styles')
<style>
.assessment-container {
    max-width: 900px;
    margin: 0 auto;
}

.assessment-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.assessment-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.assessment-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.assessment-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.assessment-meta-item i {
    color: var(--primary);
}

/* Timer */
.assessment-timer {
    position: sticky;
    top: 80px;
    z-index: 100;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.timer-display {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
}

.timer-display i {
    color: var(--primary);
}

.timer-display.warning {
    color: #f59e0b;
}

.timer-display.danger {
    color: #ef4444;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.progress-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.question-progress {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* Question Card */
.question-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.question-card-header {
    background: var(--background);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.question-number {
    font-weight: 700;
    color: var(--text-primary);
}

.question-points {
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.question-card-body {
    padding: 1.5rem;
}

.question-text {
    font-size: 1.05rem;
    color: var(--text-primary);
    margin-bottom: 1.25rem;
    line-height: 1.6;
}

.question-source {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
}

/* Answer Options */
.answer-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.answer-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border: 2px solid var(--border);
    border-radius: calc(var(--border-radius) / 2);
    cursor: pointer;
    transition: all 0.2s ease;
}

.answer-option:hover {
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.05);
}

.answer-option.selected {
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
}

.answer-option input[type="radio"],
.answer-option input[type="checkbox"] {
    margin-top: 0.2rem;
}

.answer-option-text {
    flex: 1;
}

/* Text Inputs */
.answer-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: calc(var(--border-radius) / 2);
    font-size: 1rem;
    transition: all 0.2s ease;
}

.answer-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
}

textarea.answer-input {
    min-height: 120px;
    resize: vertical;
}

/* True/False */
.tf-options {
    display: flex;
    gap: 1rem;
}

.tf-option {
    flex: 1;
    padding: 1rem;
    text-align: center;
    border: 2px solid var(--border);
    border-radius: calc(var(--border-radius) / 2);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
}

.tf-option:hover {
    border-color: var(--primary);
}

.tf-option.selected {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}

.tf-option.true {
    color: #10b981;
}

.tf-option.false {
    color: #ef4444;
}

.tf-option.true.selected {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.tf-option.false.selected {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

/* Submit Section */
.assessment-footer {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-top: 2rem;
    text-align: center;
}

.assessment-footer p {
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.btn-submit-assessment {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .assessment-timer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .tf-options {
        flex-direction: column;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="assessment-container">
        {{-- Header --}}
        <div class="assessment-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1><i class="fas fa-clipboard-check me-2 text-primary"></i>Final Assessment</h1>
                    <p class="mb-2 text-muted">{{ $module->module_number }}: {{ $module->module_title }}</p>
                </div>
                <a href="{{ route('courses.modules.show', [$course, $module]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Exit
                </a>
            </div>
            <div class="assessment-meta">
                <div class="assessment-meta-item">
                    <i class="fas fa-question-circle"></i>
                    <span>{{ $questions->count() }} Questions</span>
                </div>
                <div class="assessment-meta-item">
                    <i class="fas fa-star"></i>
                    <span>{{ $submission->total_points }} Points</span>
                </div>
                <div class="assessment-meta-item">
                    <i class="fas fa-percentage"></i>
                    <span>{{ $module->assessment_passing_score }}% to Pass</span>
                </div>
                @if($timeLimit)
                <div class="assessment-meta-item">
                    <i class="fas fa-clock"></i>
                    <span>{{ $timeLimit }} Minutes</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Timer Bar --}}
        <form id="assessmentForm" action="{{ route('courses.modules.assessment.submit', [$course, $module]) }}" method="POST">
            @csrf

            <div class="assessment-timer">
                @if($timeLimit)
                <div class="timer-display" id="timerDisplay">
                    <i class="fas fa-hourglass-half"></i>
                    <span id="timerText">--:--</span>
                </div>
                @else
                <div class="timer-display">
                    <i class="fas fa-infinity"></i>
                    <span>No Time Limit</span>
                </div>
                @endif
                <div class="progress-info">
                    <span class="question-progress">
                        <span id="answeredCount">0</span> / {{ $questions->count() }} Answered
                    </span>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="saveProgress()">
                        <i class="fas fa-save me-1"></i>Save Progress
                    </button>
                </div>
            </div>

            {{-- Questions --}}
            @foreach($questions as $index => $item)
            @php
                $question = $item['question'];
                $savedAnswer = $savedAnswers[$question->id] ?? null;
            @endphp
            <div class="question-card" id="question-{{ $question->id }}">
                <div class="question-card-header">
                    <span class="question-number">Question {{ $index + 1 }}</span>
                    <span class="question-points">{{ $question->points ?? 1 }} {{ Str::plural('point', $question->points ?? 1) }}</span>
                </div>
                <div class="question-card-body">
                    <div class="question-source">
                        From: {{ $item['self_check']->title ?? 'Self Check' }} | {{ $item['sheet']->title ?? 'Information Sheet' }}
                    </div>
                    <div class="question-text">{!! nl2br(e($question->question_text)) !!}</div>

                    {{-- Render based on question type --}}
                    @switch($question->question_type)
                        @case('multiple_choice')
                        @case('image_choice')
                            <div class="answer-options">
                                @foreach($question->options['choices'] ?? [] as $optIndex => $choice)
                                <label class="answer-option {{ $savedAnswer == $optIndex ? 'selected' : '' }}"
                                       onclick="selectOption(this, '{{ $question->id }}', {{ $optIndex }})">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optIndex }}"
                                           {{ $savedAnswer == $optIndex ? 'checked' : '' }} style="display: none;">
                                    <span class="answer-option-text">{{ $choice }}</span>
                                </label>
                                @endforeach
                            </div>
                            @break

                        @case('multiple_select')
                            @php $selectedAnswers = is_array($savedAnswer) ? $savedAnswer : []; @endphp
                            <div class="answer-options">
                                @foreach($question->options['choices'] ?? [] as $optIndex => $choice)
                                <label class="answer-option {{ in_array($optIndex, $selectedAnswers) ? 'selected' : '' }}"
                                       onclick="toggleMultiSelect(this, '{{ $question->id }}', {{ $optIndex }})">
                                    <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $optIndex }}"
                                           {{ in_array($optIndex, $selectedAnswers) ? 'checked' : '' }} style="display: none;">
                                    <span class="answer-option-text">{{ $choice }}</span>
                                </label>
                                @endforeach
                            </div>
                            @break

                        @case('true_false')
                            <div class="tf-options">
                                <div class="tf-option true {{ $savedAnswer === 'true' || $savedAnswer === true || $savedAnswer === '1' ? 'selected' : '' }}"
                                     onclick="selectTrueFalse(this, '{{ $question->id }}', 'true')">
                                    <i class="fas fa-check me-1"></i> True
                                </div>
                                <div class="tf-option false {{ $savedAnswer === 'false' || $savedAnswer === false || $savedAnswer === '0' ? 'selected' : '' }}"
                                     onclick="selectTrueFalse(this, '{{ $question->id }}', 'false')">
                                    <i class="fas fa-times me-1"></i> False
                                </div>
                            </div>
                            <input type="hidden" name="answers[{{ $question->id }}]" id="tf-{{ $question->id }}" value="{{ $savedAnswer }}">
                            @break

                        @case('fill_blank')
                        @case('short_answer')
                            <input type="text" class="answer-input" name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}" placeholder="Type your answer here..."
                                   onchange="updateAnsweredCount()">
                            @break

                        @case('numeric')
                        @case('slider')
                            <input type="number" class="answer-input" name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}" placeholder="Enter a number..."
                                   step="any" onchange="updateAnsweredCount()">
                            @break

                        @case('essay')
                            <textarea class="answer-input" name="answers[{{ $question->id }}]"
                                      placeholder="Write your answer here..." onchange="updateAnsweredCount()">{{ $savedAnswer }}</textarea>
                            @break

                        @default
                            <input type="text" class="answer-input" name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}" placeholder="Type your answer..."
                                   onchange="updateAnsweredCount()">
                    @endswitch
                </div>
            </div>
            @endforeach

            {{-- Submit Section --}}
            <div class="assessment-footer">
                <p><i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    Please review your answers before submitting. You cannot change your answers after submission.
                </p>
                <button type="submit" class="btn btn-primary btn-submit-assessment" onclick="return confirmSubmit()">
                    <i class="fas fa-paper-plane me-2"></i>Submit Assessment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const totalQuestions = {{ $questions->count() }};
const timeLimit = {{ $timeLimit ?? 'null' }};
const remainingSeconds = {{ $remainingTime ?? 'null' }};
const saveUrl = "{{ route('courses.modules.assessment.save', [$course, $module]) }}";
const csrfToken = "{{ csrf_token() }}";

let timerInterval;

document.addEventListener('DOMContentLoaded', function() {
    updateAnsweredCount();

    if (timeLimit && remainingSeconds !== null) {
        startTimer(remainingSeconds);
    }
});

function startTimer(seconds) {
    const timerDisplay = document.getElementById('timerDisplay');
    const timerText = document.getElementById('timerText');

    function updateTimer() {
        if (seconds <= 0) {
            clearInterval(timerInterval);
            timerText.textContent = '00:00';
            alert('Time is up! Your assessment will be submitted automatically.');
            document.getElementById('assessmentForm').submit();
            return;
        }

        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerText.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

        // Warning at 5 minutes
        if (seconds <= 300 && seconds > 60) {
            timerDisplay.className = 'timer-display warning';
        } else if (seconds <= 60) {
            timerDisplay.className = 'timer-display danger';
        }

        seconds--;
    }

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}

function selectOption(element, questionId, value) {
    // Remove selected from siblings
    element.parentNode.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input').checked = true;
    updateAnsweredCount();
}

function toggleMultiSelect(element, questionId, value) {
    element.classList.toggle('selected');
    const checkbox = element.querySelector('input');
    checkbox.checked = !checkbox.checked;
    updateAnsweredCount();
}

function selectTrueFalse(element, questionId, value) {
    const parent = element.parentNode;
    parent.querySelectorAll('.tf-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('tf-' + questionId).value = value;
    updateAnsweredCount();
}

function updateAnsweredCount() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);
    let answered = 0;

    // Count questions with answers
    const questionCards = document.querySelectorAll('.question-card');
    questionCards.forEach(card => {
        const inputs = card.querySelectorAll('input[name^="answers"], textarea[name^="answers"]');
        let hasAnswer = false;

        inputs.forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked) hasAnswer = true;
            } else if (input.type === 'hidden') {
                if (input.value && input.value !== '') hasAnswer = true;
            } else {
                if (input.value && input.value.trim() !== '') hasAnswer = true;
            }
        });

        if (hasAnswer) answered++;
    });

    document.getElementById('answeredCount').textContent = answered;
}

function saveProgress() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);

    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Progress saved successfully!');
        } else {
            alert('Failed to save progress. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save progress. Please try again.');
    });
}

function confirmSubmit() {
    const answered = parseInt(document.getElementById('answeredCount').textContent);
    const unanswered = totalQuestions - answered;

    if (unanswered > 0) {
        return confirm(`You have ${unanswered} unanswered question(s). Are you sure you want to submit?`);
    }

    return confirm('Are you sure you want to submit your assessment? This action cannot be undone.');
}

// Auto-save every 30 seconds
setInterval(function() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);

    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    }).catch(console.error);
}, 30000);

// Warn before leaving
window.addEventListener('beforeunload', function(e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>
@endpush
