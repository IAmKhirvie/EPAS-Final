@extends('layouts.app')

@section('title', $checklist->title)

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Content', 'url' => route('content.management')],
        ['label' => $checklist->informationSheet->module->module_name, 'url' => route('courses.modules.show', [$checklist->informationSheet->module->course_id, $checklist->informationSheet->module])],
        ['label' => $checklist->title],
    ]" />

    @php
        $items = json_decode($checklist->items, true) ?? [];
        $percentage = $checklist->max_score > 0 ? ($checklist->total_score / $checklist->max_score) * 100 : 0;
    @endphp

    <div class="cb-show">
        {{-- Header --}}
        <div class="cb-show__header cb-header--checklist">
            <div class="cb-show__header-left">
                <h4><i class="fas fa-clipboard-check me-2"></i>{{ $checklist->title }}</h4>
                <p>{{ $checklist->checklist_number }}@if($checklist->description) &mdash; {{ Str::limit($checklist->description, 80) }}@endif</p>
            </div>
            <div class="cb-show__header-right">
                <span class="badge bg-light text-dark"><i class="fas fa-list me-1"></i>{{ count($items) }} Items</span>
                <span class="badge bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger') }}" style="font-size: 0.85rem;">
                    {{ number_format($percentage, 1) }}% &mdash; {{ $checklist->total_score }}/{{ $checklist->max_score }}
                </span>
                @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-cog"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('checklists.edit', [$checklist->informationSheet, $checklist]) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('checklists.destroy', [$checklist->informationSheet, $checklist]) }}" method="POST" onsubmit="return confirm('Delete this checklist?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
                <a href="{{ route('information-sheets.show', ['module' => $checklist->informationSheet->module_id, 'informationSheet' => $checklist->informationSheet->id]) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        {{-- Status strip --}}
        <div class="cb-meta-strip">
            <span class="cb-meta-chip cb-meta-chip--{{ $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'red') }}">
                <i class="fas fa-chart-pie"></i> {{ number_format($percentage, 1) }}%
            </span>
            <span class="cb-meta-chip cb-meta-chip--purple"><i class="fas fa-star"></i> {{ $checklist->total_score }}/{{ $checklist->max_score }} points</span>
            @if($checklist->completed_by)
            <span class="cb-meta-chip cb-meta-chip--blue"><i class="fas fa-user"></i> {{ optional(\App\Models\User::find($checklist->completed_by))->first_name ?? 'Unknown' }}</span>
            @endif
            @if($checklist->completed_at)
            <span class="cb-meta-chip cb-meta-chip--info"><i class="fas fa-calendar"></i> {{ $checklist->completed_at->format('M d, Y') }}</span>
            @endif
            @if($checklist->evaluated_by)
            <span class="cb-meta-chip cb-meta-chip--green"><i class="fas fa-user-check"></i> Evaluated by {{ optional(\App\Models\User::find($checklist->evaluated_by))->first_name ?? 'Unknown' }}</span>
            @endif
            <span class="cb-meta-chip"><i class="fas fa-info-circle"></i> Rating: 1 (Poor) &ndash; 5 (Excellent)</span>
        </div>

        {{-- Checklist Items Grid --}}
        <div class="cb-show__body">
            <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                <h5><i class="fas fa-clipboard-check"></i> Checklist Items <span class="cb-count-badge">{{ count($items) }}</span></h5>
            </div>

            <div class="cb-grid">
                @foreach($items as $index => $item)
                @php $rating = $item['rating'] ?? 0; @endphp
                <div class="cb-item-card cb-item-card--compact">
                    <div class="cb-item-card__header">
                        <div class="left-section">
                            <span class="cb-item-card__number">{{ $index + 1 }}</span>
                            <span class="cb-item-card__title" style="font-size: 0.8rem;">{{ $item['description'] ?? '' }}</span>
                        </div>
                        <div class="right-section">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.75rem;"></i>
                            @endfor
                        </div>
                    </div>
                    @if(!empty($item['remarks']))
                    <div class="cb-item-card__body" style="padding: 0.5rem 0.75rem;">
                        <small style="color: var(--cb-text-hint);"><i class="fas fa-comment me-1"></i>{{ $item['remarks'] }}</small>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Instructor: Evaluation Form --}}
        @if(in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
        <form action="{{ route('checklists.evaluate', $checklist) }}" method="POST">
            @csrf
            <div class="cb-show__body" style="border-top: 2px solid var(--cb-border); padding-top: 1rem;">
                <div class="cb-items-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
                    <h5><i class="fas fa-clipboard-check"></i> Evaluate Student</h5>
                </div>
                <div class="cb-grid">
                    @foreach($items as $index => $item)
                    <div class="cb-item-card cb-item-card--compact">
                        <div class="cb-item-card__header">
                            <div class="left-section">
                                <span class="cb-item-card__number">{{ $index + 1 }}</span>
                                <span class="cb-item-card__title" style="font-size: 0.8rem;">{{ $item['description'] ?? '' }}</span>
                            </div>
                        </div>
                        <div class="cb-item-card__body">
                            <div style="display: flex; gap: 0.5rem;">
                                <div style="flex: 1;">
                                    <select class="form-select form-select-sm" name="items[{{ $index }}][rating]" required>
                                        @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ ($item['rating'] ?? 0) == $i ? 'selected' : '' }}>{{ $i }} - {{ ['Poor','Below Avg','Average','Good','Excellent'][$i-1] }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <input type="text" class="form-control form-control-sm" name="items[{{ $index }}][remarks]" value="{{ $item['remarks'] ?? '' }}" placeholder="Remarks">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="cb-show__footer">
                <small style="color: var(--cb-text-hint);"><i class="fas fa-info-circle me-1"></i>Rate each item and add remarks.</small>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Evaluation</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
