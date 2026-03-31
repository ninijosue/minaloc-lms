import { LitElement, html, css } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';

@customElement('form-field')
export class FormField extends LitElement {
  // Label properties
  @property({ type: String }) label = '';
  
  // Input properties
  @property({ type: String }) type = 'text';
  @property({ type: String }) placeholder = '';
  @property({ type: String }) value = '';
  @property({ type: String }) name = '';
  @property({ type: String }) id = '';
  @property({ type: Boolean }) disabled = false;
  @property({ type: Boolean }) required = false;
  @property({ type: String }) autocomplete = '';
  @property({ type: String }) inputClass = '';
  @property({ type: Boolean }) readonly = false;
  @property({ type: Function }) onChange = (value => {}) as ((value: string) => void);

  // Error handling
  @property({ type: String }) errorMessage = '';
  @property({ type: Boolean }) hasError = false;
  
  // Internal state
  @state() private focused = false;
  @state() private internalValue = '';

  static styles = css`
    :host {
      display: block;
      width: 100%;
    }
    
    .form-field {
      width: 100%;
    }
    
    input {
      width: 100%;
      box-sizing: border-box;
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
  `;

  constructor() {
    super();
    this.internalValue = this.value;
  }

  updated(changedProperties: Map<string, any>) {
    if (changedProperties.has('value')) {
      this.internalValue = this.value;
    }
  }

  private handleInput(e: Event) {
    const target = e.target as HTMLInputElement;
    this.internalValue = target.value;
    this.onChange?.(target.value)
    // Dispatch custom event for parent components
    this.dispatchEvent(new CustomEvent('input-change', {
      detail: { 
        name: this.name,
        value: this.internalValue 
      },
      bubbles: true,
      composed: true
    }));
  }

  private handleFocus() {
    this.focused = true;
    this.dispatchEvent(new CustomEvent('field-focus', {
      detail: { name: this.name },
      bubbles: true,
      composed: true
    }));
  }
  
  protected createRenderRoot(): HTMLElement | DocumentFragment {
    return this; // Disable shadow DOM to use Tailwind styles
  }

  private handleBlur() {
    this.focused = false;
    this.dispatchEvent(new CustomEvent('field-blur', {
      detail: { 
        name: this.name,
        value: this.internalValue 
      },
      bubbles: true,
      composed: true
    }));
  }

  render() {
    // Generate a unique ID if none provided
    const fieldId = this.id || `field-${this.name || Math.random().toString(36).substring(2, 9)}`;
    
    const labelClasses = `
      block text-sm font-medium leading-none mb-2 peer-disabled:cursor-not-allowed peer-disabled:opacity-70
    `;
    
    const inputClasses = `
      flex h-10 w-full rounded-md border px-3 py-2 text-sm 
      ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium 
      placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 
      focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50
      ${this.hasError || this.errorMessage ? 'border-red-500 focus-visible:ring-red-500' : 'border-input focus-visible:ring-ring'}
      ${this.inputClass}
    `;

    return html`
      <div class="form-field space-y-2">
        ${this.label ? html`
          <label for="${fieldId}" class="${labelClasses} ${this.errorMessage.length !== 0 ? "text-red-500":""}">
            ${this.label}
            ${this.required ? html`<span class="required-mark text-red-500">*</span>` : ''}
          </label>
        ` : ''}
        
        <input
          type="${this.type}"
          placeholder="${this.placeholder}"
          .value="${this.internalValue}"
          name="${this.name}"
          id="${fieldId}"
          class="${inputClasses}"
          ?disabled="${this.disabled}"
          ?readonly="${this.readonly}"
          ?required="${this.required}"
          autocomplete="${this.autocomplete}"
          @input="${this.handleInput}"
          @focus="${this.handleFocus}"
          @blur="${this.handleBlur}"
        />
        
        ${this.errorMessage ? html`
          <p class=" mt-[0px!important] text-[12px] h-0 text-red-500">
            ${this.errorMessage}
          </p>
        ` : ''}
      </div>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'form-field': FormField;
  }
}