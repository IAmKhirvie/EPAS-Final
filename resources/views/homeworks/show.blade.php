@extends('layouts.app')

@section('title', $homework->title)

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $homework->informationSheet->module->module_name, 'url' => route('courses.modules.show', [$homework->informationSheet->module->course_id, $homework->informationSheet->module])],
        ['label' => $homework->title],
    ]" />

    <div class="cb-show">
        {{-- Header --}}
        <div class="cb-show__header cb-header--homework">
            <div class="cb-show__header-left">
                <h4><i class="fas fa-book-open me-2"></i>{{ $homework->title }}</h4>
                <p>{{ $homework->homework_number }}@if($homework->description) &mdash; {{ Str::limit($homework->description, 80) }}@endif</p>
            </div>
            <div class="cb-show__header-right">
                <span class="badge bg-light text-dark"><i class="fas fa-star me-1"></i>{{ $homework->max_points }} pts</span>
                <span class="badge bg-{{ $homework->is_past_due ? 'danger' : 'light' }} {{ $homework->is_past_due ? '' : 'text-dark' }}">
                    <i class="fas fa-calendar me-1"></i>{{ $homework->due_date->format('M d, Y h:i A') }}
                </span>
                @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('homeworks.edit', [$homework->informationSheet, $homework]) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('homeworks.destroy', [$homework->informationSheet, $homework]) }}" method="POST" onsubmit="return confirm('Delete this homework?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
                <a href="{{ route('information-sheets.show', ['module' => $homework->informationSheet->module_id, 'informationSheet' => $homework->informationSheet->id]) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        {{-- Status strip --}}
        <div class="cb-meta-strip">
            @if($homework->is_past_due)
            <span class="cb-meta-chip cb-meta-chip--danger"><i class="fas fa-exclamation-circle"></i> Past Due</span>
            @elseif($homework->days_until_due <= 3)
            <span class="cb-meta-chip cb-meta-chip--warning"><i class="fas fa-clock"></i> {{ $homework->days_until_due }} day(s) left</span>
            @else
            <span class="cb-meta-chip cb-meta-chip--success"><i class="fas fa-clock"></i> {{ $homework->days_until_due }} days left</span>
            @endif
            <span class="cb-meta-chip cb-meta-chip--info"><i class="fas fa-star"></i> {{ $homework->max_points }} max points</span>
            <span class="cb-meta-chip cb-meta-chip--purple"><i class="fas fa-users"></i> {{ $homework->submission_count }} submissions</span>
        </div>

        {{-- Horizontal meta: Requirements | Guidelines | Instructions --}}
        <div class="cb-meta-sections">
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-list-check" style="color:#f57c00;"></i> Requirements</div>
                <ul class="cb-meta-section__list">
                    @foreach($homework->requirements_list as $req)
                    <li><i class="fas fa-check-square" style="color:#ffa726;"></i> {{ $req }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-clipboard-check" style="color:#388e3c;"></i> Submission Guidelines</div>
                <ul class="cb-meta-section__list">
                    @foreach($homework->submission_guidelines_list as $guide)
                    <li><i class="fas fa-info-circle" style="color:#66bb6a;"></i> {{ $guide }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="cb-meta-section" style="grid-column: span 2;">
                <div class="cb-meta-section__title"><i class="fas fa-file-alt" style="color:#5c6bc0;"></i> Instructions</div>
                <div style="font-size: 0.8rem; color: var(--cb-text-label); line-height: 1.5;">
                    {!! nl2br(e($homework->instructions)) !!}
                </div>
            </div>
        </div>

        {{-- Reference Images --}}
        @if(count($homework->reference_images_list) > 0)
        <div class="cb-show__body" style="padding-bottom: 0.75rem;">
            <div class="cb-items-header" style="margin-bottom: 0.5rem; padding-bottom: 0.5rem;">
                <h5><i class="fas fa-images"></i> Reference Images</h5>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                @foreach($homework->reference_images_list as $image)
                <a href="{{ Storage::url($image) }}" target="_blank">
                    <img src="{{ Storage::url($image) }}" alt="Reference" class="img-fluid rounded" style="max-height: 120px; border: 1px solid var(--cb-border);">
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Student Submission --}}
        @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
        <form action="{{ route('homeworks.submit', $homework) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="cb-show__body" style="border-top: 2px solid var(--cb-border); padding-top: 1rem;">
                <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                    <h5><i class="fas fa-upload"></i> Submit Your Work</h5>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <label class="cb-field-label">Upload File <span class="required">*</span></label>
                        <label class="cb-upload-area" style="padding: 1rem;">
                            <input type="file" class="d-none" name="submission_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.zip" required onchange="this.closest('.cb-upload-area').classList.add('has-file'); this.closest('.cb-upload-area').querySelector('.upload-name').textContent = this.files[0].name;">
                            <i class="fas fa-cloud-upload-alt d-block" style="font-size: 1.5rem;"></i>
                            <div class="cb-upload-area__text"><strong>Click to upload</strong><br><small>JPG, PNG, PDF, DOC, ZIP</small></div>
                            <span class="upload-name" style="color: #388e3c; font-weight: 600; font-size: 0.8rem;"></span>
                        </label>
                    </div>
                    <div>
                        <label class="cb-field-label">Notes <span class="optional">(optional)</span></label>
                        <textarea class="form-control form-control-sm" name="description" rows="4" placeholder="Notes about your submission..."></textarea>
                    </div>
                    <div>
                        <label class="cb-field-label">Time Spent <span class="optional">(optional)</span></label>
                        <input type="number" class="form-control form-control-sm" name="work_hours" step="0.5" min="0" placeholder="Hours">
                    </div>
                </div>
            </div>
            <div class="cb-show__footer">
                <small style="color: var(--cb-text-hint);"><i class="fas fa-info-circle me-1"></i>Upload your file before submitting.</small>
                <button type="submit" class="btn btn-success" {{ $homework->is_past_due ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane me-1"></i>Submit Homework
                    @if($homework->is_past_due)<small class="ms-1">(Closed)</small>@endif
                </button>
            </div>
        </form>
        @endif

        {{-- Instructor: Submissions --}}
        @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
        <div class="cb-show__body" style="border-top: 2px solid var(--cb-border); padding-top: 1rem;">
            <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                <h5><i class="fas fa-users"></i> Submissions <span class="cb-count-badge">{{ $homework->submissions->count() }}</span></h5>
            </div>

            @if($homework->submissions->count() > 0)
            <div class="cb-grid">
                @foreach($homework->submissions as $submission)
                <div class="cb-item-card cb-item-card--compact">
                    <div class="cb-item-card__header">
                        <div class="left-section">
                            <span class="cb-item-card__number"><i class="fas fa-user"></i></span>
                            <span class="cb-item-card__title">{{ $submission->user->first_name }} {{ $submission->user->last_name }}</span>
                            @if($submission->is_late)<span class="badge bg-danger" style="font-size:0.65rem;">Late</span>@endif
                        </div>
                        <div class="right-section">
                            @if($submission->score !== null)
                            <span class="badge bg-{{ $submission->score >= 70 ? 'success' : 'warning' }}">{{ $submission->score }}/{{ $homework->max_points }}</span>
                            @else
                            <span class="badge bg-secondary">Ungraded</span>
                            @endif
                            <a href="{{ Storage::url($submission->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-download"></i></a>
                        </div>
                    </div>
                    <div class="cb-item-card__body" style="padding: 0.5rem 0.75rem;">
                        <small style="color: var(--cb-text-hint);"><i class="fas fa-clock me-1"></i>{{ $submission->submitted_at->format('M d, Y h:i A') }}</small>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="cb-empty-state" style="padding: 1.5rem;">
                <i class="fas fa-inbox d-block" style="font-size: 2rem;"></i>
                <p><strong>No submissions yet</strong></p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
