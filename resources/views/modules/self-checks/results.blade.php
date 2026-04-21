@extends('layouts.app')

@section('title', 'Self-Check Results')

@section('content')
<div class="content-area">
    @php
        $breadcrumbItems = [];
        if (in_array(auth()->user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR])) {
            $breadcrumbItems[] = ['label' => 'Content', 'url' => route('content.management')];
        } else {
            $bcCourse = $selfCheck->informationSheet->module->course ?? null;
            $bcModule = $selfCheck->informationSheet->module ?? null;
            if ($bcCourse) {
                $breadcrumbItems[] = ['label' => $bcCourse->course_code, 'url' => route('courses.show', $bcCourse)];
            }
            if ($bcModule) {
                $breadcrumbItems[] = ['label' => $bcModule->title, 'url' => route('courses.modules.show', [$bcCourse, $bcModule])];
            }
        }
        $breadcrumbItems[] = ['label' => $selfCheck->title, 'url' => route('self-checks.show', $selfCheck)];
        $breadcrumbItems[] = ['label' => 'Results'];
    @endphp
    <x-breadcrumb :items="$breadcrumbItems" />
    </nav>

    <div class="cb-container">
        {{-- Sidebar --}}
        <div class="cb-sidebar">
            <div class="cb-sidebar__title">Results Summary</div>

            {{-- Score --}}
            <div class="cb-sidebar__group">
                <div class="cb-sidebar__group-label"><i class="fas fa-chart-bar"></i> Score</div>
                <div class="cb-sidebar__info">
                    <div style="text-align: center; padding: 0.5rem 0;">
                        <div style="font-size: 2.5rem; font-weight: 700; color: {{ $passed ? '#198754' : '#dc3545' }};">
                            {{ number_format($percentage, 1) }}%
                        </div>
                        <div style="font-size: 0.8rem; color: var(--cb-text-hint);">
                            {{ $score }} / {{ $totalPoints }} points
                        </div>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-{{ $passed ? 'success' : 'danger' }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        @if($selfCheck->passing_score)
                        <div style="font-size: 0.75rem; color: var(--cb-text-hint); margin-top: 0.5rem;">
                            Passing: {{ $selfCheck->passing_score }}%
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Breakdown (only when answers are revealed) --}}
            @if($selfCheck->reveal_answers)
            <div class="cb-sidebar__group">
                <div class="cb-sidebar__group-label"><i class="fas fa-list-ol"></i> Breakdown</div>
                <div class="cb-sidebar__info">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-check text-success me-1"></i> Correct</span>
                        <strong class="text-success">{{ collect($results)->where('is_correct', true)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-times text-danger me-1"></i> Incorrect</span>
                        <strong class="text-danger">{{ collect($results)->where('is_correct', false)->count() }}</strong>
                    </div>
                    @if(collect($results)->whereNull('is_correct')->count() > 0)
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-clock text-warning me-1"></i> Pending</span>
                        <strong class="text-warning">{{ collect($results)->whereNull('is_correct')->count() }}</strong>
                    </div>
                    @endif
                    <hr style="margin: 0.5rem 0;">
                    <div class="d-flex justify-content-between">
                        <span><strong>Total</strong></span>
                        <strong>{{ count($results) }}</strong>
                    </div>
                </div>
            </div>
            @endif

            {{-- Test Details --}}
            <div class="cb-sidebar__group">
                <div class="cb-sidebar__group-label"><i class="fas fa-info-circle"></i> Test Details</div>
                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title">{{ $selfCheck->title }}</div>
                    {{ $selfCheck->check_number }}
                </div>
                <div class="cb-sidebar__info">
                    <div class="cb-sidebar__info-title">Completed</div>
                    {{ $submission->completed_at->format('M d, Y H:i') }}
                </div>
            </div>

            {{-- Actions --}}
            <div class="cb-sidebar__group" style="margin-top: auto;">
                @php
                    $backModule = $selfCheck->informationSheet->module ?? null;
                    $backCourse = $backModule?->course ?? null;
                @endphp
                @if($passed && $backModule && $backCourse)
                <a href="{{ route('courses.modules.show', [$backCourse, $backModule]) }}"
                   class="btn btn-success w-100 mb-2">
                    <i class="fas fa-arrow-right me-1"></i>Continue to Module
                </a>
                @elseif(!$passed)
                <a href="{{ route('self-checks.show', $selfCheck) }}" class="btn btn-primary w-100 btn-sm mb-2">
                    <i class="fas fa-redo me-1"></i>Try Again
                </a>
                @endif
                @if($backModule && $backCourse)
                <a href="{{ route('courses.modules.show', [$backCourse, $backModule]) }}"
                   class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Module
                </a>
                @endif
            </div>
        </div>

        {{-- Main --}}
        <div class="cb-main">
            {{-- Results Header --}}
            <div class="cb-header" style="background: linear-gradient(135deg, {{ $passed ? '#198754, #20c997' : '#dc3545, #fd7e14' }}); color: #fff;">
                <div class="text-center py-2">
                    <i class="fas {{ $passed ? 'fa-check-circle' : 'fa-times-circle' }} fa-3x mb-2" style="opacity: 0.9;"></i>
                    <h4>{{ $passed ? 'Congratulations!' : 'Keep Trying!' }}</h4>
                    <p>{{ $passed ? 'You passed the self-check assessment!' : "You didn't pass this time, but you can try again!" }}</p>
                </div>
            </div>

            <div class="cb-body">
                @if(!$selfCheck->reveal_answers)
                {{-- Hidden feedback mode: only show summary --}}
                <div class="cb-section">
                    <div class="cb-items-header">
                        <h5><i class="fas fa-chart-pie"></i> Results Summary</h5>
                    </div>
                    <div class="p-4 text-center">
                        <div style="font-size: 3rem; font-weight: 700; color: {{ $passed ? '#198754' : '#dc3545' }};">
                            {{ number_format($percentage, 1) }}%
                        </div>
                        <p class="text-muted mt-2 mb-3">{{ $score }} / {{ $totalPoints }} points</p>
                        <div class="alert alert-info d-inline-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Detailed answer feedback is not available for this quiz.
                        </div>
                    </div>
                </div>
                @else
                {{-- Detailed Results --}}
                <div class="cb-section">
                    <div class="cb-items-header">
                        <h5><i class="fas fa-list-check"></i> Detailed Results <span class="cb-count-badge">{{ count($results) }}</span></h5>
                    </div>

                    @foreach($results as $index => $result)
                    @php
                        $statusColor = $result['is_correct'] === true ? 'success' : ($result['is_correct'] === null ? 'warning' : 'danger');
                        $statusIcon = $result['is_correct'] === true ? 'fa-check-circle' : ($result['is_correct'] === null ? 'fa-clock' : 'fa-times-circle');
                    @endphp
                    <div class="cb-item-card" style="border-left: 4px solid var(--bs-{{ $statusColor }}); margin-top: 1rem;">
                        <div class="cb-item-card__header">
                            <div class="left-section">
                                <span class="cb-item-card__number" style="background: var(--bs-{{ $statusColor }});">
                                    <i class="fas {{ $statusIcon }}" style="font-size: 0.7rem;"></i>
                                </span>
                                <span class="cb-item-card__title">Question {{ $index + 1 }}</span>
                            </div>
                            <div class="right-section">
                                <span class="badge bg-{{ $statusColor }}">{{ $result['points_earned'] }} / {{ $result['question']->points }} pts</span>
                            </div>
                        </div>
                        <div class="cb-item-card__body">
                            <p class="fw-bold mb-3">{{ $result['question']->question_text }}</p>

                            @php $qType = $result['question']->question_type; @endphp

                            @switch($qType)
                                @case('multiple_choice')
                                @case('true_false')
                                @case('image_choice')
                                    @php
                                        $options = $result['question']->options ?? [];
                                        $userIndex = $result['user_answer'];
                                        $correctIndex = $result['question']->correct_answer;
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Answer:</p>
                                            <p class="text-{{ $result['is_correct'] ? 'success' : 'danger' }}">
                                                @if($qType === 'true_false')
                                                    {{ ucfirst($userIndex) ?: '(No answer)' }}
                                                @elseif(isset($options[$userIndex]))
                                                    {{ chr(65 + $userIndex) }}. {{ is_array($options[$userIndex]) ? ($options[$userIndex]['label'] ?? $options[$userIndex]) : $options[$userIndex] }}
                                                @else
                                                    (No answer)
                                                @endif
                                            </p>
                                        </div>
                                        @if(!$result['is_correct'])
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Answer:</p>
                                            <p class="text-success">
                                                @if($qType === 'true_false')
                                                    {{ ucfirst($correctIndex) }}
                                                @elseif(isset($options[$correctIndex]))
                                                    {{ chr(65 + $correctIndex) }}. {{ is_array($options[$correctIndex]) ? ($options[$correctIndex]['label'] ?? $options[$correctIndex]) : $options[$correctIndex] }}
                                                @endif
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                    @break

                                @case('multiple_select')
                                    @php
                                        $options = $result['question']->options ?? [];
                                        $userAnswers = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                        $correctAnswers = json_decode($result['question']->correct_answer, true) ?? [];
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Selections:</p>
                                            @if(empty($userAnswers))
                                                <p style="color: var(--cb-text-hint);">(No answer)</p>
                                            @else
                                                @foreach($userAnswers as $idx)
                                                <span class="badge {{ in_array($idx, $correctAnswers) ? 'bg-success' : 'bg-danger' }} me-1 mb-1">
                                                    {{ chr(65 + $idx) }}. {{ $options[$idx] ?? '' }}
                                                </span>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Answers:</p>
                                            @foreach($correctAnswers as $idx)
                                            <span class="badge bg-success me-1 mb-1">{{ chr(65 + $idx) }}. {{ $options[$idx] ?? '' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;">
                                        <i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%
                                    </p>
                                    @endif
                                    @break

                                @case('numeric')
                                @case('slider')
                                    @php
                                        $tolerance = $result['question']->options['tolerance'] ?? 0;
                                        $unit = $result['question']->options['unit'] ?? '';
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Answer:</p>
                                            <p class="text-{{ $result['is_correct'] ? 'success' : 'danger' }}">
                                                {{ $result['user_answer'] ?: '(No answer)' }} {{ $unit }}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Answer:</p>
                                            <p class="text-success">
                                                {{ $result['question']->correct_answer }} {{ $unit }}
                                                @if($tolerance > 0) (± {{ $tolerance }}) @endif
                                            </p>
                                        </div>
                                    </div>
                                    @break

                                @case('identification')
                                @case('fill_blank')
                                @case('image_identification')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Answer:</p>
                                            <p class="text-{{ $result['is_correct'] ? 'success' : 'danger' }}">
                                                {{ $result['user_answer'] ?: '(No answer)' }}
                                            </p>
                                        </div>
                                        @if(!$result['is_correct'])
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Acceptable Answers:</p>
                                            <p class="text-success">{{ $result['question']->correct_answer }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @break

                                @case('enumeration')
                                    @php
                                        $correctItems = array_map('trim', explode(',', $result['question']->correct_answer));
                                        $userText = $result['user_answer'] ?: '';
                                        $userItems = array_filter(array_map('trim', preg_split('/[\n,]+/', $userText)));
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Answer:</p>
                                            @if(empty($userItems))
                                                <p class="text-danger">(No answer)</p>
                                            @else
                                                <ol class="mb-0">
                                                    @foreach($userItems as $item)
                                                    @php
                                                        $itemMatched = false;
                                                        foreach($correctItems as $c) {
                                                            if (strtolower(trim($item)) === strtolower(trim($c)) || str_contains(strtolower($item), strtolower($c)) || str_contains(strtolower($c), strtolower($item))) {
                                                                $itemMatched = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <li class="text-{{ $itemMatched ? 'success' : 'danger' }}">
                                                        {{ $item }}
                                                        <i class="fas {{ $itemMatched ? 'fa-check-circle' : 'fa-times-circle' }} ms-1"></i>
                                                    </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Answers:</p>
                                            <ol class="mb-0 text-success">
                                                @foreach($correctItems as $item)
                                                <li>{{ $item }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                    @if($result['partial_credit'] && $result['partial_credit'] < 1)
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('matching')
                                    @php
                                        $pairs = $result['question']->options['pairs'] ?? [];
                                        $userMatches = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead style="background: var(--cb-surface-alt);">
                                                <tr><th>Column A</th><th>Your Match</th><th>Correct Match</th><th class="text-center">Result</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pairs as $pairIndex => $pair)
                                                @php
                                                    $userMatchIndex = $userMatches[$pairIndex] ?? null;
                                                    $isMatch = $userMatchIndex !== null && (int)$userMatchIndex === $pairIndex;
                                                @endphp
                                                <tr>
                                                    <td>{{ $pair['left'] }}</td>
                                                    <td class="text-{{ $isMatch ? 'success' : 'danger' }}">{{ $userMatchIndex !== null && isset($pairs[$userMatchIndex]) ? $pairs[$userMatchIndex]['right'] : '(Not matched)' }}</td>
                                                    <td class="text-success">{{ $pair['right'] }}</td>
                                                    <td class="text-center"><i class="fas {{ $isMatch ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('ordering')
                                    @php
                                        $items = $result['question']->options['items'] ?? [];
                                        $userOrder = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Order:</p>
                                            <ol class="mb-0">
                                                @foreach($userOrder as $idx)
                                                <li class="text-{{ isset($items[$idx]) && $loop->index === (int)$idx ? 'success' : 'danger' }}">{{ $items[$idx] ?? '?' }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Order:</p>
                                            <ol class="mb-0 text-success">
                                                @foreach($items as $item)<li>{{ $item }}</li>@endforeach
                                            </ol>
                                        </div>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('classification')
                                    @php
                                        $categories = $result['question']->options['categories'] ?? [];
                                        $items = $result['question']->options['items'] ?? [];
                                        $correctMapping = $result['question']->options['item_categories'] ?? [];
                                        $userMapping = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead style="background: var(--cb-surface-alt);">
                                                <tr><th>Item</th><th>Your Category</th><th>Correct Category</th><th class="text-center">Result</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $itemIndex => $item)
                                                @php
                                                    $userCat = $userMapping[$itemIndex] ?? null;
                                                    $correctCat = $correctMapping[$itemIndex] ?? null;
                                                    $isCorrect = $userCat !== null && (string)$userCat === (string)$correctCat;
                                                @endphp
                                                <tr>
                                                    <td>{{ $item }}</td>
                                                    <td class="text-{{ $isCorrect ? 'success' : 'danger' }}">{{ $userCat !== null && isset($categories[$userCat]) ? $categories[$userCat] : '(Not selected)' }}</td>
                                                    <td class="text-success">{{ $categories[$correctCat] ?? '?' }}</td>
                                                    <td class="text-center"><i class="fas {{ $isCorrect ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('hotspot')
                                    @php
                                        $userCoords = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                        $correctX = $result['question']->options['hotspot_x'] ?? 50;
                                        $correctY = $result['question']->options['hotspot_y'] ?? 50;
                                        $radius = $result['question']->options['hotspot_radius'] ?? 10;
                                    @endphp
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Click:</p>
                                            <p class="text-{{ $result['is_correct'] ? 'success' : 'danger' }}">
                                                @if(!empty($userCoords['x']) && !empty($userCoords['y']))
                                                    X: {{ $userCoords['x'] }}%, Y: {{ $userCoords['y'] }}%
                                                @else
                                                    (No click recorded)
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Target Area:</p>
                                            <p class="text-success">Center: X: {{ $correctX }}%, Y: {{ $correctY }}% (Radius: {{ $radius }}%)</p>
                                        </div>
                                    </div>
                                    @break

                                @case('image_labeling')
                                    @php
                                        $correctLabels = $result['question']->options['labels'] ?? [];
                                        $userLabels = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead style="background: var(--cb-surface-alt);">
                                                <tr><th>Part</th><th>Your Label</th><th>Correct Label</th><th class="text-center">Result</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($correctLabels as $labelIndex => $correctLabel)
                                                @php
                                                    $userLabel = $userLabels[$labelIndex] ?? '';
                                                    $isCorrect = strtolower(trim($userLabel)) === strtolower(trim($correctLabel));
                                                @endphp
                                                <tr>
                                                    <td>{{ $labelIndex + 1 }}</td>
                                                    <td class="text-{{ $isCorrect ? 'success' : 'danger' }}">{{ $userLabel ?: '(Empty)' }}</td>
                                                    <td class="text-success">{{ $correctLabel }}</td>
                                                    <td class="text-center"><i class="fas {{ $isCorrect ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('drag_drop')
                                    @php
                                        $draggables = $result['question']->options['draggables'] ?? [];
                                        $dropzones = $result['question']->options['dropzones'] ?? [];
                                        $correctMapping = $result['question']->options['correct_mapping'] ?? [];
                                        $userMapping = is_array($result['user_answer']) ? $result['user_answer'] : [];
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead style="background: var(--cb-surface-alt);">
                                                <tr><th>Drop Zone</th><th>You Placed</th><th>Correct Item</th><th class="text-center">Result</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dropzones as $zoneIndex => $zone)
                                                @php
                                                    $userItem = $userMapping[$zoneIndex] ?? null;
                                                    $correctItem = $correctMapping[$zoneIndex] ?? null;
                                                    $isCorrect = $userItem !== null && (string)$userItem === (string)$correctItem;
                                                @endphp
                                                <tr>
                                                    <td>{{ $zone }}</td>
                                                    <td class="text-{{ $isCorrect ? 'success' : 'danger' }}">{{ $userItem !== null && isset($draggables[$userItem]) ? $draggables[$userItem] : '(Not placed)' }}</td>
                                                    <td class="text-success">{{ $draggables[$correctItem] ?? '?' }}</td>
                                                    <td class="text-center"><i class="fas {{ $isCorrect ? 'fa-check text-success' : 'fa-times text-danger' }}"></i></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($result['partial_credit'])
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Partial credit: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @case('short_answer')
                                @case('essay')
                                @case('audio_question')
                                @case('video_question')
                                    <div>
                                        <p class="mb-1 cb-field-label">Your Answer:</p>
                                        <div class="p-3 rounded" style="background: var(--cb-surface-alt); border-left: 4px solid var(--bs-{{ $statusColor }});">
                                            {{ $result['user_answer'] ?: '(No answer provided)' }}
                                        </div>
                                    </div>
                                    @if($result['is_correct'] === null)
                                    <div class="cb-context-badge mt-2" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                                        <i class="fas fa-info-circle" style="color: #856404;"></i>
                                        <span style="color: #856404;">This answer requires manual grading by your instructor.</span>
                                    </div>
                                    @elseif($result['partial_credit'] && $result['partial_credit'] < 1)
                                    <p class="text-info mt-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>Keyword match: {{ number_format($result['partial_credit'] * 100, 0) }}%</p>
                                    @endif
                                    @break

                                @default
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Your Answer:</p>
                                            <p class="text-{{ $result['is_correct'] === true ? 'success' : ($result['is_correct'] === null ? 'warning' : 'danger') }}">
                                                @if(is_array($result['user_answer']))
                                                    {{ json_encode($result['user_answer']) }}
                                                @else
                                                    {{ $result['user_answer'] ?: '(No answer provided)' }}
                                                @endif
                                            </p>
                                        </div>
                                        @if($result['question']->correct_answer && $result['is_correct'] === false)
                                        <div class="col-md-6">
                                            <p class="mb-1 cb-field-label">Correct Answer:</p>
                                            <p class="text-success">{{ $result['question']->correct_answer }}</p>
                                        </div>
                                        @endif
                                    </div>
                            @endswitch

                            @if($result['question']->explanation)
                            <div class="mt-3 p-2 rounded" style="background: #fff8e1; color: #bb8954; font-size: 0.85rem;">
                                <i class="fas fa-lightbulb me-1"></i>
                                <strong>Explanation:</strong> {{ $result['question']->explanation }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
