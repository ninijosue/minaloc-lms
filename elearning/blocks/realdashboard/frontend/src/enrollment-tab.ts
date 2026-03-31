import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { FilterState, PerformanceLearningMetrics, PerformanceMetrics } from "./types"
import { years } from "./constants"

import './components/search-field';
import { downloadCsv, toTitleCase } from "./helpers";

type StudentsByLocationEntity = {
  location: string,
  count: number,
  males: number,
  females: number,
  ageDistribution: {
    ageGroup: string,
    count: number
  }[]
}

@customElement("enrollment-tab")
export class EnrollmentTab extends LitElement {

  constructor() {
    super();
    this._filterTable = this._filterTable.bind(this);
  }
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

  @state() private activityYear = new Date().getFullYear();
  @state() private filteredTableData: StudentsByLocationEntity[] = [];
  @state() private activityYears: {
    label: string,
    value: string
  }[] = [{
    label: new Date().getFullYear().toString(),
    value: new Date().getFullYear().toString()
  }];
  @state() private allCoursesCount: any = [];

  private charts: Map<string, any> = new Map()

  firstUpdated(): void {
    this._initializeCharts()
    this.filteredTableData = (this.data?.enrollmentAnalytics?.studentByLocation || []) as StudentsByLocationEntity[];
    this.getListOfCourses();
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
    const data = this.data?.enrollmentAnalytics?.studentJoiningCourses || [] as { month: string, year: number, count: number }[];
    const yearsFound: string[] = [];
    data.forEach((d: any) => {
      if (!yearsFound.includes(d.year) && d.year > 2022) {
        yearsFound.push(d.year);
      }
    });
    this.activityYears = yearsFound.map(y => ({
      value: y,
      label: y
    }))
      .sort((a, b) => Number(b.value) - Number(a.value));
    this.activityYear = Number(this.activityYears[0]?.value) || new Date().getFullYear();
    this.requestUpdate();

  }

  async getListOfCourses(){
    try {
     
      const response = await fetch("/blocks/realdashboard/get_courses_list.php", {
         method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      }
      });
      const data = await response.json();
      const courses = data?.availableCourses || [];
      this.allCoursesCount = courses;
      console.log("this.allCoursesCount", courses.lenght,this.allCoursesCount)
      this.requestUpdate()
    } catch (error) {
      console.error("Error fetching courses list:", error);
    }
  }

  updated(changedProperties: Map<string, any>): void {
    if (changedProperties.has("filters")) {
      this._updateChartsWithFilters()
    }
  }

  private _initializeCharts(): void {
    this._createActivityChart()
    this._createAgeDistributionChart()
    this._createDistrictChart()
    this._createDistrictPercentageChart()
    this._createGenderDistributionChartEnrolling()
  }

  private _createGenderDistributionChartEnrolling(): void {
    const data = (this.data?.enrollmentAnalytics?.metrics?.sexMetrics ?? {})
    const canvas = document.querySelector("#sexDistributionChartEnrolling") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    this.destroyCharts(canvas);
    const metrics = this._getMetrics();

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
            data: [metrics.males || 0, metrics.females || 0],
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
          },
        },
      },
    })

    this.charts.set("sexDistribution", chart)
  }

  private destroyCharts(canvas: any): void {
    const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }
  }

  getFullYearMonthsWithData(data: { month: string, year: number, count: number }[]): { month: string, year: number, count: number }[] {
    // Ensure every month of the year is present, fill missing months with count 0
    const monthsList = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ];
    if (!data || !Array.isArray(data)) return [];
    const grouped: { [year: number]: { [month: string]: number } } = {};

    data.forEach(({ month, year, count }) => {
      if (!grouped[year]) grouped[year] = {};
      grouped[year][month] = count;
    });

    const result: { month: string, year: number, count: number }[] = [];
    Object.keys(grouped).forEach(yearStr => {
      const year = Number(yearStr);
      monthsList.forEach(month => {
        result.push({
          month,
          year,
          count: grouped[year][month] ?? 0
        });
      });
    });

    return result;

  }

  private _createActivityChart(): void {
    const rowData = (this.data?.enrollmentAnalytics?.studentJoiningCourses || []) as { month: string, year: number, count: number }[];
    const data = this.getFullYearMonthsWithData(rowData);
    const currentYear = Number(this.activityYear);
    const yearData = data.filter(d => d.year == currentYear);
    const months = yearData.map(yd => yd.month.slice(0, 3));
    const monthsData = yearData.map(yd => yd.count)
    const canvas = document.querySelector("#activityChart") as HTMLCanvasElement
    if (!canvas) return;

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }
    const chart = new window.Chart(ctx, {
      type: "line",
      data: {
        labels: months,
        datasets: [
          {
            label: "Learners Joining Courses",
            data: monthsData,
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0,
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
            ticks: {
              precision: 0,
              callback: (value: any) => value.toLocaleString(),
            },
          },
        },
      },
    })
    this.charts.set("activity", chart)
  }

  private _createAgeDistributionChart(): void {
    const data = (this.data?.enrollmentAnalytics?.ageDistribution || []) as { ageGroup: string, count: number }[];
    const groups = data.map(d => d.ageGroup);
    const groupsData = data.map(d => d.count)
    const canvas = document.querySelector("#ageDistributionChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: groups,
        datasets: [
          {
            label: "Learners",
            data: groupsData,
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
            ticks: {
              precision: 0,
            },
            beginAtZero: true,
          },
        },
      },
    })

    this.charts.set("ageDistribution", chart)
  }



  private _createDistrictChart(): void {
    const data = (this.data?.enrollmentAnalytics?.studentByLocation || []) as StudentsByLocationEntity[];
    const canvas = document.querySelector("#districtChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return
    const existingChart = window.Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }
    // sort from highest to lowest
    data.sort((a, b) => b.count - a.count);
    const limitedData = data.slice(0, 20); // Top 5 districts
    const labels = limitedData.map(d => toTitleCase(d.location));
    const counts = limitedData.map(d => d.count);
    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Learners",
            data: counts,
            backgroundColor: "#3B82F6",
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
            ticks: {
              precision: 0,
            }
            // max: 1600,
          },
        },
      },
    })

    this.charts.set("district", chart)
  }

  private _createDistrictPercentageChart(): void {
    const canvas = document.querySelector("#districtPercentageChart") as HTMLCanvasElement
    if (!canvas) return

    const ctx = canvas.getContext("2d")
    if (!ctx) return

    const chart = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Kigali", "Bugesera", "Burera", "Nyothu", "Rulindo", "Nyanza"],
        datasets: [
          {
            label: "Percentage",
            data: [120, 95, 85, 75, 70, 60],
            backgroundColor: "#3B82F6",
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
            ticks: {
              precision: 0,
              callback: (value: any) => value + "%",
            },
          },
        },
      },
    })

    this.charts.set("districtPercentage", chart)
  }

  private _updateChartsWithFilters(): void {
    // Update chart data based on filters
    console.log("[v0] Updating charts with filters:", this.filters)
    // Implementation would filter data and update charts
  }

  private _getMetrics(): PerformanceMetrics {
    const data = this.data?.enrollmentAnalytics.metrics || {};
    const learningMetrics = (this.data?.learningAnalytics?.metrics ?? {}) as PerformanceLearningMetrics
    const totalAnalytics = this.data?.totalAnalytics?.metrics || {} as PerformanceLearningMetrics;
    return {
      totalStudents: totalAnalytics.totalStudents || 0,
      enrolledStudents: data.enrolledStudents || 0,
      excelledStudents: 166,
      completedCourses: 12450,
      failedCourses: 890,
      notStarted: 4439,
      allCourses: learningMetrics.totalCourses || 0,
      males: data.sexMetrics?.males || 0,
      females: data.sexMetrics?.females || 0
    }
  }

  _downloadCSV(location: string, tableData: StudentsByLocationEntity[]) {

    const headers = [location, "Total Learners", "Male", "Female", "Age(< 24)", "Age(25-34)", "Age(35-44)", "Age(45+)"];
    const data = tableData.map(d => {
      return [
        toTitleCase(d.location),
        d.count,
        d.males,
        d.females,
        d.ageDistribution[0]?.count ?? 0,
        d.ageDistribution[1]?.count ?? 0,
        d.ageDistribution[2]?.count ?? 0,
        d.ageDistribution[3]?.count ?? 0
      ];
    })

    downloadCsv(headers, data, `${location} enrollment analysis.csv`);
  }

  _filterTable(e: Event, primitiveDataTable: StudentsByLocationEntity[] = []) {
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
    const metrics = this._getMetrics();
    const studentByLocationData = (this.data?.enrollmentAnalytics?.studentByLocation || []) as StudentsByLocationEntity[];
    const tableData = this.filteredTableData;
    const coursesCount = this.allCoursesCount.length;

    return html`
      <div class="space-y-6">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3  gap-6">
          
          <div class="bg-white p-6 px-[20px!important] rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">${metrics.totalStudents.toLocaleString()}</p>
                <p class="text-sm text-gray-500">All Learners</p>
              </div>
            </div>
          </div>
          <!-- <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" xmlns:xlink="http://www.w3.org/1999/xlink" height="800px" width="800px" version="1.1" id="_x32_" viewBox="0 0 512 512" xml:space="preserve">
                    <style type="text/css">
                      .st0{fill:#000000;}
                    </style>
                    <g>
                      <path class="st0" d="M432.871,404.268c-10.756-18.361-27.411-29.408-43.426-36.782c-16.038-7.367-31.903-11.337-41.438-14.935   c-7.56-2.808-15.799-7.195-21.676-11.948c-2.943-2.346-5.274-4.782-6.674-6.904c-1.446-2.13-1.885-3.784-1.885-4.894   c0-7.591,0-11.025,0-22.57c10.116-11.263,24.655-28.7,30.615-56.358c2.093-0.938,4.156-1.996,6.138-3.389   c4.96-3.412,9.154-8.365,12.708-15.106c3.59-6.771,6.756-15.427,10.138-27.27c1.706-6.011,2.51-11.226,2.51-15.874   c0-5.356-1.103-9.996-3.129-13.772c-1.743-3.293-4.127-5.661-6.592-7.419c32.73-73.058-9.289-131.94-9.289-131.94l12.335-31.709   c0,0-52.849,3.523-99.814-1.758c-135.694-15.247-143.277,79.858-143.277,122.13c0,25.326,3.784,40.045,7.061,48.06   c-0.663,0.871-1.378,1.631-1.929,2.644c-2.018,3.769-3.121,8.417-3.121,13.772c0.015,4.64,0.797,9.855,2.518,15.866   c4.529,15.769,8.611,25.944,13.9,33.422c2.652,3.71,5.654,6.69,8.931,8.954c1.996,1.393,4.06,2.451,6.138,3.389   c5.974,27.642,20.506,45.087,30.622,56.35c0,11.546,0,14.987,0,22.578c0.022,0.946-0.455,2.681-2.026,4.924   c-2.287,3.359-6.771,7.359-11.985,10.741c-5.192,3.404-11.106,6.287-16.074,8.03c-6.458,2.279-15.732,4.819-25.885,8.409   c-15.248,5.401-32.73,13.178-46.726,27.151c-14.018,13.914-23.985,34.316-23.902,62.324c0,3.561,0.156,7.256,0.484,11.062   c0.209,2.391,1.042,4.365,2.048,6.049c1.944,3.136,4.558,5.571,7.844,8.045c5.758,4.268,13.75,8.469,24.141,12.611   c31.062,12.342,83.614,23.836,153.855,23.85c57.073-0.007,102.495-7.612,134.168-17.072c15.836-4.738,28.208-9.908,37.095-15.025   c4.461-2.57,8.052-5.117,10.89-7.888c1.415-1.386,2.652-2.831,3.672-4.522c1.02-1.684,1.855-3.658,2.064-6.041   c0.32-3.814,0.469-7.493,0.469-11.039C444.38,431.754,440.045,416.477,432.871,404.268z M243.374,496.291   c-0.246-0.008-0.492-0.008-0.745-0.008l-24.812-58.228l0.32,0.253l22.57-28.216l3.702,15.575h0.991L243.374,496.291z    M212.975,426.704l-28.462-66.756c3.568-2.071,7.076-4.35,10.294-6.905c1.966-1.579,3.844-3.24,5.564-5.006l47.56,34.965   L212.975,426.704z M207.068,338.979c1.572-3.053,2.645-6.435,2.66-10.174c0-8.224,0-11.441,0-25.535v-2.979l-1.982-2.205   c-10.57-11.776-24.879-27.404-29.848-55.173l-0.79-4.447l-4.238-1.505c-2.696-0.96-4.744-1.951-6.548-3.195   c-2.644-1.869-5.05-4.425-7.858-9.653c-2.764-5.2-5.714-12.976-8.916-24.261c-1.423-4.924-1.915-8.76-1.915-11.605   c0-3.314,0.633-5.236,1.282-6.465c0.976-1.774,2.175-2.533,3.702-3.143c1.043-0.403,2.145-0.552,2.778-0.604l6.011,1.274   l8.633,14.92c0,0-0.469-74.205,7.047-79.851c9.393-7.054,63.426,14.093,79.858,14.093c16.446,0,68.7-22.905,77.974-15.97   c10.786,8.067,8.931,88.774,8.931,88.774l8.834-22.86l3.621-0.388c0.633,0.008,2.942,0.276,4.514,1.311   c0.879,0.574,1.609,1.236,2.272,2.443c0.641,1.229,1.274,3.151,1.274,6.458c0.014,2.853-0.492,6.689-1.899,11.62   c-4.276,15.046-8.104,23.806-11.628,28.663c-1.758,2.458-3.367,3.986-5.162,5.244c-1.788,1.244-3.851,2.235-6.548,3.195   l-4.238,1.505l-0.782,4.447c-4.953,27.769-19.27,43.397-29.84,55.173l-1.996,2.205v2.979c0,14.094,0,17.311,0,25.535   c0.015,3.724,1.035,7.143,2.592,10.235l-48.857,35.895L207.068,338.979z M269.341,496.35c-0.246,0.015-0.462,0.022-0.716,0.022   l-2.033-70.704h1.028l3.695-15.575l22.562,28.208l0.32-0.246L269.341,496.35z M299.024,426.704l-34.95-43.694l47.516-34.928   c3.247,3.315,6.994,6.294,11.054,8.968c1.564,1.021,3.248,1.862,4.886,2.793L299.024,426.704z"/>
                    </g>
                    </svg>
              </div>
              <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">${metrics.males.toLocaleString()}</p>
                <p class="text-sm text-gray-500">Male</p>
              </div>
            </div>
          </div> -->
           <!-- <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-pink-600"  xmlns:xlink="http://www.w3.org/1999/xlink" height="800px" width="800px" version="1.1" id="_x32_" viewBox="0 0 512 512" xml:space="preserve">
                  <style type="text/css">
                    .st0{fill:#000000;}
                  </style>
                  <g>
                    <path class="st0" d="M441.707,398.886c-11.3-19.282-28.783-30.878-45.609-38.615c-16.833-7.731-33.486-11.906-43.506-15.692   c-8.301-3.077-17.072-7.08-23.191-11.371c-3.056-2.139-5.412-4.335-6.777-6.199c-1.38-1.914-1.755-3.236-1.77-4.393   c0-7.976,0-11.58,0-23.704c3.568-3.973,7.629-8.626,11.762-14.232c28.811-10.244,82.525-41.425,73.054-103.029   C388.628,70.814,349.218,0,258.651,0c-1.596,0-3.128,0.086-4.696,0.123C252.388,0.086,250.856,0,249.26,0   c-90.566,0-129.976,70.814-147.019,181.652c-10.107,65.765,51.807,96.874,78.537,104.835c3.656,4.79,7.196,8.901,10.368,12.426   c0,12.123,0,15.728,0,23.704c0,0.982-0.39,2.398-1.915,4.422c-2.232,3.005-6.863,6.646-12.361,9.731   c-5.484,3.121-11.79,5.815-17.31,7.766c-6.777,2.384-16.515,5.057-27.186,8.821c-16.002,5.671-34.353,13.842-49.047,28.508   c-14.731,14.615-25.192,36.043-25.098,65.432c0,3.743,0.159,7.615,0.491,11.632c0.224,2.5,1.105,4.574,2.168,6.343   c2.03,3.294,4.783,5.845,8.236,8.445c6.04,4.487,14.434,8.886,25.329,13.243c32.626,12.96,87.8,25.018,161.547,25.04   c59.92,0,107.624-8.004,140.863-17.924c16.631-4.978,29.628-10.396,38.962-15.772c4.682-2.716,8.446-5.382,11.422-8.279   c1.488-1.46,2.788-2.991,3.865-4.754c1.062-1.77,1.943-3.843,2.16-6.343c0.34-4.017,0.499-7.874,0.499-11.603   C453.786,427.741,449.234,411.702,441.707,398.886z M173.995,232.434l-0.824-4.66l-4.45-1.589   c-2.832-1.012-4.992-2.045-6.878-3.353c-2.782-1.965-5.303-4.645-8.251-10.142c-2.911-5.462-5.996-13.619-9.37-25.46   c-1.481-5.18-2.008-9.204-2.008-12.195c0.007-3.467,0.672-5.505,1.351-6.784c1.025-1.864,2.283-2.659,3.887-3.302   c1.098-0.426,2.261-0.585,2.918-0.635l2.182,0.462c9.508,24.238,9.349,29.396,9.132,30.343   c9.175-21.602-23.819-116.315,87.576-110.044c1.633-0.087,3.121-0.13,4.696-0.202c1.575,0.072,3.063,0.115,4.696,0.202   c111.394-6.271,78.401,88.442,87.576,110.044c-0.217-0.94-0.376-6.076,9.067-30.178l5.924-0.636   c0.512-0.014,3.114,0.238,4.826,1.38c0.932,0.593,1.69,1.287,2.384,2.565c0.686,1.279,1.343,3.309,1.358,6.784   c0,2.991-0.527,7.008-2.008,12.195c-4.487,15.808-8.51,24.998-12.202,30.098c-1.85,2.572-3.54,4.19-5.418,5.505   c-1.886,1.308-4.046,2.341-6.878,3.353l-4.451,1.589l-0.823,4.66c-5.209,29.166-20.236,45.565-31.333,57.926l-2.088,2.319v3.128   c0,14.788,0,18.17,0,26.81c0,1.236,0.13,2.435,0.332,3.605L256,380.088l-48.939-53.895c0.202-1.156,0.354-2.341,0.354-3.576   c0-8.64,0-12.014,0-26.81v-3.128l-2.088-2.319C194.231,277.999,179.204,261.599,173.995,232.434z M175.823,355.474   c5.303-2.398,10.663-5.209,15.504-8.575c4.111-2.876,7.875-6.105,10.866-10.021c0.693-0.91,1.336-1.886,1.936-2.897l46.128,50.802   c-11.66,6.849-22.15,9.602-31.282,9.602c-13.401-0.007-24.151-5.823-31.723-13.972   C180.316,372.965,176.423,363.587,175.823,355.474z M247.324,404.933l-0.94-3.829c0.044-0.021,0.065-0.021,0.109-0.065   C246.493,401.039,246.934,403.264,247.324,404.933z M244.767,402.007c-7.883,15.172-15.83,18.748-16.856,19.196   c-1.568,0.672-4.06,0.138-4.688-0.232c-2.695-1.568-1.763-3.287-1.568-4.79C221.742,415.532,226.127,403.517,244.767,402.007z    M255.162,494.892c-32.792-34.215-55.722-86.181-61.444-99.907c7.166,4.19,15.677,6.806,25.256,6.798   c1.322,0,2.674-0.065,4.032-0.166c-13.64,8.352-13.12,19.926-8.294,24.954c10.395,10.807,25.018,0.376,33.868-16.574   c-4.876,11.863-14.174,43.434-14.174,43.434s4.927,2.233,9.847,2.233c2.233-21.002,12.044-50.001,12.087-50.116   c0.036,0.116,9.854,29.114,12.086,50.116c4.92,0,9.84-2.233,9.84-2.233s-9.298-31.57-14.168-43.434   c8.85,16.949,23.473,27.381,33.862,16.574c4.826-5.014,5.353-16.58-8.236-24.925c1.105,0.072,2.218,0.138,3.302,0.138   c9.168,0.008,17.332-2.413,24.296-6.293C310.162,412.316,287.217,462.39,255.162,494.892z M265.356,404.933   c0.382-1.669,0.831-3.894,0.831-3.894c0.043,0.044,0.058,0.044,0.1,0.065L265.356,404.933z M267.906,402.007   c18.646,1.51,23.024,13.524,23.118,14.174c0.188,1.503,1.127,3.222-1.575,4.79c-0.636,0.369-3.121,0.903-4.689,0.232   C283.728,420.755,275.81,417.179,267.906,402.007z M324.749,380.413c-7.572,8.15-18.322,13.965-31.723,13.972   c-9.132,0-19.622-2.753-31.282-9.602l46.078-50.752c0.528,0.91,1.084,1.799,1.69,2.622c4.335,5.808,10.346,10.107,16.891,13.864   c3.157,1.784,6.459,3.403,9.782,4.906C335.593,363.544,331.692,372.95,324.749,380.413z"/>
                  </g>
                  </svg>
              </div>
              <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">${metrics.females.toLocaleString()}</p>
                <p class="text-sm text-gray-500">Female</p>
              </div>
            </div>
          </div> -->

          <div class="bg-white p-6 px-[20px!important] rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
               <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 " fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                </svg>


              </div>
              <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">${metrics.enrolledStudents.toLocaleString()}</p>
                <p class="text-sm text-gray-500">Total Course Enrollments</p>
              </div>
            </div>
          </div>

           <div class="bg-white p-6 px-[20px!important] rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 "  fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>


              </div>
              <div class="ml-4">
                <p class="text-2xl font-bold text-gray-900">${coursesCount?.toLocaleString()}</p>
                <p class="text-sm text-gray-500">All Available Courses</p>
              </div>
            </div>
          </div>


        
        </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 mb-2">Total enrollments by month in Year of ${this.activityYear}</h3>

            <div class="flex justify-end align-center">
              ${this.activityYears.length > 1 ? html`
                 <search-field
                    label="Year"
                    name="year"
                    placeholder="Select year"
                    .onChange=${(value: string) => {
          this.activityYear = Number(value);
          this._createActivityChart()
        }}
                    .value=${this.activityYear}
                    className="min-w-[120px]"
                    searchPlaceholder=""
                    
                    options=${JSON.stringify(this.activityYears)}
                  ></search-field>
                  `: ""}
            </div>
            <!-- <p class="text-sm text-gray-600 mb-4">
              Learners activity over months in the year of ${this.activityYear}.
            </p> -->
            <div class="chart-container">
              <canvas id="activityChart"></canvas>
            </div>
          </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Activity Chart -->
        

          <!-- age distribution Chart -->
          
           <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 mb-4">Distribution of enrollments by sex</h3>
            <!-- <h4 class="text-sm text-gray-800 mb-3" > Sex distribution for all learners</h4> -->
            <div class="chart-container">
              <canvas id="sexDistributionChartEnrolling"></canvas>
            </div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 mb-4">Distribution of enrollments by age</h3>
            <!-- <h4 class="text-sm text-gray-800 mb-3" > 
              Age distribution for all learners
            </h4> -->
            <div class="chart-container">
              <canvas id="ageDistributionChart"></canvas>
            </div>
          </div>

          <!-- District Chart -->
          <!-- <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student by District</h3>
            <div class="chart-container">
              <canvas id="districtChart"></canvas>
            </div>
          </div> -->

          <!-- District Percentage Chart -->
          <!-- <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student by District (%)</h3>
            <div class="chart-container">
              <canvas id="districtPercentageChart"></canvas>
            </div>
          </div> -->
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Total enrolment by ${location}</h3>
            <!-- <h4 class="text-sm text-gray-600 mb-3">Total enrolment by ${location} in a selected period.</h4> -->
            <div class="chart-container">
              <canvas id="districtChart"></canvas>
            </div>
          </div>
          <!-- District Performance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="flex justify-between h-fit align-center px-6 py-4 border-b border-gray-200">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">${location} Performance </h3>
                <div class="relative w-full mt-3 max-w-md">
                  <input
                    type="text"
                    @input=${(e: Event) => this._filterTable(e, studentByLocationData)}
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
              @click=${() => this._downloadCSV(location, tableData)}
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Learners</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                  <th colspan=${5} class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Distribution</th>
                  
                </tr>
                 <tr>
                  <th colspan=${4} ></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">< 25</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">25-34</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">35-44</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">45+</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
               ${tableData.map(d => {
          return html`
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap capitalize text-sm font-medium text-gray-900 ">${d.location}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.count.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.males.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.females.toLocaleString()}</td>
                    <!-- age distribution -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.ageDistribution[0].count.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.ageDistribution[1].count.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.ageDistribution[2].count.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${d.ageDistribution[3].count.toLocaleString()}</td>
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


