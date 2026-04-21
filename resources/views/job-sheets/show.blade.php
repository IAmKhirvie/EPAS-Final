@extends('layouts.app')

@section('title', $jobSheet->title)

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $jobSheet->informationSheet->module->module_name, 'url' => route('courses.modules.show', [$jobSheet->informationSheet->module->course_id, $jobSheet->informationSheet->module])],
        ['label' => $jobSheet->title],
    ]" />

    <div class="cb-show">
        {{-- Header --}}
        <div class="cb-show__header cb-header--job">
            <div class="cb-show__header-left">
                <h4><i class="fas fa-hard-hat me-2"></i>{{ $jobSheet->title }}</h4>
                <p>{{ $jobSheet->job_number }}@if($jobSheet->description) &mdash; {{ Str::limit($jobSheet->description, 80) }}@endif</p>
            </div>
            <div class="cb-show__header-right">
                <span class="badge bg-light text-dark"><i class="fas fa-list-ol me-1"></i>{{ $jobSheet->steps->count() }} Steps</span>
                @if($jobSheet->file_path)
                <a href="{{ route('job-sheets.download', $jobSheet) }}" class="badge bg-light text-dark text-decoration-none">
                    <i class="fas fa-paperclip me-1"></i>{{ $jobSheet->original_filename }}
                </a>
                @endif
                @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('job-sheets.edit', [$jobSheet->informationSheet, $jobSheet]) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('job-sheets.destroy', [$jobSheet->informationSheet, $jobSheet]) }}" method="POST" onsubmit="return confirm('Delete this job sheet?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
                @php
                    $moduleForBack = $jobSheet->informationSheet->module ?? null;
                    $courseForBack = $moduleForBack?->course ?? null;
                @endphp
                @if($moduleForBack && $courseForBack)
                <a href="{{ route('courses.modules.show', [$courseForBack, $moduleForBack]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-arrow-left me-1"></i>Back to Module
                </a>
                @else
                <a href="{{ route('information-sheets.show', ['module' => $jobSheet->informationSheet->module_id, 'informationSheet' => $jobSheet->informationSheet->id]) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                @endif
            </div>
        </div>

        {{-- Horizontal meta: Objectives | Tools | Safety | References --}}
        <div class="cb-meta-sections">
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-bullseye" style="color:#388e3c;"></i> Objectives</div>
                <ul class="cb-meta-section__list">
                    @foreach($jobSheet->objectives_list as $obj)
                    <li><i class="fas fa-check-circle" style="color:#66bb6a;"></i> {{ $obj }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-wrench" style="color:#f57c00;"></i> Tools Required</div>
                <ul class="cb-meta-section__list">
                    @foreach($jobSheet->tools_required_list as $tool)
                    <li><i class="fas fa-tools" style="color:#ffa726;"></i> {{ $tool }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-shield-alt" style="color:#e53935;"></i> Safety</div>
                <ul class="cb-meta-section__list">
                    @foreach($jobSheet->safety_requirements_list as $safety)
                    <li><i class="fas fa-exclamation-triangle" style="color:#ef5350;"></i> {{ $safety }}</li>
                    @endforeach
                </ul>
            </div>
            @if(count($jobSheet->reference_materials_list) > 0)
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-book" style="color:#5c6bc0;"></i> References</div>
                <ul class="cb-meta-section__list">
                    @foreach($jobSheet->reference_materials_list as $ref)
                    <li><i class="fas fa-file-alt" style="color:#7986cb;"></i> {{ $ref }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Document Viewer --}}
        @if($jobSheet->document_content || $jobSheet->file_path)
        <div style="padding: 0.75rem 1.5rem;">
            @include('components.document-viewer', [
                'documentContent' => $jobSheet->document_content,
                'filePath' => $jobSheet->file_path,
                'originalFilename' => $jobSheet->original_filename,
                'downloadRoute' => route('job-sheets.download', $jobSheet),
            ])
        </div>
        @endif

        {{-- Steps Grid --}}
        <div class="cb-show__body">
            <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                <h5><i class="fas fa-list-ol"></i> Procedure Steps <span class="cb-count-badge">{{ $jobSheet->steps->count() }}</span></h5>
            </div>

            <div class="cb-grid">
                @foreach($jobSheet->steps->sortBy('step_number') as $step)
                <div class="cb-item-card cb-item-card--compact">
                    <div class="cb-item-card__header">
                        <div class="left-section">
                            <span class="cb-item-card__number">{{ $step->step_number }}</span>
                            <span class="cb-item-card__title">Step {{ $step->step_number }}</span>
                        </div>
                    </div>
                    <div class="cb-item-card__body">
                        <p style="font-size: 0.8rem; margin-bottom: 0.5rem; color: var(--cb-text-label);">{{ $step->instruction }}</p>
                        <div class="cb-meta-chip cb-meta-chip--green" style="white-space: normal;">
                            <i class="fas fa-check-circle"></i> {{ $step->expected_outcome }}
                        </div>
                        @if($step->image_path)
                        <div style="margin-top: 0.5rem;">
                            <img src="{{ Storage::url($step->image_path) }}" alt="Step {{ $step->step_number }}" class="img-fluid rounded" style="max-height: 120px;">
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Student Submission --}}
        @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
        <form action="{{ route('job-sheets.submit', $jobSheet) }}" method="POST">
            @csrf
            <div class="cb-show__body" style="border-top: 2px solid var(--cb-border); padding-top: 1rem;">
                <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                    <h5><i class="fas fa-paper-plane"></i> Submit Your Work</h5>
                </div>

                <div class="cb-show__split">
                    {{-- Left: Completed steps --}}
                    <div>
                        <label class="cb-field-label"><i class="fas fa-check-double me-1"></i>Completed Steps</label>
                        <div style="background: var(--cb-surface-alt); border-radius: var(--cb-radius-sm); padding: 0.75rem;">
                            @foreach($jobSheet->steps->sortBy('step_number') as $step)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="completed_steps[]" value="{{ $step->id }}" id="step{{ $step->id }}">
                                <label class="form-check-label" for="step{{ $step->id }}" style="font-size: 0.8rem;">
                                    Step {{ $step->step_number }}: {{ Str::limit($step->instruction, 50) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Right: Text fields --}}
                    <div>
                        <div class="mb-3">
                            <label class="cb-field-label">Observations <span class="required">*</span></label>
                            <textarea class="form-control form-control-sm" name="observations" rows="2" required placeholder="What you observed..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="cb-field-label">Challenges <span class="optional">(optional)</span></label>
                            <textarea class="form-control form-control-sm" name="challenges" rows="2" placeholder="Difficulties faced..."></textarea>
                        </div>
                        <div>
                            <label class="cb-field-label">Solutions <span class="optional">(optional)</span></label>
                            <textarea class="form-control form-control-sm" name="solutions" rows="2" placeholder="How you solved them..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cb-show__footer">
                <small style="color: var(--cb-text-hint);"><i class="fas fa-info-circle me-1"></i>Check completed steps and fill in observations.</small>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Submit Job Sheet</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection

@if(!empty($jobSheet->document_content))
@push('styles')
@include('components.document-viewer-css')
@endpush
@push('scripts')
@include('components.document-viewer-js')
@endpush
@endif
