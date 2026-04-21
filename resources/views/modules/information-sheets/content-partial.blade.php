{{-- Content partial for AJAX loading --}}
<style>
.topic-parts {
    margin-top: 1rem;
}

.part-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #ffb902;
}

.part-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
}

.part-image-wrapper img {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.part-image-wrapper:hover img {
    transform: scale(1.02);
}

.part-number-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #ffb902;
    color: white;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 600;
}

.part-title {
    color: #333;
    font-weight: 600;
}

.part-explanation {
    color: #555;
    line-height: 1.7;
}

.content-card-header .badge {
    font-size: 0.7rem;
    font-weight: normal;
}
</style>
<div class="sheet-content">
    <div class="content-card">
        <div class="content-card-header">
            <div class="icon"><i class="fas fa-file-alt"></i></div>
            <h3>Information Sheet {{ $sheet->sheet_number }}: {{ $sheet->title }}</h3>
        </div>

        @if($sheet->objective)
        <div class="mb-4">
            <h5 class="text-primary"><i class="fas fa-bullseye me-2"></i>Objective</h5>
            <div class="topic-content-area">
                {!! nl2br(e($sheet->objective)) !!}
            </div>
        </div>
        @endif

        @if($sheet->content)
        <div class="mb-4">
            <div class="topic-content-area">
                {!! $sheet->content !!}
            </div>
        </div>
        @endif
    </div>

    {{-- Topics --}}
    @if($sheet->topics && $sheet->topics->count() > 0)
        @foreach($sheet->topics as $topic)
        <div class="content-card" id="topic-{{ $topic->id }}">
            <div class="content-card-header">
                <div class="icon"><i class="fas fa-bookmark"></i></div>
                <h3>{{ $topic->title }}</h3>
                @if($topic->topic_number)
                <span class="badge bg-secondary ms-2">Topic {{ $topic->topic_number }}</span>
                @endif
            </div>

            @if($topic->content)
            <div class="topic-content-area mb-3">
                {!! $topic->content !!}
            </div>
            @endif

            {{-- Topic Parts with Images --}}
            @if($topic->parts && count($topic->parts) > 0)
            <div class="topic-parts mt-4">
                @foreach($topic->parts as $partIndex => $part)
                <div class="part-item mb-4">
                    <div class="row align-items-start">
                        @if(!empty($part['image']))
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="part-image-wrapper">
                                <img src="{{ $part['image'] }}" alt="{{ $part['title'] ?? 'Part Image' }}" class="img-fluid rounded shadow-sm">
                            </div>
                        </div>
                        <div class="col-md-9">
                        @else
                        <div class="col-12">
                        @endif
                            <div class="part-content">
                                @if(!empty($part['title']))
                                <h5 class="part-title mb-2">
                                    <span class="part-number-badge me-2">{{ $partIndex + 1 }}</span>
                                    {{ $part['title'] }}
                                </h5>
                                @endif
                                @if(!empty($part['explanation']))
                                <div class="part-explanation">
                                    {!! nl2br(e($part['explanation'])) !!}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if(!$loop->last)
                <hr class="my-3">
                @endif
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    @endif

    {{-- Self-Check Link --}}
    @if($sheet->selfChecks && $sheet->selfChecks->count() > 0)
    <div class="content-card bg-warning bg-opacity-10">
        <div class="content-card-header">
            <div class="icon bg-warning"><i class="fas fa-question-circle"></i></div>
            <h3>Self-Check</h3>
        </div>
        <p class="text-muted mb-3">Test your understanding of this information sheet.</p>
        <a href="{{ route('courses.modules.information-sheets.self-check', [$sheet->module->course_id, $sheet->module_id, $sheet->id]) }}" class="btn btn-warning">
            <i class="fas fa-play me-1"></i> Start Self-Check
        </a>
    </div>
    @endif

    {{-- Navigation --}}
    <div class="topic-nav">
        <button class="btn btn-outline-secondary" id="prevTopic" disabled>
            <i class="fas fa-arrow-left me-1"></i> Previous
        </button>
        <button class="btn btn-primary" id="nextTopic">
            Next <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </div>
</div>
