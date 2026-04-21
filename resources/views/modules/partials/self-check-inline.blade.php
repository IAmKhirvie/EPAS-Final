{{-- Inline Self-Check Partial (loaded via AJAX into unified module view) --}}
@php
if (!function_exists('formatQuestionType')) {
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
}
if (!function_exists('getQuestionTypeBadgeColor')) {
    function getQuestionTypeBadgeColor($type) {
        $colors = [
            'multiple_choice' => 'primary', 'true_false' => 'info', 'fill_blank' => 'purple',
            'short_answer' => 'warning', 'matching' => 'success', 'ordering' => 'teal',
            'image_choice' => 'pink', 'multiple_select' => 'indigo', 'numeric' => 'secondary',
        ];
        return $colors[$type] ?? 'secondary';
    }
}
@endphp
<div class="self-check-inline">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5><i class="fas fa-clipboard-check me-2 text-warning"></i>{{ $selfCheck->title }}</h5>
            <p class="text-muted mb-0">{{ $selfCheck->check_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-light text-dark"><i class="fas fa-question-circle me-1"></i>{{ $selfCheck->questions->count() }} Questions</span>
            <span class="badge bg-light text-dark"><i class="fas fa-star me-1"></i>{{ $selfCheck->total_points }} Points</span>
            @if($selfCheck->time_limit)
            <span class="badge bg-light text-dark"><i class="fas fa-clock me-1"></i>{{ $selfCheck->time_limit }} min</span>
            @endif
        </div>
    </div>

    @if($selfCheck->description)
    <p class="text-muted">{{ $selfCheck->description }}</p>
    @endif

    @if($selfCheck->instructions)
    <div class="alert alert-info py-2 mb-3">
        <i class="fas fa-info-circle me-1"></i> {{ $selfCheck->instructions }}
    </div>
    @endif

    {{-- Student: Take Self-Check --}}
    @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
    <form action="{{ route('self-checks.submit', $selfCheck) }}" method="POST" data-inline-submit>
        @csrf
        @foreach($selfCheck->questions->sortBy('order') as $index => $question)
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                    <span class="badge bg-{{ getQuestionTypeBadgeColor($question->question_type) }}">
                        {{ formatQuestionType($question->question_type) }}
                    </span>
                </div>
                <span class="badge bg-primary">{{ $question->points }} pt(s)</span>
            </div>
            <div class="card-body">
                @if(!empty($question->options['question_image']))
                <div class="text-center mb-3">
                    <img src="{{ $question->options['question_image'] }}" alt="Question Image" class="img-fluid rounded" style="max-height: 200px;">
                </div>
                @endif

                <p class="mb-3">
                    @if($question->question_type === 'fill_blank')
                        {!! preg_replace('/___+/', '<span style="display:inline-block;border-bottom:2px solid #ffb902;min-width:80px;">________</span>', e($question->question_text)) !!}
                    @else
                        {{ $question->question_text }}
                    @endif
                </p>

                @include('modules.self-checks.partials.question-input', ['question' => $question])
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Answer all questions before submitting.</small>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-paper-plane me-1"></i>Submit Answers
            </button>
        </div>
    </form>

    @else
    {{-- Instructor Preview --}}
    <div class="alert alert-secondary">
        <i class="fas fa-eye me-1"></i> Instructor preview: {{ $selfCheck->questions->count() }} questions, {{ $selfCheck->total_points }} total points.
        <a href="{{ route('self-checks.show', $selfCheck) }}" class="alert-link ms-2">View Full Self-Check</a>
    </div>
    @endif
</div>
