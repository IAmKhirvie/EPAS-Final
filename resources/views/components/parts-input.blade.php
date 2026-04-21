{{-- Reusable Parts Input Component --}}
{{-- Usage: @include('components.parts-input', ['parts' => $existingParts ?? [], 'fieldPrefix' => 'parts']) --}}

@php
    $parts = $parts ?? [];
    $fieldPrefix = $fieldPrefix ?? 'parts';
@endphp

<div class="parts-input-component mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1"><i class="fas fa-puzzle-piece text-primary me-2"></i>Content Parts</h5>
            <small class="text-muted">Add multiple parts with images and explanations (optional)</small>
            <div class="form-text mt-1">
                <small><strong>Formatting:</strong> Use <code>&lt;b&gt;</code> <code>&lt;i&gt;</code> <code>&lt;u&gt;</code> <code>&lt;strong&gt;</code> <code>&lt;em&gt;</code> <code>&lt;br&gt;</code> for basic formatting</small>
            </div>
        </div>
        <button type="button" class="btn btn-success btn-sm" id="addPartBtn-{{ $fieldPrefix }}">
            <i class="fas fa-plus me-1"></i> Add Part
        </button>
    </div>

    <div id="partsContainer-{{ $fieldPrefix }}">
        @if(count($parts) > 0)
            @foreach($parts as $index => $part)
            <div class="part-card">
                <span class="part-number">Part {{ $index + 1 }}</span>
                <button type="button" class="btn btn-outline-danger btn-sm part-remove-btn" onclick="removePart_{{ $fieldPrefix }}(this)">
                    <i class="fas fa-times"></i>
                </button>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Image</label>
                        <div class="image-preview-container" onclick="this.querySelector('input[type=file]').click()">
                            <input type="file" name="part_images[{{ $index }}]" accept="image/*" class="d-none" onchange="previewPartImage_{{ $fieldPrefix }}(this)">
                            <input type="hidden" name="{{ $fieldPrefix }}[{{ $index }}][existing_image]" value="{{ $part['image'] ?? '' }}">
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
                        <div class="form-group mb-3">
                            <label class="form-label small text-muted">Title / Name</label>
                            <input type="text" class="form-control" name="{{ $fieldPrefix }}[{{ $index }}][title]"
                                   value="{{ $part['title'] ?? '' }}"
                                   placeholder="e.g., Benjamin Franklin">
                        </div>
                        <div class="form-group">
                            <label class="form-label small text-muted">Explanation / Description</label>
                            <textarea class="form-control" name="{{ $fieldPrefix }}[{{ $index }}][explanation]" rows="3"
                                      placeholder="e.g., American scientist and inventor who discovered that lightning is electrical...">{{ $part['explanation'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div id="noPartsMessage-{{ $fieldPrefix }}" class="text-center py-4 bg-light rounded border-dashed" @if(count($parts) > 0) style="display: none;" @endif>
        <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
        <p class="text-muted mb-0">No parts added yet. Click "Add Part" to create content sections with images.</p>
    </div>
</div>

<style>
.parts-input-component .border-dashed {
    border: 2px dashed #dee2e6 !important;
}

.parts-input-component .part-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
    position: relative;
    transition: box-shadow 0.2s;
}

.parts-input-component .part-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.parts-input-component .part-number {
    position: absolute;
    top: -12px;
    left: 15px;
    background: #ffb902;
    color: white;
    padding: 2px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.parts-input-component .part-remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
}

.parts-input-component .image-preview-container {
    width: 150px;
    height: 150px;
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.2s;
    background: #f8f9fa;
}

.parts-input-component .image-preview-container:hover {
    border-color: #ffb902;
    background: #fff8e1;
}

.parts-input-component .image-preview-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.parts-input-component .image-preview-container .placeholder-content {
    text-align: center;
    color: #6c757d;
}

.parts-input-component .image-preview-container .placeholder-content i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
</style>

<script>
(function() {
    const fieldPrefix = '{{ $fieldPrefix }}';
    let partIndex_{{ $fieldPrefix }} = {{ count($parts) }};
    const partsContainer_{{ $fieldPrefix }} = document.getElementById('partsContainer-{{ $fieldPrefix }}');
    const noPartsMessage_{{ $fieldPrefix }} = document.getElementById('noPartsMessage-{{ $fieldPrefix }}');
    const addPartBtn_{{ $fieldPrefix }} = document.getElementById('addPartBtn-{{ $fieldPrefix }}');

    function updateNoPartsMessage_{{ $fieldPrefix }}() {
        const parts = partsContainer_{{ $fieldPrefix }}.querySelectorAll('.part-card');
        noPartsMessage_{{ $fieldPrefix }}.style.display = parts.length === 0 ? 'block' : 'none';
    }

    function renumberParts_{{ $fieldPrefix }}() {
        const parts = partsContainer_{{ $fieldPrefix }}.querySelectorAll('.part-card');
        parts.forEach((part, index) => {
            part.querySelector('.part-number').textContent = 'Part ' + (index + 1);
        });
    }

    function createPartCard_{{ $fieldPrefix }}(index) {
        const card = document.createElement('div');
        card.className = 'part-card';
        card.innerHTML = `
            <span class="part-number">Part ${index + 1}</span>
            <button type="button" class="btn btn-outline-danger btn-sm part-remove-btn" onclick="removePart_{{ $fieldPrefix }}(this)">
                <i class="fas fa-times"></i>
            </button>

            <div class="row mt-2">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Image</label>
                    <div class="image-preview-container" onclick="this.querySelector('input[type=file]').click()">
                        <input type="file" name="part_images[${index}]" accept="image/*" class="d-none" onchange="previewPartImage_{{ $fieldPrefix }}(this)">
                        <input type="hidden" name="{{ $fieldPrefix }}[${index}][existing_image]" value="">
                        <div class="placeholder-content">
                            <i class="fas fa-image d-block"></i>
                            <small>Click to upload</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group mb-3">
                        <label class="form-label small text-muted">Title / Name</label>
                        <input type="text" class="form-control" name="{{ $fieldPrefix }}[${index}][title]"
                               placeholder="e.g., Benjamin Franklin">
                    </div>
                    <div class="form-group">
                        <label class="form-label small text-muted">Explanation / Description</label>
                        <textarea class="form-control" name="{{ $fieldPrefix }}[${index}][explanation]" rows="3"
                                  placeholder="e.g., American scientist and inventor who discovered that lightning is electrical..."></textarea>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    addPartBtn_{{ $fieldPrefix }}.addEventListener('click', function() {
        const card = createPartCard_{{ $fieldPrefix }}(partIndex_{{ $fieldPrefix }});
        partsContainer_{{ $fieldPrefix }}.appendChild(card);
        partIndex_{{ $fieldPrefix }}++;
        updateNoPartsMessage_{{ $fieldPrefix }}();
    });

    // Make functions globally accessible
    window.removePart_{{ $fieldPrefix }} = function(btn) {
        const card = btn.closest('.part-card');
        card.remove();
        renumberParts_{{ $fieldPrefix }}();
        updateNoPartsMessage_{{ $fieldPrefix }}();

        // Re-index remaining parts
        const parts = partsContainer_{{ $fieldPrefix }}.querySelectorAll('.part-card');
        parts.forEach((part, idx) => {
            part.querySelectorAll('input, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${idx}]`));
                }
            });
        });
    };

    window.previewPartImage_{{ $fieldPrefix }} = function(input) {
        const container = input.closest('.image-preview-container');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const hiddenInput = container.querySelector('input[type=hidden]');
                const hiddenInputHtml = hiddenInput ? hiddenInput.outerHTML : '';

                container.innerHTML = `
                    <input type="file" name="${input.name}" accept="image/*" class="d-none" onchange="previewPartImage_{{ $fieldPrefix }}(this)">
                    ${hiddenInputHtml}
                    <img src="${e.target.result}" alt="Preview">
                `;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    updateNoPartsMessage_{{ $fieldPrefix }}();
})();
</script>
