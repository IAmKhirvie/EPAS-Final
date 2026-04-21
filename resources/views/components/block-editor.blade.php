{{-- Block Editor Component
     Usage: @include('components.block-editor', ['existingBlocks' => $topic->blocks ?? []])
--}}
@php
    $existingBlocks = $existingBlocks ?? [];
@endphp

<div class="cb-section" id="blockEditorSection">
    <div class="cb-items-header">
        <h5><i class="fas fa-cubes"></i> Content Blocks <span class="cb-count-badge block-count-badge">{{ count($existingBlocks) }}</span></h5>
        <small style="color: var(--cb-text-hint);">Build your lesson by adding and arranging content blocks</small>
    </div>

    {{-- Add Block Toolbar --}}
    <div class="block-add-toolbar mb-3">
        <div class="block-type-buttons">
            <button type="button" class="block-type-btn" data-block-type="text" title="Rich text content">
                <i class="fas fa-align-left"></i> Text
            </button>
            <button type="button" class="block-type-btn" data-block-type="image_text" title="Image with text side by side">
                <i class="fas fa-columns"></i> Image + Text
            </button>
            <button type="button" class="block-type-btn" data-block-type="image" title="Standalone image">
                <i class="fas fa-image"></i> Image
            </button>
            <button type="button" class="block-type-btn" data-block-type="table" title="Data table">
                <i class="fas fa-table"></i> Table
            </button>
            <button type="button" class="block-type-btn" data-block-type="callout" title="Info, warning, or tip box">
                <i class="fas fa-exclamation-circle"></i> Callout
            </button>
            <button type="button" class="block-type-btn" data-block-type="heading" title="Section heading">
                <i class="fas fa-heading"></i> Heading
            </button>
            <button type="button" class="block-type-btn" data-block-type="document" title="Upload a document (PDF, Word, PPT, Excel)">
                <i class="fas fa-file-alt"></i> Document
            </button>
            <button type="button" class="block-type-btn" data-block-type="divider" title="Visual separator">
                <i class="fas fa-minus"></i> Divider
            </button>
        </div>
    </div>

    {{-- Blocks Container --}}
    <div id="blocksContainer">
        {{-- Blocks rendered here dynamically --}}
    </div>

    <div id="noBlocksMessage" class="cb-empty-state" style="{{ count($existingBlocks) > 0 ? 'display:none;' : '' }}">
        <i class="fas fa-cubes"></i>
        <p>No content blocks yet. Click a block type above to start building your lesson.</p>
    </div>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="blocks" id="blocksJsonInput" value="">

    {{-- Existing blocks data for JS --}}
    <script id="existingBlocksData" type="application/json">@json($existingBlocks)</script>
</div>

@push('styles')
<style>
/* Block Add Toolbar */
.block-add-toolbar {
    background: var(--cb-surface, #f8f9fa);
    border: 1px solid var(--cb-border, #dee2e6);
    border-radius: var(--cb-radius-sm, 8px);
    padding: 0.75rem;
}

.block-type-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.block-type-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid var(--cb-border, #dee2e6);
    border-radius: 6px;
    background: #fff;
    color: #495057;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}

.block-type-btn:hover {
    background: #e8f5e9;
    border-color: #6d9773;
    color: #2E7D32;
}

.block-type-btn i {
    font-size: 0.85rem;
}

/* Block Card */
.block-card {
    background: var(--cb-surface, #fff);
    border: 1px solid var(--cb-border, #dee2e6);
    border-radius: var(--cb-radius-sm, 8px);
    padding: 1.25rem;
    margin-bottom: 0.75rem;
    position: relative;
    transition: box-shadow 0.2s;
}

.block-card:hover {
    box-shadow: var(--cb-shadow-hover, 0 2px 8px rgba(0,0,0,0.08));
}

.block-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--cb-border, #eee);
}

.block-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: linear-gradient(135deg, #6d9773, #4a7c59);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.block-card__actions {
    display: flex;
    gap: 4px;
}

.block-card__actions .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.block-card__body {
    margin-top: 0.5rem;
}

/* Block-specific field labels */
.block-field-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.35rem;
    display: block;
}

/* Image upload area in blocks */
.block-image-upload {
    width: 100%;
    min-height: 120px;
    border: 2px dashed var(--cb-border-dashed, #ccc);
    border-radius: var(--cb-radius-sm, 8px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.2s;
    background: var(--cb-surface-alt, #fafafa);
}

.block-image-upload:hover {
    border-color: #6d9773;
    background: #e8f5e9;
}

.block-image-upload img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
}

.block-image-upload .placeholder-content {
    text-align: center;
    color: var(--cb-text-hint, #999);
    padding: 1rem;
}

.block-image-upload .placeholder-content i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Callout style preview */
.callout-style-preview {
    display: flex;
    gap: 0.5rem;
}

.callout-style-option {
    flex: 1;
    padding: 0.4rem;
    border: 2px solid transparent;
    border-radius: 6px;
    text-align: center;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.15s;
}

.callout-style-option.active {
    border-color: currentColor;
}

.callout-style-option[data-style="info"] { background: #e8f4fd; color: #1565C0; }
.callout-style-option[data-style="warning"] { background: #fff8e1; color: #E65100; }
.callout-style-option[data-style="tip"] { background: #e8f5e9; color: #2E7D32; }
.callout-style-option[data-style="danger"] { background: #fbe9e7; color: #c62828; }

/* Divider block preview */
.block-divider-preview {
    padding: 0.5rem 0;
    text-align: center;
    color: var(--cb-text-hint, #999);
    font-size: 0.8rem;
}

.block-divider-preview hr {
    border: none;
    border-top: 2px solid #e9ecef;
}

/* Document upload in blocks */
.block-doc-upload {
    width: 100%;
    min-height: 80px;
    border: 2px dashed var(--cb-border-dashed, #ccc);
    border-radius: var(--cb-radius-sm, 8px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--cb-surface-alt, #fafafa);
}

.block-doc-upload:hover {
    border-color: #6d9773;
    background: #e8f5e9;
}

.block-doc-upload .placeholder-content {
    text-align: center;
    color: var(--cb-text-hint, #999);
    padding: 1rem;
}

.block-doc-upload .doc-name {
    font-weight: 600;
    color: #333;
}

/* Dark mode */
.dark-mode .block-add-toolbar {
    background: var(--card-bg, #1a1a2e);
    border-color: var(--border, #3d3d4d);
}

.dark-mode .block-type-btn {
    background: var(--input-bg, #2d2d3d);
    border-color: var(--border, #3d3d4d);
    color: #adb5bd;
}

.dark-mode .block-type-btn:hover {
    background: rgba(109, 151, 115, 0.15);
    color: #81c784;
}

.dark-mode .block-card {
    background: var(--card-bg, #1a1a2e);
    border-color: var(--border, #3d3d4d);
}

.dark-mode .block-field-label {
    color: #adb5bd;
}

.dark-mode .block-image-upload {
    background: var(--input-bg, #2d2d3d);
    border-color: var(--border, #3d3d4d);
}

.dark-mode .block-image-upload:hover {
    border-color: #6d9773;
    background: rgba(109, 151, 115, 0.1);
}

.dark-mode .block-doc-upload {
    background: var(--input-bg, #2d2d3d);
    border-color: var(--border, #3d3d4d);
}

.dark-mode .callout-style-option[data-style="info"] { background: rgba(33, 150, 243, 0.15); }
.dark-mode .callout-style-option[data-style="warning"] { background: rgba(255, 152, 0, 0.15); }
.dark-mode .callout-style-option[data-style="tip"] { background: rgba(76, 175, 80, 0.15); }
.dark-mode .callout-style-option[data-style="danger"] { background: rgba(244, 67, 54, 0.15); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const blocksContainer = document.getElementById('blocksContainer');
    const noBlocksMsg = document.getElementById('noBlocksMessage');
    const blocksInput = document.getElementById('blocksJsonInput');
    const countBadge = document.querySelector('.block-count-badge');
    let blockCounter = 0;

    // Block type labels
    const blockTypeLabels = {
        text: '<i class="fas fa-align-left"></i> Text',
        image_text: '<i class="fas fa-columns"></i> Image + Text',
        image: '<i class="fas fa-image"></i> Image',
        table: '<i class="fas fa-table"></i> Table',
        callout: '<i class="fas fa-exclamation-circle"></i> Callout',
        divider: '<i class="fas fa-minus"></i> Divider',
        heading: '<i class="fas fa-heading"></i> Heading',
        document: '<i class="fas fa-file-alt"></i> Document',
    };

    // Generate unique block ID
    function generateId() {
        return 'blk_' + Date.now() + '_' + (blockCounter++);
    }

    // Create rich editor HTML (matches the structure in rich-editor.blade.php)
    function createRichEditorHtml(blockId, fieldName, value, toolbar, height) {
        const editorId = 'editor_' + blockId + '_' + fieldName;
        let toolbarHtml = `
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="bold" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button>
                <button type="button" class="toolbar-btn" data-command="italic" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button>
                <button type="button" class="toolbar-btn" data-command="underline" title="Underline (Ctrl+U)"><i class="fas fa-underline"></i></button>
                <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
            </div>`;

        if (toolbar === 'full') {
            toolbarHtml += `
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <select class="toolbar-select" data-command="formatBlock" title="Heading">
                    <option value="">Normal</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                </select>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left"><i class="fas fa-align-left"></i></button>
                <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center"><i class="fas fa-align-center"></i></button>
                <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right"><i class="fas fa-align-right"></i></button>
            </div>
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="insertTable" title="Insert Table"><i class="fas fa-table"></i></button>
                <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link"><i class="fas fa-link"></i></button>
                <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link"><i class="fas fa-unlink"></i></button>
            </div>`;
        }

        toolbarHtml += `
            <div class="toolbar-divider"></div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="removeFormat" title="Clear Formatting"><i class="fas fa-eraser"></i></button>
            </div>`;

        return `
        <div class="rich-editor-container" data-editor-id="${editorId}">
            <div class="rich-editor-toolbar">${toolbarHtml}</div>
            <div class="rich-editor-content" contenteditable="true" data-placeholder="Start typing..." style="min-height: ${height}px;" id="${editorId}_content">${value || ''}</div>
            <textarea name="_block_editor_${editorId}" id="${editorId}" class="rich-editor-hidden" style="display: none;">${value || ''}</textarea>
        </div>`;
    }

    // Build block body HTML by type
    function buildBlockBody(type, blockId, data) {
        data = data || {};

        switch (type) {
            case 'text':
                return `
                    <div class="block-text-editor">
                        ${createRichEditorHtml(blockId, 'content', data.content || '', 'full', 180)}
                    </div>`;

            case 'image_text':
                const pos = data.image_position || 'left';
                return `
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="block-field-label">Image</label>
                            <div class="block-image-upload" onclick="this.querySelector('input[type=file]').click()">
                                <input type="file" name="block_images[${blockId}]" accept="image/*" class="d-none" onchange="window.blockEditor.previewImage(this)">
                                ${data.image ? `<img src="${data.image}" alt="Block image">` : `
                                <div class="placeholder-content">
                                    <i class="fas fa-image d-block"></i>
                                    <small>Click to upload image</small>
                                </div>`}
                            </div>
                            <input type="hidden" class="block-existing-image" value="${data.image || ''}">
                            <div class="mt-2">
                                <label class="block-field-label">Caption <span class="text-muted">(optional)</span></label>
                                <input type="text" class="form-control form-control-sm block-caption" value="${data.caption || ''}" placeholder="Image caption...">
                            </div>
                            <div class="mt-2">
                                <label class="block-field-label">Image Position</label>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <button type="button" class="btn btn-outline-secondary block-pos-btn ${pos === 'left' ? 'active' : ''}" data-pos="left"><i class="fas fa-arrow-left me-1"></i>Left</button>
                                    <button type="button" class="btn btn-outline-secondary block-pos-btn ${pos === 'right' ? 'active' : ''}" data-pos="right">Right<i class="fas fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <label class="block-field-label">Text Content</label>
                            ${createRichEditorHtml(blockId, 'content', data.content || '', 'full', 200)}
                        </div>
                    </div>`;

            case 'image':
                const size = data.size || 'medium';
                return `
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="block-image-upload" onclick="this.querySelector('input[type=file]').click()">
                                <input type="file" name="block_images[${blockId}]" accept="image/*" class="d-none" onchange="window.blockEditor.previewImage(this)">
                                ${data.image ? `<img src="${data.image}" alt="Block image">` : `
                                <div class="placeholder-content">
                                    <i class="fas fa-image d-block"></i>
                                    <small>Click to upload image</small>
                                </div>`}
                            </div>
                            <input type="hidden" class="block-existing-image" value="${data.image || ''}">
                            <div class="row mt-2">
                                <div class="col-md-8">
                                    <label class="block-field-label">Caption <span class="text-muted">(optional)</span></label>
                                    <input type="text" class="form-control form-control-sm block-caption" value="${data.caption || ''}" placeholder="Image caption...">
                                </div>
                                <div class="col-md-4">
                                    <label class="block-field-label">Size</label>
                                    <select class="form-select form-select-sm block-size">
                                        <option value="small" ${size === 'small' ? 'selected' : ''}>Small</option>
                                        <option value="medium" ${size === 'medium' ? 'selected' : ''}>Medium</option>
                                        <option value="large" ${size === 'large' ? 'selected' : ''}>Large</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>`;

            case 'table':
                return `
                    <div class="block-table-editor">
                        ${createRichEditorHtml(blockId, 'content', data.content || '', 'full', 200)}
                    </div>`;

            case 'callout':
                const style = data.style || 'info';
                return `
                    <div class="mb-2">
                        <label class="block-field-label">Style</label>
                        <div class="callout-style-preview">
                            <div class="callout-style-option ${style === 'info' ? 'active' : ''}" data-style="info" onclick="window.blockEditor.selectCalloutStyle(this)"><i class="fas fa-info-circle"></i> Info</div>
                            <div class="callout-style-option ${style === 'warning' ? 'active' : ''}" data-style="warning" onclick="window.blockEditor.selectCalloutStyle(this)"><i class="fas fa-exclamation-triangle"></i> Warning</div>
                            <div class="callout-style-option ${style === 'tip' ? 'active' : ''}" data-style="tip" onclick="window.blockEditor.selectCalloutStyle(this)"><i class="fas fa-lightbulb"></i> Tip</div>
                            <div class="callout-style-option ${style === 'danger' ? 'active' : ''}" data-style="danger" onclick="window.blockEditor.selectCalloutStyle(this)"><i class="fas fa-exclamation-circle"></i> Danger</div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="block-field-label">Title <span class="text-muted">(optional)</span></label>
                        <input type="text" class="form-control form-control-sm block-callout-title" value="${data.title || ''}" placeholder="e.g., Important Note">
                    </div>
                    <div>
                        <label class="block-field-label">Content</label>
                        ${createRichEditorHtml(blockId, 'content', data.content || '', 'standard', 120)}
                    </div>`;

            case 'heading':
                const level = data.level || 3;
                return `
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="block-field-label">Level</label>
                            <select class="form-select form-select-sm block-heading-level">
                                <option value="2" ${level == 2 ? 'selected' : ''}>H2 - Large</option>
                                <option value="3" ${level == 3 ? 'selected' : ''}>H3 - Medium</option>
                                <option value="4" ${level == 4 ? 'selected' : ''}>H4 - Small</option>
                            </select>
                        </div>
                        <div class="col-md-9 mb-2">
                            <label class="block-field-label">Heading Text</label>
                            <input type="text" class="form-control block-heading-text" value="${data.text || ''}" placeholder="Section heading...">
                        </div>
                    </div>`;

            case 'document':
                const hasDoc = data.file_path || data.original_filename;
                return `
                    <div class="block-doc-upload" onclick="this.querySelector('input[type=file]').click()">
                        <input type="file" name="block_documents[${blockId}]" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx" class="d-none" onchange="window.blockEditor.previewDoc(this)">
                        ${hasDoc ? `
                        <div class="placeholder-content">
                            <i class="fas fa-file-alt d-block" style="font-size:2rem;color:#6d9773;"></i>
                            <span class="doc-name">${data.original_filename || 'Uploaded Document'}</span><br>
                            <small>Click to replace</small>
                        </div>` : `
                        <div class="placeholder-content">
                            <i class="fas fa-cloud-upload-alt d-block" style="font-size:2rem;"></i>
                            <small>Click to upload PDF, Word, Excel, or PowerPoint</small>
                        </div>`}
                    </div>
                    <input type="hidden" class="block-existing-doc" value="${data.file_path || ''}">
                    <input type="hidden" class="block-existing-doc-name" value="${data.original_filename || ''}">
                    <input type="hidden" class="block-existing-doc-content" value="">`;

            case 'divider':
                return `
                    <div class="block-divider-preview">
                        <hr>
                        <small>Visual separator</small>
                    </div>`;

            default:
                return '<p class="text-muted">Unknown block type</p>';
        }
    }

    // Create a block card
    function createBlockCard(type, data, existingId) {
        const blockId = existingId || generateId();
        const card = document.createElement('div');
        card.className = 'block-card';
        card.dataset.blockId = blockId;
        card.dataset.blockType = type;

        card.innerHTML = `
            <div class="block-card__header">
                <span class="block-type-badge">${blockTypeLabels[type] || type}</span>
                <div class="block-card__actions">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.blockEditor.moveUp(this)" title="Move up">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.blockEditor.moveDown(this)" title="Move down">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="window.blockEditor.removeBlock(this)" title="Delete block">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="block-card__body">
                ${buildBlockBody(type, blockId, data)}
            </div>
        `;

        return card;
    }

    // Add block
    function addBlock(type, data, existingId) {
        const card = createBlockCard(type, data, existingId);
        blocksContainer.appendChild(card);
        updateUI();

        // Initialize any rich editors inside the new block
        card.querySelectorAll('.rich-editor-container').forEach(function(editorEl) {
            if (window.initRichEditor) {
                window.initRichEditor(editorEl);
            }
        });

        // Setup position buttons
        card.querySelectorAll('.block-pos-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                card.querySelectorAll('.block-pos-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Scroll to the new block
        if (!existingId) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Update UI state
    function updateUI() {
        const cards = blocksContainer.querySelectorAll('.block-card');
        noBlocksMsg.style.display = cards.length === 0 ? 'flex' : 'none';
        if (countBadge) countBadge.textContent = cards.length;
    }

    // Collect all block data for form submission
    function collectBlocksData() {
        const blocks = [];
        blocksContainer.querySelectorAll('.block-card').forEach(function(card, index) {
            const type = card.dataset.blockType;
            const blockId = card.dataset.blockId;
            const data = {};

            switch (type) {
                case 'text':
                case 'table':
                    const textEditor = card.querySelector('.rich-editor-hidden');
                    if (textEditor) data.content = textEditor.value;
                    break;

                case 'image_text':
                    const itEditor = card.querySelectorAll('.rich-editor-hidden');
                    if (itEditor.length > 0) data.content = itEditor[itEditor.length - 1].value;
                    const activePos = card.querySelector('.block-pos-btn.active');
                    data.image_position = activePos ? activePos.dataset.pos : 'left';
                    const itCaption = card.querySelector('.block-caption');
                    if (itCaption) data.caption = itCaption.value;
                    const itExisting = card.querySelector('.block-existing-image');
                    if (itExisting && itExisting.value) data.image = itExisting.value;
                    break;

                case 'image':
                    const imgCaption = card.querySelector('.block-caption');
                    if (imgCaption) data.caption = imgCaption.value;
                    const imgSize = card.querySelector('.block-size');
                    if (imgSize) data.size = imgSize.value;
                    const imgExisting = card.querySelector('.block-existing-image');
                    if (imgExisting && imgExisting.value) data.image = imgExisting.value;
                    break;

                case 'callout':
                    const calloutEditor = card.querySelector('.rich-editor-hidden');
                    if (calloutEditor) data.content = calloutEditor.value;
                    const activeStyle = card.querySelector('.callout-style-option.active');
                    data.style = activeStyle ? activeStyle.dataset.style : 'info';
                    const calloutTitle = card.querySelector('.block-callout-title');
                    if (calloutTitle) data.title = calloutTitle.value;
                    break;

                case 'heading':
                    const headingLevel = card.querySelector('.block-heading-level');
                    if (headingLevel) data.level = parseInt(headingLevel.value);
                    const headingText = card.querySelector('.block-heading-text');
                    if (headingText) data.text = headingText.value;
                    break;

                case 'document':
                    const docPath = card.querySelector('.block-existing-doc');
                    if (docPath && docPath.value) data.file_path = docPath.value;
                    const docName = card.querySelector('.block-existing-doc-name');
                    if (docName && docName.value) data.original_filename = docName.value;
                    break;

                case 'divider':
                    // No data needed
                    break;
            }

            blocks.push({
                id: blockId,
                type: type,
                order: index,
                data: data
            });
        });

        return blocks;
    }

    // Global block editor functions
    window.blockEditor = {
        moveUp: function(btn) {
            const card = btn.closest('.block-card');
            const prev = card.previousElementSibling;
            if (prev && prev.classList.contains('block-card')) {
                blocksContainer.insertBefore(card, prev);
            }
        },

        moveDown: function(btn) {
            const card = btn.closest('.block-card');
            const next = card.nextElementSibling;
            if (next && next.classList.contains('block-card')) {
                blocksContainer.insertBefore(next, card);
            }
        },

        removeBlock: function(btn) {
            const card = btn.closest('.block-card');
            if (confirm('Remove this block?')) {
                card.remove();
                updateUI();
            }
        },

        previewImage: function(input) {
            const container = input.closest('.block-image-upload');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const existingInput = container.querySelector('input[type=hidden]');
                    // Clear existing image reference since we have a new upload
                    const card = container.closest('.block-card');
                    const existingImgInput = card.querySelector('.block-existing-image');
                    if (existingImgInput) existingImgInput.value = '';

                    container.innerHTML = `
                        <input type="file" name="${input.name}" accept="image/*" class="d-none" onchange="window.blockEditor.previewImage(this)">
                        <img src="${e.target.result}" alt="Preview">
                    `;
                };
                reader.readAsDataURL(input.files[0]);
            }
        },

        previewDoc: function(input) {
            const container = input.closest('.block-doc-upload');
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                container.innerHTML = `
                    <input type="file" name="${input.name}" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx" class="d-none" onchange="window.blockEditor.previewDoc(this)">
                    <div class="placeholder-content">
                        <i class="fas fa-file-alt d-block" style="font-size:2rem;color:#6d9773;"></i>
                        <span class="doc-name">${fileName}</span><br>
                        <small>Click to replace</small>
                    </div>
                `;
                // Clear existing doc reference
                const card = container.closest('.block-card');
                const existingDoc = card.querySelector('.block-existing-doc');
                if (existingDoc) existingDoc.value = '';
                const existingDocName = card.querySelector('.block-existing-doc-name');
                if (existingDocName) existingDocName.value = fileName;
            }
        },

        selectCalloutStyle: function(option) {
            const parent = option.closest('.callout-style-preview');
            parent.querySelectorAll('.callout-style-option').forEach(o => o.classList.remove('active'));
            option.classList.add('active');
        }
    };

    // Handle add block buttons
    document.querySelectorAll('.block-type-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            addBlock(btn.dataset.blockType);
        });
    });

    // Sync blocks data before form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const blocks = collectBlocksData();
            blocksInput.value = blocks.length > 0 ? JSON.stringify(blocks) : '';
        });
    }

    // Load existing blocks
    const existingBlocks = JSON.parse(document.getElementById('existingBlocksData').textContent);
    if (existingBlocks && existingBlocks.length > 0) {
        existingBlocks.forEach(function(block) {
            addBlock(block.type, block.data, block.id);
        });
    }
});
</script>
@endpush
