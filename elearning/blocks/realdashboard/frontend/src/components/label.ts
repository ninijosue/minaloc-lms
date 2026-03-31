import { LitElement, html, css } from 'lit';
import { customElement, property } from 'lit/decorators.js';


@customElement('ui-label')
export class Label extends LitElement {
  @property({ type: String }) for = '';
  @property({ type: String }) class = '';

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }

  static styles = css`
    :host {
      display: inline-block;
    }
  `;

  render() {
    return html`
      <label 
        for="${this.for}"
        class="text-sm font-medium mt-[0px!important] leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 ${this.class}"
      >
        testing well<slot></slot>
      </label>
    `;
  }
}

declare global {
  interface HTMLElementTagNameMap {
    'ui-label': Label;
  }
}