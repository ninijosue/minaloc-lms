import { LitElement, html, css } from 'lit';
import { customElement, property, state, query } from 'lit/decorators.js';

export interface ComboboxOption {
  value: string;
  label: string;
  disabled?: boolean;
}

@customElement('search-field')
export class Combobox extends LitElement {
  // Label properties
  @property({ type: String }) label = '';
  @property({ type: Boolean }) required = false;
  
  // Combobox properties
  @property({ type: String }) placeholder = 'Select an option...';
  @property({ type: String }) searchPlaceholder = 'Search...';
  @property({ type: String }) value = '';
  @property({ type: String }) name = '';
  @property({ type: String }) id = '';
  @property({ type: Boolean }) disabled = false;
  @property({ type: String }) emptyMessage = 'No results found.';
  @property({type: Function }) onChange: ((value: string) => void) | null = null;
  @property({ type: Boolean }) readonly = false;
  
  // Options - can be set as JSON string or array
  @property({ 
    type: Array,
    converter: {
      fromAttribute: (value: string | null) => {
        if (!value) return [];
        try {
          return JSON.parse(value);
        } catch {
          return [];
        }
      },
      toAttribute: (value: ComboboxOption[]) => JSON.stringify(value)
    }
  }) 
  options: ComboboxOption[] = [];
  
  // Error handling
  @property({ type: String }) errorMessage = '';
  @property({ type: Boolean }) hasError = false;
  
  // Styling
  @property({ type: String }) triggerClass = '';
  @property({ type: String }) contentClass = '';
  
  // Internal state
  @state() private isOpen = false;
  @state() private searchValue = '';
  @state() private highlightedIndex = -1;
  @state() private filteredOptions: ComboboxOption[] = [];
  
  @query('.combobox-trigger') private triggerElement!: HTMLButtonElement;
  @query('.combobox-content') private contentElement!: HTMLDivElement;
  @query('.combobox-search') private searchElement!: HTMLInputElement;


  constructor() {
    super();
    this.filteredOptions = [...this.options];
    this.addEventListener('keydown', this.handleKeyDown);
  }

  connectedCallback() {
    super.connectedCallback();
    document.addEventListener('click', this.handleClickOutside);
  }

  disconnectedCallback() {
    super.disconnectedCallback();
    document.removeEventListener('click', this.handleClickOutside);
  }

  updated(changedProperties: Map<string, any>) {
    if (changedProperties.has('options')) {
      this.filteredOptions = [...this.options];
    }
    
    if (changedProperties.has('isOpen') && this.isOpen && this.searchElement) {
      // Focus search input when dropdown opens
      setTimeout(() => {
        this.searchElement?.focus();
      }, 0);
    }
  }

  protected createRenderRoot(): HTMLElement | DocumentFragment {
    return this; // Disable shadow DOM to use Tailwind styles
  }

  private handleClickOutside = (e: Event) => {
    if (!this.contains(e.target as Node)) {
      this.closeDropdown();
    }
  };

  private handleKeyDown = (e: KeyboardEvent) => {
    if (!this.isOpen) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
        e.preventDefault();
        this.openDropdown();
      }
      return;
    }

    switch (e.key) {
      case 'Escape':
        e.preventDefault();
        this.closeDropdown();
        this.triggerElement?.focus();
        break;
      case 'ArrowDown':
        e.preventDefault();
        this.highlightNext();
        break;
      case 'ArrowUp':
        e.preventDefault();
        this.highlightPrevious();
        break;
      case 'Enter':
        e.preventDefault();
        if (this.highlightedIndex >= 0) {
          this.selectOption(this.filteredOptions[this.highlightedIndex]);
        }
        break;
    }
  };

  private openDropdown() {
    if (this.disabled) return;
    this.isOpen = true;
    this.searchValue = '';
    this.filteredOptions = [...this.options];
    this.highlightedIndex = -1;
  }

  private closeDropdown() {
    this.isOpen = false;
    this.searchValue = '';
    this.highlightedIndex = -1;
  }

  private toggleDropdown() {
    if(this.readonly || this.disabled) return;
    if (this.isOpen) {
      this.closeDropdown();
    } else {
      this.openDropdown();
    }
  }

  private handleSearch(e: Event) {
    const target = e.target as HTMLInputElement;
    this.searchValue = target.value;
    this.filterOptions();
  }

  private filterOptions() {
    const search = this.searchValue.toLowerCase();
    this.filteredOptions = this.options.filter(option =>
      option.label.toLowerCase().includes(search) ||
      option.value.toLowerCase().includes(search)
    );
    this.highlightedIndex = -1;
  }

  private highlightNext() {
    const availableOptions = this.filteredOptions.filter(opt => !opt.disabled);
    if (availableOptions.length === 0) return;
    
    this.highlightedIndex = Math.min(
      this.highlightedIndex + 1,
      this.filteredOptions.length - 1
    );
    
    // Skip disabled options
    while (
      this.highlightedIndex < this.filteredOptions.length &&
      this.filteredOptions[this.highlightedIndex]?.disabled
    ) {
      this.highlightedIndex++;
    }
    
    if (this.highlightedIndex >= this.filteredOptions.length) {
      this.highlightedIndex = this.filteredOptions.findIndex(opt => !opt.disabled);
    }
  }

  private highlightPrevious() {
    const availableOptions = this.filteredOptions.filter(opt => !opt.disabled);
    if (availableOptions.length === 0) return;
    
    this.highlightedIndex = Math.max(this.highlightedIndex - 1, -1);
    
    // Skip disabled options
    while (
      this.highlightedIndex >= 0 &&
      this.filteredOptions[this.highlightedIndex]?.disabled
    ) {
      this.highlightedIndex--;
    }
    
    if (this.highlightedIndex < 0) {
      // Find last non-disabled option
      for (let i = this.filteredOptions.length - 1; i >= 0; i--) {
        if (!this.filteredOptions[i].disabled) {
          this.highlightedIndex = i;
          break;
        }
      }
    }
  }

  private selectOption(option: ComboboxOption) {
    if (option.disabled) return;
    this.onChange?.(option.value);
    this.value = option.value;
    this.closeDropdown();
    this.triggerElement?.focus();
    
    // Dispatch change event
    this.dispatchEvent(new CustomEvent('combobox-change', {
      detail: { 
        value: option.value,
        label: option.label,
        name: this.name 
      },
      bubbles: true,
      composed: true
    }));
  }

  private getSelectedOption(): ComboboxOption | undefined {
    return this.options.find(option => option.value === this.value);
  }

  render() {
    const fieldId = this.id || `combobox-${this.name || Math.random().toString(36).substring(2, 9)}`;
    const selectedOption = this.getSelectedOption();
    
    const triggerClasses = `
      flex h-10 w-full items-center justify-between rounded-md border bg-white px-3 py-2 text-sm 
      ring-offset-background placeholder:text-muted-foreground focus:outline-none 
      focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed 
      disabled:opacity-50 transition-colors
      ${this.hasError || this.errorMessage ? 'border-red-500 focus:ring-red-500' : 'border-input hover:border-gray-400'}
      ${this.triggerClass}
    `;

    return html`
    <style>

          :host {
      display: block;
      width: 100%;
      position: relative;
    }
    
    .combobox-container {
      width: 100%;
      position: relative;
    }
    
    .combobox-trigger {
      width: 100%;
      box-sizing: border-box;
      cursor: pointer;
      background-color: white;
      position: relative;
    }
    
    /* CRITICAL: Absolute positioning and white background for floating dropdown */
    .combobox-content {
      position: absolute !important;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      z-index: 1000;
      background-color: white !important;
      border: 1px solid hsl(214.3 31.8% 91.4%);
      border-radius: 6px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      max-height: 300px;
      overflow: hidden;
      box-sizing: border-box;
    }
    
    .combobox-search {
      width: 100%;
      box-sizing: border-box;
      background-color: white !important;
      border: none;
      outline: none;
    }
    
    .combobox-options-container {
      max-height: 200px;
      overflow-y: auto;
      background-color: white !important;
    }
    
    .combobox-option {
      width: 100%;
      cursor: pointer;
      box-sizing: border-box;
      background-color: white !important;
      transition: background-color 0.1s ease;
    }
    
    .combobox-option:hover,
    .combobox-option.highlighted {
      background-color: hsl(210 40% 96%) !important;
    }
    
    .combobox-option.selected {
      background-color: hsl(210 40% 96%) !important;
    }
    
    .combobox-option.disabled {
      cursor: not-allowed;
      opacity: 0.5;
    }
    
    .required-mark {
      color: #f43f5e;
      margin-left: 2px;
    }
    
    .error-message {
      color: #f43f5e;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
    
    .chevron-icon {
      transition: transform 0.2s ease;
      flex-shrink: 0;
    }
    
    .chevron-icon.open {
      transform: rotate(180deg);
    }
    
    .search-icon {
      flex-shrink: 0;
      opacity: 0.5;
    }
    
    .check-icon {
      flex-shrink: 0;
    }
    
    /* Search input container */
    .search-container {
      background-color: white !important;
      border-bottom: 1px solid hsl(214.3 31.8% 91.4%);
    }
    
    .search-input-wrapper {
      background-color: white !important;
      border: 1px solid hsl(214.3 31.8% 91.4%);
    }
    
    /* Scrollbar styling */
    .combobox-options-container::-webkit-scrollbar {
      width: 6px;
    }
    
    .combobox-options-container::-webkit-scrollbar-track {
      background: transparent;
    }
    
    .combobox-options-container::-webkit-scrollbar-thumb {
      background: hsl(215.4 16.3% 46.9%);
      border-radius: 3px;
    }
    
    .combobox-options-container::-webkit-scrollbar-thumb:hover {
      background: hsl(215.4 16.3% 36.9%);
    }
    </style>
      <div class="combobox-container space-y-2">
        ${this.label ? html`
          <label for="${fieldId}" class="block mt-0 text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            ${this.label}
            ${this.required ? html`<span class="required-mark">*</span>` : ''}
          </label>
        ` : ''}
        
        <div class="relative">
          <button
            type="button"
            class="combobox-trigger ${triggerClasses}"
            id="${fieldId}"
            ?disabled="${this.disabled}"
            @click="${this.toggleDropdown}"
            aria-expanded="${this.isOpen}"
            aria-haspopup="listbox"
            role="combobox"
          >
            <span class="truncate text-left flex-1">
              ${selectedOption ? selectedOption.label : html`<span class="text-muted-foreground">${this.placeholder}</span>`}
            </span>
            <svg 
              class="chevron-icon h-4 w-4 opacity-50 ml-2 ${this.isOpen ? 'open' : ''}" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
            </svg>
          </button>
          
          ${this.isOpen ? html`
            <div class="combobox-content ${this.contentClass}">
              <div class="search-container p-2">
                <div class="search-input-wrapper flex items-center px-3 py-1 rounded border">
                  <svg class="search-icon h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                  <input
                    class="combobox-search flex-1 text-sm placeholder:text-muted-foreground"
                    placeholder="${this.searchPlaceholder}"
                    .value="${this.searchValue}"
                    @input="${this.handleSearch}"
                  />
                </div>
              </div>
              
              <div class="combobox-options-container" role="listbox">
                ${this.filteredOptions.length === 0 ? html`
                  <div class="py-6 text-center text-sm text-muted-foreground">
                    ${this.emptyMessage}
                  </div>
                ` : ''}
                
                ${this.filteredOptions.map((option, index) => html`
                  <div
                    class="combobox-option relative flex cursor-pointer select-none items-center px-3 py-2 text-sm
                           ${index === this.highlightedIndex ? 'highlighted' : ''}
                           ${option.value === this.value ? 'selected' : ''}
                           ${option.disabled ? 'disabled' : ''}"
                    @click="${() => this.selectOption(option)}"
                    role="option"
                    aria-selected="${option.value === this.value}"
                  >
                    <span class="check-icon mr-2 h-4 w-4 flex items-center justify-center">
                      ${option.value === this.value ? html`
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                      ` : ''}
                    </span>
                    <span class="flex-1">${option.label}</span>
                  </div>
                `)}
              </div>
            </div>
          ` : ''}
        </div>
        
        ${this.errorMessage ? html`
          <p class="error-message">
            ${this.errorMessage}
          </p>
        ` : ''}
      </div>
    `;
  }
}


declare global {
  interface HTMLElementTagNameMap {
    'search-field': Combobox;
  }
}