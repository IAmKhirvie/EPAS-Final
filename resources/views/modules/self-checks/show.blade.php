@extends('layouts.app')

@section('title', $selfCheck->title)

@section('content')
<div class="content-area sc-content-reset">
    @php
        $breadcrumbItems = [];
        if (in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR])) {
            $breadcrumbItems[] = ['label' => 'Content', 'url' => route('content.management')];
        } else {
            $course = $selfCheck->informationSheet->module->course ?? null;
            $module = $selfCheck->informationSheet->module ?? null;
            if ($course) {
                $breadcrumbItems[] = ['label' => $course->course_code, 'url' => route('courses.show', $course)];
            }
            if ($module) {
                $breadcrumbItems[] = ['label' => $module->title, 'url' => route('courses.modules.show', [$course, $module])];
            }
        }
        $breadcrumbItems[] = ['label' => $selfCheck->title];
    @endphp
    <x-breadcrumb :items="$breadcrumbItems" />

    {{-- Header --}}
    <div class="sc-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-clipboard-check me-2"></i>{{ $selfCheck->title }}
                    @if($selfCheck->file_path)
                    <a href="{{ route('self-checks.download', $selfCheck) }}" class="badge bg-secondary text-decoration-none ms-2" style="font-size: 0.6em; vertical-align: middle;">
                        <i class="fas fa-paperclip me-1"></i>{{ $selfCheck->original_filename }}
                    </a>
                    @endif
                </h4>
                <p class="text-muted mb-0">{{ $selfCheck->check_number }}</p>
            </div>
            @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('self-checks.edit', [$selfCheck->informationSheet, $selfCheck]) }}">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('self-checks.destroy', [$selfCheck->informationSheet, $selfCheck]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this self-check?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash me-2"></i>Delete
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>
        @if($selfCheck->description)
        <p class="mt-2 mb-0" style="color: var(--cb-text-label, #495057);">{{ $selfCheck->description }}</p>
        @endif
    </div>

    @php
        $attemptCount = 0;
        $attemptsExhausted = false;
        if (auth()->user()->role === \App\Constants\Roles::STUDENT) {
            $attemptCount = $selfCheck->submissions()->where('user_id', auth()->id())->count();
            $attemptsExhausted = $selfCheck->max_attempts !== null && $attemptCount >= $selfCheck->max_attempts;
        }
    @endphp

    {{-- Two-column: Questions + Sidebar --}}
    <div class="sc-layout">
        {{-- Left: Questions (full-width feel) --}}
        <div class="sc-questions">

            {{-- Instructions --}}
            @if($selfCheck->instructions)
            <div class="sc-instructions">
                <i class="fas fa-info-circle me-1"></i>
                {{ $selfCheck->instructions }}
            </div>
            @endif

            {{-- Document Viewer --}}
            @include('components.document-viewer', [
                'documentContent' => $selfCheck->document_content,
                'filePath' => $selfCheck->file_path,
                'originalFilename' => $selfCheck->original_filename,
                'downloadRoute' => route('self-checks.download', $selfCheck),
            ])

            {{-- ═══════ Student: Quiz Questions ═══════ --}}
            @if(auth()->user()->role === \App\Constants\Roles::STUDENT)

                @if($selfCheck->due_date && now()->gt($selfCheck->due_date))
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Deadline passed.</strong> This self-check was due {{ $selfCheck->due_date->format('M d, Y h:i A') }}. Submissions are no longer accepted.
                </div>
                @elseif($attemptsExhausted)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-ban me-2"></i>
                    <strong>No attempts remaining.</strong> You have used all {{ $selfCheck->max_attempts }} attempt(s) for this self-check.
                </div>
                @else

                <form action="{{ route('self-checks.submit', $selfCheck) }}" method="POST" id="selfCheckForm">
                    @csrf
                    @foreach($selfCheck->questions->sortBy('order') as $index => $question)
                    <div class="sc-question-card" data-question-type="{{ $question->question_type }}">
                        <div class="sc-question-card__header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="sc-question-card__number">{{ $index + 1 }}</span>
                                <span class="badge bg-{{ getQuestionTypeBadgeColor($question->question_type) }}">
                                    {{ formatQuestionType($question->question_type) }}
                                </span>
                            </div>
                            <span class="badge bg-primary">{{ $question->points }} pt(s)</span>
                        </div>
                        <div class="sc-question-card__body">
                            @if(!empty($question->options['question_image']))
                            <div class="text-center mb-3">
                                <img src="{{ $question->options['question_image'] }}" alt="Question Image" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                            @endif

                            <p class="fs-5 mb-3">
                                @if($question->question_type === 'fill_blank')
                                    {!! preg_replace('/___+/', '<span style="display:inline-block;border-bottom:2px solid #ffb902;min-width:100px;color:#ffb902;">________</span>', e($question->question_text)) !!}
                                @else
                                    {{ $question->question_text }}
                                @endif
                            </p>

                            @include('modules.self-checks.partials.question-input', ['question' => $question])
                        </div>
                    </div>
                    @endforeach
                </form>
                @endif

            @else
            {{-- ═══════ Instructor/Admin: Preview ═══════ --}}

            {{-- Reveal Answers Toggle --}}
            <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-light rounded">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="revealAnswersToggle" role="switch">
                    <label class="form-check-label fw-semibold" for="revealAnswersToggle">
                        <i class="fas fa-eye me-1"></i>Reveal Answers
                    </label>
                </div>
            </div>

            @foreach($selfCheck->questions->sortBy('order') as $index => $question)
            <div class="sc-question-card">
                <div class="sc-question-card__header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="sc-question-card__number">{{ $index + 1 }}</span>
                        <span class="badge bg-{{ getQuestionTypeBadgeColor($question->question_type) }}">
                            {{ formatQuestionType($question->question_type) }}
                        </span>
                    </div>
                    <span class="badge bg-primary">{{ $question->points }} pts</span>
                </div>
                <div class="sc-question-card__body">
                    @if(!empty($question->options['question_image']))
                    <div class="text-center mb-3">
                        <img src="{{ $question->options['question_image'] }}" alt="Question Image" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                    @endif

                    <p class="mb-2"><strong>{{ $question->question_text }}</strong></p>

                    @if($question->question_type === 'multiple_choice' || $question->question_type === 'image_choice')
                        @php
                            $rawOptions = $question->options ?? [];
                            $rawOptions = is_array($rawOptions) ? $rawOptions : (is_string($rawOptions) ? json_decode($rawOptions, true) ?? [] : []);
                            $options = array_filter($rawOptions, fn($v, $k) => is_int($k), ARRAY_FILTER_USE_BOTH);
                        @endphp
                        <div class="ms-3">
                            @foreach($options as $optIndex => $option)
                            <div class="answer-option {{ $question->correct_answer == $optIndex ? 'correct-answer' : '' }}" style="padding: 0.25rem 0;">
                                @if(is_array($option))
                                    {{ chr(65 + $optIndex) }}. {{ $option['label'] ?? 'Option' }}
                                    @if(!empty($option['image']))
                                        <img src="{{ $option['image'] }}" alt="Option" class="ms-2" style="max-height: 50px;">
                                    @endif
                                @else
                                    {{ chr(65 + $optIndex) }}. {{ $option }}
                                @endif
                                @if($question->correct_answer == $optIndex)
                                <span class="correct-indicator d-none">
                                    <i class="fas fa-check ms-2 text-success"></i>
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @elseif($question->question_type === 'matching')
                        @php $pairs = $question->options['pairs'] ?? []; @endphp
                        <div class="row ms-3 mt-2">
                            <div class="col-5">
                                <strong class="d-block mb-1" style="font-size: 0.8rem; color: #6c757d;">Column A</strong>
                                @foreach($pairs as $pair)
                                <div class="p-2 mb-1" style="border: 1px solid #dee2e6; border-radius: 6px;">{{ $pair['left'] }}</div>
                                @endforeach
                            </div>
                            <div class="col-2 text-center d-flex align-items-center justify-content-center">
                                <i class="fas fa-arrows-alt-h text-muted"></i>
                            </div>
                            <div class="col-5">
                                <strong class="d-block mb-1" style="font-size: 0.8rem; color: #6c757d;">Column B</strong>
                                @foreach($pairs as $pair)
                                <div class="p-2 mb-1 answer-reveal d-none" style="border: 1px solid #d4edda; border-radius: 6px; background: #f8fff9;">{{ $pair['right'] }}</div>
                                <div class="p-2 mb-1 answer-hide" style="border: 1px solid #dee2e6; border-radius: 6px;">{{ $pair['right'] }}</div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($question->question_type === 'ordering')
                        @php $items = $question->options['items'] ?? []; @endphp
                        <div class="ms-3 mt-2">
                            <strong class="d-block mb-1 answer-reveal d-none" style="font-size: 0.8rem; color: #6c757d;">Correct Order:</strong>
                            @foreach($items as $itemIndex => $item)
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-secondary me-2 answer-reveal d-none">{{ $itemIndex + 1 }}</span>
                                <span class="badge bg-light text-dark me-2 answer-hide">{{ $itemIndex + 1 }}</span>
                                {{ $item }}
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 ms-3 answer-reveal d-none" style="color: #198754;">
                            <i class="fas fa-check me-1"></i><strong>Answer:</strong> {{ $question->correct_answer }}
                        </p>
                        <p class="mb-0 ms-3 answer-hide text-muted">
                            <i class="fas fa-eye-slash me-1"></i><em>Answer hidden</em>
                        </p>
                    @endif

                    @if($question->explanation)
                    <div class="mt-2 ms-3 answer-reveal d-none" style="color: #0288d1; font-size: 0.85rem;">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Explanation:</strong> {{ $question->explanation }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Right: Sticky Sidebar --}}
        <div class="sc-sidebar">
            <div class="sc-sidebar__title">Self-Check Info</div>

            {{-- Test Details --}}
            <div class="sc-sidebar__group">
                <div class="sc-sidebar__label"><i class="fas fa-info-circle me-1"></i>Test Details</div>
                <div class="sc-sidebar__row">
                    <span>Questions</span>
                    <strong>{{ $selfCheck->questions->count() }}</strong>
                </div>
                <div class="sc-sidebar__row">
                    <span>Total Points</span>
                    <strong>{{ $selfCheck->total_points }}</strong>
                </div>
                @if($selfCheck->time_limit)
                <div class="sc-sidebar__row sc-timer-row" id="sidebarTimerRow">
                    <span><i class="fas fa-stopwatch me-1"></i>Timer</span>
                    <strong id="sidebarTimerDisplay" class="sc-timer-value">{{ $selfCheck->time_limit }}:00</strong>
                </div>
                @endif
                @if($selfCheck->due_date)
                <div class="sc-sidebar__row">
                    <span>Deadline</span>
                    <strong class="{{ now()->gt($selfCheck->due_date) ? 'text-danger' : '' }}">
                        {{ $selfCheck->due_date->format('M d, Y h:i A') }}
                    </strong>
                </div>
                @endif
                @if($selfCheck->passing_score)
                <div class="sc-sidebar__row">
                    <span>Passing Score</span>
                    <strong>{{ $selfCheck->passing_score }}%</strong>
                </div>
                @endif
                @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
                <div class="sc-sidebar__row">
                    <span>Attempts</span>
                    <strong>{{ $attemptCount }} / {{ $selfCheck->max_attempts ?? '∞' }}</strong>
                </div>
                @if($selfCheck->max_attempts !== null)
                <div class="sc-sidebar__row">
                    <span>Remaining</span>
                    <strong class="{{ ($selfCheck->max_attempts - $attemptCount) <= 1 ? 'text-danger' : 'text-success' }}">
                        {{ max(0, $selfCheck->max_attempts - $attemptCount) }}
                    </strong>
                </div>
                @endif
                @endif
            </div>

            {{-- Question Types --}}
            @php $typeCounts = $selfCheck->questions->groupBy('question_type')->map->count(); @endphp
            @if($typeCounts->count() > 0)
            <div class="sc-sidebar__group">
                <div class="sc-sidebar__label"><i class="fas fa-list me-1"></i>Question Types</div>
                @foreach($typeCounts as $type => $count)
                <div class="sc-sidebar__row">
                    <span>{{ formatQuestionType($type) }}</span>
                    <strong>{{ $count }}</strong>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Submit Button (students only) --}}
            @if(auth()->user()->role === \App\Constants\Roles::STUDENT && !($selfCheck->due_date && now()->gt($selfCheck->due_date)) && !($attemptsExhausted))
            <div class="sc-sidebar__group">
                <div class="quiz-progress mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold">Progress</small>
                        <small id="quizProgressText">0 / {{ $selfCheck->questions->count() }}</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" id="quizProgressBar" style="width: 0%"></div>
                    </div>
                </div>
                <button type="button" onclick="validateAndSubmitQuiz()" class="btn btn-success w-100" id="quizSubmitBtn">
                    <i class="fas fa-paper-plane me-1"></i>Submit Answers
                </button>
                <small class="text-muted d-block text-center mt-1" id="quizProgressHint">Answer all questions first</small>
            </div>
            <script>
            function updateQuizProgress() {
                var total = document.querySelectorAll('.sc-question-card').length;
                var answered = 0;
                document.querySelectorAll('.sc-question-card').forEach(function(card) {
                    var inputs = card.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, input[type="hidden"][value]:not([value=""])');
                    var textInputs = card.querySelectorAll('input[type="text"], input[type="number"], textarea');
                    var hasText = false;
                    textInputs.forEach(function(t) { if (t.value.trim()) hasText = true; });
                    if (inputs.length > 0 || hasText) answered++;
                });
                var pct = total > 0 ? Math.round((answered / total) * 100) : 0;
                document.getElementById('quizProgressBar').style.width = pct + '%';
                document.getElementById('quizProgressText').textContent = answered + ' / ' + total;
                var hint = document.getElementById('quizProgressHint');
                if (answered === total) {
                    hint.textContent = 'All questions answered!';
                    hint.classList.remove('text-muted');
                    hint.classList.add('text-success');
                } else {
                    hint.textContent = (total - answered) + ' question(s) remaining';
                    hint.classList.remove('text-success');
                    hint.classList.add('text-muted');
                }
            }
            function validateAndSubmitQuiz() {
                var total = document.querySelectorAll('.sc-question-card').length;
                var answered = 0;
                var firstUnanswered = null;
                document.querySelectorAll('.sc-question-card').forEach(function(card, i) {
                    var inputs = card.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked, input[type="hidden"][value]:not([value=""])');
                    var textInputs = card.querySelectorAll('input[type="text"], input[type="number"], textarea');
                    var hasText = false;
                    textInputs.forEach(function(t) { if (t.value.trim()) hasText = true; });
                    if (inputs.length > 0 || hasText) {
                        answered++;
                    } else if (!firstUnanswered) {
                        firstUnanswered = card;
                    }
                });
                if (answered < total) {
                    if (firstUnanswered) {
                        firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstUnanswered.style.outline = '2px solid #dc3545';
                        setTimeout(function() { firstUnanswered.style.outline = ''; }, 3000);
                    }
                    if (!confirm('You have ' + (total - answered) + ' unanswered question(s). Submit anyway?')) return;
                }
                document.getElementById('selfCheckForm').submit();
            }
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('selfCheckForm').addEventListener('input', updateQuizProgress);
                document.getElementById('selfCheckForm').addEventListener('change', updateQuizProgress);
                document.getElementById('selfCheckForm').addEventListener('click', function() { setTimeout(updateQuizProgress, 100); });
                updateQuizProgress();
            });
            </script>
            @endif

            {{-- Submissions (Instructors/Admins) --}}
            @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
            @php
                // Group by student, show latest submission per student
                $latestByStudent = $selfCheck->submissions
                    ->sortByDesc('created_at')
                    ->groupBy('user_id')
                    ->map(function ($group) {
                        return (object) [
                            'latest' => $group->first(),
                            'attempts' => $group->count(),
                            'user' => $group->first()->user,
                        ];
                    });
            @endphp
            <div class="sc-sidebar__group">
                <div class="sc-sidebar__label"><i class="fas fa-users me-1"></i>Submissions ({{ $latestByStudent->count() }} students)</div>
                @if($latestByStudent->count() > 0)
                    @foreach($latestByStudent->take(5) as $entry)
                    <div class="sc-sidebar__row">
                        <span>
                            {{ $entry->user->first_name }} {{ $entry->user->last_name }}
                            @if($entry->attempts > 1)
                            <small class="text-muted">({{ $entry->attempts }}x)</small>
                            @endif
                        </span>
                        <span class="badge bg-{{ $entry->latest->passed ? 'success' : 'danger' }}">{{ number_format($entry->latest->percentage, 1) }}%</span>
                    </div>
                    @endforeach
                    @if($latestByStudent->count() > 5)
                    <div class="text-center text-muted" style="font-size: 0.8rem; padding: 0.25rem;">
                        + {{ $latestByStudent->count() - 5 }} more students
                    </div>
                    @endif
                @else
                <div class="text-muted" style="font-size: 0.85rem;">No submissions yet</div>
                @endif
            </div>
            @endif

            {{-- Back --}}
            @php
                $backModule = $selfCheck->informationSheet->module ?? null;
                $backCourse = $backModule?->course ?? null;
            @endphp
            @if($backModule && $backCourse)
            <a href="{{ route('courses.modules.show', [$backCourse, $backModule]) }}"
               class="btn btn-outline-secondary w-100 btn-sm mt-auto">
                <i class="fas fa-arrow-left me-1"></i>Back to Module
            </a>
            @endif
        </div>
    </div>
</div>

@push('styles')
@include('components.document-viewer-css')
<style>
/* Strip the content-area visual container but keep flex/overflow structure */
.sc-content-reset {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
}

/* ═══════ Self-Check Layout ═══════ */
.sc-header {
    background: var(--cb-surface, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
}
.sc-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.25rem;
    align-items: start;
}
.sc-questions {
    min-width: 0;
    overflow: hidden;
}
.sc-instructions {
    background: #fff8e1;
    border-left: 4px solid #ffb902;
    border-radius: 6px;
    padding: 0.85rem 1rem;
    color: #bb8954;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

/* Question Cards — lightweight, no heavy container */
.sc-question-card {
    background: var(--cb-surface, #fff);
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    margin-bottom: 1rem;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.sc-question-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.sc-question-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.25rem;
    background: var(--cb-surface-alt, #f8f9fa);
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.sc-question-card__number {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary, #4361ee);
    color: #fff;
    border-radius: 50%;
    font-weight: 700;
    font-size: 0.8rem;
}
.sc-question-card__body {
    padding: 1.25rem;
}

/* Sticky Sidebar */
.sc-sidebar {
    position: sticky;
    top: 1rem;
    background: var(--cb-surface, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 2rem);
    overflow-y: auto;
}
.sc-sidebar__title {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--cb-text-hint, #6c757d);
    padding-bottom: 0.5rem;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid var(--cb-border, #e9ecef);
}
.sc-sidebar__group {
    margin-bottom: 1rem;
}
.sc-sidebar__label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cb-text-hint, #6c757d);
    margin-bottom: 0.5rem;
}
.sc-sidebar__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    font-size: 0.85rem;
    border-bottom: 1px solid rgba(0,0,0,0.04);
}
.sc-sidebar__row:last-child {
    border-bottom: none;
}

/* Sidebar Timer */
.sc-timer-row {
    padding: 0.5rem 0 !important;
    transition: background 0.4s, border-color 0.4s;
    border-radius: 6px;
    margin: 0 -0.5rem;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}
.sc-timer-value {
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    letter-spacing: 0.04em;
    transition: color 0.4s;
}
.sc-timer-row.timer-green .sc-timer-value { color: #198754; }
.sc-timer-row.timer-yellow { background: rgba(255, 193, 7, 0.1); }
.sc-timer-row.timer-yellow .sc-timer-value { color: #b8860b; }
.sc-timer-row.timer-orange { background: rgba(253, 126, 20, 0.12); }
.sc-timer-row.timer-orange .sc-timer-value { color: #e65100; }
.sc-timer-row.timer-red { background: rgba(220, 53, 69, 0.1); animation: timer-pulse 1s infinite; }
.sc-timer-row.timer-red .sc-timer-value { color: #dc3545; font-weight: 800; }
@keyframes timer-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

/* Dark mode */
.dark-mode .sc-header,
.dark-mode .sc-question-card,
.dark-mode .sc-sidebar { background: var(--card-bg); color: var(--card-text); }
.dark-mode .sc-question-card__header { background: rgba(255,255,255,0.03); }
.dark-mode .sc-instructions { background: rgba(33,150,243,0.1); color: #90caf9; }

/* Responsive */
@media (max-width: 992px) {
    .sc-layout { grid-template-columns: 1fr; }
    .sc-sidebar { position: static; max-height: none; order: -1; }
}
</style>
@endpush

@push('scripts')
<script>
// Reveal Answers Toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('revealAnswersToggle');
    if (!toggle) return;

    toggle.addEventListener('change', function() {
        const revealed = this.checked;

        // Toggle correct answer highlighting on multiple choice
        document.querySelectorAll('.correct-answer').forEach(el => {
            el.classList.toggle('text-success', revealed);
            el.classList.toggle('fw-bold', revealed);
        });

        // Toggle check indicators
        document.querySelectorAll('.correct-indicator').forEach(el => {
            el.classList.toggle('d-none', !revealed);
        });

        // Toggle answer-reveal / answer-hide pairs
        document.querySelectorAll('.answer-reveal').forEach(el => {
            el.classList.toggle('d-none', !revealed);
        });
        document.querySelectorAll('.answer-hide').forEach(el => {
            el.classList.toggle('d-none', revealed);
        });
    });
});
</script>
@if($selfCheck->document_content)
@include('components.document-viewer-js')
@endif

@if($selfCheck->time_limit && auth()->user()->role === \App\Constants\Roles::STUDENT && !($selfCheck->due_date && now()->gt($selfCheck->due_date)) && !$attemptsExhausted)
<script>
document.addEventListener('DOMContentLoaded', function() {
    var timerRow = document.getElementById('sidebarTimerRow');
    var displayEl = document.getElementById('sidebarTimerDisplay');
    var form = document.getElementById('selfCheckForm');
    if (!timerRow || !displayEl || !form) return;

    var totalSeconds = {{ $selfCheck->time_limit }} * 60;
    var storageKey = 'sc_timer_{{ $selfCheck->id }}_' + '{{ auth()->id() }}';

    var saved = localStorage.getItem(storageKey);
    if (saved) {
        var elapsed = Math.floor((Date.now() - parseInt(saved)) / 1000);
        totalSeconds = Math.max(0, totalSeconds - elapsed);
    } else {
        localStorage.setItem(storageKey, Date.now().toString());
    }

    var submitted = false;

    function getTimerClass(seconds) {
        if (seconds <= 60) return 'timer-red';
        if (seconds <= 180) return 'timer-orange';
        if (seconds <= 300) return 'timer-yellow';
        return 'timer-green';
    }

    function tick() {
        if (totalSeconds <= 0) {
            displayEl.textContent = '0:00';
            timerRow.className = 'sc-sidebar__row sc-timer-row timer-red';
            if (!submitted) {
                submitted = true;
                localStorage.removeItem(storageKey);
                alert('Time is up! Your answers will be submitted automatically.');
                form.submit();
            }
            return;
        }
        var m = Math.floor(totalSeconds / 60);
        var s = totalSeconds % 60;
        displayEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        timerRow.className = 'sc-sidebar__row sc-timer-row ' + getTimerClass(totalSeconds);
        totalSeconds--;
        setTimeout(tick, 1000);
    }
    tick();
    form.addEventListener('submit', function() { submitted = true; localStorage.removeItem(storageKey); });
});
</script>
@endif
@endpush

@php
function formatQuestionType($type) {
    $labels = [
        'multiple_choice' => 'Multiple Choice', 'true_false' => 'True/False',
        'fill_blank' => 'Fill in the Blank', 'short_answer' => 'Short Answer',
        'matching' => 'Matching', 'ordering' => 'Ordering', 'image_choice' => 'Image Choice',
        'multiple_select' => 'Multiple Select', 'numeric' => 'Numeric',
        'classification' => 'Classification', 'drag_drop' => 'Drag & Drop',
        'image_identification' => 'Image ID', 'hotspot' => 'Hotspot',
        'image_labeling' => 'Image Labeling', 'essay' => 'Essay',
        'audio_question' => 'Audio', 'video_question' => 'Video', 'slider' => 'Slider',
    ];
    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

function getQuestionTypeBadgeColor($type) {
    $colors = [
        'multiple_choice' => 'primary', 'true_false' => 'info', 'fill_blank' => 'purple',
        'short_answer' => 'warning', 'matching' => 'success', 'ordering' => 'teal',
        'image_choice' => 'pink', 'multiple_select' => 'indigo', 'numeric' => 'secondary',
    ];
    return $colors[$type] ?? 'secondary';
}
@endphp
@endsection
