@extends('layouts.app')

@section('title', 'Edit Topic - EPAS-E')

@section('content')
<div class="content-area">
    <x-breadcrumb :items="[
        ['label' => 'Courses', 'url' => route('content.management')],
        ['label' => $informationSheet->module->course->course_name],
        ['label' => 'Module ' . $informationSheet->module->module_number],
        ['label' => 'Info Sheet ' . $informationSheet->sheet_number],
        ['label' => 'Edit: ' . $topic->title],
    ]" />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="cb-container--simple">
        <form action="{{ route('topics.update', [$informationSheet->id, $topic->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="cb-main">
                <div class="cb-header cb-header--topic">
                    <h4><i class="fas fa-edit me-2"></i>Edit Topic</h4>
                    <p>{{ $topic->topic_number }}: {{ $topic->title }}</p>
                </div>

                <div class="cb-body">
                    {{-- Context --}}
                    <div class="cb-context-badge">
                        <i class="fas fa-file-alt"></i>
                        <span>Info Sheet: <strong>{{ $informationSheet->sheet_number }} &mdash; {{ $informationSheet->title }}</strong></span>
                    </div>

                    {{-- Topic Details --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-pen"></i> Topic Details</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="cb-field-label">Topic Number <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('topic_number') is-invalid @enderror"
                                           name="topic_number" value="{{ old('topic_number', $topic->topic_number) }}"
                                           placeholder="e.g., 1, 2, 3 or 1.1.1" required>
                                    @error('topic_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="cb-field-label">Title <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           name="title" value="{{ old('title', $topic->title) }}"
                                           placeholder="e.g., Scientists Who Contributed to Electricity" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Block-based Content Editor --}}
                    @if($topic->usesBlocks())
                        {{-- Load rich editor CSS/JS for block editor --}}
                        <div style="display:none;">
                            <x-rich-editor name="_block_editor_init" />
                        </div>
                        @include('components.block-editor', ['existingBlocks' => $topic->blocks])
                        @error('blocks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @else
                        {{-- Legacy topic: show old editing fields + option to convert --}}
                        <div class="cb-section" id="legacyConvertSection">
                            <div class="alert alert-info d-flex align-items-center gap-3">
                                <i class="fas fa-info-circle fa-lg"></i>
                                <div class="flex-grow-1">
                                    <strong>Tip:</strong> You can convert this topic to the new block editor for more flexible layouts.
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" id="convertToBlocksBtn">
                                    <i class="fas fa-magic me-1"></i>Convert to Blocks
                                </button>
                            </div>
                        </div>

                        {{-- Legacy: Introduction Content --}}
                        <div class="cb-section" id="legacyContentSection">
                            <div class="cb-section__title"><i class="fas fa-align-left"></i> Introduction Content</div>
                            <div>
                                <x-rich-editor
                                    name="content"
                                    placeholder="Enter introductory content for this topic..."
                                    :value="old('content', $topic->content ?? '')"
                                    toolbar="full"
                                    :height="200"
                                />
                                @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Legacy: Document Attachment --}}
                        <div class="cb-section" id="legacyDocSection">
                            <div class="cb-section__title"><i class="fas fa-upload"></i> Document Attachment <span class="optional">(optional)</span></div>
                            @if($topic->file_path)
                            <div class="cb-context-badge mb-3">
                                <i class="fas fa-file-alt"></i>
                                <span class="flex-grow-1">Current file: <strong>{{ $topic->original_filename }}</strong></span>
                                <a href="{{ route('topics.download', $topic) }}" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                            @endif
                            <label class="cb-upload-area">
                                <input type="file" class="d-none" name="file"
                                       accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx"
                                       onchange="this.closest('.cb-upload-area').classList.add('has-file'); this.closest('.cb-upload-area').querySelector('.upload-name').textContent = this.files[0].name;">
                                <i class="fas fa-cloud-upload-alt d-block"></i>
                                <div class="cb-upload-area__text">
                                    <strong>{{ $topic->file_path ? 'Upload new file to replace' : 'Click to upload' }}</strong> or drag and drop<br>
                                    <small>PDF, Word, Excel, PowerPoint (max 10MB)</small>
                                </div>
                                <span class="upload-name"></span>
                            </label>
                            @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Legacy: Content Parts --}}
                        <div class="cb-section" id="legacyPartsSection">
                            <div class="cb-items-header">
                                <h5><i class="fas fa-puzzle-piece"></i> Content Parts <span class="cb-count-badge">{{ ($topic->parts && count($topic->parts) > 0) ? count($topic->parts) : 0 }}</span></h5>
                                <small style="color: var(--cb-text-hint);">Add multiple parts with images and explanations</small>
                            </div>

                            <div id="partsContainer">
                                @if($topic->parts && count($topic->parts) > 0)
                                    @foreach($topic->parts as $index => $part)
                                    <div class="part-card">
                                        <span class="part-number">Part {{ $index + 1 }}</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm part-remove-btn" onclick="removePart(this)">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <label class="cb-field-label small">Image</label>
                                                <div class="image-preview-container" onclick="this.querySelector('input[type=file]').click()">
                                                    <input type="file" name="part_images[{{ $index }}]" accept="image/*" class="d-none" onchange="previewPartImage(this)">
                                                    <input type="hidden" name="parts[{{ $index }}][existing_image]" value="{{ $part['image'] ?? '' }}">
                                                    @if(!empty($part['image']))
                                                        <img src="{{ $part['image'] }}" alt="Part Image">
                                                    @else
                                                        <div class="placeholder-content">
                                                            <i class="fas fa-image d-block"></i>
                                                            <small>Click to upload</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label class="cb-field-label small">Title / Name</label>
                                                    <input type="text" class="form-control" name="parts[{{ $index }}][title]"
                                                           value="{{ $part['title'] ?? '' }}"
                                                           placeholder="e.g., Benjamin Franklin">
                                                </div>
                                                <div>
                                                    <label class="cb-field-label small">Explanation / Description</label>
                                                    <textarea class="form-control" name="parts[{{ $index }}][explanation]" rows="3"
                                                              placeholder="e.g., American scientist and inventor...">{{ $part['explanation'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>

                            <div id="noPartsMessage" class="cb-empty-state" @if($topic->parts && count($topic->parts) > 0) style="display: none;" @endif>
                                <i class="fas fa-puzzle-piece"></i>
                                <p>No parts added yet. Click "Add Part" to create content sections with images.</p>
                            </div>

                            <button type="button" class="cb-add-btn" id="addPartBtn">
                                <i class="fas fa-plus"></i> Add Part
                            </button>
                        </div>

                        {{-- Hidden block editor (shown only after conversion) --}}
                        <div id="blockEditorWrapper" style="display: none;">
                            @include('components.block-editor', ['existingBlocks' => []])
                        </div>
                        @error('blocks')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @endif

                    {{-- Settings --}}
                    <div class="cb-section">
                        <div class="cb-section__title"><i class="fas fa-cog"></i> Settings</div>
                        <div class="cb-settings">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="cb-field-label">Display Order</label>
                                    <input type="number" class="form-control @error('order') is-invalid @enderror"
                                           name="order" value="{{ old('order', $topic->order) }}" min="0">
                                    @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="cb-field-hint">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cb-footer">
                    <a href="{{ route('content.management') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <div class="btn-group-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Topic
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if(!$topic->usesBlocks())
<script id="legacyTopicData" type="application/json">
<?php echo json_encode([
    'content' => $topic->content,
    'parts' => $topic->parts,
    'document_content' => $topic->document_content,
    'file_path' => $topic->file_path,
    'original_filename' => $topic->original_filename
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
</script>
@push('styles')
<style>
.part-card {
    background: var(--cb-surface);
    border: 1px solid var(--cb-border);
    border-radius: var(--cb-radius-sm);
    padding: 1.25rem;
    margin-bottom: 1rem;
    position: relative;
    transition: box-shadow 0.2s;
}
.part-card:hover { box-shadow: var(--cb-shadow-hover); }
.part-number {
    position: absolute; top: -12px; left: 15px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white; padding: 2px 12px; border-radius: 12px;
    font-size: 0.8rem; font-weight: 600;
}
.part-remove-btn { position: absolute; top: 10px; right: 10px; }
.image-preview-container {
    width: 150px; height: 150px;
    border: 2px dashed var(--cb-border-dashed); border-radius: var(--cb-radius-sm);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden; transition: all 0.2s;
    background: var(--cb-surface-alt);
}
.image-preview-container:hover { border-color: #f59e0b; background: #fffbeb; }
.image-preview-container img { max-width: 100%; max-height: 100%; object-fit: cover; }
.image-preview-container .placeholder-content { text-align: center; color: var(--cb-text-hint); }
.image-preview-container .placeholder-content i { font-size: 2rem; margin-bottom: 0.5rem; }
.dark-mode .part-card { background: var(--card-bg); border-color: var(--border); }
.dark-mode .image-preview-container { background: var(--input-bg); border-color: var(--border); }
.dark-mode .image-preview-container:hover { border-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Legacy parts editor
    let partIndex = {{ ($topic->parts && count($topic->parts) > 0) ? count($topic->parts) : 0 }};
    const partsContainer = document.getElementById('partsContainer');
    const noPartsMessage = document.getElementById('noPartsMessage');
    const addPartBtn = document.getElementById('addPartBtn');

    if (partsContainer && addPartBtn) {
        function updatePartsUI() {
            const parts = partsContainer.querySelectorAll('.part-card');
            if (noPartsMessage) noPartsMessage.style.display = parts.length === 0 ? 'flex' : 'none';
            const badge = document.querySelector('.cb-count-badge');
            if (badge) badge.textContent = parts.length;
        }

        function renumberParts() {
            partsContainer.querySelectorAll('.part-card').forEach((part, index) => {
                part.querySelector('.part-number').textContent = 'Part ' + (index + 1);
            });
        }

        function createPartCard(index) {
            const card = document.createElement('div');
            card.className = 'part-card';
            card.innerHTML = `
                <span class="part-number">Part ${index + 1}</span>
                <button type="button" class="btn btn-outline-danger btn-sm part-remove-btn" onclick="removePart(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <label class="cb-field-label small">Image</label>
                        <div class="image-preview-container" onclick="this.querySelector('input[type=file]').click()">
                            <input type="file" name="part_images[${index}]" accept="image/*" class="d-none" onchange="previewPartImage(this)">
                            <input type="hidden" name="parts[${index}][existing_image]" value="">
                            <div class="placeholder-content">
                                <i class="fas fa-image d-block"></i>
                                <small>Click to upload</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="mb-3">
                            <label class="cb-field-label small">Title / Name</label>
                            <input type="text" class="form-control" name="parts[${index}][title]" placeholder="e.g., Benjamin Franklin">
                        </div>
                        <div>
                            <label class="cb-field-label small">Explanation / Description</label>
                            <textarea class="form-control" name="parts[${index}][explanation]" rows="3" placeholder="e.g., American scientist and inventor..."></textarea>
                        </div>
                    </div>
                </div>
            `;
            return card;
        }

        addPartBtn.addEventListener('click', function() {
            partsContainer.appendChild(createPartCard(partIndex++));
            updatePartsUI();
        });

        window.removePart = function(btn) {
            btn.closest('.part-card').remove();
            renumberParts();
            updatePartsUI();
            partsContainer.querySelectorAll('.part-card').forEach((part, idx) => {
                part.querySelectorAll('input, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(new RegExp('\\[\\d+\\]'), '[' + idx + ']'));
                });
            });
        };

        window.previewPartImage = function(input) {
            const container = input.closest('.image-preview-container');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const hiddenInput = container.querySelector('input[type=hidden]');
                    const hiddenInputHtml = hiddenInput ? hiddenInput.outerHTML : '';
                    container.innerHTML = `
                        <input type="file" name="${input.name}" accept="image/*" class="d-none" onchange="previewPartImage(this)">
                        ${hiddenInputHtml}
                        <img src="${e.target.result}" alt="Preview">
                    `;
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        updatePartsUI();
    }

    // Convert to Blocks button
    const convertBtn = document.getElementById('convertToBlocksBtn');
    if (convertBtn) {
        convertBtn.addEventListener('click', function() {
            const legacyData = JSON.parse(document.getElementById('legacyTopicData').textContent);

            // Show block editor, hide legacy fields
            document.getElementById('blockEditorWrapper').style.display = 'block';
            document.getElementById('legacyConvertSection').style.display = 'none';
            document.getElementById('legacyContentSection').style.display = 'none';
            document.getElementById('legacyDocSection').style.display = 'none';
            document.getElementById('legacyPartsSection').style.display = 'none';

            // Disable legacy form fields so they don't submit
            document.querySelectorAll('#legacyContentSection input, #legacyContentSection textarea, #legacyDocSection input, #legacyPartsSection input, #legacyPartsSection textarea').forEach(function(el) {
                el.disabled = true;
            });

            const blocksContainer = document.getElementById('blocksContainer');
            const addBlockByType = function(type) {
                const btn = document.querySelector('.block-type-btn[data-block-type="' + type + '"]');
                if (btn) btn.click();
            };

            // Convert document to a document block
            if (legacyData.document_content || legacyData.file_path) {
                addBlockByType('document');
                setTimeout(function() {
                    const lastCard = blocksContainer.lastElementChild;
                    if (lastCard) {
                        const docInput = lastCard.querySelector('.block-existing-doc');
                        if (docInput) docInput.value = legacyData.file_path || '';
                        const docNameInput = lastCard.querySelector('.block-existing-doc-name');
                        if (docNameInput) docNameInput.value = legacyData.original_filename || '';
                        const docUpload = lastCard.querySelector('.block-doc-upload');
                        if (docUpload && legacyData.original_filename) {
                            const fileInput = docUpload.querySelector('input[type=file]');
                            docUpload.innerHTML = '<input type="file" name="' + (fileInput ? fileInput.name : '') + '" accept=".pdf,.xlsx,.xls,.doc,.docx,.ppt,.pptx" class="d-none" onchange="window.blockEditor.previewDoc(this)"><div class="placeholder-content"><i class="fas fa-file-alt d-block" style="font-size:2rem;color:#6d9773;"></i><span class="doc-name">' + legacyData.original_filename + '</span><br><small>Click to replace</small></div>';
                        }
                    }
                }, 100);
            }

            // Convert content to a text block
            if (legacyData.content && legacyData.content.trim()) {
                addBlockByType('text');
                setTimeout(function() {
                    const lastCard = blocksContainer.lastElementChild;
                    if (lastCard) {
                        const editorContent = lastCard.querySelector('.rich-editor-content');
                        const editorHidden = lastCard.querySelector('.rich-editor-hidden');
                        if (editorContent) editorContent.innerHTML = legacyData.content;
                        if (editorHidden) editorHidden.value = legacyData.content;
                    }
                }, 200);
            }

            // Convert parts to image_text or text blocks
            if (legacyData.parts && legacyData.parts.length > 0) {
                legacyData.parts.forEach(function(part, idx) {
                    const hasImage = part.image && part.image.trim();
                    const type = hasImage ? 'image_text' : 'text';

                    setTimeout(function() {
                        addBlockByType(type);
                        setTimeout(function() {
                            const lastCard = blocksContainer.lastElementChild;
                            if (!lastCard) return;

                            if (type === 'image_text') {
                                const existingImg = lastCard.querySelector('.block-existing-image');
                                if (existingImg) existingImg.value = part.image;
                                const imgUpload = lastCard.querySelector('.block-image-upload');
                                if (imgUpload) {
                                    const fileInput = imgUpload.querySelector('input[type=file]');
                                    imgUpload.innerHTML = '<input type="file" name="' + (fileInput ? fileInput.name : '') + '" accept="image/*" class="d-none" onchange="window.blockEditor.previewImage(this)"><img src="' + part.image + '" alt="Part image">';
                                }
                            }

                            var html = '';
                            if (part.title) html += '<h4>' + part.title + '</h4>';
                            if (part.explanation) html += '<p>' + part.explanation.replace(/\n/g, '<br>') + '</p>';

                            const editors = lastCard.querySelectorAll('.rich-editor-content');
                            const hiddens = lastCard.querySelectorAll('.rich-editor-hidden');
                            const targetEditor = editors[editors.length - 1];
                            const targetHidden = hiddens[hiddens.length - 1];
                            if (targetEditor) targetEditor.innerHTML = html;
                            if (targetHidden) targetHidden.value = html;
                        }, 100);
                    }, 300 + (idx * 200));
                });
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
