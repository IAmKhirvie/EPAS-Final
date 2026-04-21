{{-- Inline Task Sheet Partial (loaded via AJAX into unified module view) --}}
<div class="task-sheet-inline">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5><i class="fas fa-clipboard-list me-2 text-info"></i>{{ $taskSheet->title }}</h5>
            <p class="text-muted mb-0">{{ $taskSheet->task_number }}@if($taskSheet->description) &mdash; {{ Str::limit($taskSheet->description, 80) }}@endif</p>
        </div>
        <span class="badge bg-light text-dark"><i class="fas fa-list-check me-1"></i>{{ $taskSheet->items->count() }} Items</span>
    </div>

    {{-- Meta Sections --}}
    <div class="row mb-3">
        <div class="col-md-6 mb-2">
            <div class="card bg-light border-0">
                <div class="card-body py-2 px-3">
                    <small class="fw-bold text-success"><i class="fas fa-bullseye me-1"></i>Objectives</small>
                    <ul class="list-unstyled mb-0 mt-1 small">
                        @foreach($taskSheet->objectives_list as $obj)
                        <li><i class="fas fa-check-circle text-success me-1"></i> {{ $obj }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="card bg-light border-0">
                <div class="card-body py-2 px-3">
                    <small class="fw-bold text-warning"><i class="fas fa-tools me-1"></i>Materials</small>
                    <ul class="list-unstyled mb-0 mt-1 small">
                        @foreach($taskSheet->materials_list as $mat)
                        <li><i class="fas fa-wrench text-warning me-1"></i> {{ $mat }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Task Items --}}
    <h6 class="mb-2"><i class="fas fa-tasks me-1"></i> Task Items</h6>
    <div class="row">
        @foreach($taskSheet->items as $index => $item)
        <div class="col-md-6 mb-2">
            <div class="card border">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                        <strong class="small">{{ $item->part_name }}</strong>
                    </div>
                    <p class="small text-muted mb-1">{{ $item->description }}</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-info bg-opacity-10 text-info"><i class="fas fa-crosshairs me-1"></i>{{ $item->expected_finding }}</span>
                        <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-arrows-alt-h me-1"></i>{{ $item->acceptable_range }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Student Submission --}}
    @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
    <hr>
    <form action="{{ route('task-sheets.submit', $taskSheet) }}" method="POST" data-inline-submit>
        @csrf
        <h6><i class="fas fa-paper-plane me-1"></i> Your Findings</h6>
        <div class="row">
            @foreach($taskSheet->items as $index => $item)
            <div class="col-md-6 mb-2">
                <div class="card border">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                            <strong class="small">{{ $item->part_name }}</strong>
                        </div>
                        <input type="text" class="form-control form-control-sm" name="findings[{{ $item->id }}]" placeholder="Enter your finding" required>
                        <small class="text-muted">Expected: {{ $item->expected_finding }} | Range: {{ $item->acceptable_range }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Fill in all findings before submitting.</small>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i>Submit Task Sheet
            </button>
        </div>
    </form>
    @endif
</div>
