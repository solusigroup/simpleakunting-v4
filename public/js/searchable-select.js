/**
 * Searchable Select Utility
 * Converts regular <select> elements into searchable dropdowns
 */

class SearchableSelect {
    constructor(selectElement, options = {}) {
        this.select = selectElement;
        this.options = {
            placeholder: options.placeholder || 'Cari akun...',
            maxHeight: options.maxHeight || '350px',
            ...options
        };

        this.isOpen = false;
        this.filteredOptions = [];
        this.selectedIndex = -1;

        this.init();
    }

    init() {
        // Hide original select
        this.select.style.display = 'none';

        // Create wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'searchable-select-wrapper relative';
        this.select.parentNode.insertBefore(this.wrapper, this.select);
        this.wrapper.appendChild(this.select);

        // Create display button
        this.display = document.createElement('button');
        this.display.type = 'button';
        this.display.className = 'searchable-select-display w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-left flex items-center justify-between focus:border-primary focus:ring-1 focus:ring-primary';
        this.display.innerHTML = `
            <span class="truncate">${this.getSelectedText()}</span>
            <span class="material-symbols-outlined text-base text-text-muted ml-2">expand_more</span>
        `;
        this.wrapper.appendChild(this.display);

        // Create dropdown container - use fixed positioning
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'searchable-select-dropdown hidden';
        this.dropdown.style.cssText = 'position: fixed; z-index: 9999; width: 400px; background: #1a1f2e; border: 1px solid #2d3548; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);';
        this.dropdown.innerHTML = `
            <div style="padding: 8px; border-bottom: 1px solid #2d3548;">
                <input type="text" class="searchable-select-input" style="width: 100%; padding: 8px 12px; border-radius: 6px; background: #0d1117; border: 1px solid #2d3548; color: white; font-size: 14px; outline: none;" placeholder="${this.options.placeholder}">
            </div>
            <div class="searchable-select-options" style="max-height: ${this.options.maxHeight}; overflow-y: auto;"></div>
        `;
        document.body.appendChild(this.dropdown);

        this.searchInput = this.dropdown.querySelector('.searchable-select-input');
        this.optionsContainer = this.dropdown.querySelector('.searchable-select-options');

        this.bindEvents();
        this.renderOptions();
    }

    bindEvents() {
        // Toggle dropdown
        this.display.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        });

        // Search input
        this.searchInput.addEventListener('input', () => {
            this.filterOptions(this.searchInput.value);
        });

        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.navigateOptions(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.navigateOptions(-1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0 && this.filteredOptions[this.selectedIndex]) {
                        this.selectOption(this.filteredOptions[this.selectedIndex].value);
                    }
                    break;
                case 'Escape':
                    this.close();
                    break;
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.close();
            }
        });

        // Reposition on scroll/resize
        window.addEventListener('scroll', () => this.isOpen && this.positionDropdown(), true);
        window.addEventListener('resize', () => this.isOpen && this.positionDropdown());

        // Listen for changes to original select (for dynamic updates)
        const observer = new MutationObserver(() => {
            this.renderOptions();
            this.updateDisplay();
        });
        observer.observe(this.select, { childList: true, subtree: true });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    positionDropdown() {
        const rect = this.display.getBoundingClientRect();
        const dropdownHeight = 400;
        const spaceBelow = window.innerHeight - rect.bottom;

        this.dropdown.style.left = rect.left + 'px';
        this.dropdown.style.width = Math.max(rect.width, 400) + 'px';

        if (spaceBelow >= dropdownHeight) {
            this.dropdown.style.top = (rect.bottom + 4) + 'px';
        } else {
            this.dropdown.style.top = (rect.top - dropdownHeight - 4) + 'px';
        }
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.remove('hidden');
        this.dropdown.style.display = 'block';
        this.positionDropdown();
        this.searchInput.value = '';
        this.filterOptions('');
        this.searchInput.focus();
        this.display.querySelector('.material-symbols-outlined').textContent = 'expand_less';
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.add('hidden');
        this.dropdown.style.display = 'none';
        this.selectedIndex = -1;
        this.display.querySelector('.material-symbols-outlined').textContent = 'expand_more';
    }

    getSelectedText() {
        const selected = this.select.options[this.select.selectedIndex];
        return selected ? selected.text : 'Pilih...';
    }

    renderOptions() {
        const options = Array.from(this.select.options);
        this.filteredOptions = options.map(opt => ({
            value: opt.value,
            text: opt.text,
            element: opt
        }));
        this.renderFilteredOptions();
    }

    filterOptions(query) {
        const q = query.toLowerCase().trim();
        const options = Array.from(this.select.options);

        this.filteredOptions = options
            .map(opt => ({
                value: opt.value,
                text: opt.text,
                element: opt
            }))
            .filter(opt => {
                if (!q) return true;
                return opt.text.toLowerCase().includes(q);
            });

        this.selectedIndex = -1;
        this.renderFilteredOptions();
    }

    renderFilteredOptions() {
        if (this.filteredOptions.length === 0) {
            this.optionsContainer.innerHTML = '<div style="padding: 12px; color: #8b949e; text-align: center; font-size: 14px;">Tidak ditemukan</div>';
            return;
        }

        this.optionsContainer.innerHTML = this.filteredOptions.map((opt, index) => `
            <div class="searchable-select-option" 
                 style="padding: 10px 12px; cursor: pointer; font-size: 14px; color: ${opt.value === this.select.value ? '#22c55e' : 'white'}; background: ${index === this.selectedIndex ? 'rgba(34, 197, 94, 0.2)' : (opt.value === this.select.value ? 'rgba(34, 197, 94, 0.1)' : 'transparent')};"
                 data-value="${opt.value}"
                 onmouseover="this.style.background='rgba(34, 197, 94, 0.2)'"
                 onmouseout="this.style.background='${opt.value === this.select.value ? 'rgba(34, 197, 94, 0.1)' : 'transparent'}'">
                ${opt.text}
            </div>
        `).join('');

        // Bind click events
        this.optionsContainer.querySelectorAll('.searchable-select-option').forEach(el => {
            el.addEventListener('click', () => {
                this.selectOption(el.dataset.value);
            });
        });
    }

    navigateOptions(direction) {
        const newIndex = this.selectedIndex + direction;
        if (newIndex >= 0 && newIndex < this.filteredOptions.length) {
            this.selectedIndex = newIndex;
            this.renderFilteredOptions();

            // Scroll into view
            const activeEl = this.optionsContainer.children[this.selectedIndex];
            if (activeEl) {
                activeEl.scrollIntoView({ block: 'nearest' });
            }
        }
    }

    selectOption(value) {
        this.select.value = value;
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
        this.updateDisplay();
        this.close();
    }

    updateDisplay() {
        this.display.querySelector('span:first-child').textContent = this.getSelectedText();
    }

    // Refresh options (useful for dynamically loaded selects)
    refresh() {
        this.renderOptions();
        this.updateDisplay();
    }

    // Destroy and restore original select
    destroy() {
        this.select.style.display = '';
        this.wrapper.parentNode.insertBefore(this.select, this.wrapper);
        this.wrapper.remove();
        this.dropdown.remove();
    }
}

// Utility function for easy initialization
function makeSearchable(selectElement, options = {}) {
    if (!selectElement || selectElement._searchableSelect) return null;
    const instance = new SearchableSelect(selectElement, options);
    selectElement._searchableSelect = instance;
    return instance;
}

// Auto-initialize all selects with [data-searchable] attribute
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[data-searchable]').forEach(sel => {
        makeSearchable(sel);
    });
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SearchableSelect, makeSearchable };
}
