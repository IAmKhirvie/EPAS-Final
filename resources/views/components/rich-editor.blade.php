@props([
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'required' => false,
    'placeholder' => 'Start typing...',
    'height' => 200,
    'toolbar' => 'standard', // 'minimal', 'standard', 'full'
    'class' => ''
])

@php
    $editorId = $id ?? 'editor_' . Str::random(8);
@endphp

@if($label)
<label for="{{ $editorId }}" class="form-label {{ $required ? 'required-field' : '' }}">{{ $label }}</label>
@endif

<div class="rich-editor-container {{ $class }}" data-editor-id="{{ $editorId }}">
    {{-- Toolbar --}}
    <div class="rich-editor-toolbar">
        <div class="toolbar-group">
            <button type="button" class="toolbar-btn" data-command="bold" title="Bold (Ctrl+B)">
                <i class="fas fa-bold"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="italic" title="Italic (Ctrl+I)">
                <i class="fas fa-italic"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="underline" title="Underline (Ctrl+U)">
                <i class="fas fa-underline"></i>
            </button>
            @if($toolbar !== 'minimal')
            <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strikethrough">
                <i class="fas fa-strikethrough"></i>
            </button>
            @endif
        </div>

        @if($toolbar !== 'minimal')
        <div class="toolbar-divider"></div>
        <div class="toolbar-group">
            <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                <i class="fas fa-list-ul"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                <i class="fas fa-list-ol"></i>
            </button>
        </div>
        @endif

        @if($toolbar === 'full')
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
            <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left">
                <i class="fas fa-align-left"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center">
                <i class="fas fa-align-center"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right">
                <i class="fas fa-align-right"></i>
            </button>
        </div>

        <div class="toolbar-divider"></div>
        <div class="toolbar-group">
            <button type="button" class="toolbar-btn" data-command="insertTable" title="Insert Table">
                <i class="fas fa-table"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link">
                <i class="fas fa-link"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link">
                <i class="fas fa-unlink"></i>
            </button>
        </div>
        @endif

        <div class="toolbar-divider"></div>
        <div class="toolbar-group">
            <button type="button" class="toolbar-btn" data-command="removeFormat" title="Clear Formatting">
                <i class="fas fa-eraser"></i>
            </button>
        </div>
    </div>

    {{-- Editable Area --}}
    <div class="rich-editor-content"
         contenteditable="true"
         data-placeholder="{{ $placeholder }}"
         style="min-height: {{ $height }}px;"
         id="{{ $editorId }}_content">{!! old($name, $value) !!}</div>

    {{-- Hidden textarea for form submission --}}
    <textarea name="{{ $name }}"
              id="{{ $editorId }}"
              class="rich-editor-hidden"
              {{ $required ? 'required' : '' }}
              style="display: none;">{!! old($name, $value) !!}</textarea>
</div>

@once
@push('styles')
<style>
.rich-editor-container {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}

.rich-editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    padding: 8px 10px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.toolbar-group {
    display: flex;
    gap: 2px;
}

.toolbar-divider {
    width: 1px;
    height: 24px;
    background: #dee2e6;
    margin: 0 6px;
}

.toolbar-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    border-radius: 4px;
    color: #495057;
    cursor: pointer;
    transition: all 0.15s ease;
}

.toolbar-btn:hover {
    background: #e9ecef;
    color: #212529;
}

.toolbar-btn:active,
.toolbar-btn.active {
    background: #6d9773;
    color: #fff;
}

.toolbar-btn i {
    font-size: 14px;
}

.rich-editor-content {
    padding: 12px 15px;
    min-height: 150px;
    max-height: 500px;
    overflow-y: auto;
    outline: none;
    font-size: 14px;
    line-height: 1.6;
    color: #212529;
}

.rich-editor-content:empty:before {
    content: attr(data-placeholder);
    color: #adb5bd;
    pointer-events: none;
}

.rich-editor-content:focus {
    background: #fff;
}

.rich-editor-content p {
    margin: 0 0 10px 0;
}

.rich-editor-content ul,
.rich-editor-content ol {
    margin: 0 0 10px 0;
    padding-left: 25px;
}

.rich-editor-content a {
    color: #6d9773;
    text-decoration: underline;
}

.rich-editor-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
}

.rich-editor-content table td,
.rich-editor-content table th {
    border: 1px solid #dee2e6;
    padding: 8px 12px;
    min-width: 60px;
}

.rich-editor-content table th {
    background: #f8f9fa;
    font-weight: 600;
}

.rich-editor-content h2,
.rich-editor-content h3,
.rich-editor-content h4 {
    margin: 15px 0 8px;
    font-weight: 600;
    color: #0c3a2d;
}

.toolbar-select {
    height: 32px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: #fff;
    color: #495057;
    font-size: 13px;
    padding: 0 8px;
    cursor: pointer;
}

.toolbar-select:focus {
    border-color: #6d9773;
    outline: none;
}

/* Dark mode */
.dark-mode .rich-editor-container {
    border-color: #3d3d4d;
    background: #1a1a2e;
}

.dark-mode .rich-editor-toolbar {
    background: #2d2d3d;
    border-color: #3d3d4d;
}

.dark-mode .toolbar-divider {
    background: #3d3d4d;
}

.dark-mode .toolbar-btn {
    color: #adb5bd;
}

.dark-mode .toolbar-btn:hover {
    background: #3d3d4d;
    color: #e9ecef;
}

.dark-mode .rich-editor-content {
    color: #e9ecef;
}

.dark-mode .rich-editor-content:empty:before {
    color: #6c757d;
}

/* Focus state */
.rich-editor-container:focus-within {
    border-color: #6d9773;
    box-shadow: 0 0 0 3px rgba(109, 151, 115, 0.15);
}
</style>
@endpush

@push('scripts')
<script>
/**
 * Initialize a single rich editor container.
 * Callable on dynamically created editors: window.initRichEditor(containerEl)
 */
window.initRichEditor = function(container) {
    if (container.dataset.initialized) return;
    container.dataset.initialized = 'true';

    const content = container.querySelector('.rich-editor-content');
    const hidden = container.querySelector('.rich-editor-hidden');
    const buttons = container.querySelectorAll('.toolbar-btn');

    if (!content || !hidden) return;

    // Sync content to hidden textarea
    function syncContent() {
        hidden.value = content.innerHTML;
    }

    // Handle toolbar button clicks
    buttons.forEach(function(btn) {
        btn.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const command = btn.dataset.command;

            if (command === 'createLink') {
                const url = prompt('Enter URL:', 'https://');
                if (url) {
                    document.execCommand(command, false, url);
                }
            } else if (command === 'insertTable') {
                const size = prompt('Table size (rows x cols):', '3x3');
                if (size) {
                    const parts = size.split('x').map(n => parseInt(n.trim()));
                    const rows = Math.min(parts[0] || 3, 20);
                    const cols = Math.min(parts[1] || 3, 10);
                    let html = '<table><thead><tr>';
                    for (let c = 0; c < cols; c++) html += '<th>Header</th>';
                    html += '</tr></thead><tbody>';
                    for (let r = 0; r < rows - 1; r++) {
                        html += '<tr>';
                        for (let c = 0; c < cols; c++) html += '<td>&nbsp;</td>';
                        html += '</tr>';
                    }
                    html += '</tbody></table><p><br></p>';
                    document.execCommand('insertHTML', false, html);
                }
            } else {
                document.execCommand(command, false, null);
            }

            content.focus();
            syncContent();
            updateButtonStates();
        });
    });

    // Handle heading select
    container.querySelectorAll('.toolbar-select').forEach(function(sel) {
        sel.addEventListener('change', function(e) {
            const value = sel.value;
            if (value) {
                document.execCommand('formatBlock', false, '<' + value + '>');
            } else {
                document.execCommand('formatBlock', false, '<p>');
            }
            content.focus();
            syncContent();
        });
    });

    // Update button active states
    function updateButtonStates() {
        buttons.forEach(function(btn) {
            const command = btn.dataset.command;
            try {
                if (document.queryCommandState(command)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            } catch (e) {
                btn.classList.remove('active');
            }
        });
    }

    // Event listeners
    content.addEventListener('input', syncContent);
    content.addEventListener('keyup', updateButtonStates);
    content.addEventListener('mouseup', updateButtonStates);

    // Keyboard shortcuts
    content.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key.toLowerCase()) {
                case 'b':
                    e.preventDefault();
                    document.execCommand('bold', false, null);
                    break;
                case 'i':
                    e.preventDefault();
                    document.execCommand('italic', false, null);
                    break;
                case 'u':
                    e.preventDefault();
                    document.execCommand('underline', false, null);
                    break;
            }
            syncContent();
            updateButtonStates();
        }
    });

    // Handle paste - clean up pasted content
    content.addEventListener('paste', function(e) {
        e.preventDefault();

        // Get plain text or HTML
        let paste = (e.clipboardData || window.clipboardData).getData('text/html');
        if (!paste) {
            paste = (e.clipboardData || window.clipboardData).getData('text/plain');
            paste = paste.replace(/\n/g, '<br>');
        } else {
            // Clean dangerous content from pasted HTML
            paste = paste.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '');
            paste = paste.replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '');
            paste = paste.replace(/on\w+="[^"]*"/gi, '');
            paste = paste.replace(/javascript:/gi, '');
        }

        document.execCommand('insertHTML', false, paste);
        syncContent();
    });

    // Initial sync
    syncContent();
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rich-editor-container').forEach(function(container) {
        window.initRichEditor(container);
    });
});
</script>
@endpush
@endonce
