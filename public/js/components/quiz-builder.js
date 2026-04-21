// Keyword Tags — global functions (called from inline handlers)
function handleKeywordInput(e, index) {
    if (e.key !== 'Enter' && e.key !== ',') return;
    e.preventDefault();
    var val = e.target.value.trim().replace(/,$/,'').trim();
    if (!val) return;
    addKeywordTag(index, val);
    e.target.value = '';
}
function addKeywordTag(index, keyword) {
    var container = document.querySelector('.keyword-tags[data-index="' + index + '"]');
    var input = container.querySelector('input');
    var existing = container.querySelectorAll('.keyword-tag');
    for (var i = 0; i < existing.length; i++) {
        if (existing[i].dataset.keyword.toLowerCase() === keyword.toLowerCase()) return;
    }
    var tag = document.createElement('span');
    tag.className = 'keyword-tag';
    tag.dataset.keyword = keyword;
    tag.innerHTML = keyword + '<button type="button" class="tag-remove" onclick="removeKeywordTag(this, ' + index + ')">&times;</button>';
    container.insertBefore(tag, input);
    syncKeywordHidden(index);
}
function removeKeywordTag(btn, index) {
    btn.parentElement.remove();
    syncKeywordHidden(index);
}
function syncKeywordHidden(index) {
    var container = document.querySelector('.keyword-tags[data-index="' + index + '"]');
    var hidden = document.querySelector('.keyword-hidden[data-index="' + index + '"]');
    var tags = container.querySelectorAll('.keyword-tag');
    hidden.value = Array.from(tags).map(function(t) { return t.dataset.keyword; }).join(',');
}

document.addEventListener('DOMContentLoaded', function() {
    /*=========================================================================
      QUIZ BUILDER JAVASCRIPT
      Handles all question type creation and management
    =========================================================================*/

    let questionIndex = 0;
    const container = document.getElementById('questions-container');
    const emptyState = document.getElementById('empty-state');
    const questionCount = document.getElementById('question-count');
    const totalPoints = document.getElementById('total-points');
    const saveBtn = document.getElementById('save-btn') || document.getElementById('update-btn');
    const csrfToken = window.quizConfig.csrf;

    // Question type configurations
    const questionTypes = {
        // Basic Types
        multiple_choice: {
            name: 'Multiple Choice',
            icon: 'fa-list-ul',
            color: '#bb8954',
            bgColor: '#fff8e1'
        },
        multiple_select: {
            name: 'Multiple Select',
            icon: 'fa-check-double',
            color: '#0c3a2d',
            bgColor: '#e8f5e9'
        },
        true_false: {
            name: 'True / False',
            icon: 'fa-check-circle',
            color: '#00838f',
            bgColor: '#e0f7fa'
        },
        fill_blank: {
            name: 'Fill in the Blank',
            icon: 'fa-i-cursor',
            color: '#7b1fa2',
            bgColor: '#f3e5f5'
        },
        identification: {
            name: 'Identification',
            icon: 'fa-pen',
            color: '#5c6bc0',
            bgColor: '#e8eaf6'
        },
        short_answer: {
            name: 'Short Answer',
            icon: 'fa-align-left',
            color: '#ef6c00',
            bgColor: '#fff3e0'
        },
        enumeration: {
            name: 'Enumeration',
            icon: 'fa-list-ol',
            color: '#8d6e63',
            bgColor: '#efebe9'
        },
        numeric: {
            name: 'Numeric',
            icon: 'fa-calculator',
            color: '#0277bd',
            bgColor: '#e1f5fe'
        },
        essay: {
            name: 'Essay',
            icon: 'fa-file-alt',
            color: '#d84315',
            bgColor: '#fbe9e7'
        },
        // Interactive Types
        matching: {
            name: 'Matching',
            icon: 'fa-arrows-alt-h',
            color: '#2e7d32',
            bgColor: '#e8f5e9'
        },
        ordering: {
            name: 'Ordering',
            icon: 'fa-sort-numeric-down',
            color: '#00695c',
            bgColor: '#e0f2f1'
        },
        classification: {
            name: 'Classification',
            icon: 'fa-th-large',
            color: '#558b2f',
            bgColor: '#f1f8e9'
        },
        drag_drop: {
            name: 'Drag & Drop',
            icon: 'fa-hand-pointer',
            color: '#4caf50',
            bgColor: '#e8f5e9'
        },
        slider: {
            name: 'Slider',
            icon: 'fa-sliders-h',
            color: '#009688',
            bgColor: '#e0f2f1'
        },
        // Image-Based Types
        image_choice: {
            name: 'Image Choice',
            icon: 'fa-images',
            color: '#c2185b',
            bgColor: '#fce4ec'
        },
        image_identification: {
            name: 'Name This Picture',
            icon: 'fa-search',
            color: '#ad1457',
            bgColor: '#f8bbd9'
        },
        hotspot: {
            name: 'Hotspot',
            icon: 'fa-crosshairs',
            color: '#c62828',
            bgColor: '#ffebee'
        },
        image_labeling: {
            name: 'Image Labeling',
            icon: 'fa-tags',
            color: '#b71c1c',
            bgColor: '#ffcdd2'
        },
        // Media Types
        audio_question: {
            name: 'Audio Question',
            icon: 'fa-headphones',
            color: '#6a1b9a',
            bgColor: '#f3e5f5'
        },
        video_question: {
            name: 'Video Question',
            icon: 'fa-video',
            color: '#5e35b1',
            bgColor: '#ede7f6'
        }
    };

    // Add question button handlers
    document.querySelectorAll('.cb-sidebar__item[data-type]').forEach(btn => {
        btn.addEventListener('click', () => addQuestion(btn.dataset.type));
    });

    // Add a new question
    function addQuestion(type) {
        questionIndex++;
        const config = questionTypes[type] || { name: type, icon: 'fa-question', bgColor: '#f8f9fa', color: '#333' };

        // Hide empty state
        if (emptyState) emptyState.style.display = 'none';

        // Create question card
        const card = document.createElement('div');
        card.className = 'cb-item-card';
        card.dataset.index = questionIndex;
        card.dataset.type = type;
        card.innerHTML = getQuestionHTML(type, questionIndex, config);

        container.appendChild(card);

        // Initialize handlers for this question
        initQuestionHandlers(card, type, questionIndex);

        // Update counts
        updateCounts();

        // Scroll to new question
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Enable save button
        if (saveBtn) saveBtn.disabled = false;
    }

    // Generate question HTML based on type
    function getQuestionHTML(type, index, config) {
        const baseFields = `
            <input type="hidden" name="questions[${index}][question_type]" value="${type}">

            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-question me-1"></i>
                    Question Text <span class="required">*</span>
                </label>
                ${type === 'fill_blank'
                    ? `<input type="text" class="form-control" name="questions[${index}][question_text]"
                             placeholder="Use ___ for blanks. Example: The capital of France is ___." required>
                       <div class="cb-field-hint"><i class="fas fa-info-circle me-1"></i>Use three underscores (___) where the blank should appear.</div>`
                    : `<textarea class="form-control" name="questions[${index}][question_text]"
                                 rows="2" placeholder="Enter your question here..." required></textarea>`
                }
            </div>
        `;

        const imageUpload = `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-image me-1"></i>
                    Question Image <span class="optional">(optional)</span>
                </label>
                <input type="hidden" name="questions[${index}][options][question_image]" class="image-url-input">
                <div class="cb-upload-area" onclick="document.getElementById('img_${index}').click()">
                    <i class="fas fa-cloud-upload-alt d-block"></i>
                    <span class="upload-text">Click to upload or drag image here</span>
                    <input type="file" id="img_${index}" class="d-none question-image-file" accept="image/*">
                </div>
                <div class="image-preview-container mt-2"></div>
            </div>
        `;

        const explanation = `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-lightbulb me-1"></i>
                    Explanation <span class="optional">(shown after answering)</span>
                </label>
                <input type="text" class="form-control form-control-sm" name="questions[${index}][explanation]"
                       placeholder="Explain why this is the correct answer...">
            </div>
        `;

        let typeSpecificFields = '';

        switch(type) {
            case 'multiple_choice':
                typeSpecificFields = getMultipleChoiceFields(index, false);
                break;
            case 'multiple_select':
                typeSpecificFields = getMultipleChoiceFields(index, true);
                break;
            case 'true_false':
                typeSpecificFields = getTrueFalseFields(index);
                break;
            case 'fill_blank':
            case 'identification':
                typeSpecificFields = getFillBlankFields(index);
                break;
            case 'short_answer':
            case 'enumeration':
                typeSpecificFields = getShortAnswerFields(index);
                break;
            case 'numeric':
                typeSpecificFields = getNumericFields(index);
                break;
            case 'essay':
                typeSpecificFields = getEssayFields(index);
                break;
            case 'matching':
                typeSpecificFields = getMatchingFields(index);
                break;
            case 'ordering':
                typeSpecificFields = getOrderingFields(index);
                break;
            case 'classification':
                typeSpecificFields = getClassificationFields(index);
                break;
            case 'image_choice':
                typeSpecificFields = getImageChoiceFields(index);
                break;
            case 'image_identification':
                typeSpecificFields = getImageIdentificationFields(index);
                break;
            case 'hotspot':
                typeSpecificFields = getHotspotFields(index);
                break;
            case 'image_labeling':
                typeSpecificFields = getImageLabelingFields(index);
                break;
            case 'drag_drop':
                typeSpecificFields = getDragDropFields(index);
                break;
            case 'slider':
                typeSpecificFields = getSliderFields(index);
                break;
            case 'audio_question':
                typeSpecificFields = getAudioQuestionFields(index);
                break;
            case 'video_question':
                typeSpecificFields = getVideoQuestionFields(index);
                break;
        }

        return `
            <div class="cb-item-card__header">
                <div class="left-section">
                    <div class="cb-item-card__number">${index}</div>
                    <span class="question-type-badge badge-${type}" style="background:${config.bgColor};color:${config.color}">
                        <i class="fas ${config.icon}"></i> ${config.name}
                    </span>
                </div>
                <div class="right-section">
                    <div class="input-group input-group-sm points-input">
                        <span class="input-group-text">Pts</span>
                        <input type="number" class="form-control points-value" name="questions[${index}][points]"
                               value="1" min="1" required>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-question" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="cb-item-card__body">
                ${baseFields}
                ${['true_false', 'essay'].includes(type) ? '' : imageUpload}
                ${typeSpecificFields}
                ${explanation}
            </div>
        `;
    }

    // Multiple Choice / Multiple Select Fields
    function getMultipleChoiceFields(index, isMultiple) {
        const inputType = isMultiple ? 'checkbox' : 'radio';
        const hint = isMultiple ? 'Check all correct answers' : 'Select the correct answer';

        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-list-ol me-1"></i>
                    Answer Options <span class="required">*</span>
                    <span class="optional ms-2">(${hint})</span>
                </label>
                <div class="options-container" data-type="${inputType}">
                    ${[0,1,2,3].map(i => `
                        <div class="option-item">
                            <input type="${inputType}" name="questions[${index}][correct_answer]${isMultiple ? '[]' : ''}"
                                   value="${i}" class="form-check-input" ${i === 0 && !isMultiple ? 'checked' : ''}>
                            <span class="option-letter">${String.fromCharCode(65 + i)}</span>
                            <input type="text" class="form-control form-control-sm"
                                   name="questions[${index}][options][]"
                                   placeholder="Option ${String.fromCharCode(65 + i)}" ${i < 2 ? 'required' : ''}>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-option"
                                    ${i < 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-option">
                    <i class="fas fa-plus me-1"></i>Add Option
                </button>
            </div>
        `;
    }

    // True/False Fields
    function getTrueFalseFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-check me-1"></i>
                    Correct Answer <span class="required">*</span>
                </label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[${index}][correct_answer]"
                               value="true" id="tf_true_${index}" checked>
                        <label class="form-check-label" for="tf_true_${index}">
                            <i class="fas fa-check text-success me-1"></i>True
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="questions[${index}][correct_answer]"
                               value="false" id="tf_false_${index}">
                        <label class="form-check-label" for="tf_false_${index}">
                            <i class="fas fa-times text-danger me-1"></i>False
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    // Fill in the Blank Fields
    function getFillBlankFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-keyboard me-1"></i>
                    Correct Answer(s) <span class="required">*</span>
                </label>
                <input type="text" class="form-control" name="questions[${index}][correct_answer]"
                       placeholder="paris, Paris, PARIS" required>
                <div class="cb-field-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Separate multiple acceptable answers with commas. Matching is case-insensitive.
                </div>
            </div>
        `;
    }

    // Keyword Tags HTML helper
    function getKeywordTagsHTML(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-key me-1"></i>
                    Keywords for Auto-Grading <span class="optional">(optional)</span>
                </label>
                <div class="keyword-tags" data-index="${index}" onclick="this.querySelector('input').focus()">
                    <input type="text" placeholder="Type a keyword and press Enter..."
                           onkeydown="handleKeywordInput(event, ${index})">
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" class="keyword-hidden" data-index="${index}" value="">
                <div class="cb-field-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Type a keyword and press Enter to add it. Each keyword found in the student's answer earns partial credit. Leave empty for manual grading only.
                </div>
            </div>`;
    }

    // Short Answer Fields
    function getShortAnswerFields(index) {
        return getKeywordTagsHTML(index) + `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-book me-1"></i>
                    Model Answer <span class="optional">(reference for grading)</span>
                </label>
                <textarea class="form-control" name="questions[${index}][options][model_answer]" rows="2"
                          placeholder="Enter the ideal answer for reference..."></textarea>
            </div>
        `;
    }

    // Numeric Fields
    function getNumericFields(index) {
        return `
            <div class="question-field">
                <div class="row">
                    <div class="col-md-6">
                        <label class="cb-field-label">
                            <i class="fas fa-hashtag me-1"></i>
                            Correct Answer <span class="required">*</span>
                        </label>
                        <input type="number" step="any" class="form-control" name="questions[${index}][correct_answer]"
                               placeholder="42" required>
                    </div>
                    <div class="col-md-6">
                        <label class="cb-field-label">
                            <i class="fas fa-plus-minus me-1"></i>
                            Tolerance (±) <span class="optional">(optional)</span>
                        </label>
                        <input type="number" step="any" class="form-control" name="questions[${index}][options][tolerance]"
                               placeholder="0.1" value="0">
                    </div>
                </div>
                <div class="cb-field-hint mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    If tolerance is 0.1 and answer is 42, values from 41.9 to 42.1 will be accepted.
                </div>
            </div>
        `;
    }

    // Essay Fields
    function getEssayFields(index) {
        return getKeywordTagsHTML(index) + `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-ruler me-1"></i>
                    Minimum Word Count <span class="optional">(optional)</span>
                </label>
                <input type="number" class="form-control" name="questions[${index}][options][min_words]"
                       placeholder="50" min="0">
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-clipboard-check me-1"></i>
                    Grading Rubric <span class="optional">(optional)</span>
                </label>
                <textarea class="form-control" name="questions[${index}][options][rubric]" rows="3"
                          placeholder="Enter grading criteria or rubric..."></textarea>
            </div>
        `;
    }

    // Matching Fields (Column A vs Column B)
    function getMatchingFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-link me-1"></i>
                    Match Pairs <span class="required">*</span>
                </label>
                <div class="matching-container">
                    <div class="matching-column">
                        <div class="matching-column-title">Column A</div>
                        <div class="matching-left-items">
                            ${[1,2,3].map(i => `
                                <div class="matching-pair">
                                    <span class="pair-number">${i}</span>
                                    <input type="text" class="form-control form-control-sm"
                                           name="questions[${index}][options][left][]"
                                           placeholder="Term ${i}" ${i <= 2 ? 'required' : ''}>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="matching-arrow">
                        <i class="fas fa-arrows-alt-h fa-lg"></i>
                    </div>
                    <div class="matching-column">
                        <div class="matching-column-title">Column B</div>
                        <div class="matching-right-items">
                            ${[1,2,3].map(i => `
                                <div class="matching-pair">
                                    <span class="pair-number" style="background:#6c757d">${String.fromCharCode(64 + i)}</span>
                                    <input type="text" class="form-control form-control-sm"
                                           name="questions[${index}][options][right][]"
                                           placeholder="Definition ${i}" ${i <= 2 ? 'required' : ''}>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-match-pair">
                    <i class="fas fa-plus me-1"></i>Add Pair
                </button>
                <div class="cb-field-hint mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Items will be shuffled when displayed to students. They must match Column A to Column B.
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" value="matching">
            </div>
        `;
    }

    // Ordering Fields
    function getOrderingFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-sort me-1"></i>
                    Items in Correct Order <span class="required">*</span>
                </label>
                <div class="ordering-container">
                    ${[1,2,3].map(i => `
                        <div class="ordering-item">
                            <span class="ordering-handle"><i class="fas fa-grip-vertical"></i></span>
                            <span class="ordering-number">${i}</span>
                            <input type="text" class="form-control form-control-sm"
                                   name="questions[${index}][options][]"
                                   placeholder="Step ${i}" ${i <= 2 ? 'required' : ''}>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-order-item"
                                    ${i <= 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-order-item">
                    <i class="fas fa-plus me-1"></i>Add Item
                </button>
                <div class="cb-field-hint mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Enter items in the correct order. They will be shuffled when displayed to students.
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" value="ordering">
            </div>
        `;
    }

    // Classification Fields
    function getClassificationFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-folder me-1"></i>
                    Categories <span class="required">*</span>
                </label>
                <div class="categories-container mb-3">
                    ${[1,2].map(i => `
                        <div class="category-item mb-2">
                            <div class="input-group">
                                <span class="input-group-text">Category ${i}</span>
                                <input type="text" class="form-control"
                                       name="questions[${index}][options][categories][]"
                                       placeholder="Category name" required>
                                <button type="button" class="btn btn-outline-danger remove-category"
                                        ${i <= 2 ? 'style="display:none"' : ''}>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-category">
                    <i class="fas fa-plus me-1"></i>Add Category
                </button>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-tags me-1"></i>
                    Items to Classify <span class="required">*</span>
                </label>
                <div class="classification-items-container">
                    ${[1,2,3,4].map(i => `
                        <div class="input-group mb-2">
                            <input type="text" class="form-control"
                                   name="questions[${index}][options][items][]"
                                   placeholder="Item ${i}" ${i <= 2 ? 'required' : ''}>
                            <select class="form-select" style="max-width:150px"
                                    name="questions[${index}][options][item_categories][]" required>
                                <option value="0">Category 1</option>
                                <option value="1">Category 2</option>
                            </select>
                            <button type="button" class="btn btn-outline-danger remove-class-item"
                                    ${i <= 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-class-item">
                    <i class="fas fa-plus me-1"></i>Add Item
                </button>
                <input type="hidden" name="questions[${index}][correct_answer]" value="classification">
            </div>
        `;
    }

    // Image Choice Fields
    function getImageChoiceFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-images me-1"></i>
                    Image Options <span class="required">*</span>
                </label>
                <div class="image-options-container row g-2">
                    ${[0,1,2,3].map(i => `
                        <div class="col-md-6 image-option-item">
                            <div class="card h-100">
                                <div class="card-body text-center p-2">
                                    <div class="form-check d-inline-block">
                                        <input type="radio" class="form-check-input"
                                               name="questions[${index}][correct_answer]"
                                               value="${i}" ${i === 0 ? 'checked' : ''}>
                                    </div>
                                    <div class="image-upload-mini" onclick="document.getElementById('opt_img_${index}_${i}').click()">
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                        <input type="file" id="opt_img_${index}_${i}" class="d-none option-image-file"
                                               data-option="${i}" accept="image/*">
                                        <input type="hidden" name="questions[${index}][options][images][]" class="option-image-url">
                                    </div>
                                    <input type="text" class="form-control form-control-sm mt-2"
                                           name="questions[${index}][options][labels][]"
                                           placeholder="Option ${String.fromCharCode(65 + i)}" ${i < 2 ? 'required' : ''}>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-image-option">
                    <i class="fas fa-plus me-1"></i>Add Option
                </button>
            </div>
        `;
    }

    // Image Identification (Name This Picture) Fields
    function getImageIdentificationFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-image me-1"></i>
                    Image to Identify <span class="required">*</span>
                </label>
                <input type="hidden" name="questions[${index}][options][main_image]" class="main-image-url" required>
                <div class="cb-upload-area large-upload" onclick="document.getElementById('identify_img_${index}').click()">
                    <i class="fas fa-cloud-upload-alt d-block"></i>
                    <span class="upload-text">Upload the image students need to identify</span>
                    <input type="file" id="identify_img_${index}" class="d-none main-image-file" accept="image/*" required>
                </div>
                <div class="main-image-preview mt-2"></div>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-keyboard me-1"></i>
                    Correct Answer(s) <span class="required">*</span>
                </label>
                <input type="text" class="form-control" name="questions[${index}][correct_answer]"
                       placeholder="resistor, Resistor, RESISTOR" required>
                <div class="cb-field-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Separate multiple acceptable answers with commas (case-insensitive).
                </div>
            </div>
        `;
    }

    // Hotspot Fields (Click on image area)
    function getHotspotFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-image me-1"></i>
                    Hotspot Image <span class="required">*</span>
                </label>
                <input type="hidden" name="questions[${index}][options][hotspot_image]" class="hotspot-image-url">
                <div class="cb-upload-area" onclick="document.getElementById('hotspot_img_${index}').click()">
                    <i class="fas fa-cloud-upload-alt d-block"></i>
                    <span class="upload-text">Upload the image with the target area</span>
                    <input type="file" id="hotspot_img_${index}" class="d-none hotspot-image-file" accept="image/*">
                </div>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-crosshairs me-1"></i>
                    Click on the Image to Set Hotspot <span class="required">*</span>
                </label>
                <div class="hotspot-canvas-container" id="hotspot_canvas_${index}">
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-image fa-3x mb-2"></i>
                        <p>Upload an image first, then click to set the hotspot location</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label small">X Position (%)</label>
                        <input type="number" class="form-control form-control-sm hotspot-x"
                               name="questions[${index}][options][hotspot_x]" value="50" min="0" max="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Y Position (%)</label>
                        <input type="number" class="form-control form-control-sm hotspot-y"
                               name="questions[${index}][options][hotspot_y]" value="50" min="0" max="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Radius (%)</label>
                        <input type="range" class="form-range hotspot-radius"
                               name="questions[${index}][options][hotspot_radius]" value="10" min="5" max="30">
                        <small class="text-muted">Tolerance: <span class="radius-value">10</span>%</small>
                    </div>
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" value="hotspot">
            </div>
        `;
    }

    // Image Labeling Fields
    function getImageLabelingFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-image me-1"></i>
                    Image to Label <span class="required">*</span>
                </label>
                <input type="hidden" name="questions[${index}][options][label_image]" class="label-image-url">
                <div class="cb-upload-area" onclick="document.getElementById('label_img_${index}').click()">
                    <i class="fas fa-cloud-upload-alt d-block"></i>
                    <span class="upload-text">Upload the image with parts to label</span>
                    <input type="file" id="label_img_${index}" class="d-none label-image-file" accept="image/*">
                </div>
                <div class="label-image-preview mt-2"></div>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-tags me-1"></i>
                    Labels (Correct Answers) <span class="required">*</span>
                </label>
                <div class="labels-container">
                    ${[1,2,3].map(i => `
                        <div class="input-group mb-2 label-item">
                            <span class="input-group-text">${i}</span>
                            <input type="text" class="form-control"
                                   name="questions[${index}][options][labels][]"
                                   placeholder="Label for part ${i}" ${i <= 2 ? 'required' : ''}>
                            <button type="button" class="btn btn-outline-danger remove-label"
                                    ${i <= 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-label">
                    <i class="fas fa-plus me-1"></i>Add Label
                </button>
                <div class="cb-field-hint mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Students will need to enter these labels in order.
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" value="labeling">
            </div>
        `;
    }

    // Drag & Drop Fields
    function getDragDropFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-hand-rock me-1"></i>
                    Draggable Items <span class="required">*</span>
                </label>
                <div class="draggables-container">
                    ${[1,2,3].map(i => `
                        <div class="input-group mb-2 draggable-item">
                            <span class="input-group-text">${i}</span>
                            <input type="text" class="form-control"
                                   name="questions[${index}][options][draggables][]"
                                   placeholder="Draggable item ${i}" ${i <= 2 ? 'required' : ''}>
                            <button type="button" class="btn btn-outline-danger remove-draggable"
                                    ${i <= 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-draggable">
                    <i class="fas fa-plus me-1"></i>Add Item
                </button>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-bullseye me-1"></i>
                    Drop Zones <span class="required">*</span>
                </label>
                <div class="dropzones-container">
                    ${[1,2,3].map(i => `
                        <div class="input-group mb-2 dropzone-item">
                            <span class="input-group-text bg-success text-white">${String.fromCharCode(64 + i)}</span>
                            <input type="text" class="form-control dropzone-name"
                                   name="questions[${index}][options][dropzones][]"
                                   placeholder="Drop zone ${String.fromCharCode(64 + i)}" ${i <= 2 ? 'required' : ''}>
                            <select class="form-select correct-mapping" style="max-width:120px"
                                    name="questions[${index}][options][correct_mapping][]">
                                <option value="0">Item 1</option>
                                <option value="1">Item 2</option>
                                <option value="2">Item 3</option>
                            </select>
                            <button type="button" class="btn btn-outline-danger remove-dropzone"
                                    ${i <= 2 ? 'style="display:none"' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-dropzone">
                    <i class="fas fa-plus me-1"></i>Add Zone
                </button>
                <div class="cb-field-hint mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Select which item should be dropped in each zone.
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" value="drag_drop">
            </div>
        `;
    }

    // Slider Fields
    function getSliderFields(index) {
        return `
            <div class="question-field">
                <div class="row">
                    <div class="col-md-3">
                        <label class="cb-field-label">Min Value</label>
                        <input type="number" step="any" class="form-control slider-min-input"
                               name="questions[${index}][options][min]" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="cb-field-label">Max Value</label>
                        <input type="number" step="any" class="form-control slider-max-input"
                               name="questions[${index}][options][max]" value="100" required>
                    </div>
                    <div class="col-md-3">
                        <label class="cb-field-label">Step</label>
                        <input type="number" step="any" class="form-control slider-step-input"
                               name="questions[${index}][options][step]" value="1">
                    </div>
                    <div class="col-md-3">
                        <label class="cb-field-label">Tolerance (±)</label>
                        <input type="number" step="any" class="form-control"
                               name="questions[${index}][options][tolerance]" value="0">
                    </div>
                </div>
            </div>
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-sliders-h me-1"></i>
                    Correct Value <span class="required">*</span>
                </label>
                <div class="slider-preview-container p-3 bg-light rounded">
                    <input type="range" class="form-range slider-preview"
                           min="0" max="100" value="50" step="1">
                    <div class="d-flex justify-content-between mt-1">
                        <span class="slider-min-display">0</span>
                        <span class="slider-value-display fw-bold">50</span>
                        <span class="slider-max-display">100</span>
                    </div>
                </div>
                <input type="hidden" name="questions[${index}][correct_answer]" class="slider-answer" value="50">
            </div>
        `;
    }

    // Audio Question Fields
    function getAudioQuestionFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-music me-1"></i>
                    Audio File <span class="required">*</span>
                </label>
                <input type="hidden" name="questions[${index}][options][audio_url]" class="audio-url-input">
                <div class="cb-upload-area audio-upload-area" onclick="document.getElementById('audio_file_${index}').click()">
                    <i class="fas fa-headphones d-block"></i>
                    <span class="upload-text">Click to upload audio (MP3, WAV, OGG - Max 20MB)</span>
                    <input type="file" id="audio_file_${index}" class="d-none audio-file-input" accept="audio/*">
                </div>
                <audio controls class="audio-preview mt-2 d-none w-100"></audio>
            </div>
            <div class="question-field">
                <div class="row">
                    <div class="col-md-6">
                        <label class="cb-field-label">Play Limit</label>
                        <input type="number" class="form-control" name="questions[${index}][options][play_limit]"
                               value="0" min="0" placeholder="0 = Unlimited">
                        <small class="text-muted">Number of times students can play the audio (0 = unlimited)</small>
                    </div>
                    <div class="col-md-6">
                        <label class="cb-field-label">Response Type</label>
                        <select class="form-select response-type-select" name="questions[${index}][options][response_type]">
                            <option value="text">Text Answer</option>
                            <option value="multiple_choice">Multiple Choice</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="question-field audio-mc-options d-none">
                <label class="cb-field-label">
                    <i class="fas fa-list-ol me-1"></i>
                    Answer Options <span class="required">*</span>
                </label>
                <div class="options-container" data-type="radio">
                    ${[0,1,2,3].map(i => `
                        <div class="option-item">
                            <input type="radio" name="questions[${index}][correct_answer]"
                                   value="${i}" class="form-check-input" ${i === 0 ? 'checked' : ''}>
                            <span class="option-letter">${String.fromCharCode(65 + i)}</span>
                            <input type="text" class="form-control form-control-sm"
                                   name="questions[${index}][options][mc_options][]"
                                   placeholder="Option ${String.fromCharCode(65 + i)}">
                        </div>
                    `).join('')}
                </div>
            </div>
            <div class="question-field audio-text-answer">
                <label class="cb-field-label">
                    <i class="fas fa-key me-1"></i>
                    Keywords for Auto-Grading <span class="optional">(optional)</span>
                </label>
                <input type="text" class="form-control" name="questions[${index}][correct_answer]"
                       placeholder="keyword1, keyword2, keyword3">
                <div class="cb-field-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Enter keywords that must appear in the answer. Leave empty for manual grading.
                </div>
            </div>
        `;
    }

    // Video Question Fields
    function getVideoQuestionFields(index) {
        return `
            <div class="question-field">
                <label class="cb-field-label">
                    <i class="fas fa-video me-1"></i>
                    Video File <span class="required">*</span>
                </label>
                <input type="hidden" name="questions[${index}][options][video_url]" class="video-url-input">
                <div class="cb-upload-area video-upload-area" onclick="document.getElementById('video_file_${index}').click()">
                    <i class="fas fa-film d-block"></i>
                    <span class="upload-text">Click to upload video (MP4, WebM - Max 100MB)</span>
                    <input type="file" id="video_file_${index}" class="d-none video-file-input" accept="video/*">
                </div>
                <video controls class="video-preview mt-2 d-none w-100" style="max-height:300px"></video>
            </div>
            <div class="question-field">
                <div class="row">
                    <div class="col-md-4">
                        <label class="cb-field-label">Start Time (sec)</label>
                        <input type="number" class="form-control" name="questions[${index}][options][start_time]"
                               value="0" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="cb-field-label">End Time (sec)</label>
                        <input type="number" class="form-control" name="questions[${index}][options][end_time]"
                               placeholder="Full video">
                    </div>
                    <div class="col-md-4">
                        <label class="cb-field-label">Response Type</label>
                        <select class="form-select response-type-select" name="questions[${index}][options][response_type]">
                            <option value="text">Text Answer</option>
                            <option value="multiple_choice">Multiple Choice</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="question-field video-mc-options d-none">
                <label class="cb-field-label">
                    <i class="fas fa-list-ol me-1"></i>
                    Answer Options <span class="required">*</span>
                </label>
                <div class="options-container" data-type="radio">
                    ${[0,1,2,3].map(i => `
                        <div class="option-item">
                            <input type="radio" name="questions[${index}][correct_answer]"
                                   value="${i}" class="form-check-input" ${i === 0 ? 'checked' : ''}>
                            <span class="option-letter">${String.fromCharCode(65 + i)}</span>
                            <input type="text" class="form-control form-control-sm"
                                   name="questions[${index}][options][mc_options][]"
                                   placeholder="Option ${String.fromCharCode(65 + i)}">
                        </div>
                    `).join('')}
                </div>
            </div>
            <div class="question-field video-text-answer">
                <label class="cb-field-label">
                    <i class="fas fa-key me-1"></i>
                    Keywords for Auto-Grading <span class="optional">(optional)</span>
                </label>
                <input type="text" class="form-control" name="questions[${index}][correct_answer]"
                       placeholder="keyword1, keyword2, keyword3">
                <div class="cb-field-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Enter keywords that must appear in the answer. Leave empty for manual grading.
                </div>
            </div>
        `;
    }

    // Initialize handlers for a question
    function initQuestionHandlers(card, type, index) {
        // Delete question
        card.querySelector('.delete-question').addEventListener('click', () => {
            if (confirm('Delete this question?')) {
                card.remove();
                updateCounts();
                if (container.querySelectorAll('.cb-item-card').length === 0) {
                    if (emptyState) emptyState.style.display = 'block';
                    if (saveBtn) saveBtn.disabled = true;
                }
            }
        });

        // Points change
        card.querySelector('.points-value').addEventListener('input', updateCounts);

        // Image upload handlers
        initImageUploads(card, index);

        // Type-specific handlers
        switch(type) {
            case 'multiple_choice':
            case 'multiple_select':
                initOptionHandlers(card, index, type === 'multiple_select');
                break;
            case 'matching':
                initMatchingHandlers(card, index);
                break;
            case 'ordering':
                initOrderingHandlers(card, index);
                break;
            case 'classification':
                initClassificationHandlers(card, index);
                break;
            case 'image_choice':
                initImageChoiceHandlers(card, index);
                break;
            case 'hotspot':
                initHotspotHandlers(card, index);
                break;
            case 'image_labeling':
                initLabelingHandlers(card, index);
                break;
            case 'image_identification':
                initImageIdentificationHandlers(card, index);
                break;
            case 'drag_drop':
                initDragDropHandlers(card, index);
                break;
            case 'slider':
                initSliderHandlers(card, index);
                break;
            case 'audio_question':
                initAudioQuestionHandlers(card, index);
                break;
            case 'video_question':
                initVideoQuestionHandlers(card, index);
                break;
        }
    }

    // Initialize image upload handlers
    function initImageUploads(card, index) {
        // Main question image upload
        const mainImageInput = card.querySelector('.question-image-file');
        if (mainImageInput) {
            mainImageInput.addEventListener('change', (e) => uploadImage(e.target, card.querySelector('.image-url-input'), card.querySelector('.image-preview-container')));
        }

        // Hotspot image
        const hotspotInput = card.querySelector('.hotspot-image-file');
        if (hotspotInput) {
            hotspotInput.addEventListener('change', (e) => {
                uploadImage(e.target, card.querySelector('.hotspot-image-url'), null, (url) => {
                    initHotspotCanvas(card, index, url);
                });
            });
        }

        // Main identification image
        const identifyInput = card.querySelector('.main-image-file');
        if (identifyInput) {
            identifyInput.addEventListener('change', (e) => {
                uploadImage(e.target, card.querySelector('.main-image-url'), card.querySelector('.main-image-preview'));
            });
        }

        // Label image
        const labelInput = card.querySelector('.label-image-file');
        if (labelInput) {
            labelInput.addEventListener('change', (e) => {
                uploadImage(e.target, card.querySelector('.label-image-url'), card.querySelector('.label-image-preview'));
            });
        }
    }

    // Upload image to server
    async function uploadImage(fileInput, urlInput, previewContainer, callback) {
        const file = fileInput.files[0];
        if (!file) return;

        const uploadArea = fileInput.closest('.cb-upload-area');
        if (uploadArea) {
            uploadArea.innerHTML = '<i class="fas fa-spinner fa-spin d-block"></i><span>Uploading...</span>';
        }

        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', csrfToken);

        try {
            const response = await fetch(window.quizConfig.uploadImageUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                urlInput.value = data.url;

                if (uploadArea) {
                    uploadArea.classList.add('has-file');
                    uploadArea.innerHTML = `
                        <i class="fas fa-check-circle d-block text-success"></i>
                        <span class="text-success d-block">Image uploaded</span>
                        <img src="${data.url}" class="image-preview mt-2 d-block mx-auto">
                    `;
                }

                if (previewContainer) {
                    previewContainer.innerHTML = `<img src="${data.url}" class="img-thumbnail" style="max-height:200px">`;
                }

                if (callback) callback(data.url);
            } else {
                throw new Error(data.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            if (uploadArea) {
                uploadArea.innerHTML = `
                    <i class="fas fa-exclamation-circle d-block text-danger"></i>
                    <span class="text-danger">Upload failed. Click to retry.</span>
                    <input type="file" class="d-none" accept="image/*">
                `;
            }
            alert('Image upload failed. Please try again.');
        }

        fileInput.value = '';
    }

    // Initialize option handlers (multiple choice)
    function initOptionHandlers(card, index, isMultiple) {
        const container = card.querySelector('.options-container');
        const addBtn = card.querySelector('.add-option');

        addBtn.addEventListener('click', () => {
            const count = container.querySelectorAll('.option-item').length;
            const inputType = isMultiple ? 'checkbox' : 'radio';
            const newOption = document.createElement('div');
            newOption.className = 'option-item';
            newOption.innerHTML = `
                <input type="${inputType}" name="questions[${index}][correct_answer]${isMultiple ? '[]' : ''}"
                       value="${count}" class="form-check-input">
                <span class="option-letter">${String.fromCharCode(65 + count)}</span>
                <input type="text" class="form-control form-control-sm"
                       name="questions[${index}][options][]" placeholder="Option ${String.fromCharCode(65 + count)}">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newOption);
            updateRemoveButtons(container, '.option-item', '.remove-option');
        });

        // Remove option handlers
        container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-option')) {
                const item = e.target.closest('.option-item');
                if (container.querySelectorAll('.option-item').length > 2) {
                    item.remove();
                    updateOptionLetters(container);
                    updateRemoveButtons(container, '.option-item', '.remove-option');
                }
            }
        });
    }

    function updateOptionLetters(container) {
        container.querySelectorAll('.option-item').forEach((item, i) => {
            item.querySelector('.option-letter').textContent = String.fromCharCode(65 + i);
            item.querySelector('input[type="radio"], input[type="checkbox"]').value = i;
        });
    }

    // Initialize matching handlers
    function initMatchingHandlers(card, index) {
        const addBtn = card.querySelector('.add-match-pair');
        const leftContainer = card.querySelector('.matching-left-items');
        const rightContainer = card.querySelector('.matching-right-items');

        addBtn.addEventListener('click', () => {
            const count = leftContainer.querySelectorAll('.matching-pair').length + 1;

            const leftPair = document.createElement('div');
            leftPair.className = 'matching-pair';
            leftPair.innerHTML = `
                <span class="pair-number">${count}</span>
                <input type="text" class="form-control form-control-sm"
                       name="questions[${index}][options][left][]" placeholder="Term ${count}">
            `;
            leftContainer.appendChild(leftPair);

            const rightPair = document.createElement('div');
            rightPair.className = 'matching-pair';
            rightPair.innerHTML = `
                <span class="pair-number" style="background:#6c757d">${String.fromCharCode(64 + count)}</span>
                <input type="text" class="form-control form-control-sm"
                       name="questions[${index}][options][right][]" placeholder="Definition ${count}">
            `;
            rightContainer.appendChild(rightPair);
        });
    }

    // Initialize ordering handlers
    function initOrderingHandlers(card, index) {
        const container = card.querySelector('.ordering-container');
        const addBtn = card.querySelector('.add-order-item');

        addBtn.addEventListener('click', () => {
            const count = container.querySelectorAll('.ordering-item').length + 1;
            const newItem = document.createElement('div');
            newItem.className = 'ordering-item';
            newItem.innerHTML = `
                <span class="ordering-handle"><i class="fas fa-grip-vertical"></i></span>
                <span class="ordering-number">${count}</span>
                <input type="text" class="form-control form-control-sm"
                       name="questions[${index}][options][]" placeholder="Step ${count}">
                <button type="button" class="btn btn-sm btn-outline-danger remove-order-item">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newItem);
            updateRemoveButtons(container, '.ordering-item', '.remove-order-item');
        });

        container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-order-item')) {
                const item = e.target.closest('.ordering-item');
                if (container.querySelectorAll('.ordering-item').length > 2) {
                    item.remove();
                    updateOrderNumbers(container);
                    updateRemoveButtons(container, '.ordering-item', '.remove-order-item');
                }
            }
        });
    }

    function updateOrderNumbers(container) {
        container.querySelectorAll('.ordering-item').forEach((item, i) => {
            item.querySelector('.ordering-number').textContent = i + 1;
        });
    }

    // Initialize classification handlers
    function initClassificationHandlers(card, index) {
        // Add category
        card.querySelector('.add-category').addEventListener('click', () => {
            const container = card.querySelector('.categories-container');
            const count = container.querySelectorAll('.category-item').length + 1;
            const newItem = document.createElement('div');
            newItem.className = 'category-item mb-2';
            newItem.innerHTML = `
                <div class="input-group">
                    <span class="input-group-text">Category ${count}</span>
                    <input type="text" class="form-control"
                           name="questions[${index}][options][categories][]" placeholder="Category name">
                    <button type="button" class="btn btn-outline-danger remove-category">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            updateCategorySelects(card, index);
        });

        // Add item
        card.querySelector('.add-class-item').addEventListener('click', () => {
            const container = card.querySelector('.classification-items-container');
            const categories = card.querySelectorAll('.categories-container input');
            const count = container.querySelectorAll('.input-group').length + 1;

            let options = '';
            categories.forEach((cat, i) => {
                options += `<option value="${i}">Category ${i + 1}</option>`;
            });

            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2';
            newItem.innerHTML = `
                <input type="text" class="form-control"
                       name="questions[${index}][options][items][]" placeholder="Item ${count}">
                <select class="form-select" style="max-width:150px"
                        name="questions[${index}][options][item_categories][]">${options}</select>
                <button type="button" class="btn btn-outline-danger remove-class-item">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newItem);
        });
    }

    function updateCategorySelects(card, index) {
        const categories = card.querySelectorAll('.categories-container input');
        const selects = card.querySelectorAll('.classification-items-container select');

        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '';
            categories.forEach((cat, i) => {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = cat.value || `Category ${i + 1}`;
                select.appendChild(option);
            });
            select.value = currentValue;
        });
    }

    // Initialize image choice handlers
    function initImageChoiceHandlers(card, index) {
        // Image option uploads
        card.querySelectorAll('.option-image-file').forEach(input => {
            input.addEventListener('change', (e) => {
                const optionIndex = e.target.dataset.option;
                const urlInput = e.target.closest('.card-body').querySelector('.option-image-url');
                const preview = e.target.closest('.image-upload-mini');

                uploadImage(e.target, urlInput, null, (url) => {
                    preview.innerHTML = `
                        <img src="${url}" class="img-fluid rounded" style="max-height:100px">
                        <input type="file" class="d-none option-image-file" data-option="${optionIndex}" accept="image/*">
                        <input type="hidden" name="questions[${index}][options][images][]" value="${url}" class="option-image-url">
                    `;
                });
            });
        });

        // Add image option
        card.querySelector('.add-image-option').addEventListener('click', () => {
            const container = card.querySelector('.image-options-container');
            const count = container.querySelectorAll('.image-option-item').length;

            const newItem = document.createElement('div');
            newItem.className = 'col-md-6 image-option-item';
            newItem.innerHTML = `
                <div class="card h-100">
                    <div class="card-body text-center p-2">
                        <div class="form-check d-inline-block">
                            <input type="radio" class="form-check-input"
                                   name="questions[${index}][correct_answer]" value="${count}">
                        </div>
                        <div class="image-upload-mini" onclick="document.getElementById('opt_img_${index}_${count}').click()">
                            <i class="fas fa-image fa-2x text-muted"></i>
                            <input type="file" id="opt_img_${index}_${count}" class="d-none option-image-file"
                                   data-option="${count}" accept="image/*">
                            <input type="hidden" name="questions[${index}][options][images][]" class="option-image-url">
                        </div>
                        <input type="text" class="form-control form-control-sm mt-2"
                               name="questions[${index}][options][labels][]"
                               placeholder="Option ${String.fromCharCode(65 + count)}">
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            initImageChoiceHandlers(card, index); // Reinit for new inputs
        });
    }

    // Initialize hotspot handlers
    function initHotspotHandlers(card, index) {
        const radiusInput = card.querySelector('.hotspot-radius');
        const radiusValue = card.querySelector('.radius-value');

        radiusInput.addEventListener('input', () => {
            radiusValue.textContent = radiusInput.value;
            updateHotspotMarker(card);
        });

        card.querySelector('.hotspot-x').addEventListener('input', () => updateHotspotMarker(card));
        card.querySelector('.hotspot-y').addEventListener('input', () => updateHotspotMarker(card));
    }

    function initHotspotCanvas(card, index, imageUrl) {
        const canvas = card.querySelector('.hotspot-canvas-container');
        canvas.innerHTML = `
            <img src="${imageUrl}" style="max-width:100%;display:block;cursor:crosshair" id="hotspot_img_${index}">
            <div class="hotspot-marker" id="hotspot_marker_${index}"></div>
        `;

        const img = canvas.querySelector('img');
        const xInput = card.querySelector('.hotspot-x');
        const yInput = card.querySelector('.hotspot-y');

        img.addEventListener('click', (e) => {
            const rect = img.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width * 100).toFixed(1);
            const y = ((e.clientY - rect.top) / rect.height * 100).toFixed(1);

            xInput.value = x;
            yInput.value = y;
            updateHotspotMarker(card);
        });

        updateHotspotMarker(card);
    }

    function updateHotspotMarker(card) {
        const marker = card.querySelector('.hotspot-marker');
        const img = card.querySelector('.hotspot-canvas-container img');
        if (!marker || !img) return;

        const x = parseFloat(card.querySelector('.hotspot-x').value) || 50;
        const y = parseFloat(card.querySelector('.hotspot-y').value) || 50;
        const radius = parseFloat(card.querySelector('.hotspot-radius').value) || 10;

        const imgRect = img.getBoundingClientRect();
        const minDim = Math.min(imgRect.width, imgRect.height);
        const pixelRadius = (radius / 100) * minDim;

        marker.style.left = `${x}%`;
        marker.style.top = `${y}%`;
        marker.style.width = `${pixelRadius * 2}px`;
        marker.style.height = `${pixelRadius * 2}px`;
        marker.style.display = 'block';
    }

    // Initialize image identification handlers
    function initImageIdentificationHandlers(card, index) {
        // Handler already set up in initImageUploads
    }

    // Initialize labeling handlers
    function initLabelingHandlers(card, index) {
        const addBtn = card.querySelector('.add-label');
        const container = card.querySelector('.labels-container');

        addBtn.addEventListener('click', () => {
            const count = container.querySelectorAll('.label-item').length + 1;
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2 label-item';
            newItem.innerHTML = `
                <span class="input-group-text">${count}</span>
                <input type="text" class="form-control"
                       name="questions[${index}][options][labels][]" placeholder="Label for part ${count}">
                <button type="button" class="btn btn-outline-danger remove-label">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newItem);
            updateRemoveButtons(container, '.label-item', '.remove-label');
        });

        container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-label')) {
                const item = e.target.closest('.label-item');
                if (container.querySelectorAll('.label-item').length > 2) {
                    item.remove();
                    updateLabelNumbers(container);
                    updateRemoveButtons(container, '.label-item', '.remove-label');
                }
            }
        });
    }

    function updateLabelNumbers(container) {
        container.querySelectorAll('.label-item').forEach((item, i) => {
            item.querySelector('.input-group-text').textContent = i + 1;
        });
    }

    // Initialize drag & drop handlers
    function initDragDropHandlers(card, index) {
        const draggablesContainer = card.querySelector('.draggables-container');
        const dropzonesContainer = card.querySelector('.dropzones-container');

        // Add draggable item
        card.querySelector('.add-draggable').addEventListener('click', () => {
            const count = draggablesContainer.querySelectorAll('.draggable-item').length + 1;
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2 draggable-item';
            newItem.innerHTML = `
                <span class="input-group-text">${count}</span>
                <input type="text" class="form-control"
                       name="questions[${index}][options][draggables][]" placeholder="Draggable item ${count}">
                <button type="button" class="btn btn-outline-danger remove-draggable">
                    <i class="fas fa-times"></i>
                </button>
            `;
            draggablesContainer.appendChild(newItem);
            updateDragDropOptions(card, index);
            updateRemoveButtons(draggablesContainer, '.draggable-item', '.remove-draggable');
        });

        // Add drop zone
        card.querySelector('.add-dropzone').addEventListener('click', () => {
            const count = dropzonesContainer.querySelectorAll('.dropzone-item').length + 1;
            const draggableCount = draggablesContainer.querySelectorAll('.draggable-item').length;
            let options = '';
            for (let i = 0; i < draggableCount; i++) {
                options += `<option value="${i}">Item ${i + 1}</option>`;
            }
            const newItem = document.createElement('div');
            newItem.className = 'input-group mb-2 dropzone-item';
            newItem.innerHTML = `
                <span class="input-group-text bg-success text-white">${String.fromCharCode(64 + count)}</span>
                <input type="text" class="form-control dropzone-name"
                       name="questions[${index}][options][dropzones][]" placeholder="Drop zone ${String.fromCharCode(64 + count)}">
                <select class="form-select correct-mapping" style="max-width:120px"
                        name="questions[${index}][options][correct_mapping][]">
                    ${options}
                </select>
                <button type="button" class="btn btn-outline-danger remove-dropzone">
                    <i class="fas fa-times"></i>
                </button>
            `;
            dropzonesContainer.appendChild(newItem);
            updateRemoveButtons(dropzonesContainer, '.dropzone-item', '.remove-dropzone');
        });

        // Remove draggable
        draggablesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-draggable')) {
                const item = e.target.closest('.draggable-item');
                if (draggablesContainer.querySelectorAll('.draggable-item').length > 2) {
                    item.remove();
                    updateDraggableNumbers(draggablesContainer);
                    updateDragDropOptions(card, index);
                    updateRemoveButtons(draggablesContainer, '.draggable-item', '.remove-draggable');
                }
            }
        });

        // Remove dropzone
        dropzonesContainer.addEventListener('click', (e) => {
            if (e.target.closest('.remove-dropzone')) {
                const item = e.target.closest('.dropzone-item');
                if (dropzonesContainer.querySelectorAll('.dropzone-item').length > 2) {
                    item.remove();
                    updateDropzoneLabels(dropzonesContainer);
                    updateRemoveButtons(dropzonesContainer, '.dropzone-item', '.remove-dropzone');
                }
            }
        });
    }

    function updateDraggableNumbers(container) {
        container.querySelectorAll('.draggable-item').forEach((item, i) => {
            item.querySelector('.input-group-text').textContent = i + 1;
        });
    }

    function updateDropzoneLabels(container) {
        container.querySelectorAll('.dropzone-item').forEach((item, i) => {
            item.querySelector('.input-group-text').textContent = String.fromCharCode(65 + i);
        });
    }

    function updateDragDropOptions(card, index) {
        const draggables = card.querySelectorAll('.draggable-item');
        const selects = card.querySelectorAll('.correct-mapping');

        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '';
            draggables.forEach((item, i) => {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Item ${i + 1}`;
                select.appendChild(option);
            });
            if (currentValue < draggables.length) {
                select.value = currentValue;
            }
        });
    }

    // Initialize slider handlers
    function initSliderHandlers(card, index) {
        const slider = card.querySelector('.slider-preview');
        const minInput = card.querySelector('.slider-min-input');
        const maxInput = card.querySelector('.slider-max-input');
        const stepInput = card.querySelector('.slider-step-input');
        const valueDisplay = card.querySelector('.slider-value-display');
        const minDisplay = card.querySelector('.slider-min-display');
        const maxDisplay = card.querySelector('.slider-max-display');
        const answerInput = card.querySelector('.slider-answer');

        // Update slider when min/max/step changes
        const updateSliderRange = () => {
            const min = parseFloat(minInput.value) || 0;
            const max = parseFloat(maxInput.value) || 100;
            const step = parseFloat(stepInput.value) || 1;

            slider.min = min;
            slider.max = max;
            slider.step = step;
            minDisplay.textContent = min;
            maxDisplay.textContent = max;

            // Clamp value
            if (parseFloat(slider.value) < min) slider.value = min;
            if (parseFloat(slider.value) > max) slider.value = max;

            valueDisplay.textContent = slider.value;
            answerInput.value = slider.value;
        };

        minInput.addEventListener('input', updateSliderRange);
        maxInput.addEventListener('input', updateSliderRange);
        stepInput.addEventListener('input', updateSliderRange);

        // Update value display when slider moves
        slider.addEventListener('input', () => {
            valueDisplay.textContent = slider.value;
            answerInput.value = slider.value;
        });
    }

    // Initialize audio question handlers
    function initAudioQuestionHandlers(card, index) {
        const audioInput = card.querySelector('.audio-file-input');
        const audioPreview = card.querySelector('.audio-preview');
        const audioUrlInput = card.querySelector('.audio-url-input');
        const uploadArea = card.querySelector('.audio-upload-area');
        const responseTypeSelect = card.querySelector('.response-type-select');
        const mcOptions = card.querySelector('.audio-mc-options');
        const textAnswer = card.querySelector('.audio-text-answer');

        // Audio file upload
        audioInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            uploadArea.innerHTML = '<i class="fas fa-spinner fa-spin d-block"></i><span>Uploading...</span>';

            const formData = new FormData();
            formData.append('audio', file);

            try {
                const response = await fetch(window.quizConfig.uploadAudioUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    audioUrlInput.value = data.url;
                    audioPreview.src = data.url;
                    audioPreview.classList.remove('d-none');
                    uploadArea.innerHTML = `
                        <i class="fas fa-check-circle text-success d-block"></i>
                        <span>Audio uploaded! Click to replace</span>
                        <input type="file" id="audio_file_${index}" class="d-none audio-file-input" accept="audio/*">
                    `;
                    // Re-attach event listener
                    uploadArea.querySelector('.audio-file-input').addEventListener('change', audioInput.onchange);
                }
            } catch (err) {
                uploadArea.innerHTML = `
                    <i class="fas fa-exclamation-triangle text-danger d-block"></i>
                    <span>Upload failed. Click to retry</span>
                    <input type="file" id="audio_file_${index}" class="d-none audio-file-input" accept="audio/*">
                `;
            }
        });

        // Response type toggle
        responseTypeSelect.addEventListener('change', () => {
            if (responseTypeSelect.value === 'multiple_choice') {
                mcOptions.classList.remove('d-none');
                textAnswer.classList.add('d-none');
            } else {
                mcOptions.classList.add('d-none');
                textAnswer.classList.remove('d-none');
            }
        });
    }

    // Initialize video question handlers
    function initVideoQuestionHandlers(card, index) {
        const videoInput = card.querySelector('.video-file-input');
        const videoPreview = card.querySelector('.video-preview');
        const videoUrlInput = card.querySelector('.video-url-input');
        const uploadArea = card.querySelector('.video-upload-area');
        const responseTypeSelect = card.querySelector('.response-type-select');
        const mcOptions = card.querySelector('.video-mc-options');
        const textAnswer = card.querySelector('.video-text-answer');

        // Video file upload
        videoInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            uploadArea.innerHTML = '<i class="fas fa-spinner fa-spin d-block"></i><span>Uploading video...</span>';

            const formData = new FormData();
            formData.append('video', file);

            try {
                const response = await fetch(window.quizConfig.uploadVideoUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    videoUrlInput.value = data.url;
                    videoPreview.src = data.url;
                    videoPreview.classList.remove('d-none');
                    uploadArea.innerHTML = `
                        <i class="fas fa-check-circle text-success d-block"></i>
                        <span>Video uploaded! Click to replace</span>
                        <input type="file" id="video_file_${index}" class="d-none video-file-input" accept="video/*">
                    `;
                    // Re-attach event listener
                    uploadArea.querySelector('.video-file-input').addEventListener('change', videoInput.onchange);
                }
            } catch (err) {
                uploadArea.innerHTML = `
                    <i class="fas fa-exclamation-triangle text-danger d-block"></i>
                    <span>Upload failed. Click to retry</span>
                    <input type="file" id="video_file_${index}" class="d-none video-file-input" accept="video/*">
                `;
            }
        });

        // Response type toggle
        responseTypeSelect.addEventListener('change', () => {
            if (responseTypeSelect.value === 'multiple_choice') {
                mcOptions.classList.remove('d-none');
                textAnswer.classList.add('d-none');
            } else {
                mcOptions.classList.add('d-none');
                textAnswer.classList.remove('d-none');
            }
        });
    }

    // Utility: Update remove button visibility
    function updateRemoveButtons(container, itemSelector, buttonSelector) {
        const items = container.querySelectorAll(itemSelector);
        items.forEach((item, i) => {
            const btn = item.querySelector(buttonSelector);
            if (btn) btn.style.display = items.length > 2 ? 'block' : 'none';
        });
    }

    // Update question and point counts
    function updateCounts() {
        const questions = container.querySelectorAll('.cb-item-card');
        questionCount.textContent = questions.length;

        let points = 0;
        questions.forEach(q => {
            const pts = parseInt(q.querySelector('.points-value').value) || 0;
            points += pts;
        });
        totalPoints.textContent = points;

        // Update question numbers
        questions.forEach((q, i) => {
            q.querySelector('.cb-item-card__number').textContent = i + 1;
        });

        // Toggle save buttons and hint
        const saveContinueBtn = document.getElementById('save-continue-btn');
        const saveHint = document.getElementById('save-hint');
        if (questions.length > 0) {
            if (saveBtn) saveBtn.disabled = false;
            if (saveContinueBtn) saveContinueBtn.disabled = false;
            if (saveHint) saveHint.classList.add('d-none');
        } else {
            if (saveBtn) saveBtn.disabled = true;
            if (saveContinueBtn) saveContinueBtn.disabled = true;
            if (saveHint) saveHint.classList.remove('d-none');
        }
    }

    // ===== LOAD EXISTING QUESTIONS (Edit Mode) =====
    function loadExistingQuestions(questions) {
        if (!questions || !questions.length) return;

        questions.forEach(q => {
            addQuestion(q.question_type);

            // Get the last added card
            const cards = container.querySelectorAll('.cb-item-card');
            const card = cards[cards.length - 1];
            if (!card) return;

            const idx = card.dataset.index;

            // Set common fields
            const questionInput = card.querySelector(`[name="questions[${idx}][question_text]"]`);
            if (questionInput) questionInput.value = q.question_text || '';

            const pointsInput = card.querySelector('.points-value');
            if (pointsInput) pointsInput.value = q.points || 1;

            const explanationInput = card.querySelector(`[name="questions[${idx}][explanation]"]`);
            if (explanationInput) explanationInput.value = q.explanation || '';

            // Restore question image if present
            if (q.question_image) {
                const imageUrlInput = card.querySelector('.image-url-input');
                if (imageUrlInput) imageUrlInput.value = q.question_image;
                const previewContainer = card.querySelector('.image-preview-container');
                if (previewContainer) {
                    previewContainer.innerHTML = `<img src="${q.question_image}" class="img-fluid rounded" style="max-height:200px">`;
                }
            }

            // Set type-specific fields
            switch(q.question_type) {
                case 'multiple_choice':
                case 'multiple_select': {
                    const optionInputs = card.querySelectorAll('.options-container .option-item input[type="text"]');
                    const optionTexts = q.option_texts || [];
                    // Fill existing options
                    optionInputs.forEach((input, i) => {
                        if (optionTexts[i]) input.value = optionTexts[i];
                    });
                    // Add more options if needed
                    const addBtn = card.querySelector('.add-option');
                    for (let i = optionInputs.length; i < optionTexts.length; i++) {
                        if (addBtn) addBtn.click();
                        const newInputs = card.querySelectorAll('.options-container .option-item input[type="text"]');
                        if (newInputs[i]) newInputs[i].value = optionTexts[i];
                    }
                    // Check correct answer
                    if (q.correct_answer !== null && q.correct_answer !== undefined) {
                        if (q.question_type === 'multiple_select' && Array.isArray(q.correct_answer)) {
                            q.correct_answer.forEach(ans => {
                                const cb = card.querySelector(`.options-container input[type="checkbox"][value="${ans}"]`);
                                if (cb) cb.checked = true;
                            });
                        } else {
                            const radio = card.querySelector(`.options-container input[type="radio"][value="${q.correct_answer}"]`);
                            if (radio) radio.checked = true;
                        }
                    }
                    break;
                }

                case 'true_false': {
                    const radio = card.querySelector(`input[type="radio"][value="${q.correct_answer}"]`);
                    if (radio) radio.checked = true;
                    break;
                }

                case 'fill_blank':
                case 'image_identification': {
                    const answerInput = card.querySelector(`[name="questions[${idx}][correct_answer]"]`);
                    if (answerInput) answerInput.value = q.correct_answer || '';
                    break;
                }

                case 'short_answer': {
                    // Restore keyword tags
                    const keywords = (q.correct_answer || '').split(',').map(k => k.trim()).filter(k => k);
                    keywords.forEach(k => addKeywordTag(idx, k));
                    // Restore model answer
                    const modelAnswer = card.querySelector(`[name="questions[${idx}][options][model_answer]"]`);
                    if (modelAnswer && (q.metadata || {}).model_answer) modelAnswer.value = q.metadata.model_answer;
                    break;
                }

                case 'numeric': {
                    const answerInput = card.querySelector(`[name="questions[${idx}][correct_answer]"]`);
                    if (answerInput) answerInput.value = q.correct_answer || '';
                    const metadata = q.metadata || {};
                    const tolInput = card.querySelector(`[name="questions[${idx}][options][tolerance]"]`);
                    if (tolInput && metadata.tolerance !== undefined) tolInput.value = metadata.tolerance;
                    break;
                }

                case 'essay': {
                    // Restore keyword tags
                    const essayKeywords = (q.correct_answer || '').split(',').map(k => k.trim()).filter(k => k && k !== 'essay');
                    essayKeywords.forEach(k => addKeywordTag(idx, k));
                    // Restore essay metadata
                    const metadata = q.metadata || {};
                    const minWords = card.querySelector(`[name="questions[${idx}][options][min_words]"]`);
                    if (minWords && metadata.min_words) minWords.value = metadata.min_words;
                    const rubric = card.querySelector(`[name="questions[${idx}][options][rubric]"]`);
                    if (rubric && metadata.rubric) rubric.value = metadata.rubric;
                    break;
                }

                case 'matching': {
                    const metadata = q.metadata || {};
                    const pairs = metadata.pairs || [];
                    const leftContainer = card.querySelector('.matching-left-items');
                    const rightContainer = card.querySelector('.matching-right-items');
                    const addPairBtn = card.querySelector('.add-match-pair');

                    // Fill existing pairs
                    const leftInputs = leftContainer ? leftContainer.querySelectorAll('input') : [];
                    const rightInputs = rightContainer ? rightContainer.querySelectorAll('input') : [];
                    pairs.forEach((pair, i) => {
                        if (i >= leftInputs.length && addPairBtn) addPairBtn.click();
                        const updatedLeft = leftContainer.querySelectorAll('input');
                        const updatedRight = rightContainer.querySelectorAll('input');
                        if (updatedLeft[i]) updatedLeft[i].value = pair.left || '';
                        if (updatedRight[i]) updatedRight[i].value = pair.right || '';
                    });
                    break;
                }

                case 'ordering': {
                    const metadata = q.metadata || {};
                    const items = metadata.items || [];
                    const orderContainer = card.querySelector('.ordering-container');
                    const addOrderBtn = card.querySelector('.add-order-item');

                    const orderInputs = orderContainer ? orderContainer.querySelectorAll('input[type="text"]') : [];
                    items.forEach((item, i) => {
                        if (i >= orderInputs.length && addOrderBtn) addOrderBtn.click();
                        const updatedInputs = orderContainer.querySelectorAll('input[type="text"]');
                        if (updatedInputs[i]) updatedInputs[i].value = item;
                    });
                    break;
                }

                case 'slider': {
                    const metadata = q.metadata || {};
                    const minInput = card.querySelector(`[name="questions[${idx}][options][min]"]`);
                    const maxInput = card.querySelector(`[name="questions[${idx}][options][max]"]`);
                    const stepInput = card.querySelector(`[name="questions[${idx}][options][step]"]`);
                    const tolInput = card.querySelector(`[name="questions[${idx}][options][tolerance]"]`);
                    if (minInput && metadata.min !== undefined) minInput.value = metadata.min;
                    if (maxInput && metadata.max !== undefined) maxInput.value = metadata.max;
                    if (stepInput && metadata.step !== undefined) stepInput.value = metadata.step;
                    if (tolInput && metadata.tolerance !== undefined) tolInput.value = metadata.tolerance;
                    // Trigger slider update
                    if (minInput) minInput.dispatchEvent(new Event('input'));
                    const slider = card.querySelector('.slider-preview');
                    const answerInput = card.querySelector('.slider-answer');
                    if (slider && q.correct_answer) {
                        slider.value = q.correct_answer;
                        slider.dispatchEvent(new Event('input'));
                    }
                    break;
                }
            }
        });

        updateCounts();
    }

    // Auto-load existing questions if in edit mode
    if (window.quizConfig && window.quizConfig.existingQuestions) {
        loadExistingQuestions(window.quizConfig.existingQuestions);
    }
});
