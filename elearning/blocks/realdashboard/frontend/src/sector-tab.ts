import { LitElement, html, css } from "lit"
import { property, customElement } from "lit/decorators.js"
import type { FilterState, PerformanceMetrics } from "./types"

@customElement("sector-tab")
export class SectorTab extends LitElement {
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
    this._createPerformanceTrendChart()
    this._createCompletionChart()
    this._createProgressChart()
    this._createGenderDistributionChart()
  }

  private _createPerformanceTrendChart(): void {
    const canvas = document.querySelector("#performanceTrendChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
          {
            label: "Kigali",
            data: [85, 87, 89, 91, 88, 92],
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
          },
          {
            label: "Bugesera",
            data: [78, 80, 82, 85, 83, 87],
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
          },
          {
            label: "Rulindo",
            data: [72, 75, 77, 79, 81, 84],
            borderColor: "#F59E0B",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            tension: 0.4,
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
            min: 60,
            max: 100,
            ticks: {
              callback: (value: any) => value + "%",
            },
          },
        },
      },
    })

    this.charts.set("performanceTrend", chart)
  }

  private _createCompletionChart(): void {
    const canvas = document.querySelector("#completionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Completed", "Failed", "Not Started", "In Progress"],
        datasets: [
          {
            data: [12450, 890, 4439, 2100],
            backgroundColor: ["#10B981", "#EF4444", "#6B7280", "#3B82F6"],
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

    this.charts.set("completion", chart)
  }

  private _createProgressChart(): void {
    const canvas = document.querySelector("#progressChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Kigali", "Bugesera", "Rulindo", "Nyarugenge"],
        datasets: [
          {
            label: "Completion",
            data: [40, 45, 35, 38],
            backgroundColor: "#3B82F6",
          },
          {
            label: "Fail",
            data: [8, 5, 10, 7],
            backgroundColor: "#F59E0B",
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
            beginAtZero: true,
            max: 50,
          },
        },
      },
    })

    this.charts.set("progress", chart)
  }

  private _createGenderDistributionChart(): void {
    const canvas = document.querySelector("#genderDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Male", "Female"],
        datasets: [
          {
            data: [8890, 8890],
            backgroundColor: ["#3B82F6", "#EC4899"],
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

    this.charts.set("genderDistribution", chart)
  }

  private _updateChartsWithFilters(): void {
    console.log("[v0] Updating sector charts with filters:", this.filters)
    // Implementation would filter data and update charts
  }

  private _getSectorMetrics(): PerformanceMetrics {
    return {
      totalStudents: 17780,
      enrolledStudents: 17779,
      excelledStudents: 166,
      completedCourses: 12450,
      failedCourses: 890,
      notStarted: 4439,
      females: 0,
      males: 0,
      allCourses: 0
    }
  }

  render() {
    const metrics = this._getSectorMetrics()

    return html`
      <div class="space-y-6">
        <!-- Sector Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">${metrics.completedCourses.toLocaleString()}</p>
            <p class="text-sm text-gray-600">Completed Courses</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-red-600">${metrics.failedCourses.toLocaleString()}</p>
            <p class="text-sm text-gray-600">Failed Courses</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-gray-600">${metrics.notStarted.toLocaleString()}</p>
            <p class="text-sm text-gray-600">Not Started</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">2,100</p>
            <p class="text-sm text-gray-600">In Progress</p>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Performance Trend -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sector Performance Trends</h3>
            <div class="chart-container">
              <canvas id="performanceTrendChart"></canvas>
            </div>
          </div>

          <!-- Course Completion -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Completion Status</h3>
            <div class="chart-container">
              <canvas id="completionChart"></canvas>
            </div>
          </div>

          <!-- Age Distribution -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress</h3>
            <div class="chart-container">
              <canvas id="progressChart"></canvas>
            </div>
          </div>

          <!-- Gender Distribution -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibent text-gray-900 mb-4">Gender Distribution</h3>
            <div class="chart-container">
              <canvas id="genderDistributionChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Sector Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Sector Performance Summary</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Students</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kigali</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5,200</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">4,784</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">416</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">92%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Bugesera</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4,100</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">3,567</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">533</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">87%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rulindo</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3,800</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">3,192</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">608</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">84%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nyarugenge</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">4,680</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">4,212</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">468</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">90%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `
  }
}
