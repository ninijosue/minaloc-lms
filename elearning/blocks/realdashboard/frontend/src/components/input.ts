import { LitElement, html, css } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';

@customElement('ui-input')
export class Input extends LitElement {
  constructor() {
    super();
  }
  @property({ type: String }) type = 'text';
  @property({ type: String }) placeholder = '';
  @property({ type: String }) value = '';
  @property({ type: String }) name = '';
  @property({ type: String }) id = '';
  @property({ type: String }) class = '';
  @property({ type: Boolean }) disabled = false;
  @property({ type: Boolean }) required = false;
  @property({ type: String }) autocomplete = '';
  @property({ type: Function }) onChange = (value => {}) as ((value: string) => void);
  
  @state() private focused = false

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }

  static styles = css`
    :host {
      display: block;
      width: 100%;
    }
    
    input {
      width: 100%;
      box-sizing: border-box;
    }
  `;

  private handleInput(e: Event) {
    const target = e.target as HTMLInputElement;
    this.value = target.value;
    this.onChange?.(target.value);
    console.log("Input value changed:", this.value);
    // Dispatch custom event for parent components
    this.dispatchEvent(new CustomEvent('input-change', {
      detail: { value: this.value },
      bubbles: true,
      composed: true
    }));
  }

  private handleFocus() {
    this.focused = true;
  }

  private handleBlur() {
    this.focused = false;
  }

  render() {
    

    return html`
      <input
        type="${this.type}"
        placeholder="${this.placeholder}"
        .value="${this.value}"
        name="${this.name}"
        id="${this.id}"
        class="  flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm 
      ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium 
      placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 
      focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed 
      disabled:opacity-50 ${this.class}"
        ?disabled="${this.disabled}"
        ?required="${this.required}"
        autocomplete="${this.autocomplete}"
        @input=${this.handleInput}
        @focus="${this.handleFocus}"
        @blur="${this.handleBlur}"
      />
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'ui-input': Input;
  }
}