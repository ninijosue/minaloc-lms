import { LitElement, html, css } from "lit"
import { customElement, property } from "lit/decorators.js"

export interface PaginationState {
  page: number
  limit: number
  total: number
  totalPages: number
}

@customElement("pagination-controls")
export class PaginationControls extends LitElement {
  static styles = css`
    :host {
      display: block;
    }
  `

  @property({ type: Object })
  pagination: PaginationState = {
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0
  }

  @property({ type: Boolean })
  disabled: boolean = false

  protected createRenderRoot(): HTMLElement | DocumentFragment {
    return this
  }

  private _handleLimitChange(e: Event): void {
    const select = e.target as HTMLSelectElement
    const newLimit = parseInt(select.value, 10)
    this.dispatchEvent(
      new CustomEvent("limit-change", {
        detail: { limit: newLimit },
        bubbles: true
      })
    )
  }

  private _handlePageChange(newPage: number): void {
    if (newPage < 1 || newPage > this.pagination.totalPages || this.disabled) {
      return
    }
    this.dispatchEvent(
      new CustomEvent("page-change", {
        detail: { page: newPage },
        bubbles: true
      })
    )
  }

  render() {
    const { page, limit, totalPages } = this.pagination

    return html`
      <div class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-200">
        <!-- Rows per page selector -->
        <div class="flex items-center space-x-2 gap-2">
          <span class="text-sm text-gray-700">Rows per page:</span>
          <select
            .value=${limit.toString()}
            @change=${this._handleLimitChange}
            ?disabled=${this.disabled}
            class="px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="500">500</option>
            <option value="1000">1000</option>

          </select>
        </div>

        <!-- Page info -->
        <div class="flex items-center space-x-4 gap-4">
          <span class="text-sm text-gray-700">
            Page ${page} of ${totalPages || 1}
          </span>

          <!-- Navigation buttons -->
          <div class="flex items-center space-x-1 gap-2">
            <!-- First page -->
            <button
              @click=${() => this._handlePageChange(1)}
              ?disabled=${this.disabled || page === 1}
              class="px-2 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              title="First page"
            >
              ⏮
            </button>

            <!-- Previous page -->
            <button
              @click=${() => this._handlePageChange(page - 1)}
              ?disabled=${this.disabled || page === 1}
              class="px-2 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              title="Previous page"
            >
              ◀
            </button>

            <!-- Next page -->
            <button
              @click=${() => this._handlePageChange(page + 1)}
              ?disabled=${this.disabled || page === totalPages}
              class="px-2 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              title="Next page"
            >
              ▶
            </button>

            <!-- Last page -->
            <button
              @click=${() => this._handlePageChange(totalPages)}
              ?disabled=${this.disabled || page === totalPages}
              class="px-2 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              title="Last page"
            >
              ⏭
            </button>
          </div>
        </div>
      </div>
    `
  }
}
