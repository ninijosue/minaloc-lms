import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { FilterState, LearnerPerformanceTrend, PerformanceMetrics } from "./types"

import type { LocationPerformanceTrend, PerformanceLearningMetrics } from "./types"
import { years } from "./constants";
import { downloadCsv } from "./helpers";

@customElement("learner-dashboard")
export class LearnerDashboardMain extends LitElement {
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
  filters: any = {}
  @property({ type: Object })
  data: any = null;
  @state() private performanceYear:string = new Date().getFullYear().toString();
  @state() private loading: boolean = false;
  private charts: Map<string, any> = new Map()

  firstUpdated(): void {
    this._initializeCharts()
  }

   connectedCallback(): void {
    super.connectedCallback()
    // document.addEventListener("data-loaded", (event: Event) => {
    //   const customEvent = event as CustomEvent
    //   this.data = customEvent.detail
    //   this._initializeCharts()
    // })
      this.getData();


    // document.addEventListener('on-filter-change', (e: Event) => {
    //   const customEvent = e as CustomEvent<any>;
    //   const sentData = customEvent.detail;
    //   this.filters = sentData;
    // });
  }

 

  async getData(args?:  any): Promise<void> {
    // Fetch data from the server or use a static sample
    // For now, we will use a static sample
    this.loading = true
    try {
      const response = await fetch("/blocks/realdashboard/learner_analytics.php", {
       method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
    });
    const data = await response.json();
    console.log("Learner Analytics Data:", data);
    this.data = data;
    this._initializeCharts();
    } catch (error) {
      console.error("Error fetching data:", error);
    }
    finally {
      this.loading = false
    }
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
    this._createGenderDistributionChartEnrolling()
    this._createGenderDistributionChartSucceeding()
    this._createAgeDistributionChart();
  }

private destroyCharts(canvas: any): void {
     const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }  
}

  private _createPerformanceTrendChart(): void {
     const data = (this.data?.learningAnalytics?.performanceTrend || []) as {
      month: string, year: number, 
      enrolementCount: number,
      completionCount: number,
      faillerCount: number
    }[];
    const canvas = document.querySelector("#performanceTrendChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    const currentYear = Number(this.performanceYear);
    const yearData = data.filter(d=>d.year == currentYear);

    const months = yearData.map(d => `${d.month}`).slice(0, 3)
    const enrollmentData = yearData.map(d => d.enrolementCount)
    const completionData = yearData.map(d => d.completionCount)
    const failureData = yearData.map(d => d.faillerCount)

    const chart = new window.Chart(ctx, {
      type: "line",
      data: {
        labels: months,
        datasets: [
          {
            label: "Enrollment",
            data: enrollmentData,
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
          },
          {
            label: "Completion",
            data: completionData,
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
          },
          {
            label: "Failure",
            data: failureData,
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
            ticks: {
              callback: (value: any) => value,
            },
          },
        },
      },
    })

    this.charts.set("performanceTrend", chart)
  }

  private _createCompletionChart(): void {
    const data = (this.data?.learningAnalytics?.metrics ?? {}) as PerformanceLearningMetrics
    const canvas = document.querySelector("#completionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    const chart = new window.Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Completed", "Failed", "In Progress"],
        datasets: [
          {
            data: [data.completedCourses, data.failedCourses, data.inProgressCourses],
            backgroundColor: ["#10B981", "#EF4444", "#3B82F6"],
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
    const data = (this.data?.Analytics || []) as LearnerPerformanceTrend[];
      console.log("this.data?.Analytics:", this.data?.Analytics);
      const canvas = document.querySelector("#progressChart") as HTMLCanvasElement
      if (!canvas) return
      this.destroyCharts(canvas);
      const ctx = canvas.getContext("2d")
      if (!ctx) return
      console.log("Creating Progress Chart with data:", data);
      const months = data.map(d => d.month)
      const enrollmentCounts = data.map(d => d.numberOfEnrolments)
      const completionCounts = data.map(d => d.numberOfcomplted)
      const failureCounts = data.map(d => d.numberOfFailed)
      const inProgressCounts = data.map(d => d.numberOfInprogress)
      const chart = new window.Chart(ctx, {
        type: "bar",
        data: {
          labels: months,
          datasets: [
            {
              label: "Enrollment",
              data: enrollmentCounts,
              backgroundColor: "#F59E0B",
            },
            {
              label: "Completion",
              data: completionCounts,
              backgroundColor: "#3B82F6",
            },
            {
              label: "In Progress",
              data: inProgressCounts,
              backgroundColor: "#718096",
            },
            {
              label: "Failure",
              data: failureCounts,
              backgroundColor: "#f44336",
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
  
      this.charts.set("progress", chart)
      console.log("Progress Chart created:", chart);
  }

  private _createGenderDistributionChart(): void {
   const data = (this.data?.learningAnalytics?.sexFaillingMetrics ?? {}) as {male: number, female: number}
    const canvas = document.querySelector("#genderDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Male", "Female"],
        datasets: [
          {
            data: [data.male || 0, data.female || 0],
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
  private _createGenderDistributionChartSucceeding(): void {
    const data = (this.data?.learningAnalytics?.sexCompletionMetrics ?? {}) as {male: number, female: number}
    const canvas = document.querySelector("#genderDistributionChartSucceeding") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Female", "Male"],
        datasets: [
          {
            data: [data.male || 0, data.female || 0],
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
  private _createGenderDistributionChartEnrolling(): void {
    const data = (this.data?.enrollmentAnalytics?.metrics?.sexMetrics ?? {})
    const canvas = document.querySelector("#genderDistributionChartEnrolling") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Male", "Female"],
        datasets: [
          {
            data: [data.males || 0, data.females || 0],
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
    // Implementation would filter data and update charts
  }



   private _createAgeDistributionChart(): void {
    const data = (this.data?.learningAnalytics?.ageDistribution || []) as {
      ageGroup: string,
      completeCount: number,
      inProgressCount: number,
      failedCount: number
    }[];
    const canvas = document.querySelector("#ageDistributionChart") as HTMLCanvasElement
    if (!canvas) return
    const groups = data.map(d => d.ageGroup)
    const completeCounts = data.map(d => d.completeCount)
    const inProgressCounts = data.map(d => d.inProgressCount)
    const failedCounts = data.map(d => d.failedCount)
    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: groups,
        datasets: [
          {
            label: "Completed Courses",
            data: completeCounts,
            backgroundColor: "#10B981",
          },
          {
            label: "In Progress courses",
            data: inProgressCounts,
            backgroundColor: "#3B82F6",
          },
          {
            label: "Failed Courses",
            data: failedCounts,
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

    this.charts.set("ageDistribution", chart)
  }

   _downloadCSV(location: string, tableData: LocationPerformanceTrend[]) {
  
      const headers = [location, "Total Enrollment", "Completed", "Failed", "Success Rate (%)"];
    const data = tableData.map(d=>{
      return [
        d.location,
        d.enrolementCount ?? 0,
        d.completionCount ?? 0,
        d.faillerCount ?? 0,
        d.successRate ?? 0
      ];
    })
  
    downloadCsv(headers, data, `${location} learning analysis.csv`);
    }


  render() {
    const {district, sector, cell, village} = (this.filters ?? {}) as any;
    const location = village?.length ? "Villages": cell?.length ? "Cells": sector?.length ? "Sectors": district?.length ? "Districts": "Districts";
    const metrics = (this.data?.learningAnalytics?.metrics ?? {}) as PerformanceLearningMetrics
    const locationPerformances = (this.data?.learningAnalytics?.performanceLocationTrend || []) as LocationPerformanceTrend[];

   

    return html`
      <div class="min-h-[300px] space-y-6 space-y-6">
        ${this.loading ? html`
          <div class="flex items-center justify-center ">
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
    </div>
            `: ""}
        <!-- District Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
           <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-gray-600">${this.data?.totalEnrolments?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">All Enrollments</p>
          </div>
           <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">${this.data?.totalInprogress?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">In progress</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">${this.data?.totalCompleted?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Completed Courses</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-red-600">${this.data?.totalFailed?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Failed Courses</p>
          </div>
        </div>
          <!-- Performance Trend -->
       
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Trend</h3>
            <div class="chart-container">
              <canvas id="progressChart"></canvas>
            </div>
          </div>
        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Age Distribution -->
        
     
          
          
        </div>
          
   
      </div>
    `
  }
}


class  arnerDashboardMain extends LitElement {
    static styles = css`
    :host {
      display: block;
      font-family: system-ui, -apple-system, sans-serif;
    }
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }
  `
  @property({ type: String })
  activeTab = "enrollment"

  @property({ type: Object })
  filters: any = {}

  @state() private data: any = null;
  @state() private loading: boolean = false;
  private charts: Map<string, any> = new Map()

  protected createRenderRoot(): HTMLElement | DocumentFragment {
      return this;
  }

  firstUpdated(): void {
   this._createProgressChart();

  }

  connectedCallback(): void {
      super.connectedCallback();
      this.getData();
  }

  async getData(args?:  any): Promise<void> {
    // Fetch data from the server or use a static sample
    // For now, we will use a static sample
    this.loading = true
    try {
      const response = await fetch("/blocks/realdashboard/learner_analytics.php", {
       method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
    });
    const data = await response.json();
    console.log("Learner Analytics Data:", data);
    this.data = data;
    this._createProgressChart();
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

   private _createProgressChart(): void {
      const data = (this.data?.Analytics || []) as LearnerPerformanceTrend[];
      console.log("this.data?.Analytics:", this.data?.Analytics);
      const canvas = document.querySelector("#progressChart") as HTMLCanvasElement
      if (!canvas) return
      this.destroyCharts(canvas);
      const ctx = canvas.getContext("2d")
      if (!ctx) return
      console.log("Creating Progress Chart with data:", data);
      const months = data.map(d => d.month)
      const enrollmentCounts = data.map(d => d.numberOfEnrolments)
      const completionCounts = data.map(d => d.numberOfcomplted)
      const failureCounts = data.map(d => d.numberOfFailed)
      const inProgressCounts = data.map(d => d.numberOfInprogress)
      const chart = new window.Chart(ctx, {
        type: "bar",
        data: {
          labels: months,
          datasets: [
            {
              label: "Enrollment",
              data: enrollmentCounts,
              backgroundColor: "#F59E0B",
            },
            {
              label: "Completion",
              data: completionCounts,
              backgroundColor: "#3B82F6",
            },
            {
              label: "In Progress",
              data: inProgressCounts,
              backgroundColor: "#718096",
            },
            {
              label: "Failure",
              data: failureCounts,
              backgroundColor: "#f44336",
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
  
      this.charts.set("progress", chart)
      console.log("Progress Chart created:", chart);
    }
    private destroyCharts(canvas: any): void {
     const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }  
}

  
 
  render() {
    if (this.loading) {
      return html`<div class="flex items-center justify-center h-64">
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
    </div>`
    }
    return html`
      <div class="min-h-[300px] space-y-6 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
           <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-gray-600">${this.data?.totalEnrolments?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">All Enrollments</p>
          </div>
           <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">${this.data?.totalInprogress?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">In progress</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">${this.data?.totalCompleted?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Completed Courses</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-red-600">${this.data?.totalFailed?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Failed Courses</p>
          </div>
          
         
         
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress Trend</h3>
            <div class="chart-container">
              <canvas id="progressChart"></canvas>
            </div>
          </div>
      </div>
    `
  }
}
