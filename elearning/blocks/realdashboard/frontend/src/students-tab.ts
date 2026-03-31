import { LitElement, html, css } from "lit"
import { property, customElement, state } from "lit/decorators.js"
import type { StudentDataEntity } from "./types"
import { downloadCsv, toTitleCase } from "./helpers";
 

type StudentCourseType = {
        id: string,
        coursename: string,
        enrollmenttime: string,
        completiontime: string | null,
        status: string,
        score: number | null
    }
@customElement("students-tab")
export class StudentsTab extends LitElement {
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
    @property({ type: String })
    activeTab = "inprogress"
  @property({ type: Object })
  data: any = null;
  @state() private performanceYear:string = new Date().getFullYear().toString();
  private charts: Map<string, any> = new Map()
  @state() private current: "dataTable" | "studentDetails" = "dataTable";
  @state() private loading: boolean = false;
  @state() private studentDetails: StudentDataEntity | null = null;
  @state() private studentCourses: {
    enrolled: StudentCourseType[],
    inprogress: StudentCourseType[],
    completed: StudentCourseType[],
    failed: StudentCourseType[]
  } = {enrolled: [], inprogress: [], completed: [], failed: []};
  @state() private filteredTableData: StudentDataEntity[] = [];
  



   connectedCallback(): void {
    super.connectedCallback()
    document.addEventListener("data-loaded", (event: Event) => {
      const customEvent = event as CustomEvent
      this.data = customEvent.detail
    })
    document.addEventListener('on-filter-change', (e: Event) => {
        const customEvent = e as CustomEvent<any>;
        const sentData = customEvent.detail;
        this.filters = sentData;
      });
    this.filteredTableData = (this.data?.studentsData || []) as StudentDataEntity[];
  }


   _downloadCSV(location: string, tableData: StudentDataEntity[]) {
  
      const headers = ["", "Names", "Sex" , "Phone number", "email","Enrollment", "Inprogress courses", "Completed courses"
        ,"National ID", "Date of Birth", "Workplace (Urwego akorera)", "District", "Sector", "Cell", "Village", "Health facility", "Position", "Service Country"
      ];
    const data = tableData.map((d, i)=>{
      const category = d.servicepointcategory ?? "-";
      const position  = d.position == "sedo"? "SEDO": d.position == "cro"? "CRO": toTitleCase( d.position ?? "-");
      return [
        i+1,
        d.names,
        d.sex == "M" ? "Male": "Female" ,
        `250${d.phoneNumber}`,
        d.email ?? "-",
        d.enrollments ?? 0, 
        d.inprogress ?? 0,
        d.completed ?? 0,
        d.nationalid ?? "-",  
        d.dateofbirth ? new Date(Number(d.dateofbirth)).toLocaleDateString() : "-",
          category == "health"? "Health facility":  toTitleCase(category)
        ,
        toTitleCase(d.district ?? "-"),
        toTitleCase(d.sector ?? "-"),
        toTitleCase(d.cell ?? "-"),
        toTitleCase(d.village ?? "-"),
        toTitleCase(d.healthfacility ?? "-"),
        position,
        d.servicecountry ?? "-"
      ];
    })
  
    downloadCsv(headers, data, `${location} learners analysis.csv`);
    }

  _filterTable(e: Event, primitiveDataTable: StudentDataEntity[] = []) {
    const input = e.target as HTMLInputElement;
    const searchTerm = input.value.toLowerCase();
    const tableData = primitiveDataTable;
    if(searchTerm.length){
      this.filteredTableData = tableData.filter(d=>JSON.stringify(d).trim().replace(" ", "").toLowerCase().includes(searchTerm.trim().replace(" ", "")));
    }
    else{
      this.filteredTableData = primitiveDataTable;
    }
    this.requestUpdate();
  }
  render() {
    const {district, sector, cell, village} = (this.filters ?? {}) as any;
    const location = village?.length ? "Villages": cell?.length ? "Cells": sector?.length ? "Sectors": district?.length ? "Districts": "Districts";
 
    const studentsData = (this.data?.studentsData || []) as StudentDataEntity[];
    const locationPerformances = this.filteredTableData;

    if(this.current == "studentDetails")return this.studentDetailsRender(); 

    return html`
      <div class="space-y-6">
       
        <!-- District Performance Table -->
        <div class="bg-white rounded-sm shadow overflow-hidden">
          <div class="flex justify-between align-center px-3 py-4 border-b border-gray-200">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Detailed Learners performance</h3>
              <div class="relative w-full mt-3 max-w-md">
                  <input
                    type="text"
                    @input=${(e: Event) => this._filterTable(e, studentsData)}
                    placeholder="Search..."
                    class="w-[250px] pl-10 pr-4 py-2 pt-[12px!important]  rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 bg-white shadow-sm"
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
              @click=${()=>this._downloadCSV(location, locationPerformances)}
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
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Names</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sex</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone number</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inprogress courses</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed courses</th>

                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">National ID</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workplace (Urwego akorera)</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">District</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cell</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Village</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Health facility</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                  <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Country</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                ${locationPerformances.map((s, i)=>{
                  const category = s.servicepointcategory ?? "-";
                  const position  = s.position == "sedo"? "SEDO": s.position == "cro"? "CRO": toTitleCase(s.position ?? "-");
                  return html`
                  <tr @click=${()=>{
                    this.studentDetails = s;
                    this.current = "studentDetails";
                    this.requestUpdate();
                    this.fetchStudentCourses(s.userid);
                  }} class="hover:bg-gray-100 cursor-pointer">
                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${i+1}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${toTitleCase(s.names)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${s.sex == "M"? "Male": "Female"}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${s.phoneNumber}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${s.email}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${s.enrollments?.toLocaleString() || 0}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${s.inprogress?.toLocaleString() || 0}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-green-600">${s.completed?.toLocaleString() || 0}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${s.nationalid}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(Number(s.dateofbirth)).toLocaleDateString()}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${category == "health"? "Health facility":  toTitleCase(category)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.district)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.sector)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.cell)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.village)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.healthfacility)}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${position}</td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">${toTitleCase(s.servicecountry)}</td>
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

  private async fetchStudentCourses(userid: string){
    try {
        this.loading = true;
        const response = await fetch("/blocks/realdashboard/get_student_coruses.php", {
       method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        userid
      } as any)
    });
    const data = await response.json();
    this.studentCourses = {
        enrolled: data.allCoursesInrolements || [],
        inprogress: data.inprogressCourses || [],
        completed: data.completedCourses || [],
        failed: data.failedCourses || []
    };
    } catch (error) {

    }
    finally {
      this.loading = false
    }
  }

private _handleTabChange(tab: string): void {
    this.activeTab = tab
  }

  studentDetailsRender() {
    const {inprogress, enrolled, failed, completed} = this.studentCourses;
    if(this.studentDetails == null && this.loading == false) return html`
     <div>
        <button
            type="button"
            @click=${()=>{
                this.current = "dataTable";
                this.studentDetails = null;
                this.requestUpdate();
            }}
            class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
            <!-- Left arrow icon -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-5 w-5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span>Back</span>
            </button>
        <h1 class="text-3xl font-bold mb-2">No Learners Selected</h1>
     </div>
    `

    if(this.loading) return html`<div class="flex items-center justify-center h-64">
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
    return html`
       <div>
        <button
          @click=${()=>{
                this.current = "dataTable";
                this.studentDetails = null;
                this.requestUpdate();
            }}
            type="button"
            class="inline-flex mb-10 items-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            >
            <!-- Left arrow icon -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-5 w-5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span>Back</span>
            </button>
        
        <h1 class="text-3xl font-bold mb-2">${this.studentDetails?.names ?? ""} </h1>
        <p class="text-gray-600 mb-4">Detailed information about ${this.studentDetails?.names ?? ""}.</p>
         <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
              ${["enrolled", "inprogress", "completed",
              //  "failed"
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
 ${this.activeTab === "enrolled" ? html`
                <div>
                    <div class="flex justify-between align-center px-3 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Completed courses</h3>
                    
                </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course name</th>
                        <!-- <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolment time</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${enrolled.map((c, i)=>{
                        return html`
                        <tr>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${i+1}</td>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${c.coursename}</td>
                            <!-- <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${new Date(Number(c.enrollmenttime)).toDateString()}</td> -->
                        </tr>
                        `
                        })}
                        
                    </tbody>
                    </table>
                </div>
                </div>
                </div>
                ` : ""}

            ${this.activeTab === "inprogress" ? html`
                <div>
                    <div class="flex justify-between align-center px-3 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Inprogress courses</h3>
                    
                </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course name</th>
                        <!-- <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolment time</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${inprogress.map((c, i)=>{
                        return html`
                        <tr>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${i+1}</td>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${c.coursename}</td>
                            <!-- <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${new Date(Number(c.enrollmenttime)).toDateString()}</td> -->
                        </tr>
                        `
                        })}
                        
                    </tbody>
                    </table>
                </div>
                </div>
                </div>
                ` : ""}



                 ${this.activeTab === "completed" ? html`
                <div>
                    <div class="flex justify-between align-center px-3 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Completed courses</h3>
                    
                </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course name</th>
                        <!-- <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion time</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${completed.map((c, i)=>{
                        return html`
                        <tr>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${i+1}</td>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${c.coursename}</td>
                            <!-- <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${new Date(Number(c.completiontime)).toDateString()}</td> -->
                        </tr>
                        `
                        })}
                        
                    </tbody>
                    </table>
                </div>
                </div>
                </div>
                ` : ""}



                 ${this.activeTab === "failed" ? html`
                <div>
                    <div class="flex justify-between align-center px-3 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Completed courses</h3>
                    
                </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course name</th>
                        <!-- <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion time</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${failed.map((c, i)=>{
                        return html`
                        <tr>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${i+1}</td>
                            <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${c.coursename}</td>
                        </tr>
                        `
                        })}
                        
                    </tbody>
                    </table>
                </div>
                </div>
                </div>
                ` : ""}
       </div>
    `
  }
}
