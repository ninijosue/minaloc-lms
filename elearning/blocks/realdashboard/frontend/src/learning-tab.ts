import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { CourseTrendType, LearningJourneyTrendType, LocationPerformanceTrend, PerformanceLearningMetrics, PositionTrendType } from "./types"
import { years } from "./constants";
import { downloadCsv, toTitleCase } from "./helpers";


@customElement("learning-tab")
export class DistrictTab extends LitElement {
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
  @state() private performanceYear: number = new Date().getFullYear();
  private charts: Map<string, any> = new Map()
  @state() private filteredTableData: LocationPerformanceTrend[] = [];
  @state() private activityYears: {
    label: string,
    value: string
  }[] = [{
    label: new Date().getFullYear().toString(),
    value: new Date().getFullYear().toString()
  }];

  firstUpdated(): void {
    this._initializeCharts()
  }

  connectedCallback(): void {
    super.connectedCallback()
    document.addEventListener("data-loaded", (event: Event) => {
      const customEvent = event as CustomEvent
      this.data = customEvent.detail
      this._initializeCharts()
    })

    document.addEventListener('on-filter-change', (e: Event) => {
      const customEvent = e as CustomEvent<any>;
      const sentData = customEvent.detail;
      this.filters = sentData;
    });
    this.getYears();
  }

  getYears(): void {
    const data = this.data?.learningAnalytics?.performanceTrend || [] as {
      month: string, year: number,
      count: number,
      enrolementCount: number,
      completionCount: number,
      faillerCount: number
    }[];
    const yearsFound: string[] = [];
    data.forEach((d: any) => {
      const total = d.enrolementCount + d.completionCount + d.faillerCount;
      if (!yearsFound.includes(d.year) && Number(total) !== 0 && d.year > 2022) {
        yearsFound.push(d.year);
      }
    });
    this.activityYears = yearsFound.map(y => ({
      value: y,
      label: y
    }))
      .sort((a, b) => Number(b.value) - Number(a.value));
    this.performanceYear = Number(this.activityYears[0]?.value) || new Date().getFullYear();
    this.requestUpdate();

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
    this._createPositionChart()
    this._createLearningJourneyChart()
    this._createCoursesChart()
    this._createGenderDistributionChart()
    this._createGenderDistributionChartEnrolling()
    this._createGenderDistributionChartSucceeding()
    this._createAgeDistributionChart();
    this.filteredTableData = (this.data?.learningAnalytics?.performanceLocationTrend || []) as LocationPerformanceTrend[];
  }

  private destroyCharts(canvas: any): void {
    const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }
  }

  getFullYearMonthsWithData(data: {
    month: string, year: number,
    enrolementCount: number,
    completionCount: number,
    faillerCount: number
  }[]): {
    month: string, year: number,
    enrolementCount: number,
    completionCount: number,
    faillerCount: number
  }[] {
  const monthsList = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];
  if (!data || !Array.isArray(data)) return [];
  const grouped: { [year: number]: { [month: string]: { enrolementCount: number, completionCount: number, faillerCount: number } } } = {};

  data.forEach(({ month, year, enrolementCount, completionCount, faillerCount }) => {
    if (!grouped[year]) grouped[year] = {};
    grouped[year][month] = { enrolementCount, completionCount, faillerCount };
  });

  const result: {
    month: string, year: number,
    enrolementCount: number,
    completionCount: number,
    faillerCount: number
  }[] = [];

  Object.keys(grouped).forEach(yearStr => {
    const year = Number(yearStr);
    monthsList.forEach(month => {
      const values = grouped[year][month] || { enrolementCount: 0, completionCount: 0, faillerCount: 0 };
      result.push({
        month,
        year,
        enrolementCount: values.enrolementCount,
        completionCount: values.completionCount,
        faillerCount: values.faillerCount
      });
    });
  });

  return result;

  }

  private _createPerformanceTrendChart(): void {
    const rowData = (this.data?.learningAnalytics?.performanceTrend || []) as {
      month: string, year: number,
      enrolementCount: number,
      completionCount: number,
      faillerCount: number
    }[];
    const data = this.getFullYearMonthsWithData(rowData);
    const canvas = document.querySelector("#performanceTrendChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    const currentYear = Number(this.performanceYear);
    const yearData = data.filter(d => d.year == currentYear);

    const months = yearData.map(d => `${d.month}`)
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
          // {
          //   label: "Failure",
          //   data: failureData,
          //   borderColor: "#F59E0B",
          //   backgroundColor: "rgba(245, 158, 11, 0.1)",
          //   tension: 0.4,
          // },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        decimalPlaces: 0,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
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

    const valueLabelPlugin = {
      id: 'valueLabel',
      afterDatasetsDraw(chart: any) {
        const { ctx } = chart;
        chart.data.datasets.forEach((dataset: any, i: any) => {
          const meta = chart.getDatasetMeta(i);
          if (!meta.hidden) {
            meta.data.forEach((element: any, index: any) => {
              const view = element;
              const position = view.getCenterPoint();
              const label = Number(dataset.data[index]).toLocaleString()

              ctx.save();
              ctx.font = 'bold 20px Arial';
              ctx.fillStyle = '#fff';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(label, position.x, position.y);
              ctx.restore();
            });
          }
        });
      }
    };

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: [`Completed `, `In Progress`],
        datasets: [
          {
            data: [data.completedCourses, data.inProgressCourses],
            backgroundColor: ["#10B981", "#3B82F6"],
            borderWidth: 2,
            borderColor: "#ffffff",
          },
        ],
      },
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
      },
    })

    this.charts.set("completion", chart)
  }

  private _createProgressChart(): void {
    const data = (this.data?.learningAnalytics?.performanceLocationTrend || []) as LocationPerformanceTrend[];
    const canvas = document.querySelector("#progressChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    // sort from highest to lowest based on enrollment count by combining the total of count
    data.sort((a, b) => {
      const totalA = (a.completionCount || 0);
      const totalB = (b.completionCount || 0);
      return totalB - totalA;
    });
    // take top 20 only
    const topData = data.slice(0, 20);
    const locations = data.map(d => toTitleCase(d.location))
    const enrollmentCounts = data.map(d => d.enrolementCount)
    const completionCounts = data.map(d => d.completionCount)
    const failureCounts = data.map(d => d.faillerCount)
    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: locations,
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
          // {
          //   label: "Failure",
          //   data: failureCounts,
          //   backgroundColor: "#f44336",
          // },

        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              callback: (value: any) => value,
            },
          },
        },
      },
    })

    this.charts.set("progress", chart)
  }
  // ---------leaning journey analysis chart -----------
  private _createLearningJourneyChart(): void {
    const data = (this.data?.learningAnalytics?.categoriesAnalytics || []) as LearningJourneyTrendType[];


    const canvas = document.querySelector("#LearningJourneyChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    // sort from highest to lowest based on enrollment count by combining the total of count
    data.sort((a, b) => {
      const totalA = (a.completions || 0);
      const totalB = (b.completions || 0);
      return totalB - totalA;
    });
    // take top 20 only
    const topData = data.slice(0, 20);
    const positions = data.map(d => {
      if(d.name == "health") return "Health facility"
      return toTitleCase(d.name);
    })
    const enrollmentCounts = data.map(d => d.enrollments)
    const completionCounts = data.map(d => d.completions)
    const failureCounts = data.map(d => d.failures)
    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: positions,
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
          // {
          //   label: "Failure",
          //   data: failureCounts,
          //   backgroundColor: "#f44336",
          // },

        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              callback: (value: any) => value,
            },
          },
        },
      },
    })

    this.charts.set("progress", chart)
  }

  // ---------positions analysis chart -----------
  private _createPositionChart(): void {
    const rowData = (this.data?.learningAnalytics?.positionAnalytics || []) as PositionTrendType[];
    let data: PositionTrendType[] = [];
    const otherPositions: PositionTrendType = {
      name: "others",
      completions: 0,
      enrollments: 0,
      failures: 0,
    }
    rowData.forEach(d => {
      const postName = d.name.toLowerCase().trim();
      const isInAvailablePositions = allPositions.includes(postName.toLowerCase());
      if (isInAvailablePositions) {
        data.push(d);
      }
      else {
        otherPositions.completions += d.completions || 0;
        otherPositions.enrollments += d.enrollments || 0;
        otherPositions.failures += d.failures || 0;
      }

    })
    data.push(otherPositions);
    const canvas = document.querySelector("#positionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    // sort from highest to lowest based on enrollment count by combining the total of count
    data.sort((a, b) => {
      const totalA = (a.completions || 0);
      const totalB = (b.completions || 0);
      return totalB - totalA;
    });
    // take top 20 only
    const topData = data.slice(0, 20);
    const positions = data.map(d => {
      if(d.name == "sedo") return "SEDO";
      if(d.name == "it") return "IT";
      if(d.name == "cro") return "CRO";
      return  toTitleCase(d.name);
    })
    const enrollmentCounts = data.map(d => d.enrollments)
    const completionCounts = data.map(d => d.completions)
    const failureCounts = data.map(d => d.failures)
    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: positions,
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
          // {
          //   label: "Failure",
          //   data: failureCounts,
          //   backgroundColor: "#f44336",
          // },

        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              callback: (value: any) => value,
            },
          },
        },
      },
    })

    this.charts.set("progress", chart)
  }

  // ---------Courses analysis chart -----------
  private _createCoursesChart(): void {
    const data = (this.data?.learningAnalytics?.coursesAnalytics || []) as CourseTrendType[];
    const canvas = document.querySelector("#coursesChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    // sort from highest to lowest based on enrollment count by combining the total of count
    data.sort((a, b) => {
      const totalA = (a.completions || 0);
      const totalB = (b.completions || 0);
      return totalB - totalA;
    });
    // take top 20 only
    const topData = data.slice(0, 20);
    const courses = data.map(d => toTitleCase(d.name))
    const enrollmentCounts = data.map(d => d.enrollments)
    const completionCounts = data.map(d => d.completions)
    const failureCounts = data.map(d => d.failures)
    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: courses,
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
          // {
          //   label: "Failure",
          //   data: failureCounts,
          //   backgroundColor: "#f44336",
          // },

        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              callback: (value: any) => value,
            },
          },
        },
      },
    })

    this.charts.set("progress", chart)
  }



  private _createGenderDistributionChart(): void {
    const data = (this.data?.learningAnalytics?.sexFaillingMetrics ?? {}) as { male: number, female: number }
    const canvas = document.querySelector("#genderDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    const valueLabelPlugin = {
      id: 'valueLabel',
      afterDatasetsDraw(chart: any) {
        const { ctx } = chart;
        chart.data.datasets.forEach((dataset: any, i: any) => {
          const meta = chart.getDatasetMeta(i);
          if (!meta.hidden) {
            meta.data.forEach((element: any, index: any) => {
              const view = element;
              const position = view.getCenterPoint();
              const label = Number(dataset.data[index]).toLocaleString()

              ctx.save();
              ctx.font = 'bold 20px Arial';
              ctx.fillStyle = '#fff';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(label, position.x, position.y);
              ctx.restore();
            });
          }
        });
      }
    };

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
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
          },
        },
      },
    })

    this.charts.set("genderDistribution", chart)
  }
  private _createGenderDistributionChartSucceeding(): void {
    const data = (this.data?.learningAnalytics?.sexCompletionMetrics ?? {}) as { male: number, female: number }
    const canvas = document.querySelector("#genderDistributionChartSucceeding") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);

    const valueLabelPlugin = {
      id: 'valueLabel',
      afterDatasetsDraw(chart: any) {
        const { ctx } = chart;
        chart.data.datasets.forEach((dataset: any, i: any) => {
          const meta = chart.getDatasetMeta(i);
          if (!meta.hidden) {
            meta.data.forEach((element: any, index: any) => {
              const view = element;
              const position = view.getCenterPoint();
              const label = Number(dataset.data[index]).toLocaleString()

              ctx.save();
              ctx.font = 'bold 20px Arial';
              ctx.fillStyle = '#fff';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(label, position.x, position.y);
              ctx.restore();
            });
          }
        });
      }
    };

    const chart = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Male", "Female"],
        datasets: [
          {
            data: [data.male || 0, data.female || 0],
            backgroundColor: ["#3B82F6", "#EC4899",],
            borderWidth: 2,
            borderColor: "#ffffff",
          },
        ],
      },
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
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
    const valueLabelPlugin = {
      id: 'valueLabel',
      afterDatasetsDraw(chart: any) {
        const { ctx } = chart;
        chart.data.datasets.forEach((dataset: any, i: any) => {
          const meta = chart.getDatasetMeta(i);
          if (!meta.hidden) {
            meta.data.forEach((element: any, index: any) => {
              const view = element;
              const position = view.getCenterPoint();
              const label = Number(dataset.data[index]).toLocaleString()

              ctx.save();
              ctx.font = 'bold 20px Arial';
              ctx.fillStyle = '#fff';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(label, position.x, position.y);
              ctx.restore();
            });
          }
        });
      }
    };
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
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom" as const,
            align: "end",
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
            label: "Completed",
            data: completeCounts,
            backgroundColor: "#10B981",
          },
          {
            label: "In Progress",
            data: inProgressCounts,
            backgroundColor: "#3B82F6",
          },
          {
            label: "Failed",
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
            position: "bottom" as const,
            align: "end",
          },
        },
        scales: {
          x: {
            stacked: true,
          },
          y: {
            stacked: true,
            beginAtZero: true,
            ticks: {
              precision: 0,
            }
          },
        },
      },
    })

    this.charts.set("ageDistribution", chart)
  }

  _downloadCSV(location: string, tableData: LocationPerformanceTrend[]) {

    const headers = [location, "Total Enrollment", "Completed",
     "Success Rate (%)"
    ];
    const data = tableData.map(d => {
      return [
        toTitleCase(d.location),
        d.enrolementCount ?? 0,
        d.completionCount ?? 0,
        // d.faillerCount ?? 0,
        d.successRate ?? 0
      ];
    })

    downloadCsv(headers, data, `${location} learning analysis.csv`);
  }

  _filterTable(e: Event, primitiveDataTable: LocationPerformanceTrend[] = []) {
    const input = e.target as HTMLInputElement;
    const searchTerm = input.value.toLowerCase();
    const tableData = primitiveDataTable;
    if (searchTerm.length) {
      this.filteredTableData = tableData.filter(d => d.location.trim().replace(" ", "").toLowerCase().includes(searchTerm.trim().replace(" ", "")));
    }
    else {
      this.filteredTableData = primitiveDataTable;
    }
    this.requestUpdate();
  }


  render() {
    const { district, sector, cell, village } = (this.filters ?? {}) as any;
    const location = village?.length ? "Villages" : cell?.length ? "Cells" : sector?.length ? "Sectors" : district?.length ? "Districts" : "Districts";
    const metrics = (this.data?.learningAnalytics?.metrics ?? {}) as PerformanceLearningMetrics
    const performanceLocationTrend = (this.data?.learningAnalytics?.performanceLocationTrend || []) as LocationPerformanceTrend[];
    const locationPerformances = this.filteredTableData;
    return html`
      <div class="space-y-6">
        <!-- District Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-green-600">${metrics.completedCourses?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Completed Courses</p>
          </div>
          <!-- <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-red-600">${metrics.failedCourses?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">Failed Courses</p>
          </div> -->
          
          <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-blue-600">${metrics.inProgressCourses?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">In progress</p>
          </div>
          <!-- <div class="bg-white p-4 rounded-lg shadow text-center">
            <p class="text-2xl font-bold text-gray-600">${metrics.totalCourses?.toLocaleString() || 0}</p>
            <p class="text-sm text-gray-600">All Courses</p>
          </div> -->
        </div>
          <!-- Performance Trend -->
        <div class="bg-white p-6 rounded-lg shadow">
               <h3 class="text-lg text-center font-semibold text-gray-900 mb-2">Monthly trends of learner's performance in the year of ${this.performanceYear}</h3>
             <div class="flex justify-end align-center">
             <div>
               <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Performance trends for all learners over months in the year of ${this.performanceYear}.
                </h4> -->
             </div>
               ${this.activityYears.length > 1 ? html`
                <search-field
              label="Year"
              name="year"
              placeholder="Select year"
              .onChange=${(value: string) => {

          this.performanceYear = parseInt(value, 10);
          this._createPerformanceTrendChart()
        }}
              .value=${this.performanceYear}
              className="min-w-[120px]"
              searchPlaceholder=""
              
              options=${JSON.stringify(this.activityYears)}
            ></search-field>
                `: ""
      }
            </div>
            <div class="chart-container">
              <canvas id="performanceTrendChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-center text-gray-900 mb-4"> Course completion by ${location}</h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Top locations by enrollment, completion and failure counts.
              </h4> -->
            <div class="chart-container">
              <canvas id="progressChart"></canvas>
            </div>
          </div>
         <!-- <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Courses Perform Trends</h3>
             <h4 class="text-sm text-gray-800 mb-3" > 
                Performance trends of courses by enrollment, completion and failure counts.
              </h4>
            <div class="chart-container">
              <canvas id="coursesChart"></canvas>
            </div>
          </div> -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-center text-gray-900 mb-4">Course completion by position</h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Performance trends of positions by enrollment, completion and failure counts.
                </h4> -->
            <div class="chart-container">
              <canvas id="positionChart"></canvas>
            </div>
          </div>

           <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-center text-gray-900 mb-4">Course Completion by learning journey</h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Performance trends of positions by enrollment, completion and failure counts.
                </h4> -->
            <div class="chart-container">
              <canvas id="LearningJourneyChart"></canvas>
            </div>
          </div>
        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Age Distribution -->
        
        <!-- Course Completion -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-4">Performance status of learners</h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Distribution of completed and failed courses among all learners.
                </h4> -->
            <div class="chart-container">
              <canvas id="completionChart"></canvas>
            </div>
          </div>
        
           <!-- Gender Distribution -->
         
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 mb-4">Courses performance by age group </h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Distribution of learners by age groups and their course outcomes.
                </h4> -->
            <div class="chart-container">
              <canvas id="ageDistributionChart"></canvas>
            </div>
          </div>
           <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibent text-center text-gray-900 mb-4">Total enrollments by Sex</h3>
             <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Distribution of learners by sex among all enrollments.
                </h4> -->
            <div class="chart-container">
              <canvas id="genderDistributionChartEnrolling"></canvas>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibent text-gray-900 text-center mb-4">Total Completions by Sex</h3>
            <!-- <h4 class="text-sm text-gray-800 mb-3" > 
                Distribution of completed courses by sex among all learners.
                </h4> -->
            <div class="chart-container">
              <canvas id="genderDistributionChartSucceeding"></canvas>
            </div>
          </div>
           <!-- <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibent text-gray-900 text-center mb-4">Total Failures by Sex</h3>
             <h4 class="text-sm text-gray-800 mb-3" > 
                Distribution of failed courses by sex among all learners.
                </h4>
            <div class="chart-container">
              <canvas id="genderDistributionChart"></canvas>
            </div>
          </div> -->
            
          
          
        </div>
          
        

        <!-- District Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="flex justify-between align-center px-6 py-4 border-b border-gray-200">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">${location} Performance </h3>
             <div class="relative w-full mt-3 max-w-md">
                  <input
                    type="text"
                    @input=${(e: Event) => this._filterTable(e, performanceLocationTrend)}
                    placeholder="Search..."
                    class="w-[200px] pl-10 pr-4 py-2 pt-[12px!important]  rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 bg-white shadow-sm"
                  />
                  <span class="absolute left-3 top-3 text-gray-400">
                    <!-- Heroicons: Magnifying Glass -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                  </span>
                </div>
            </div>
             <button
              type="button"
              class="flex items-center h-fit px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
              @click=${() => this._downloadCSV(location, locationPerformances)}
            >
              <!-- Download icon (Heroicons) -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
              </svg>
              Download
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${location}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Enrollment</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                  <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th> -->
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                ${locationPerformances.map(lp => {
        return html`
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap capitalize text-sm font-medium text-gray-900">${lp.location}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${lp.enrolementCount?.toLocaleString() || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">${lp.completionCount?.toLocaleString() || 0}</td>
                   <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">${lp.faillerCount?.toLocaleString() || 0}</td> -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${lp.successRate?.toLocaleString() || 0}%</td>
                  </tr>
                  `
      })}
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `
  }
}

const allPositions = [
  "sector executive secretary",
  "cro",
  "data manager",
  "mayor",
  "statistician",
  "director of good governance",
  "territorial administration officer",
  "director of health",
  "it",
  "executive secretary",
  "sedo",
  "director of nursing and midwives",
  "head of health center",
  "civil registrar at private health facility",
  "data manager at private health facility",
  "ambassador",
  "first secretary",
  "parasocial"
];
