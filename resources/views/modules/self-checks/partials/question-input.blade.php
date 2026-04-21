{{--
    Question Input Partial
    Renders the appropriate input UI based on question type.

    Variables:
    - $question: SelfCheckQuestion model
--}}

@switch($question->question_type)
    {{-- Multiple Choice --}}
    @case('multiple_choice')
        @php
            $rawOptions = $question->options ?? [];
            if (is_string($rawOptions)) {
                $rawOptions = json_decode($rawOptions, true) ?? [];
            }
            $options = array_filter($rawOptions, fn($v, $k) => is_int($k), ARRAY_FILTER_USE_BOTH);
        @endphp
        <div class="multiple-choice-options">
            @foreach($options as $optIndex => $option)
            <div class="form-check mb-2 p-3 border rounded option-item" onclick="selectOption(this, {{ $question->id }}, {{ $optIndex }})">
                <input class="form-check-input" type="radio"
                       name="answers[{{ $question->id }}]"
                       value="{{ $optIndex }}"
                       id="q{{ $question->id }}_opt{{ $optIndex }}"
                       required>
                <label class="form-check-label w-100 cursor-pointer" for="q{{ $question->id }}_opt{{ $optIndex }}">
                    <strong class="me-2">{{ chr(65 + $optIndex) }}.</strong>
                    {{ $option }}
                </label>
            </div>
            @endforeach
        </div>
        @break

    {{-- True/False --}}
    @case('true_false')
        <div class="true-false-options d-flex gap-3">
            <div class="tf-option p-3 border rounded flex-fill option-item" onclick="selectOption(this, {{ $question->id }}, 'true')">
                <input class="form-check-input" type="radio"
                       name="answers[{{ $question->id }}]"
                       value="true"
                       id="q{{ $question->id }}_true"
                       required>
                <label class="form-check-label cursor-pointer fs-5" for="q{{ $question->id }}_true">
                    <i class="fas fa-check text-success me-2"></i>True
                </label>
            </div>
            <div class="tf-option p-3 border rounded flex-fill option-item" onclick="selectOption(this, {{ $question->id }}, 'false')">
                <input class="form-check-input" type="radio"
                       name="answers[{{ $question->id }}]"
                       value="false"
                       id="q{{ $question->id }}_false"
                       required>
                <label class="form-check-label cursor-pointer fs-5" for="q{{ $question->id }}_false">
                    <i class="fas fa-times text-danger me-2"></i>False
                </label>
            </div>
        </div>
        @break

    {{-- Fill in the Blank --}}
    @case('fill_blank')
        <div class="fill-blank-input">
            <input type="text"
                   class="form-control form-control-lg"
                   name="answers[{{ $question->id }}]"
                   placeholder="Type your answer here..."
                   required
                   autocomplete="off">
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Enter the word or phrase that fills the blank.
            </small>
        </div>
        @break

    {{-- Short Answer --}}
    @case('short_answer')
        <div class="short-answer-input">
            <textarea class="form-control"
                      name="answers[{{ $question->id }}]"
                      rows="4"
                      placeholder="Write your answer here..."
                      required></textarea>
            @if(!empty($question->options['model_answer']))
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Provide a complete answer in your own words.
            </small>
            @endif
        </div>
        @break

    {{-- Matching (Column A vs Column B) --}}
    @case('matching')
        @php
            $pairs = $question->options['pairs'] ?? [];
            $shuffledRight = collect($pairs)->pluck('right')->shuffle()->values()->all();
        @endphp
        <div class="matching-question" data-question-id="{{ $question->id }}">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle me-1"></i>
                Click an item from Column A, then click its match in Column B.
            </p>
            <div class="row">
                {{-- Column A --}}
                <div class="col-5">
                    <h6 class="text-center mb-3 fw-bold">Column A</h6>
                    @foreach($pairs as $pairIndex => $pair)
                    <div class="matching-item left-item"
                         data-index="{{ $pairIndex }}"
                         data-value="{{ $pair['left'] }}"
                         onclick="selectMatchingItem(this, 'left', {{ $question->id }})">
                        <span class="badge bg-primary me-2">{{ $pairIndex + 1 }}</span>
                        {{ $pair['left'] }}
                    </div>
                    @endforeach
                </div>

                {{-- Connection Lines Area --}}
                <div class="col-2 d-flex align-items-center justify-content-center">
                    <div class="matching-lines" id="lines_{{ $question->id }}">
                        <i class="fas fa-arrows-alt-h fa-2x text-muted"></i>
                    </div>
                </div>

                {{-- Column B (Shuffled) --}}
                <div class="col-5">
                    <h6 class="text-center mb-3 fw-bold">Column B</h6>
                    @foreach($shuffledRight as $rightIndex => $rightValue)
                    <div class="matching-item right-item"
                         data-index="{{ $rightIndex }}"
                         data-value="{{ $rightValue }}"
                         data-original-index="{{ array_search($rightValue, array_column($pairs, 'right')) }}"
                         onclick="selectMatchingItem(this, 'right', {{ $question->id }})">
                        <span class="badge bg-secondary me-2">{{ chr(65 + $rightIndex) }}</span>
                        {{ $rightValue }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Hidden inputs to store matching answers --}}
            @foreach($pairs as $pairIndex => $pair)
            <input type="hidden"
                   name="answers[{{ $question->id }}][{{ $pairIndex }}]"
                   id="match_{{ $question->id }}_{{ $pairIndex }}"
                   value="">
            @endforeach

            {{-- Match Summary --}}
            <div class="match-summary mt-3" id="summary_{{ $question->id }}">
                <small class="text-muted">Matches made: <span class="match-count">0</span>/{{ count($pairs) }}</small>
            </div>
        </div>
        @break

    {{-- Ordering --}}
    @case('ordering')
        @php
            $items = $question->options['items'] ?? [];
            $shuffledItems = collect($items)->shuffle()->values()->all();
        @endphp
        <div class="ordering-question" data-question-id="{{ $question->id }}">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle me-1"></i>
                Drag and drop items to arrange them in the correct order.
            </p>
            <div class="ordering-container" id="ordering_{{ $question->id }}">
                @foreach($shuffledItems as $itemIndex => $item)
                @php $originalIndex = array_search($item, $items); @endphp
                <div class="ordering-item"
                     data-original-index="{{ $originalIndex }}"
                     data-item="{{ $item }}"
                     draggable="true">
                    <span class="ordering-number">{{ $itemIndex + 1 }}</span>
                    <span class="ordering-grip me-2"><i class="fas fa-grip-vertical text-muted"></i></span>
                    <span class="ordering-text">{{ $item }}</span>
                </div>
                @endforeach
            </div>

            {{-- Hidden inputs to store order --}}
            @foreach($items as $itemIndex => $item)
            <input type="hidden"
                   name="answers[{{ $question->id }}][]"
                   class="order-input-{{ $question->id }}"
                   value="">
            @endforeach
        </div>
        @break

    {{-- Image Choice --}}
    @case('image_choice')
        @php
            $rawOptions = $question->options ?? [];
            if (is_string($rawOptions)) {
                $rawOptions = json_decode($rawOptions, true) ?? [];
            }
            $options = array_filter($rawOptions, fn($v, $k) => is_int($k), ARRAY_FILTER_USE_BOTH);
        @endphp
        <div class="image-choice-container">
            @foreach($options as $optIndex => $option)
            <div class="image-choice-item" onclick="selectImageOption(this, {{ $question->id }}, {{ $optIndex }})">
                <input type="radio"
                       name="answers[{{ $question->id }}]"
                       value="{{ $optIndex }}"
                       id="q{{ $question->id }}_img{{ $optIndex }}"
                       class="d-none"
                       required>
                @if(!empty($option['image']))
                <img src="{{ $option['image'] }}" alt="{{ $option['label'] ?? 'Option ' . chr(65 + $optIndex) }}">
                @else
                <div class="no-image-placeholder p-4 bg-light rounded">
                    <i class="fas fa-image fa-3x text-muted"></i>
                </div>
                @endif
                <div class="image-choice-label">
                    <strong>{{ chr(65 + $optIndex) }}.</strong>
                    {{ $option['label'] ?? 'Option ' . chr(65 + $optIndex) }}
                </div>
            </div>
            @endforeach
        </div>
        @break

    {{-- Multiple Select (Checkboxes) --}}
    @case('multiple_select')
        @php
            $rawOptions = $question->options ?? [];
            if (is_string($rawOptions)) {
                $rawOptions = json_decode($rawOptions, true) ?? [];
            }
            $options = array_filter($rawOptions, fn($v, $k) => is_int($k), ARRAY_FILTER_USE_BOTH);
        @endphp
        <div class="multiple-select-options">
            <p class="text-muted small mb-3">
                <i class="fas fa-info-circle me-1"></i>Select all answers that apply.
            </p>
            @foreach($options as $optIndex => $option)
            <div class="form-check mb-2 p-3 border rounded option-item checkbox-item" onclick="toggleCheckbox(this, {{ $question->id }}, {{ $optIndex }})">
                <input class="form-check-input" type="checkbox"
                       name="answers[{{ $question->id }}][]"
                       value="{{ $optIndex }}"
                       id="q{{ $question->id }}_opt{{ $optIndex }}">
                <label class="form-check-label w-100 cursor-pointer" for="q{{ $question->id }}_opt{{ $optIndex }}">
                    <strong class="me-2">{{ chr(65 + $optIndex) }}.</strong>
                    {{ $option }}
                </label>
            </div>
            @endforeach
        </div>
        @break

    {{-- Numeric --}}
    @case('numeric')
        <div class="numeric-input">
            <div class="input-group input-group-lg">
                <input type="number"
                       step="any"
                       class="form-control"
                       name="answers[{{ $question->id }}]"
                       placeholder="Enter your numeric answer"
                       required>
                @if(!empty($question->options['unit']))
                <span class="input-group-text">{{ $question->options['unit'] }}</span>
                @endif
            </div>
            @if(!empty($question->options['tolerance']) && $question->options['tolerance'] > 0)
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Tolerance: ± {{ $question->options['tolerance'] }}
            </small>
            @endif
        </div>
        @break

    {{-- Classification (Sort into categories) --}}
    @case('classification')
        @php
            $categories = $question->options['categories'] ?? [];
            $items = $question->options['items'] ?? [];
            $shuffledItems = collect($items)->shuffle()->values()->all();
        @endphp
        <div class="classification-question" data-question-id="{{ $question->id }}">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle me-1"></i>
                Select the correct category for each item.
            </p>
            @foreach($shuffledItems as $itemIndex => $item)
            @php $originalIndex = array_search($item, $items); @endphp
            <div class="classification-item mb-2">
                <div class="input-group">
                    <span class="input-group-text flex-grow-1">{{ $item }}</span>
                    <select class="form-select" style="max-width: 200px;"
                            name="answers[{{ $question->id }}][{{ $originalIndex }}]" required>
                        <option value="">Select category...</option>
                        @foreach($categories as $catIndex => $category)
                        <option value="{{ $catIndex }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endforeach
        </div>
        @break

    {{-- Image Identification (Name this picture) --}}
    @case('image_identification')
        <div class="image-identification-question">
            @if(!empty($question->options['main_image']))
            <div class="text-center mb-3">
                <img src="{{ $question->options['main_image'] }}"
                     alt="Identify this"
                     class="img-fluid rounded shadow"
                     style="max-height: 400px;">
            </div>
            @endif
            <input type="text"
                   class="form-control form-control-lg"
                   name="answers[{{ $question->id }}]"
                   placeholder="Type what you see in the image..."
                   required
                   autocomplete="off">
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Identify what is shown in the image above.
            </small>
        </div>
        @break

    {{-- Hotspot (Click on image area) --}}
    @case('hotspot')
        <div class="hotspot-question" data-question-id="{{ $question->id }}">
            @if(!empty($question->options['hotspot_image']))
            <p class="text-muted mb-3">
                <i class="fas fa-crosshairs me-1"></i>
                Click on the correct area of the image.
            </p>
            <div class="hotspot-image-container position-relative" style="display: inline-block;">
                <img src="{{ $question->options['hotspot_image'] }}"
                     class="img-fluid rounded"
                     id="hotspot_img_{{ $question->id }}"
                     style="max-height: 400px; cursor: crosshair;"
                     onclick="handleHotspotClick(event, {{ $question->id }})">
                <div class="hotspot-marker d-none" id="marker_{{ $question->id }}"></div>
            </div>
            <input type="hidden" name="answers[{{ $question->id }}][x]" id="hotspot_x_{{ $question->id }}">
            <input type="hidden" name="answers[{{ $question->id }}][y]" id="hotspot_y_{{ $question->id }}">
            <p class="text-muted mt-2 small" id="coords_{{ $question->id }}">
                <i class="fas fa-map-marker-alt me-1"></i>Click position: Not selected
            </p>
            @endif
        </div>
        @break

    {{-- Image Labeling --}}
    @case('image_labeling')
        @php $labels = $question->options['labels'] ?? []; @endphp
        <div class="image-labeling-question">
            @if(!empty($question->options['label_image']))
            <div class="text-center mb-3">
                <img src="{{ $question->options['label_image'] }}"
                     class="img-fluid rounded"
                     style="max-height: 400px;">
            </div>
            @endif
            <p class="text-muted mb-3">
                <i class="fas fa-tags me-1"></i>
                Enter the correct label for each numbered part.
            </p>
            @foreach($labels as $index => $label)
            <div class="input-group mb-2">
                <span class="input-group-text fw-bold">{{ $index + 1 }}</span>
                <input type="text" class="form-control"
                       name="answers[{{ $question->id }}][{{ $index }}]"
                       placeholder="Label for part {{ $index + 1 }}"
                       required>
            </div>
            @endforeach
        </div>
        @break

    {{-- Audio Question --}}
    @case('audio_question')
        <div class="audio-question" data-question-id="{{ $question->id }}">
            @if(!empty($question->options['audio_url']))
            <div class="audio-player-container mb-3 p-3 bg-light rounded">
                <audio controls class="w-100" id="audio_{{ $question->id }}"
                       @if(!empty($question->options['play_limit']) && $question->options['play_limit'] > 0)
                       data-play-limit="{{ $question->options['play_limit'] }}"
                       data-plays-remaining="{{ $question->options['play_limit'] }}"
                       @endif>
                    <source src="{{ $question->options['audio_url'] }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                @if(!empty($question->options['play_limit']) && $question->options['play_limit'] > 0)
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Play limit: <span id="plays_remaining_{{ $question->id }}">{{ $question->options['play_limit'] }}</span> plays remaining
                </small>
                @endif
            </div>
            @endif

            @if(($question->options['response_type'] ?? 'text') === 'multiple_choice' && !empty($question->options['mc_options']))
                <div class="multiple-choice-options">
                    @foreach($question->options['mc_options'] as $optIndex => $option)
                    <div class="form-check mb-2 p-3 border rounded option-item" onclick="selectOption(this, {{ $question->id }}, {{ $optIndex }})">
                        <input class="form-check-input" type="radio"
                               name="answers[{{ $question->id }}]"
                               value="{{ $optIndex }}"
                               id="q{{ $question->id }}_opt{{ $optIndex }}"
                               required>
                        <label class="form-check-label w-100 cursor-pointer" for="q{{ $question->id }}_opt{{ $optIndex }}">
                            <strong class="me-2">{{ chr(65 + $optIndex) }}.</strong>
                            {{ $option }}
                        </label>
                    </div>
                    @endforeach
                </div>
            @else
                <textarea class="form-control"
                          name="answers[{{ $question->id }}]"
                          rows="4"
                          placeholder="Write your answer here..."
                          required></textarea>
            @endif
        </div>
        @break

    {{-- Video Question --}}
    @case('video_question')
        <div class="video-question" data-question-id="{{ $question->id }}">
            @if(!empty($question->options['video_url']))
            <div class="video-player-container mb-3">
                <video controls class="w-100 rounded" style="max-height: 400px;"
                       id="video_{{ $question->id }}">
                    <source src="{{ $question->options['video_url'] }}" type="video/mp4">
                    Your browser does not support the video element.
                </video>
            </div>
            @endif

            @if(($question->options['response_type'] ?? 'text') === 'multiple_choice' && !empty($question->options['mc_options']))
                <div class="multiple-choice-options">
                    @foreach($question->options['mc_options'] as $optIndex => $option)
                    <div class="form-check mb-2 p-3 border rounded option-item" onclick="selectOption(this, {{ $question->id }}, {{ $optIndex }})">
                        <input class="form-check-input" type="radio"
                               name="answers[{{ $question->id }}]"
                               value="{{ $optIndex }}"
                               id="q{{ $question->id }}_opt{{ $optIndex }}"
                               required>
                        <label class="form-check-label w-100 cursor-pointer" for="q{{ $question->id }}_opt{{ $optIndex }}">
                            <strong class="me-2">{{ chr(65 + $optIndex) }}.</strong>
                            {{ $option }}
                        </label>
                    </div>
                    @endforeach
                </div>
            @else
                <textarea class="form-control"
                          name="answers[{{ $question->id }}]"
                          rows="4"
                          placeholder="Write your answer here..."
                          required></textarea>
            @endif
        </div>
        @break

    {{-- Drag & Drop --}}
    @case('drag_drop')
        @php
            $draggables = $question->options['draggables'] ?? [];
            $dropzones = $question->options['dropzones'] ?? [];
            $shuffledDraggables = collect($draggables)->shuffle()->values()->all();
        @endphp
        <div class="drag-drop-question" data-question-id="{{ $question->id }}">
            <p class="text-muted mb-3">
                <i class="fas fa-hand-pointer me-1"></i>
                Drag items from the left and drop them in the correct zones on the right.
            </p>

            <div class="row">
                {{-- Draggable Items --}}
                <div class="col-md-5">
                    <h6 class="text-muted mb-2 fw-bold">Items</h6>
                    <div class="draggables-container" id="draggables_{{ $question->id }}">
                        @foreach($shuffledDraggables as $item)
                        @php $originalIndex = array_search($item, $draggables); @endphp
                        <div class="draggable-item"
                             draggable="true"
                             data-index="{{ $originalIndex }}"
                             data-question="{{ $question->id }}"
                             ondragstart="dragStart(event)">
                            <i class="fas fa-grip-vertical text-muted me-2"></i>
                            {{ $item }}
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-arrows-alt-h fa-2x text-muted"></i>
                </div>

                {{-- Drop Zones --}}
                <div class="col-md-5">
                    <h6 class="text-muted mb-2 fw-bold">Drop Zones</h6>
                    @foreach($dropzones as $zoneIndex => $zone)
                    <div class="dropzone"
                         data-zone="{{ $zoneIndex }}"
                         data-question="{{ $question->id }}"
                         ondrop="drop(event, {{ $question->id }}, {{ $zoneIndex }})"
                         ondragover="allowDrop(event)"
                         ondragleave="dragLeave(event)">
                        <span class="zone-label">{{ $zone }}</span>
                        <div class="dropped-item-placeholder"></div>
                    </div>
                    <input type="hidden"
                           name="answers[{{ $question->id }}][{{ $zoneIndex }}]"
                           id="drop_answer_{{ $question->id }}_{{ $zoneIndex }}">
                    @endforeach
                </div>
            </div>
        </div>
        @break

    {{-- Slider --}}
    @case('slider')
        @php
            $min = $question->options['min'] ?? 0;
            $max = $question->options['max'] ?? 100;
            $step = $question->options['step'] ?? 1;
            $defaultValue = ($min + $max) / 2;
        @endphp
        <div class="slider-question">
            <div class="slider-container p-4 bg-light rounded">
                <input type="range" class="form-range slider-input"
                       id="slider_{{ $question->id }}"
                       name="answers[{{ $question->id }}]"
                       min="{{ $min }}"
                       max="{{ $max }}"
                       step="{{ $step }}"
                       value="{{ $defaultValue }}"
                       oninput="updateSliderDisplay({{ $question->id }}, this.value)">
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">{{ $min }}</span>
                    <span class="slider-value fw-bold fs-4" id="slider_value_{{ $question->id }}">{{ $defaultValue }}</span>
                    <span class="text-muted">{{ $max }}</span>
                </div>
            </div>
            @if(!empty($question->options['unit']))
            <small class="text-muted mt-2 d-block text-center">
                Unit: {{ $question->options['unit'] }}
            </small>
            @endif
            @if(!empty($question->options['tolerance']) && $question->options['tolerance'] > 0)
            <small class="text-muted d-block text-center">
                Tolerance: ± {{ $question->options['tolerance'] }}
            </small>
            @endif
        </div>
        @break

    {{-- Default fallback --}}
    @default
        <input type="text"
               class="form-control"
               name="answers[{{ $question->id }}]"
               placeholder="Enter your answer"
               required>
@endswitch

<script>
// Option selection handler (for multiple choice and true/false)
function selectOption(element, questionId, value) {
    // Remove selected class from siblings
    const container = element.closest('.multiple-choice-options, .true-false-options');
    if (container) {
        container.querySelectorAll('.option-item').forEach(item => {
            item.classList.remove('selected', 'border-primary', 'bg-primary-subtle');
        });
    }

    // Add selected class to clicked item
    element.classList.add('selected', 'border-primary', 'bg-primary-subtle');

    // Check the radio button
    const radio = element.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
    }
}

// Image choice selection handler
function selectImageOption(element, questionId, value) {
    // Remove selected class from siblings
    const container = element.closest('.image-choice-container');
    if (container) {
        container.querySelectorAll('.image-choice-item').forEach(item => {
            item.classList.remove('selected');
        });
    }

    // Add selected class to clicked item
    element.classList.add('selected');

    // Check the radio button
    const radio = element.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
    }
}

// Matching question state
const matchingState = {};

function selectMatchingItem(element, column, questionId) {
    if (!matchingState[questionId]) {
        matchingState[questionId] = { left: null, right: null, matches: {} };
    }

    const state = matchingState[questionId];
    const index = element.dataset.index;

    // If already matched, don't allow selection
    if (element.classList.contains('matched')) {
        return;
    }

    // Toggle selection
    if (column === 'left') {
        // Deselect previous left selection
        document.querySelectorAll(`[data-question-id="${questionId}"] .left-item.selected`).forEach(item => {
            item.classList.remove('selected');
        });

        if (state.left === index) {
            state.left = null;
        } else {
            state.left = index;
            element.classList.add('selected');
        }
    } else {
        // Deselect previous right selection
        document.querySelectorAll(`[data-question-id="${questionId}"] .right-item.selected`).forEach(item => {
            item.classList.remove('selected');
        });

        if (state.right === index) {
            state.right = null;
        } else {
            state.right = index;
            element.classList.add('selected');
        }
    }

    // Check if we have a complete match
    if (state.left !== null && state.right !== null) {
        makeMatch(questionId, state.left, state.right);
    }
}

function makeMatch(questionId, leftIndex, rightIndex) {
    const state = matchingState[questionId];
    const rightItem = document.querySelector(`[data-question-id="${questionId}"] .right-item[data-index="${rightIndex}"]`);
    const leftItem = document.querySelector(`[data-question-id="${questionId}"] .left-item[data-index="${leftIndex}"]`);
    const originalRightIndex = rightItem.dataset.originalIndex;

    // Store match
    state.matches[leftIndex] = originalRightIndex;

    // Update hidden input
    const input = document.getElementById(`match_${questionId}_${leftIndex}`);
    if (input) {
        input.value = originalRightIndex;
    }

    // Mark as matched
    leftItem.classList.remove('selected');
    rightItem.classList.remove('selected');
    leftItem.classList.add('matched');
    rightItem.classList.add('matched');

    // Add match indicator
    leftItem.innerHTML += ` <i class="fas fa-link text-success ms-2"></i>`;
    rightItem.innerHTML += ` <i class="fas fa-link text-success ms-2"></i>`;

    // Reset selection state
    state.left = null;
    state.right = null;

    // Update summary
    const matchCount = Object.keys(state.matches).length;
    const summary = document.querySelector(`#summary_${questionId} .match-count`);
    if (summary) {
        summary.textContent = matchCount;
    }
}

// Ordering drag and drop
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ordering-container').forEach(container => {
        const questionId = container.closest('.ordering-question').dataset.questionId;
        let draggedItem = null;

        container.querySelectorAll('.ordering-item').forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedItem = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });

            item.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedItem = null;
                updateOrderNumbers(container);
                updateOrderInputs(container, questionId);
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                if (draggedItem && draggedItem !== this) {
                    const rect = this.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;

                    if (e.clientY < midY) {
                        container.insertBefore(draggedItem, this);
                    } else {
                        container.insertBefore(draggedItem, this.nextSibling);
                    }
                }
            });
        });
    });
});

function updateOrderNumbers(container) {
    container.querySelectorAll('.ordering-item').forEach((item, index) => {
        item.querySelector('.ordering-number').textContent = index + 1;
    });
}

function updateOrderInputs(container, questionId) {
    const inputs = document.querySelectorAll(`.order-input-${questionId}`);
    const items = container.querySelectorAll('.ordering-item');

    items.forEach((item, index) => {
        if (inputs[index]) {
            inputs[index].value = item.dataset.originalIndex;
        }
    });
}

// Multiple Select (Checkbox) handler
function toggleCheckbox(element, questionId, value) {
    const checkbox = element.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selected', checkbox.checked);
    element.classList.toggle('border-primary', checkbox.checked);
    element.classList.toggle('bg-primary-subtle', checkbox.checked);
}

// Hotspot click handler
function handleHotspotClick(event, questionId) {
    const img = event.target;
    const rect = img.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width * 100).toFixed(2);
    const y = ((event.clientY - rect.top) / rect.height * 100).toFixed(2);

    document.getElementById(`hotspot_x_${questionId}`).value = x;
    document.getElementById(`hotspot_y_${questionId}`).value = y;

    // Show and position marker
    const marker = document.getElementById(`marker_${questionId}`);
    marker.style.left = x + '%';
    marker.style.top = y + '%';
    marker.classList.remove('d-none');

    document.getElementById(`coords_${questionId}`).innerHTML =
        `<i class="fas fa-map-marker-alt me-1"></i>Click position: (${x}%, ${y}%)`;
}

// Slider display update
function updateSliderDisplay(questionId, value) {
    document.getElementById(`slider_value_${questionId}`).textContent = value;
}

// Drag & Drop handlers
function allowDrop(event) {
    event.preventDefault();
    event.currentTarget.classList.add('drag-over');
}

function dragLeave(event) {
    event.currentTarget.classList.remove('drag-over');
}

function dragStart(event) {
    event.dataTransfer.setData('text/plain', event.target.dataset.index);
    event.dataTransfer.setData('questionId', event.target.dataset.question);
    event.target.classList.add('dragging');
}

function drop(event, questionId, zoneIndex) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');

    const draggedIndex = event.dataTransfer.getData('text/plain');
    const draggedQuestionId = event.dataTransfer.getData('questionId');

    if (draggedQuestionId != questionId) return;

    const draggedItem = document.querySelector(`.draggable-item[data-index="${draggedIndex}"][data-question="${questionId}"]`);

    if (draggedItem) {
        draggedItem.classList.remove('dragging');

        // Clone and place in dropzone
        const placeholder = event.currentTarget.querySelector('.dropped-item-placeholder');
        placeholder.innerHTML = `<span class="dropped-text">${draggedItem.textContent.trim()}</span>`;

        // Mark original as dropped
        draggedItem.classList.add('dropped');
        draggedItem.draggable = false;

        // Store the answer
        document.getElementById(`drop_answer_${questionId}_${zoneIndex}`).value = draggedIndex;

        // Mark dropzone as filled
        event.currentTarget.classList.add('filled');
    }
}

// Audio play limit enforcement
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('audio[data-play-limit]').forEach(audio => {
        const questionId = audio.id.replace('audio_', '');
        let playsRemaining = parseInt(audio.dataset.playsRemaining);

        audio.addEventListener('play', function() {
            if (playsRemaining <= 0) {
                this.pause();
                this.currentTime = 0;
                alert('You have reached the play limit for this audio.');
                return;
            }
        });

        audio.addEventListener('ended', function() {
            playsRemaining--;
            audio.dataset.playsRemaining = playsRemaining;
            const display = document.getElementById(`plays_remaining_${questionId}`);
            if (display) {
                display.textContent = playsRemaining;
            }
            if (playsRemaining <= 0) {
                this.controls = false;
                this.classList.add('disabled-audio');
            }
        });
    });
});
</script>

<style>
/* True/False: radio on left side, vertically centered */
.tf-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-left: 1rem !important;
}
.tf-option .form-check-input {
    float: none;
    margin: 0;
    flex-shrink: 0;
}

.option-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.option-item:hover {
    border-color: #ffb902 !important;
    background-color: #f8f9fa;
}

.option-item.selected {
    border-color: #ffb902 !important;
    background-color: #e7f1ff !important;
}

.cursor-pointer {
    cursor: pointer;
}

.matching-item {
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.matching-item:hover:not(.matched) {
    border-color: #ffb902;
    background-color: #f8f9fa;
}

.matching-item.selected {
    border-color: #ffb902;
    background-color: #e7f1ff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

.matching-item.matched {
    border-color: #28a745;
    background-color: #d4edda;
    cursor: default;
}

.ordering-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: move;
    background: white;
    transition: all 0.2s ease;
}

.ordering-item:hover {
    border-color: #17a2b8;
    background-color: #f8f9fa;
}

.ordering-item.dragging {
    opacity: 0.5;
    border-color: #ffb902;
    background-color: #e7f1ff;
}

.ordering-number {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #6c757d;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.ordering-grip {
    cursor: grab;
}

.ordering-text {
    flex: 1;
}

.no-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100px;
}

.bg-primary-subtle {
    background-color: #e7f1ff !important;
}

/* Hotspot Styles */
.hotspot-image-container {
    position: relative;
}

.hotspot-marker {
    position: absolute;
    width: 24px;
    height: 24px;
    background: rgba(220, 53, 69, 0.7);
    border: 3px solid #dc3545;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
    animation: hotspot-pulse 1.5s ease-in-out infinite;
}

@keyframes hotspot-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
}

/* Drag & Drop Styles */
.draggable-item {
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: white;
    cursor: grab;
    transition: all 0.2s ease;
}

.draggable-item:hover {
    border-color: #ffb902;
    background: #f8f9fa;
}

.draggable-item:active {
    cursor: grabbing;
}

.draggable-item.dragging {
    opacity: 0.5;
    border-color: #ffb902;
}

.draggable-item.dropped {
    opacity: 0.5;
    background: #e9ecef;
    cursor: not-allowed;
    text-decoration: line-through;
}

.dropzone {
    min-height: 60px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.dropzone.drag-over {
    border-color: #ffb902;
    background: #e7f1ff;
    border-style: solid;
}

.dropzone.filled {
    border-color: #28a745;
    border-style: solid;
    background: #d4edda;
}

.dropzone .zone-label {
    font-weight: 600;
    color: #495057;
    display: block;
    margin-bottom: 0.5rem;
}

.dropzone .dropped-text {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #28a745;
    color: white;
    border-radius: 4px;
    font-size: 0.875rem;
}

/* Slider Styles */
.slider-question .form-range {
    height: 8px;
}

.slider-question .form-range::-webkit-slider-thumb {
    width: 24px;
    height: 24px;
}

.slider-value {
    color: #ffb902;
}

/* Classification Styles */
.classification-item {
    transition: all 0.2s ease;
}

.classification-item:hover {
    transform: translateX(3px);
}

/* Checkbox Item (Multiple Select) */
.checkbox-item.selected {
    border-color: #ffb902 !important;
    background-color: #e7f1ff !important;
}

/* Audio disabled state */
.disabled-audio {
    pointer-events: none;
    opacity: 0.6;
}

/* Image Choice Grid */
.image-choice-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.image-choice-item {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.image-choice-item:hover {
    border-color: #ffb902;
    transform: translateY(-2px);
}

.image-choice-item.selected {
    border-color: #ffb902;
    background: #e7f1ff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

.image-choice-item img {
    max-width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
}

.image-choice-label {
    margin-top: 0.5rem;
    font-weight: 500;
}
</style>
