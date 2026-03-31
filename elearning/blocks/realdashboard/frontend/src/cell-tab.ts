import { LitElement, html, css } from "lit"
import { property, customElement } from "lit/decorators.js"
import type { FilterState, PerformanceMetrics } from "./types"

@customElement("cell-tab")
export class CellTab extends LitElement {
  static styles = css`
    :host {
      display: block;
    }
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }
  `

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }

  @property({ type: Object })
  filters: FilterState = {
    sex: "All",
    minAge: "",
    maxAge: "",
    minGrades: "",
    maxGrades: "",
  }

  private charts: Map<string, any> = new Map()

  firstUpdated(): void {
    this._initializeCharts()
  }

  updated(changedProperties: Map<string, any>): void {
    if (changedProperties.has("filters")) {
      this._updateChartsWithFilters()
    }
  }

  private _initializeCharts(): void {
    this._createCellPerformanceChart()
    this._createStudentDistributionChart()
    this._createAgeGroupChart()
    this._createProgressTimelineChart()
  }

  private _createCellPerformanceChart(): void {
    const canvas = document.querySelector("#cellPerformanceChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "radar",
      data: {
        labels: ["Enrollment", "Attendance", "Completion", "Performance", "Satisfaction"],
        datasets: [
          {
            label: "Kabeza",
            data: [90, 85, 88, 92, 87],
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.2)",
            pointBackgroundColor: "#3B82F6",
          },
          {
            label: "Kimisagara",
            data: [85, 88, 82, 86, 90],
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.2)",
            pointBackgroundColor: "#10B981",
          },
          {
            label: "Gisozi",
            data: [82, 80, 85, 88, 84],
            borderColor: "#F59E0B",
            backgroundColor: "rgba(245, 158, 11, 0.2)",
            pointBackgroundColor: "#F59E0B",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top" as const,
          },
        },
        scales: {
          r: {
            beginAtZero: true,
            max: 100,
          },
        },
      },
    })

    this.charts.set("cellPerformance", chart)
  }

  private _createStudentDistributionChart(): void {
    const canvas = document.querySelector("#studentDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Kabeza", "Kimisagara", "Gisozi", "Nyamirambo", "Muhima", "Gitega"],
        datasets: [
          {
            label: "Students",
            data: [1250, 1100, 980, 850, 720, 650],
            backgroundColor: ["#3B82F6", "#10B981", "#F59E0B", "#8B5CF6", "#EF4444", "#06B6D4"],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    })

    this.charts.set("studentDistribution", chart)
  }

  private _createAgeGroupChart(): void {
    const canvas = document.querySelector("#ageGroupChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "polarArea",
      data: {
        labels: ["18-25", "26-35", "36-45", "46-55", "55+"],
        datasets: [
          {
            data: [2800, 3200, 1800, 900, 300],
            backgroundColor: ["#3B82F6", "#10B981", "#F59E0B", "#8B5CF6", "#EF4444"],
            borderWidth: 2,
            borderColor: "#ffffff",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
          },
        },
      },
    })

    this.charts.set("ageGroup", chart)
  }

  private _createProgressTimelineChart(): void {
    const canvas = document.querySelector("#progressTimelineChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "line",
      data: {
        labels: ["Week 1", "Week 2", "Week 3", "Week 4", "Week 5", "Week 6"],
        datasets: [
          {
            label: "Kabeza",
            data: [100, 95, 92, 88, 85, 82],
            borderColor: "#3B82F6",
            tension: 0.4,
            fill: false,
          },
          {
            label: "Kimisagara",
            data: [100, 98, 94, 90, 87, 85],
            borderColor: "#10B981",
            tension: 0.4,
            fill: false,
          },
          {
            label: "Gisozi",
            data: [100, 96, 91, 86, 83, 80],
            borderColor: "#F59E0B",
            tension: 0.4,
            fill: false,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top" as const,
          },
        },
        scales: {
          y: {
            beginAtZero: false,
            min: 75,
            max: 100,
            ticks: {
              callback: (value: any) => value + "%",
            },
          },
        },
      },
    })

    this.charts.set("progressTimeline", chart)
  }

  private _updateChartsWithFilters(): void {
    console.log("[v0] Updating cell charts with filters:", this.filters)
    // Implementation would filter data and update charts
  }

  render() {
    return html`
      <div class="space-y-6">
        <!-- Cell Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">45</p>
            <p class="text-sm text-gray-600">Active Cells</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">5,550</p>
            <p class="text-sm text-gray-600">Total Learners</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-purple-600">84%</p>
            <p class="text-sm text-gray-600">Average Completion</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-orange-600">123</p>
            <p class="text-sm text-gray-600">Learners per Cell</p>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Cell Performance Radar -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cell Performance Radar</h3>
            <div class="chart-container">
              <canvas id="cellPerformanceChart"></canvas>
            </div>
          </div>

          <!-- Student Distribution by Cell -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Learners Distribution by Cell</h3>
            <div class="chart-container">
              <canvas id="studentDistributionChart"></canvas>
            </div>
          </div>

          <!-- Age Groups -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Age Groups Distribution</h3>
            <div class="chart-container">
              <canvas id="ageGroupChart"></canvas>
            </div>
          </div>

          <!-- Progress Timeline -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Timeline</h3>
            <div class="chart-container">
              <canvas id="progressTimelineChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Cell Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Top Performing Cells</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cell</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Learners</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Score</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kabeza</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,250</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">92%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">87.5</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">1st</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kimisagara</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,100</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">88%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">85.2</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">2nd</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Gisozi</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">980</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">85%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">83.8</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">3rd</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `
  }
}
