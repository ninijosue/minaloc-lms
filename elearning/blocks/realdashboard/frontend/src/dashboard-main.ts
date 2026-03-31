import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { FilterState, PerformanceMetrics } from "./types"
import "./enrollment-tab.js"
import "./learning-tab.ts"
import "./sector-tab.js"
import "./cell-tab.js"
import "./health-tab.js"
import "./filter-section.js"
import "./filter-content"
import "./students-tab.ts"

@customElement("dashboard-main")
export class DashboardMain extends LitElement {
  static styles = css`
    :host {
      display: block;
      font-family: system-ui, -apple-system, sans-serif;
    }
  `
  @property({ type: String })
  activeTab = "enrollment"

  @property({ type: Object })
  filters: any = {}

  @state() private data: any = null;
  @state() private loading: boolean = false;

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }
  

  connectedCallback(): void {
      super.connectedCallback();
      this.getData();
      document.addEventListener('on-filter-change', (e: Event) => {
        const customEvent = e as CustomEvent<any>;
        const sentData = customEvent.detail;
        this.filters = sentData;
        this.getData(sentData)
      });
  }
  async getData(args?:  any): Promise<void> {
    // Fetch data from the server or use a static sample
    // For now, we will use a static sample
    this.loading = true
    try {
      const response = await fetch("/blocks/realdashboard/get_data.php", {
       method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: args && new URLSearchParams(args as any)
    });
    const data = await response.json();
    this.data = data;
    console.log("Fetched data:", this.data);
    this.dispatchEvent(
      new CustomEvent("data-loaded", {
        detail: this.data,
        bubbles: true,
      }),
    )
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    finally {
      this.loading = false
    }
  }

  private _handleTabChange(tab: string): void {
    this.activeTab = tab
  }

  // private _handleFilterChange(filters: FilterState): void {
  //   this.filters = { ...filters }
  //   // Dispatch custom event for child components to listen
  //   this.dispatchEvent(
  //     new CustomEvent("filters-changed", {
  //       detail: this.filters,
  //       bubbles: true,
  //     }),
  //   )
  // }

  

  render() {
    return html`
      <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <!-- <header class="bg-white shadow-sm border-b">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
              <h1 class="text-2xl font-bold text-gray-900">Student Performance Dashboard</h1>
              <div class="text-sm text-gray-500">Moodle Analytics</div>
            </div>
          </div>
        </header> -->

        <!-- Filter Section -->
        <!-- <filter-section 
          .filters=${this.filters}
        </filter-section> -->
        <filter-content></filter-content>

        <!-- Navigation Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
              ${["enrollment", "progress", "learners"
              // "sector", "cell", "health"
            ].map(
                (tab) => html`
                <button
                  @click=${() => this._handleTabChange(tab)}
                  class="py-2 px-1 border-b-2 font-medium text-sm capitalize transition-colors
                    ${
                      this.activeTab === tab
                        ? "border-blue-500 text-blue-600"
                        : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                    }"
                >
                  ${tab} 
                </button>
              `,
              )}
            </nav>
          </div>
        </div>
        ${this.loading ? html`<div class="flex items-center justify-center h-64">
     <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="85" height="85" style="shape-rendering: auto; display: block; background: transparent;"><g><g transform="rotate(0 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.8888888888888888s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(40 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.7777777777777778s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(80 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.6666666666666666s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(120 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.5555555555555556s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(160 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.4444444444444444s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(200 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.3333333333333333s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(240 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.2222222222222222s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(280 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="-0.1111111111111111s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g transform="rotate(320 50 50)">
  <rect fill="#0099e5" height="10" width="3" ry="5" rx="1.5" y="25" x="48.5">
    <animate repeatCount="indefinite" begin="0s" dur="1s" keyTimes="0;1" values="1;0" attributeName="opacity"/>
  </rect>
</g><g/></g><!-- [ldio] generated by https://loading.io --></svg>
    </div>`: html`
  <!-- Tab Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          ${this.loading ? html`<div class="text-center mb-6">
            <span class="text-gray-700">Loading data...</span>
          </div>` : ""}
          ${this.activeTab === "enrollment" ? html`<enrollment-tab .data=${this.data} .filters=${this.filters}></enrollment-tab>` : ""}
          ${this.activeTab === "progress" ? html`<learning-tab .data=${this.data} .filters=${this.filters}></learning-tab>` : ""}
          ${this.activeTab === "learners" ? html`<students-tab .data=${this.data} .filters=${this.filters}></students-tab>` : ""}
          <!-- ${this.activeTab === "sector" ? html`<sector-tab .filters=${this.filters}></sector-tab>` : ""}
          ${this.activeTab === "cell" ? html`<cell-tab .filters=${this.filters}></cell-tab>` : ""}
          ${this.activeTab === "health" ? html`<health-tab .filters=${this.filters}></health-tab>` : ""} -->
        </div>`}
        
      </div>
    `
  }
}
