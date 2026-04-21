@extends('layouts.app')

@section('title', $taskSheet->title)

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $taskSheet->informationSheet->module->module_name, 'url' => route('courses.modules.show', [$taskSheet->informationSheet->module->course_id, $taskSheet->informationSheet->module])],
        ['label' => $taskSheet->title],
    ]" />

    <div class="cb-show">
        {{-- Header --}}
        <div class="cb-show__header cb-header--task">
            <div class="cb-show__header-left">
                <h4><i class="fas fa-clipboard-list me-2"></i>{{ $taskSheet->title }}</h4>
                <p>{{ $taskSheet->task_number }}@if($taskSheet->description) &mdash; {{ Str::limit($taskSheet->description, 80) }}@endif</p>
            </div>
            <div class="cb-show__header-right">
                <span class="badge bg-light text-dark"><i class="fas fa-list-check me-1"></i>{{ $taskSheet->items->count() }} Items</span>
                @if($taskSheet->file_path)
                <a href="{{ route('task-sheets.download', $taskSheet) }}" class="badge bg-light text-dark text-decoration-none">
                    <i class="fas fa-paperclip me-1"></i>{{ $taskSheet->original_filename }}
                </a>
                @endif
                @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('task-sheets.edit', [$taskSheet->informationSheet, $taskSheet]) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('task-sheets.destroy', [$taskSheet->informationSheet, $taskSheet]) }}" method="POST" onsubmit="return confirm('Delete this task sheet?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
                @php
                    $moduleForBack = $taskSheet->informationSheet->module ?? null;
                    $courseForBack = $moduleForBack?->course ?? null;
                @endphp
                @if($moduleForBack && $courseForBack)
                <a href="{{ route('courses.modules.show', [$courseForBack, $moduleForBack]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-arrow-left me-1"></i>Back to Module
                </a>
                @else
                <a href="{{ route('information-sheets.show', ['module' => $taskSheet->informationSheet->module_id, 'informationSheet' => $taskSheet->informationSheet->id]) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                @endif
            </div>
        </div>

        {{-- Horizontal meta sections: Objectives | Materials | Safety | Instructions --}}
        <div class="cb-meta-sections">
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-bullseye" style="color:#388e3c;"></i> Objectives</div>
                <ul class="cb-meta-section__list">
                    @foreach($taskSheet->objectives_list as $obj)
                    <li><i class="fas fa-check-circle"></i> {{ $obj['name'] ?? $obj }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-tools" style="color:#f57c00;"></i> Materials</div>
                <ul class="cb-meta-section__list">
                    @foreach($taskSheet->materials_list as $mat)
                        <li><i class="fas fa-wrench"></i> {{ $mat }}</li>
                    @endforeach
                </ul>
            </div>
            @if(count($taskSheet->safety_precautions_list) > 0)
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-shield-alt" style="color:#e53935;"></i> Safety</div>
                <ul class="cb-meta-section__list">
                    @foreach($taskSheet->safety_precautions_list as $safety)
                    <li><i class="fas fa-exclamation-triangle" style="color:#ef5350;"></i> {{ $safety }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="cb-meta-section">
                <div class="cb-meta-section__title"><i class="fas fa-file-alt" style="color:#5c6bc0;"></i> Instructions</div>
                <div style="font-size: 0.8rem; color: var(--cb-text-label); line-height: 1.5;">
                    {!! nl2br(e(Str::limit($taskSheet->instructions, 200))) !!}
                </div>
            </div>
        </div>

        {{-- Document Viewer --}}
        @if($taskSheet->document_content || $taskSheet->file_path)
        <div style="padding: 0.75rem 1.5rem;">
            @include('components.document-viewer', [
                'documentContent' => $taskSheet->document_content,
                'filePath' => $taskSheet->file_path,
                'originalFilename' => $taskSheet->original_filename,
                'downloadRoute' => route('task-sheets.download', $taskSheet),
            ])
        </div>
        @endif

        {{-- Image (if any) --}}
        @if($taskSheet->image_path)
        <div style="padding: 0.75rem 1.5rem; border-bottom: 1px solid var(--cb-border); text-align: center;">
            <img src="{{ Storage::url($taskSheet->image_path) }}" alt="{{ $taskSheet->title }}" class="img-fluid rounded" style="max-height: 200px;">
        </div>
        @endif

        {{-- Task Items Grid --}}
        <div class="cb-show__body">
            <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                <h5><i class="fas fa-tasks"></i> Task Items <span class="cb-count-badge">{{ $taskSheet->items->count() }}</span></h5>
            </div>

            <div class="cb-grid">
                @foreach($taskSheet->items as $index => $item)
                <div class="cb-item-card cb-item-card--compact">
                    <div class="cb-item-card__header">
                        <div class="left-section">
                            <span class="cb-item-card__number">{{ $index + 1 }}</span>
                            <span class="cb-item-card__title">{{ $item->part_name }}</span>
                        </div>
                    </div>
                    <div class="cb-item-card__body">
                        <p style="font-size: 0.8rem; margin-bottom: 0.5rem; color: var(--cb-text-label);">{{ $item->description }}</p>
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <span class="cb-meta-chip cb-meta-chip--blue"><i class="fas fa-crosshairs"></i> {{ $item->expected_finding }}</span>
                            <span class="cb-meta-chip cb-meta-chip--green"><i class="fas fa-arrows-alt-h"></i> {{ $item->acceptable_range }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Student Submission --}}
        @if(auth()->user()->role === \App\Constants\Roles::STUDENT)
        <form action="{{ route('task-sheets.submit', $taskSheet) }}" method="POST">
            @csrf
            <div class="cb-show__body" style="border-top: 2px solid var(--cb-border); padding-top: 1rem;">
                <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                    <h5><i class="fas fa-paper-plane"></i> Your Findings</h5>
                </div>
                <div class="cb-grid">
                    @foreach($taskSheet->items as $index => $item)
                    <div class="cb-item-card cb-item-card--compact">
                        <div class="cb-item-card__header">
                            <div class="left-section">
                                <span class="cb-item-card__number">{{ $index + 1 }}</span>
                                <span class="cb-item-card__title">{{ $item->part_name }}</span>
                            </div>
                        </div>
                        <div class="cb-item-card__body">
                            <input type="text" class="form-control form-control-sm" name="findings[{{ $item->id }}]" placeholder="Enter your finding" required>
                            <div class="cb-field-hint">Expected: {{ $item->expected_finding }} | Range: {{ $item->acceptable_range }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="cb-show__footer">
                <small style="color: var(--cb-text-hint);"><i class="fas fa-info-circle me-1"></i>Fill in all findings before submitting.</small>
                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Submit Task Sheet</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection

@if(!empty($taskSheet->document_content))
@push('styles')
@include('components.document-viewer-css')
@endpush
@push('scripts')
@include('components.document-viewer-js')
@endpush
@endif
