import { LitElement, PropertyValues, html } from 'lit';
import { customElement, property, state } from "lit/decorators.js";
import districtsData from "./scripts/demographic/rwanda-districts-data.json";
import facilitiesData from "./scripts/facilities/rwanda-health-facilities-data.json";
import countriesData from "./countries.json";

import './components/input';
import './components/label'
import './components/form-field';
import './components/search-field';
import './components/button';



type Sector = {
  name: string;
  id: string;
  cells: {
    name: string;
    id: string;
    villages: {
      name: string;
      id: string;
    }[];
  }[];
};

type District = {
  name: string;
  id: string;
  sectors: Sector[];
};
const districts = districtsData as Record<string, District>;

type FacilityType = {
  id: string;
  facilityName: string;
}
const healthFacilities = facilitiesData as Record<string, FacilityType[]>;

type CountryType = {
  name: string;
  flag: string;
  phoneNumberPrefix: string;
}
const countries = countriesData as CountryType[];
@customElement('filter-content')
export class MainPage extends LitElement {
  @property({type:String}) root = "";

  createRenderRoot() {
    return this; // Disable shadow DOM to use Tailwind styles
  }

  // app states
  @state() private serviceCategory: string = '';
  @state() private selectedDistrict: string = '';
  @state() private selectedSector: string = '';
  @state() private selectedCell: string = '';
  @state() private selectedHealth: string = '';
  @state() private selectedCountry: string = '';
  @state() private selectedSex: string = '';
  @state() private startDate: string = '';
  @state() private endDate: string = '';

  @state() private loading:boolean = false;
  @state() private citizenDetails: any = null; 
  @state() private nationalIdLoading: boolean = false;
  @state() private currentStep: number = 1; // Track the current step in the form
  @state() private position: string = '';
  @state() private selectedVillage: String = '';
  @state() private courses: Array<{
    id:string,
    name: string
  }> = []
  @state() private courseCategories: Array<{
    id:string,
    name: string
  }> = []
  @state() private selectCourse: string = '';
  @state() private selectedCourseCategory: string = '';
  @state() private selectedCourses: {[key: string]: {
    id: string;
    name: string;
  }} = {};
  


  // fields error messages state
  @state() private districtError: string = '';
  @state() private sectorError: string = '';
  @state() private cellError: string = '';
  @state() private healthError: string = '';
  @state() private countryError: string = ''; 
  @state() private positionError: string = '';
  @state() private villageError: string = '';


  connectedCallback(): void {
    super.connectedCallback();
    this.getListOfCourses();
    this.getListOfCourseCategories();
  }

  async getListOfCourses(){
    try {
      this.courses = [];
      this.loading = true;
      const response = await fetch("/blocks/realdashboard/get_courses_list.php", {
         method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: this.selectedCourseCategory.length ? new URLSearchParams({
        category: this.selectedCourseCategory
      } as any): null
      });
      const data = await response.json();
      this.courses = data?.availableCourses || [];
      console.log(this.courses.length);
    } catch (error) {
      console.error("Error fetching courses list:", error);
    }
    finally{
      this.loading = false;
    }
  }

    async getListOfCourseCategories(){
    try {
      this.loading = true;
      const response = await fetch("/blocks/realdashboard/get_course_categories.php");
      const data = await response.json();
      this.courseCategories = data?.availableCategories || [];
    } catch (error) {
      console.error("Error fetching courses list:", error);
    }
    finally{
      this.loading = false;
    }
  }



  districtField = () => {
    return html`
        <search-field
          label="District"
          name="district"
          placeholder="Select a district"
          searchPlaceholder="Search district..."
          .onChange=${(value: string) => {
            this.selectedDistrict = value;
            this.selectedSector = ''; // Reset sector selection when district changes
            this.selectedCell = ''; // Reset cell selection when district changes
            this.selectedVillage = ''; // Reset village selection when cell changes

          }}
          .disabled=${!this.serviceCategory.length}
          options=${JSON.stringify(Object.keys(districts).map(district => ({ value: district, label: district })))}
          
          .errorMessage=${this.districtError}
        ></search-field>
    `;
  }

  sectorField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const options: {
      value:string;
      label: string;
    }[] = sectors.map((sector) => ({
      value: sector.name,
      label: sector.name,
    }));
    return html`
     <search-field
      label="Sector"
      name="sector"
      placeholder="Select a sector"
      searchPlaceholder="Search sector..."
      .disabled=${!this.selectedDistrict.length}
      options=${JSON.stringify(options)}
      .onChange=${(value: string) => {
        this.selectedSector = value;
        this.selectedCell = ''; // Reset cell selection when sector changes
        this.selectedVillage = ''; // Reset village selection when cell changes

      }}
      
      .errorMessage=${this.sectorError}
    ></search-field>
    `
  }

 

  cellField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const selectedSector = sectors.find(sector => sector.name === this.selectedSector);
    const options: {
      value:string;
      label: string;
    }[] = selectedSector ? selectedSector.cells.map(cell => ({
      value: cell.name.toLocaleLowerCase(),
      label: cell.name,
    })) : [];
    
    return html`
     <search-field
      label="Cell"
      name="cell"
      placeholder="Select a cell"
      searchPlaceholder="Search cell..."
      .disabled=${!this.selectedSector.length}
      options=${JSON.stringify(options)}
      .onChange=${(value: string) => {
        this.selectedCell = value;
        this.selectedVillage = ''; // Reset village selection when cell changes
      }}
      
      .errorMessage=${this.cellError}
    ></search-field>
    `
  }

  villageField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const currentSector: Sector | undefined = sectors.find(sector => sector.name.toLowerCase() === this.selectedSector.toLowerCase() ) ;
    const currentCell: Sector['cells'][0] | undefined = currentSector?.cells.find(cell => cell.name.toLowerCase() === this.selectedCell.toLowerCase());
    const options: {
      value:string;
      label: string;
    }[] = currentCell ? currentCell.villages.map(village => ({
      value: village.name.toLowerCase(),
      label: village.name,
    })) : [];
    
    return html`
     <search-field
      label="Village"
      name="village"
      placeholder="Select a village"
      searchPlaceholder="Search village..."
      .disabled=${!this.selectedCell.length}
      options=${JSON.stringify(options)}
      .onChange=${(value: string) => this.selectedVillage = value}
      
      .errorMessage=${this.villageError}
    ></search-field>
    `
  }

  facilitiesField = () => {
    const facilitySectorKey = `${this.selectedDistrict.toLocaleLowerCase()}_${this.selectedSector.toLocaleLowerCase()}`;

    const facilities = healthFacilities[facilitySectorKey] || [];
    const options: {
      value: string;
      label: string;
    }[] = facilities .map(f => ({
      value: f.facilityName.toLocaleLowerCase(),
      label: f.facilityName,
    })) ;
    return html`
      <search-field
        label="Health Facility"
        name="health"
        placeholder="Select a health facility"
        searchPlaceholder="Search facility..."
        .disabled=${!this.selectedSector?.length}
        options=${JSON.stringify(options)}
        .onChange=${(value: string) => {
          this.selectedHealth = value;
        }}
        
        .errorMessage=${this.healthError}
      ></search-field>
    `;
  }

  countriesField = () => {
    const options: {
      value: string;
      label: string;
    }[] = countries.map(country => {
      return {
        value: country.name.toLocaleLowerCase(),
        label: `${country.flag} ${country.name}`,
      };
    });

    return html`
      <search-field
        label="Country"
        name="country"
        placeholder="Select a country"
        searchPlaceholder="Search countr..."
        options=${JSON.stringify(options)}
        .onChange=${(value: string) => {
          this.selectedCountry = value;
        }}
        
        .errorMessage=${this.countryError}
      ></search-field>
    `;
  }

  positionField = () => {
    let postionsList: {label: string, value: string}[] = [];
    switch (this.serviceCategory) {
      case 'district':
        postionsList = this.districtPostions;
        break;
      case 'sector':
        postionsList = this.sectorPostions;
        break;
      case 'cell':
        postionsList = this.cellPostions;
        break;
      case 'health':
        postionsList = this.healthPostions;
        break;
      case 'embassy':
        postionsList = this.embassyPostions;
        break;
      case 'village':
        postionsList = this.villagePostions;
        break;
    
      default:
        break;
    }
    return html`
     <search-field
      label="Position"
      name="position"
      placeholder="Select a position"
      searchPlaceholder="Search position..."
      options=${JSON.stringify(postionsList)}
      .onChange=${(value: string) => {
        this.position = value;
      }}
      
      .errorMessage=${this.positionError}
    ></search-field>
    `
  }

    private _applyFilters(): void {
    this.dispatchEvent(
      new CustomEvent("on-filter-change", {
        detail: {
          servicepointcategory: this.serviceCategory,
          district: this.selectedDistrict,
          sector: this.selectedSector,
          cell: this.selectedCell,
          village: this.selectedVillage,
          healthfacility: this.selectedHealth,
          servicecountry: this.selectedCountry,
          position: this.position,
          startdate: this.startDate,
          enddate: this.endDate,
          sex: this.selectedSex,
          courseid: this.selectCourse,
          selectedCourses: Object.keys(this.selectedCourses).length ? Object.values(this.selectedCourses).map(c => c.id) : [],
          courseCategory: this.selectedCourseCategory
        },
        bubbles: true,
      }),
    )
  }

  private _resetFilters(): void {
   // reset all fields
  this.serviceCategory = '';
  this.selectedDistrict = '';
  this.selectedSector = '';
  this.selectedCell = '';
  this.selectedHealth = '';
  this.selectedCountry = '';
  this.selectedSex = '';
  this.startDate = '';
  this.endDate = '';
  this.position = '';
  this.selectedVillage = '';

  this.districtError = '';
  this.sectorError = '';
  this.cellError = '';
  this.healthError = '';
  this.countryError = '';
  this.positionError = '';
  this.villageError = '';
  this.selectCourse = '';
  this.selectedCourseCategory = '';
  this.selectedCourses = {};
  this.getListOfCourses(); // refresh courses list
  
  // re-apply filters after reset
  this._applyFilters();
  }
  
 

 
  userDetailsComponent = () => {
    return html`
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
         <div class="mb-3">
          ${
            Object.keys(this.selectedCourses).length ? html`<label class="block text-sm font-medium text-gray-700 mb-1">Selected Courses</label>` : ""
          }
          <div class="flex flex-wrap gap-4">
           ${
            this.selectedCourses && Object.keys(this.selectedCourses).length ? html`
            ${
              Object.values(this.selectedCourses).map(course => html`
                <span class="inline-flex items-center pl-3 pr-1 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                  ${course.name}
                  <button
                    @click=${() => {
                      const updatedCourses = { ...this.selectedCourses };
                      delete updatedCourses[course.id];
                      this.selectedCourses = updatedCourses;
                      if(this.selectCourse === course.id){
                        this.selectCourse = '';
                      }
                    }}
                    class="ml-2 bg-blue-200 w-[40px] text-blue-800 rounded-full p-1 hover:bg-blue-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    <span class="sr-only">Remove ${course.name}</span>
                    &times;
                  </button>
                </span>
                `)
            }
            `: ""
          }
          </div>
         </div>
           <div class="mb-3 grid gap-4 grid-cols-1 md:grid-cols-2 ">
            <search-field
              label="Course Category"
              name="courseCategory"
              placeholder="Select a course category"
              .onChange=${(value: string)=>{
                this.selectedCourseCategory = value
                this.getListOfCourses();
              }}
              .value=${this.selectedCourseCategory}
              className="min-w-[120px]"
              searchPlaceholder=""
              options=${JSON.stringify(this.courseCategories.map(course => ({ value: course.id, label: course.name })))}
            ></search-field>
             <search-field
              label="Course"
              name="courses"
              placeholder="Select a course"
              .onChange=${(value: string)=>{
                this.selectCourse = value;
                const currentCourse = this.courses.find(course => course.id === value);
                if(currentCourse){
                  this.selectedCourses[currentCourse.id] = currentCourse;
                }
              }}
              .value=${this.selectCourse}
              className="min-w-[120px]"
              searchPlaceholder=""
              options=${JSON.stringify(this.courses.map(course => ({ value: course.id, label: course.name })))}
            ></search-field>
           </div>
          <div class="flex flex-wrap items-end gap-4">

        <div class="flex flex-wrap  gap-6  " >
         
         <form-field label="Start date" type="date"  name="startDate" id="startDate" 
              .value=${this.startDate} .onChange=${(value: string) => this.startDate = value} size="large" variant="primary"
              ></form-field>
          <form-field label="End date" type="date"  name="endDate" id="endDate" 
          .value=${this.endDate} .onChange=${(value: string) => this.endDate = value} size="large" variant="primary"
          ></form-field>
           
        <search-field
              label="Sex"
              name="sex"
              placeholder="Select sex"
              .onChange=${(value: string)=>{
                this.selectedSex = value
              }}
              .value=${this.selectedSex}
              className="min-w-[120px]"
              searchPlaceholder=""
              
              options='[
                {"value": "m", "label": "Male"},
                {"value": "f", "label": "Female"}
              ]'
            ></search-field>
          
            <search-field
              label="Workplace (Urwego)"
              name="serviceCategory"
              placeholder="Select workplace"
              .onChange=${(value: string)=>{
                this.serviceCategory = value;
                this.selectedDistrict = ''; // Reset district selection when service category changes
                this.selectedSector = ''; // Reset sector selection when service category changes
                this.selectedCell = ''; // Reset cell selection when service category changes
                this.position = ''; // Reset position when country changes

              }}
              .value=${this.serviceCategory}
              searchPlaceholder="Search workplace..."
              
              options='[
                {"value": "district", "label": "District Level"},
                {"value": "sector", "label": "Sector Level"},
                {"value": "cell", "label": "Cell Level"},
                {"value": "village", "label": "Village Level"},
                {"value": "health", "label": "Health Facility"},
                {"value": "embassy", "label": "Embassy"},
                {"value": "other", "label": "other"}
              ]'
            ></search-field>
        ${this.serviceCategory === "district" ? html`
            <div class="gap-6 ">
              ${this.districtField()}
            </div>
          `: ""
        }
        ${this.serviceCategory === "sector" ? html`
            <div class="flex gap-6  ">
              ${this.districtField()}
              ${this.sectorField()}
            </div>
          `: ""
        }
        ${this.serviceCategory === "cell" ? html`
          <div class="gap-6  ">
            ${this.districtField()}
          </div>
          <div class="flex gap-6 ">
            ${this.sectorField()}
            ${this.cellField()}
          </div>
        `: ""}
         ${this.serviceCategory === "village" ? html`
          <div class="flex gap-6  ">
            ${this.districtField()}
            ${this.sectorField()}

          </div>
          <div class="flex gap-6  ">
            ${this.cellField()}
            ${this.villageField()}
          </div>
        `: ""}
        ${this.serviceCategory === "health" ? html`
          <div class="flex gap-6  ">
            ${this.districtField()}
            ${this.sectorField()}
            
          </div>
          <div class="gap-6  ">
            ${this.facilitiesField()}
          </div>
          `:""}
        ${this.serviceCategory === "embassy" ? html`
          <div class="gap-6  ">
            ${this.countriesField()}
          </div>
          `:""}
       
       ${this.serviceCategory.length ? html`
       <div class=" ">
            ${this.positionField()}
          </div>
       `: ""}
       
        </div>
        
          </div>
          <div class="flex gap-2 mt-4">
              <button
                .disabled=${this.loading}
                @click=${this._applyFilters}
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ${this.loading ? 'Loading...' : 'Apply Filters'}
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
    `;
  }


  render() {
    
    return this.userDetailsComponent()
  }






 
 

  sectorPostions = [
    { label: "Sector Executive Secretary", value: "sector executive secretary" },
    { label: "Civil Registration Officer", value: "cro" },
    { label: "Data Manager", value: "data manager" },
    { label: "Other", value: "other" }, 
  ];
  districtPostions = [
    { label: "Mayor / DEA", value: "mayor" },
    { label: "Statistician", value: "statistician" },
    { label: "Director of Good Governance", value: "director of good governance" },
    { label: "Territorial Administration Officer", value: "territorial administration officer" },
    { label: "Director of Health", value: "director of health" }, 
    { label: "IT Officer", value: "it" }, 
    { label: "Data Manager", value: "data manager" }, 
    { label: "Other", value: "other" } 
  ];

  cellPostions = [
    { label: "Cell Executive Secretary", value: "executive secretary" },
    { label: "SEDO", value: "sedo" },
    { label: "Other", value: "other" }, 
  ];
  healthPostions = [
    { label: "Director Of Nursing And Midwives", value: "director of nursing and midwives" },
    { label: "Head of Health Center", value: "head of health center" },
    { label: "Data Manager", value: "data manager" }, 
    { label: "Civil Registrar at Private Health Facility", value: "civil registrar at private health facility" }, 
    { label: "Data Manager at Private Health Facility", value: "data manager at private health facility" }, 
    { label: "Other", value: "other" }, 
  ];
  embassyPostions = [
    { label: "Ambassador", value: "ambassador" },
    { label: "First Secretary", value: "first secretary" },
    { label: "Other", value: "other" }, 
  ]
  villagePostions = [
    { label: "Parasocial Worker", value: "parasocial" },
    { label: "Other", value: "other" }, 
  ];
}
