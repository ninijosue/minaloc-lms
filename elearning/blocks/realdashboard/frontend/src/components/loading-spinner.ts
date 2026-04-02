import { LitElement, html, css } from "lit"
import { customElement, property } from "lit/decorators.js"

@customElement("loading-spinner")
export class LoadingSpinner extends LitElement {
  static styles = css`
    :host {
      display: block;
    }
  `

  @property({ type: String })
  size: "small" | "medium" | "large" = "medium"

  @property({ type: String })
  message: string = ""

  @property({ type: String })
  submessage: string = ""

  protected createRenderRoot(): HTMLElement | DocumentFragment {
    return this
  }

  private get dimensions(): { width: number; height: number } {
    switch (this.size) {
      case "small":
        return { width: 50, height: 50 }
      case "large":
        return { width: 120, height: 120 }
      default:
        return { width: 85, height: 85 }
    }
  }

  render() {
    const { width, height } = this.dimensions

    return html`
      <div class="flex flex-col items-center justify-center ${this.size === 'small' ? 'py-4' : 'py-12'}">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          xmlns:xlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 100 100"
          preserveAspectRatio="xMidYMid"
          width="${width}"
          height="${height}"
          style="shape-rendering: auto; display: block; background: transparent;"
        >
          <g>
            <g transform="rotate(0 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.8888888888888888s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(40 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.7777777777777778s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(80 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.6666666666666666s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(120 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.5555555555555556s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(160 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.4444444444444444s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(200 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.3333333333333333s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(240 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.2222222222222222s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(280 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="-0.1111111111111111s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g transform="rotate(320 50 50)">
              <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
                <animate
                  repeatCount="indefinite"
                  begin="0s"
                  dur="1s"
                  keyTimes="0;1"
                  values="1;0"
                  attributeName="opacity"
                />
              </rect>
            </g>
            <g />
          </g>
        </svg>
        ${this.message
          ? html`<span class="text-lg text-gray-600 font-medium mt-4">${this.message}</span>`
          : ""}
        ${this.submessage
          ? html`<span class="text-sm text-gray-500 mt-2">${this.submessage}</span>`
          : ""}
      </div>
    `
  }
}
