import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { FilterState, PerformanceMetrics } from "./types"
import "./components/dialog"
import "./filter-content"

@customElement("filter-section")
export class FilterSection extends LitElement {
  static styles = css`
    :host {
      display: block;
    }
  `

  @property({ type: Object })
  filters: FilterState = {
    sex: "All",
    minAge: "",
    maxAge: "",
    minGrades: "",
    maxGrades: "",
  }

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }

  @state()
  private _localFilters: FilterState = { ...this.filters }
  private dialogVisible: Boolean = false;

  connectedCallback(): void {
    super.connectedCallback()
    this._localFilters = { ...this.filters }
  }

  private _handleInputChange(field: keyof FilterState, value: string): void {
    this._localFilters = {
      ...this._localFilters,
      [field]: value,
    }
  }

  private _applyFilters(): void {
    this.dispatchEvent(
      new CustomEvent("filter-change", {
        detail: this._localFilters,
        bubbles: true,
      }),
    )
  }

  private _resetFilters(): void {
    this._localFilters = {
      sex: "All",
      minAge: "",
      maxAge: "",
      minGrades: "",
      maxGrades: "",
    }
    this._applyFilters()
  }
    toggleDialog () {
    this.dialogVisible = !this.dialogVisible
    console.log(this.dialogVisible)
  }

  closeDialog () {
    this.dialogVisible = false
  }

  

  render() {
    return html`

      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex flex-wrap items-end gap-4">
            <filter-content></filter-content>
            <!-- Sex Filter -->
            <!-- <div class="flex flex-col">
              <label class="text-sm font-medium text-gray-700 mb-1">Sex</label>
              <select
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                .value=${this._localFilters.sex}
                @change=${(e: Event) => this._handleInputChange("sex", (e.target as HTMLSelectElement).value as FilterState["sex"])}
              >
                <option value="All">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div> -->

            <!-- Age Range -->
            <!-- <div class="flex flex-col">
              <label class="text-sm font-medium text-gray-700 mb-1">Age Range</label>
              <div class="flex gap-2">
                <input
                  type="number"
                  placeholder="Min Age"
                  class="border border-gray-300 rounded-md px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  .value=${this._localFilters.minAge}
                  @input=${(e: Event) => this._handleInputChange("minAge", (e.target as HTMLInputElement).value)}
                />
                <span class="self-center text-gray-500">-</span>
                <input
                  type="number"
                  placeholder="Max Age"
                  class="border border-gray-300 rounded-md px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  .value=${this._localFilters.maxAge}
                  @input=${(e: Event) => this._handleInputChange("maxAge", (e.target as HTMLInputElement).value)}
                />
              </div>
            </div> -->

            <!-- Grades Range -->
            <!-- <div class="flex flex-col">
              <label class="text-sm font-medium text-gray-700 mb-1">Grades Range</label>
              <div class="flex gap-2">
                <input
                  type="number"
                  placeholder="Min Grades"
                  class="border border-gray-300 rounded-md px-3 py-2 text-sm w-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  .value=${this._localFilters.minGrades}
                  @input=${(e: Event) => this._handleInputChange("minGrades", (e.target as HTMLInputElement).value)}
                />
                <span class="self-center text-gray-500">-</span>
                <input
                  type="number"
                  placeholder="Max Grades"
                  class="border border-gray-300 rounded-md px-3 py-2 text-sm w-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  .value=${this._localFilters.maxGrades}
                  @input=${(e: Event) => this._handleInputChange("maxGrades", (e.target as HTMLInputElement).value)}
                />
              </div>
            </div> -->




            <!-- Action Buttons -->
            <div class="flex gap-2">
              <button
                @click=${this._applyFilters}
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Apply Filters
              </button>
              <button
                @click=${this._resetFilters}
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    `
  }
}
