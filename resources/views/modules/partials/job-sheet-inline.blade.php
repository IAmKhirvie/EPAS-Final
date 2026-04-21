{{-- Inline Job Sheet Partial (loaded via AJAX into unified module view) --}}
<div class="job-sheet-inline">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5><i class="fas fa-hard-hat me-2 text-success"></i>{{ $jobSheet->title }}</h5>
            <p class="text-muted mb-0">{{ $jobSheet->job_number }}@if($jobSheet->description) &mdash; {{ Str::limit($jobSheet->description, 80) }}@endif</p>
        </div>
        <span class="badge bg-light text-dark"><i class="fas fa-list-ol me-1"></i>{{ $jobSheet->steps->count() }} Steps</span>
    </div>

    {{-- Meta Sections --}}
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card bg-light border-0">
                <div class="card-body py-2 px-3">
                    <small class="fw-bold text-success"><i class="fas fa-bullseye me-1"></i>Objectives</small>
                    <ul class="list-unstyled mb-0 mt-1 small">
                        @foreach($jobSheet->objectives_list as $obj)
                        <li><i class="fas fa-check-circle text-success me-1"></i> {{ $obj }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card bg-light border-0">
                <div class="card-body py-2 px-3">
                    <small class="fw-bold text-warning"><i class="fas fa-wrench me-1"></i>Tools Required</small>
                    <ul class="list-unstyled mb-0 mt-1 small">
                        @foreach($jobSheet->tools_required_list as $tool)
                        <li><i class="fas fa-tools text-warning me-1"></i> {{ $tool }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card bg-light border-0">
                <div class="card-body py-2 px-3">
                    <small class="fw-bold text-danger"><i class="fas fa-shield-alt me-1"></i>Safety</small>
                    <ul class="list-unstyled mb-0 mt-1 small">
                        @foreach($jobSheet->safety_requirements_list as $safety)
                        <li><i class="fas fa-exclamation-triangle text-danger me-1"></i> {{ $safety }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Steps --}}
    <h6 class="mb-2"><i class="fas fa-list-ol me-1"></i> Procedure Steps</h6>
    <div class="row">
        @foreach($jobSheet->steps->sortBy('step_number') as $step)
        <div class="col-md-6 mb-2">
            <div class="card border">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary me-2">{{ $step->step_number }}</span>
                        <strong class="small">Step {{ $step->step_number }}</strong>
                    </div>
                    <p class="small text-muted mb-1">{{ $step->instruction }}</p>
                    <span class="badge bg-success bg-opacity-10 text-success small">
                        <i class="fas fa-check-circle me-1"></i>{{ $step->expected_outcome }}
                    </span>
                    @if($step->image_path)
                    <div class="mt-1">
                        <img src="{{ Storage::url($step->image_path) }}" alt="Step {{ $step->step_number }}" class="img-fluid rounded" style="max-height: 80px;">
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Student Submission --}}
    @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
    <hr>
    <form action="{{ route('job-sheets.submit', $jobSheet) }}" method="POST" data-inline-submit>
        @csrf
        <h6><i class="fas fa-paper-plane me-1"></i> Submit Your Work</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold"><i class="fas fa-check-double me-1"></i>Completed Steps</label>
                <div class="bg-light rounded p-2">
                    @foreach($jobSheet->steps->sortBy('step_number') as $step)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="completed_steps[]" value="{{ $step->id }}" id="inlineStep{{ $step->id }}">
                        <label class="form-check-label small" for="inlineStep{{ $step->id }}">
                            Step {{ $step->step_number }}: {{ Str::limit($step->instruction, 40) }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="mb-2">
                    <label class="form-label small fw-bold">Observations <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm" name="observations" rows="2" required placeholder="What you observed..."></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Challenges <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control form-control-sm" name="challenges" rows="2" placeholder="Difficulties faced..."></textarea>
                </div>
                <div>
                    <label class="form-label small fw-bold">Solutions <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control form-control-sm" name="solutions" rows="2" placeholder="How you solved them..."></textarea>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Check completed steps and fill in observations.</small>
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-check me-1"></i>Submit Job Sheet
            </button>
        </div>
    </form>
    @endif
</div>
