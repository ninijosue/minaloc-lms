import { LitElement, html, css } from 'lit';
import { customElement, property, state } from 'lit/decorators.js';

export type ButtonVariant = 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
export type ButtonSize = 'default' | 'sm' | 'lg' | 'icon';

@customElement('ui-button')
export class Button extends LitElement {
  // Button properties
  @property({ type: String }) variant: ButtonVariant = 'default';
  @property({ type: String }) size: ButtonSize = 'default';
  @property({ type: String }) type: 'button' | 'submit' | 'reset' = 'button';
  @property({ type: Boolean }) disabled = false;
  @property({ type: Boolean }) loading = false;
  @property({ type: String }) loadingText = '';
  @property({ type: String }) name = '';
  @property({ type: String }) value = '';
  @property({ type: String }) form = '';
  @property({ type: String }) formaction = '';
  @property({ type: String }) formenctype = '';
  @property({ type: String }) formmethod = '';
  @property({ type: Boolean }) formnovalidate = false;
  @property({ type: String }) formtarget = '';
  
  // Custom styling
  @property({ type: String }) class = '';
  
  // Internal state
  @state() private pressed = false;
  @state() private focused = false;

  static styles = css`
    :host {
      display: inline-block;
    }
    
    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      white-space: nowrap;
      border-radius: 0.375rem;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.15s ease-in-out;
      cursor: pointer;
      border: 1px solid transparent;
      text-decoration: none;
      outline: none;
      position: relative;
      overflow: hidden;
    }
    
    .button:focus-visible {
      outline: 2px solid hsl(222.2 84% 4.9%);
      outline-offset: 2px;
    }
    
    .button:disabled {
      pointer-events: none;
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .button.loading {
      pointer-events: none;
    }
    
    /* Variants */
    .button.default {
      background-color: hsl(222.2 84% 4.9%);
      color: hsl(210 40% 98%);
    }
    
    .button.default:hover:not(:disabled) {
      background-color: hsl(222.2 84% 4.9% / 0.9);
    }
    
    .button.destructive {
      background-color: hsl(0 84.2% 60.2%);
      color: hsl(210 40% 98%);
    }
    
    .button.destructive:hover:not(:disabled) {
      background-color: hsl(0 84.2% 60.2% / 0.9);
    }
    
    .button.outline {
      border: 1px solid hsl(214.3 31.8% 91.4%);
      background-color: hsl(0 0% 100%);
      color: hsl(222.2 84% 4.9%);
    }
    
    .button.outline:hover:not(:disabled) {
      background-color: hsl(210 40% 96%);
      color: hsl(222.2 84% 4.9%);
    }
    
    .button.secondary {
      background-color: hsl(210 40% 96%);
      color: hsl(222.2 84% 4.9%);
    }
    
    .button.secondary:hover:not(:disabled) {
      background-color: hsl(210 40% 96% / 0.8);
    }
    
    .button.ghost {
      background-color: transparent;
      color: hsl(222.2 84% 4.9%);
    }
    
    .button.ghost:hover:not(:disabled) {
      background-color: hsl(210 40% 96%);
      color: hsl(222.2 84% 4.9%);
    }
    
    .button.link {
      background-color: transparent;
      color: hsl(222.2 84% 4.9%);
      text-decoration: underline;
      text-underline-offset: 4px;
    }
    
    .button.link:hover:not(:disabled) {
      text-decoration: none;
    }
    
    /* Sizes */
    .button.default-size {
      height: 2.5rem;
      padding: 0.5rem 1rem;
    }
    
    .button.sm {
      height: 2.25rem;
      border-radius: 0.375rem;
      padding: 0.25rem 0.75rem;
      font-size: 0.8125rem;
    }
    
    .button.lg {
      height: 2.75rem;
      border-radius: 0.375rem;
      padding: 0.5rem 2rem;
      font-size: 1rem;
    }
    
    .button.icon {
      height: 2.5rem;
      width: 2.5rem;
      padding: 0;
    }
    
    /* Loading spinner */
    .loading-spinner {
      display: inline-block;
      width: 1rem;
      height: 1rem;
      border: 2px solid transparent;
      border-top: 2px solid currentColor;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-right: 0.5rem;
    }
    
    .loading-spinner.icon-only {
      margin-right: 0;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Ripple effect */
    .ripple {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.6);
      transform: scale(0);
      animation: ripple-animation 0.6s linear;
      pointer-events: none;
    }
    
    @keyframes ripple-animation {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
    
    /* Icon spacing */
    .button-content {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .icon-left {
      margin-right: 0.5rem;
    }
    
    .icon-right {
      margin-left: 0.5rem;
    }
  `;

  private handleClick(e: MouseEvent) {
    if (this.disabled || this.loading) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    // Create ripple effect
    this.createRipple(e);

    // Dispatch custom click event
    this.dispatchEvent(new CustomEvent('button-click', {
      detail: { 
        name: this.name,
        value: this.value,
        variant: this.variant,
        size: this.size
      },
      bubbles: true,
      composed: true
    }));
  }

  private createRipple(e: MouseEvent) {
    const button = e.currentTarget as HTMLElement;
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;
    
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    
    button.appendChild(ripple);
    
    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  private handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      this.pressed = true;
    }
  }

  private handleKeyUp(e: KeyboardEvent) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      this.pressed = false;
      if (!this.disabled && !this.loading) {
        this.handleClick(e as any);
      }
    }
  }

  private handleFocus() {
    this.focused = true;
  }

  private handleBlur() {
    this.focused = false;
    this.pressed = false;
  }

  render() {
    const sizeClass = this.size === 'default' ? 'default-size' : this.size;
    const buttonClasses = `
      button ${this.variant} ${sizeClass} ${this.class}
      ${this.loading ? 'loading' : ''}
      ${this.pressed ? 'pressed' : ''}
      ${this.focused ? 'focused' : ''}
    `.trim();

    const isIconOnly = this.size === 'icon';
    const showLoadingText = this.loading && this.loadingText && !isIconOnly;
    const showSpinner = this.loading;

    return html`
      <button
        class="${buttonClasses}"
        type="${this.type}"
        ?disabled="${this.disabled || this.loading}"
        name="${this.name}"
        value="${this.value}"
        formaction="${this.formaction}"
        formenctype="${this.formenctype}"
        formmethod="${this.formmethod}"
        ?formnovalidate="${this.formnovalidate}"
        formtarget="${this.formtarget}"
        @click="${this.handleClick}"
        @keydown="${this.handleKeyDown}"
        @keyup="${this.handleKeyUp}"
        @focus="${this.handleFocus}"
        @blur="${this.handleBlur}"
        aria-disabled="${this.disabled || this.loading}"
        aria-busy="${this.loading}"
      >
        <div class="button-content">
          ${showSpinner ? html`
            <span class="loading-spinner ${isIconOnly ? 'icon-only' : ''}"></span>
          ` : ''}
          
          ${showLoadingText ? html`
            <span>${this.loadingText}</span>
          ` : !this.loading ? html`
            <slot></slot>
          ` : isIconOnly ? '' : html`
            <slot></slot>
          `}
        </div>
      </button>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'ui-button': Button;
  }
}