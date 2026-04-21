@extends('layouts.app')

@section('title', $test->title)

@section('content')
<div class="container py-4">
    <form id="testForm" action="{{ route('courses.modules.competency-tests.submit', [$course, $module, $test]) }}" method="POST">
        @csrf

        {{-- Test Header --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">{{ $test->title }}</h4>
                        <p class="text-muted mb-0">{{ $module->module_title }}</p>
                    </div>
                    <div class="text-end">
                        @if($timeLimit)
                            <div class="badge bg-warning text-dark fs-6 mb-2" id="timerBadge">
                                <i class="fas fa-clock me-1"></i>
                                <span id="timer">{{ gmdate('H:i:s', $remainingTime ?? $timeLimit * 60) }}</span>
                            </div>
                        @endif
                        <div class="text-muted small">
                            Attempt {{ $submission->attempt_number }}
                            @if($test->max_attempts)
                                of {{ $test->max_attempts }}
                            @endif
                        </div>
                    </div>
                </div>

                @if($test->instructions)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        {!! nl2br(e($test->instructions)) !!}
                    </div>
                @endif
            </div>
        </div>

        {{-- Questions by Part --}}
        @php $questionNumber = 1; @endphp
        @foreach($questionsByPart as $partKey => $partData)
            @if($partData['part']['name'])
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">{{ $partData['part']['name'] }}</h5>
                        @if($partData['part']['instructions'] ?? false)
                            <small class="text-white-50">{{ $partData['part']['instructions'] }}</small>
                        @endif
                    </div>
                    <div class="card-body">
            @endif

            @foreach($partData['questions'] as $question)
                <div class="question-item mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">
                            <span class="badge bg-primary me-2">{{ $questionNumber }}</span>
                            <span class="badge bg-light text-dark">{{ $question->points }} pt{{ $question->points > 1 ? 's' : '' }}</span>
                        </h6>
                    </div>

                    <p class="mb-3">{!! nl2br(e($question->question_text)) !!}</p>

                    @php $savedAnswer = $savedAnswers[$question->id] ?? null; @endphp

                    @switch($question->question_type)
                        @case('multiple_choice')
                        @case('image_choice')
                            @php
                                $options = $question->randomized_options ?? $question->options ?? [];
                            @endphp
                            @foreach($options as $index => $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                           name="answers[{{ $question->id }}]"
                                           id="q{{ $question->id }}_{{ $index }}"
                                           value="{{ $index }}"
                                           {{ $savedAnswer == $index ? 'checked' : '' }}>
                                    <label class="form-check-label" for="q{{ $question->id }}_{{ $index }}">
                                        @if($question->question_type === 'image_choice' && filter_var($option, FILTER_VALIDATE_URL))
                                            <img src="{{ $option }}" alt="Option {{ $index + 1 }}" class="img-thumbnail" style="max-height: 100px;">
                                        @else
                                            {{ is_array($option) ? ($option['text'] ?? $option) : $option }}
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                            @break

                        @case('multiple_select')
                            @php $options = $question->options ?? []; @endphp
                            @foreach($options as $index => $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           name="answers[{{ $question->id }}][]"
                                           id="q{{ $question->id }}_{{ $index }}"
                                           value="{{ $index }}"
                                           {{ is_array($savedAnswer) && in_array($index, $savedAnswer) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="q{{ $question->id }}_{{ $index }}">
                                        {{ is_array($option) ? ($option['text'] ?? $option) : $option }}
                                    </label>
                                </div>
                            @endforeach
                            @break

                        @case('true_false')
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="answers[{{ $question->id }}]"
                                       id="q{{ $question->id }}_true" value="true"
                                       {{ $savedAnswer === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="q{{ $question->id }}_true">True</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                       name="answers[{{ $question->id }}]"
                                       id="q{{ $question->id }}_false" value="false"
                                       {{ $savedAnswer === 'false' ? 'checked' : '' }}>
                                <label class="form-check-label" for="q{{ $question->id }}_false">False</label>
                            </div>
                            @break

                        @case('fill_blank')
                        @case('short_answer')
                            <input type="text" class="form-control"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}"
                                   placeholder="Type your answer here">
                            @break

                        @case('numeric')
                        @case('slider')
                            <input type="number" class="form-control" style="max-width: 200px;"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}"
                                   step="any"
                                   placeholder="Enter a number">
                            @break

                        @case('essay')
                            <textarea class="form-control"
                                      name="answers[{{ $question->id }}]"
                                      rows="5"
                                      placeholder="Write your answer here">{{ $savedAnswer }}</textarea>
                            @break

                        @default
                            <input type="text" class="form-control"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $savedAnswer }}"
                                   placeholder="Type your answer">
                    @endswitch
                </div>
                @php $questionNumber++; @endphp
            @endforeach

            @if($partData['part']['name'])
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Submit Section --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Total Questions: {{ $questionNumber - 1 }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="saveProgress()">
                            <i class="fas fa-save me-1"></i> Save Progress
                        </button>
                        <button type="submit" class="btn btn-primary" onclick="return confirmSubmit()">
                            <i class="fas fa-paper-plane me-1"></i> Submit Test
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Timer functionality
@if($timeLimit && $remainingTime)
let remainingSeconds = {{ $remainingTime }};
const timerElement = document.getElementById('timer');
const timerBadge = document.getElementById('timerBadge');

const timerInterval = setInterval(() => {
    remainingSeconds--;

    if (remainingSeconds <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! Your test will be submitted automatically.');
        document.getElementById('testForm').submit();
        return;
    }

    // Update display
    const hours = Math.floor(remainingSeconds / 3600);
    const minutes = Math.floor((remainingSeconds % 3600) / 60);
    const seconds = remainingSeconds % 60;
    timerElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    // Warning colors
    if (remainingSeconds <= 60) {
        timerBadge.classList.remove('bg-warning', 'text-dark');
        timerBadge.classList.add('bg-danger', 'text-white');
    } else if (remainingSeconds <= 300) {
        timerBadge.classList.remove('bg-warning', 'text-dark');
        timerBadge.classList.add('bg-orange', 'text-white');
    }
}, 1000);
@endif

// Auto-save every 30 seconds
setInterval(() => {
    saveProgress(true);
}, 30000);

function saveProgress(silent = false) {
    const form = document.getElementById('testForm');
    const formData = new FormData(form);

    fetch('{{ route("courses.modules.competency-tests.save", [$course, $module, $test]) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!silent && data.success) {
            // Show brief success message
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.innerHTML = `
                <div class="toast show" role="alert">
                    <div class="toast-body bg-success text-white rounded">
                        <i class="fas fa-check me-1"></i> Progress saved
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }
    })
    .catch(error => {
        if (!silent) {
            console.error('Save failed:', error);
        }
    });
}

function confirmSubmit() {
    return confirm('Are you sure you want to submit this test? You cannot change your answers after submission.');
}

// Warn before leaving page
window.addEventListener('beforeunload', (e) => {
    e.preventDefault();
    e.returnValue = '';
});
</script>
@endpush
@endsection
