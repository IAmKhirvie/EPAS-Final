/**
 * DynamicForm - Reusable utility for dynamic form fields
 * Consolidates addItem, removeItem, addStep, removeStep, etc.
 */
const DynamicForm = {
    /**
     * Add a simple text input item to a container
     * @param {string} containerId - The ID of the container element
     * @param {string} inputName - The name attribute for the input (e.g., 'objectives[]')
     * @param {string} placeholder - Placeholder text for the input
     * @param {boolean} required - Whether the input is required
     */
    addItem(containerId, inputName, placeholder, required = false) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control" name="${inputName}" placeholder="${placeholder}" ${required ? 'required' : ''}>
            <button type="button" class="btn btn-outline-danger" onclick="DynamicForm.removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    },

    /**
     * Add a styled list item (content builder variant)
     * @param {string} containerId - The ID of the container element
     * @param {string} inputName - The name attribute for the input
     * @param {string} placeholder - Placeholder text
     * @param {boolean} required - Whether the input is required
     */
    addListItem(containerId, inputName, placeholder, required = false) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const div = document.createElement('div');
        div.className = 'cb-list-item';
        div.innerHTML = `
            <i class="fas fa-grip-vertical cb-list-item__handle"></i>
            <input type="text" class="form-control" name="${inputName}" placeholder="${placeholder}" ${required ? 'required' : ''}>
            <button type="button" class="cb-list-item__remove" onclick="DynamicForm.removeListItem(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    },

    /**
     * Remove a list item (keeps at least one)
     * @param {HTMLElement} button - The button that triggered the removal
     */
    removeListItem(button) {
        const item = button.closest('.cb-list-item');
        if (!item) return;

        const container = item.parentElement;
        if (container && container.querySelectorAll('.cb-list-item').length > 1) {
            item.remove();
        }
    },

    /**
     * Remove an item from input group (keeps at least one)
     * @param {HTMLElement} button - The button that triggered the removal
     */
    removeItem(button) {
        const inputGroup = button.closest('.input-group');
        if (!inputGroup) return;

        const container = inputGroup.parentElement;
        if (container && container.querySelectorAll('.input-group').length > 1) {
            inputGroup.remove();
        }
    },

    /**
     * Add a card-based item (for steps, checklist items, task items, etc.)
     * @param {string} containerId - The ID of the container element
     * @param {string} cardClass - CSS class for the card (e.g., 'step-card', 'item-card')
     * @param {function} templateFn - Function that returns the card's inner HTML given the current index
     */
    addCard(containerId, cardClass, templateFn) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const currentCount = container.querySelectorAll(`.${cardClass}`).length;

        const card = document.createElement('div');
        card.className = `card mb-3 ${cardClass}`;
        card.innerHTML = templateFn(currentCount);
        container.appendChild(card);

        return currentCount + 1;
    },

    /**
     * Add a content-builder styled item card
     * @param {string} containerId - The ID of the container element
     * @param {string} cardClass - CSS class for the card
     * @param {function} templateFn - Function that returns the card body HTML given the current index
     * @param {string} prefix - Label prefix (e.g., 'Step', 'Item')
     */
    addItemCard(containerId, cardClass, templateFn, prefix = 'Item') {
        const container = document.getElementById(containerId);
        if (!container) return;

        const currentCount = container.querySelectorAll(`.${cardClass}`).length;
        const num = currentCount + 1;

        const card = document.createElement('div');
        card.className = `cb-item-card ${cardClass}`;
        card.innerHTML = `
            <div class="cb-item-card__header">
                <div class="left-section">
                    <span class="cb-item-card__number">${num}</span>
                    <span class="cb-item-card__title">${prefix} #${num}</span>
                </div>
                <div class="right-section">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="DynamicForm.removeItemCard(this, '${cardClass}', '${prefix}')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="cb-item-card__body">
                ${templateFn(currentCount)}
            </div>
        `;
        container.appendChild(card);

        // Update count badge if present
        this.updateCountBadge(containerId, cardClass);

        // Hide empty state if present
        const emptyState = container.parentElement?.querySelector('.cb-empty-state');
        if (emptyState) emptyState.style.display = 'none';

        return num;
    },

    /**
     * Remove a content-builder item card and renumber
     * @param {HTMLElement} button - The button that triggered the removal
     * @param {string} cardClass - CSS class of the cards
     * @param {string} prefix - Prefix for numbering
     */
    removeItemCard(button, cardClass, prefix = 'Item') {
        const card = button.closest(`.${cardClass}`);
        if (!card) return;

        const container = card.parentElement;
        const allCards = container.querySelectorAll(`.${cardClass}`);
        if (allCards.length > 1) {
            card.remove();
            this.renumberItemCards(container, cardClass, prefix);
            this.updateCountBadge(container.id, cardClass);
        }
    },

    /**
     * Renumber content-builder item cards after removal
     */
    renumberItemCards(container, cardClass, prefix = 'Item') {
        const cards = container.querySelectorAll(`.${cardClass}`);
        cards.forEach((card, index) => {
            const num = index + 1;
            const numberEl = card.querySelector('.cb-item-card__number');
            if (numberEl) numberEl.textContent = num;
            const titleEl = card.querySelector('.cb-item-card__title');
            if (titleEl) titleEl.textContent = `${prefix} #${num}`;

            // Update hidden step_number inputs if they exist
            const stepNumberInput = card.querySelector('input[name*="[step_number]"]');
            if (stepNumberInput) stepNumberInput.value = num;
        });
    },

    /**
     * Update count badge for a container
     */
    updateCountBadge(containerId, cardClass) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const count = container.querySelectorAll(`.${cardClass}`).length;
        const badge = container.parentElement?.querySelector('.cb-count-badge');
        if (badge) badge.textContent = count;
    },

    /**
     * Remove a card and renumber remaining cards
     * @param {HTMLElement} button - The button that triggered the removal
     * @param {string} cardClass - CSS class of the cards (e.g., 'step-card')
     * @param {string} prefix - Prefix for numbering (e.g., 'Step', 'Item')
     */
    removeCard(button, cardClass, prefix = 'Item') {
        const card = button.closest(`.${cardClass}`);
        if (!card) return;

        const allCards = document.querySelectorAll(`.${cardClass}`);
        if (allCards.length > 1) {
            card.remove();
            this.renumberCards(cardClass, prefix);
        }
    },

    /**
     * Renumber cards after removal
     * @param {string} cardClass - CSS class of the cards
     * @param {string} prefix - Prefix for numbering (e.g., 'Step', 'Item')
     * @param {string} headerSelector - Selector for the header element to update (default 'h6')
     */
    renumberCards(cardClass, prefix = 'Item', headerSelector = 'h6') {
        const cards = document.querySelectorAll(`.${cardClass}`);
        cards.forEach((card, index) => {
            const header = card.querySelector(headerSelector);
            if (header) {
                header.textContent = `${prefix} #${index + 1}`;
            }

            // Update hidden step_number inputs if they exist
            const stepNumberInput = card.querySelector('input[name*="[step_number]"]');
            if (stepNumberInput) {
                stepNumberInput.value = index + 1;
            }
        });
    }
};

// Make globally available
window.DynamicForm = DynamicForm;
