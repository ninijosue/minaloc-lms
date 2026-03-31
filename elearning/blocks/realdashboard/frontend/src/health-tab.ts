import { LitElement, html, css } from "lit"
import { property, customElement } from "lit/decorators.js"
import type { FilterState, PerformanceMetrics } from "./types"

@customElement("health-tab")
export class HealthTab extends LitElement {
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
    this._createHealthFacilityPerformanceChart()
    this._createWorkerCategoryChart()
    this._createTrainingTrendsChart()
    this._createServiceDeliveryChart()
    this._createAgeDistributionChart()
  }

   private _createAgeDistributionChart(): void {
    const canvas = document.querySelector("#ageDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: ["18-25", "26-35", "36-45", "46-55", "55+"],
        datasets: [
          {
            label: "Learners",
            data: [5200, 6800, 3900, 1500, 380],
            backgroundColor: "#8B5CF6",
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

    this.charts.set("ageDistribution", chart)
  }


  private _createHealthFacilityPerformanceChart(): void {
    const canvas = document.querySelector("#healthFacilityPerformanceChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: ["District Hospital", "Health Center", "Clinic", "Dispensary", "Private Clinic"],
        datasets: [
          {
            label: "Completed Training",
            data: [450, 1200, 800, 350, 280],
            backgroundColor: "#10B981",
          },
          {
            label: "In Progress",
            data: [50, 150, 100, 45, 35],
            backgroundColor: "#3B82F6",
          },
          {
            label: "Not Started",
            data: [25, 80, 50, 20, 15],
            backgroundColor: "#6B7280",
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
          x: {
            stacked: true,
          },
          y: {
            stacked: true,
            beginAtZero: true,
          },
        },
      },
    })

    this.charts.set("healthFacilityPerformance", chart)
  }

  private _createWorkerCategoryChart(): void {
    const canvas = document.querySelector("#workerCategoryChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Nurses", "Doctors", "Community Health Workers", "Lab Technicians", "Pharmacists", "Others"],
        datasets: [
          {
            data: [4500, 1200, 8900, 650, 480, 1270],
            backgroundColor: ["#3B82F6", "#10B981", "#F59E0B", "#8B5CF6", "#EF4444", "#06B6D4"],
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

    this.charts.set("workerCategory", chart)
  }

  private _createTrainingTrendsChart(): void {
    const canvas = document.querySelector("#trainingTrendsChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [
          {
            label: "Basic Training",
            data: [120, 150, 180, 200, 220, 250, 280, 300, 320, 340, 360, 380],
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Advanced Training",
            data: [50, 60, 75, 85, 95, 110, 125, 140, 155, 170, 185, 200],
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Specialized Training",
            data: [20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75],
            borderColor: "#F59E0B",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            fill: true,
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
            beginAtZero: true,
          },
        },
      },
    })

    this.charts.set("trainingTrends", chart)
  }

  private _createServiceDeliveryChart(): void {
    const canvas = document.querySelector("#serviceDeliveryChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "radar",
      data: {
        labels: ["Patient Care", "Emergency Response", "Preventive Care", "Health Education", "Community Outreach"],
        datasets: [
          {
            label: "Before Training",
            data: [65, 70, 60, 55, 50],
            borderColor: "#EF4444",
            backgroundColor: "rgba(239, 68, 68, 0.2)",
            pointBackgroundColor: "#EF4444",
          },
          {
            label: "After Training",
            data: [88, 92, 85, 90, 87],
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.2)",
            pointBackgroundColor: "#10B981",
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

    this.charts.set("serviceDelivery", chart)
  }

  private _updateChartsWithFilters(): void {
    console.log("[v0] Updating health charts with filters:", this.filters)
    // Implementation would filter data and update charts
  }

  render() {
    return html`
      <div class="space-y-6">
        <!-- Health Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">17,000</p>
            <p class="text-sm text-gray-600">Health Workers</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">3,080</p>
            <p class="text-sm text-gray-600">Completed Training</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-purple-600">380</p>
            <p class="text-sm text-gray-600">Health Facilities</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-orange-600">92%</p>
            <p class="text-sm text-gray-600">Service Improvement</p>
          </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Health Facility Performance -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Facility Performance</h3>
            <div class="chart-container">
              <canvas id="healthFacilityPerformanceChart"></canvas>
            </div>
          </div>

          <!-- Health Worker Categories -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Worker Categories</h3>
            <div class="chart-container">
              <canvas id="workerCategoryChart"></canvas>
            </div>
          </div>

          <!-- Training Trends -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Training Trends</h3>
            <div class="chart-container">
              <canvas id="trainingTrendsChart"></canvas>
            </div>
          </div>

          <!-- Service Delivery Impact -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Delivery Impact</h3>
            <div class="chart-container">
              <canvas id="serviceDeliveryChart"></canvas>
            </div>
          </div>
             
        </div>

        <!-- Health Facility Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Health Facility Training Summary</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Workers</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trained</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Rate</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">District Hospital</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">525</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">450</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">50</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">86%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Health Center</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1,430</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">1,200</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">150</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">84%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Clinic</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">950</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">800</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">100</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">84%</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dispensary</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">415</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">350</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">45</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">84%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `
  }
}
