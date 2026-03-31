import { LitElement, PropertyValues, html } from 'lit';
import { customElement, property, state } from "lit/decorators.js";
import districtsData from "./scripts/demographic/rwanda-districts-data.json";
import facilitiesData from "./scripts/facilities/rwanda-health-facilities-data.json";
import countriesData from "../src/countries.json";

import './components/input.ts';
import './components/label'
import './components/form-field.ts';
import './components/search-field.ts';
import './components/button.ts';



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
@customElement('main-page')
export class MainPage extends LitElement {
  @property({ type: String }) root = "";

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
  @state() private otherSpecified: string = '';
  @state() private names: string = "";
  @state() private firstName: string = "";
  @state() private lastName: string = "";
  @state() private birthDate: string = "";
  @state() private age: string = "";
  @state() private gender: string = "";
  @state() private phoneNumber: string = "";
  @state() private email: string = "";
  @state() private nationalId: string = "";
  @state() private loading: boolean = false;
  @state() private citizenDetails: any = null;
  @state() private nationalIdLoading: boolean = false;
  @state() private currentStep: number = 1; // Track the current step in the form
  @state() private position: string = '';
  @state() private selectedVillage: String = '';



  // fields error messages state
  @state() private namesError: string = '';
  @state() private birthDateError: string = '';
  @state() private ageError: string = '';
  @state() private genderError: string = '';
  @state() private phoneNumberError: string = '';
  @state() private emailError: string = '';
  @state() private serviceCategoryError: string = '';
  @state() private districtError: string = '';
  @state() private sectorError: string = '';
  @state() private cellError: string = '';
  @state() private healthError: string = '';
  @state() private countryError: string = '';
  @state() private otherSpecifiedError: string = '';
  @state() private nationalIdError: string = '';
  @state() private positionError: string = '';
  @state() private villageError: string = '';


  connectedCallback(): void {
    super.connectedCallback();
    console.log("this is root", this.root);
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
          required
          .errorMessage=${this.districtError}
        ></search-field>
    `;
  }

  sectorField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const options: {
      value: string;
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
      required
      .errorMessage=${this.sectorError}
    ></search-field>
    `
  }



  cellField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const selectedSector = sectors.find(sector => sector.name === this.selectedSector);
    const options: {
      value: string;
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
      required
      .errorMessage=${this.cellError}
    ></search-field>
    `
  }

  villageField = () => {
    const sectors: Sector[] = this.selectedDistrict ? districts[this.selectedDistrict]?.sectors || [] : [];
    const currentSector: Sector | undefined = sectors.find(sector => sector.name.toLowerCase() === this.selectedSector.toLowerCase());
    const currentCell: Sector['cells'][0] | undefined = currentSector?.cells.find(cell => cell.name.toLowerCase() === this.selectedCell.toLowerCase());
    const options: {
      value: string;
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
      required
      .errorMessage=${this.villageError}
    ></search-field>
    `
  }

  facilitiesField = () => {
    const facilitySectorKey = `${this.selectedDistrict.toLocaleLowerCase()}_${this.selectedSector.toLocaleLowerCase()}`;
    const actualKeys = Object.keys(healthFacilities).filter(key => {
      const [name, label] = key.split(' ');
      return facilitySectorKey === name.toLocaleLowerCase();
    });

    const options: {
      value: string;
      label: string;
    }[] = [];
    actualKeys.forEach(key => {
      const facilities = healthFacilities[key] || [];
      const values = facilities.map(f => ({
        value: f.facilityName.toLocaleLowerCase(),
        label: f.facilityName,
      }));
      options.push(...values);
    });
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
        required
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
        required
        .errorMessage=${this.countryError}
      ></search-field>
    `;
  }

  positionField = () => {
    let postionsList: { label: string, value: string }[] = [];
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
      .disabled=${!this.selectedDistrict.length}
      options=${JSON.stringify(postionsList)}
      .onChange=${(value: string) => {
        this.position = value;
      }}
      required
      .errorMessage=${this.positionError}
    ></search-field>
    `
  }

  onSubmit = async (e: Event) => {
    e.preventDefault();
    this.resetErrors();
    const valid = this.errorChecking();
    console.log("is valid", valid, "this.selectedVillage", this.selectedVillage);
    if (!valid) {
      return;
    }
    const nationalId = this.nationalId.replace(/\s+/g, '');
    const data = {
      names: this.names,
      nationalid: nationalId,
      dateofbirth: this.formatDate(this.citizenDetails.dateOfBirth).getTime(), // Convert to Unix timestamp
      gender: this.gender,
      servicepointcategory: this.serviceCategory,
      district: this.selectedDistrict.toLowerCase(),
      sector: this.selectedSector.toLowerCase(),
      cell: this.selectedCell.toLowerCase(),
      village: this.selectedVillage.toLowerCase(),
      healthfacility: this.selectedHealth.toLowerCase(),
      servicecountry: this.selectedCountry.toLowerCase(),
      otherspecified: this.otherSpecified.toLowerCase(),
      phonenumber: this.phoneNumber,
      email: this.email,
      firstname: this.firstName,
      lastname: this.lastName,
      position: this.position.toLowerCase()
    };
    try {
      this.loading = true;
      const response = await fetch(`${this.root}/local/additionaluserdetails/saveuserinfo.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data as any)
      });

      if (!response.ok) throw new Error('Network error');

      const result = await response.json();
      if (result.status === "success") {
        window.location.href = `${this.root}/?redirect=0`;
      }
      else alert(result.message || 'Something went wrong, please review your information and try again.');

    } catch (err) {
      this.loading = false;
      console.log(err)
      alert('Something went wrong, please review your information and try again.');
    }
    finally {
      this.loading = false;
    }

  }

  formatDate = (input: string): Date => {
    const [day, month, year] = input.split("/");
    const isoDate = `${year}-${month}-${day}`;
    const date = new Date(isoDate);
    return date;
  }

  getCitizenDetails = async (e: Event) => {
    e.preventDefault();
    this.resetErrors();
    const value = this.nationalId.replace(/\s+/g, '');
    this.nationalIdError = ''; // Reset error message
    if (value.length !== 16) {
      this.nationalIdError = "National ID must be 16 characters long.";
      return;
    }
    try {
      this.citizenDetails = null; // Reset citizen details
      this.nationalIdLoading = true;
      const response = await fetch(`${this.root}/local/additionaluserdetails/getcitizeninfo.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ nationalId: value })
      });
      if (!response.ok) throw new Error('Network error');
      const result = await response.json();
      if (result.status === 'success') {
        const data = result.data as any;
        this.names = `${data.foreName} ${data.surnames}`;
        this.firstName = data.foreName;
        this.lastName = data.surnames;
        this.birthDate = data.dateOfBirth; // Format to YYYY-MM-DD
        this.gender = data.sex;
        this.citizenDetails = data;
      }

    }
    catch (error) {
      this.nationalIdError = "Failed to fetch citizen details, please try again.";
    }
    finally {
      this.nationalIdLoading = false;
    }
  }

  infoComponent = (label: string, value: string) => {
    return html`
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-600">${label}</p>
        <p class="text-lg font-mono font-semibold">${value}</p>
      </div>
    `;
  }

  performLogout = () => {
    // Redirect to the logout page
    window.location.href = `${this.root}/login/logout.php`;
  }

  render() {

    return html`
     <div class="flex w-screen h-screen justify-center mb-[200px!important]  ">
        <div class="w-full max-w-[1200px] p-6   mb-[30px!important]">
           <div class="flex justify-between ">
              <div >
                <h1 class=" text-xl md:text-3xl font-bold text-start ">Complete Your Profile</h1>
                <h4 class="text-sm text-black-200 mt-4" >Help us get to know you better by providing some additional information</h4>
              </div>
              <button @click=${this.performLogout} 
                class="bg-blue-500 h-fit text-white text-sm px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                Logout
              </button>
           </div>
         <div class="w-full mt-10 min-h-[300px] display grid  rounded-lg border md:p-4 bg-gray-50">
              ${this.currentStep === 1 ? this.personalDetailsComponent() : ""

      }
            ${this.currentStep === 2 ? this.userDetailsComponent() : ""}

        </div>
     </div>
      
    
    `;
  }

  personalDetailsComponent = () => {
    return html`
    <div  class="p-4 mt-4  space-y-4">

    <div class="mb-[30px!important]">
      <h3 class="text-xl font-semiBold " >Personal informations</h3>
      <p class="text-[12px] mt-1">Provide you national to continue</p>
    </div>
      <form  @submit=${this.getCitizenDetails} class="flex items-center  gap-2 md:px-6  ">
        <form-field  label="National ID" class="w-15/16" type="text" placeholder="Enter you National ID" name="nationalId" id="nationalId" 
        .value=${this.nationalId} .onChange=${(value: string) => this.nationalId = value}  size="large" variant="primary"
        .errorMessage=${this.nationalIdError}></form-field>
        
        ${this.nationalIdLoading ? html`<ui-button class="mt-6" loading loadingText="Loading..."></ui-button>`
        : html`
        <button  type="submit" class="bg-blue-500 text-white px-4 mt-6 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
          Get
        </button>`}
      </form>

          ${this.citizenDetails ? html`
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
              <div class="flex flex-col gap-4">
                ${this.infoComponent("Names", this.citizenDetails.foreName + " " + this.citizenDetails.surnames)}
                ${this.infoComponent("National ID", this.nationalId)}
                ${this.infoComponent("Birth Date", this.citizenDetails.dateOfBirth)}
                ${this.infoComponent("Sex", this.citizenDetails.sex === "M" ? "Male" : "Female")}
                
              </div>
              <!-- <div class="flex flex-col gap-4">
                ${this.infoComponent("Marital Status", this.citizenDetails.maritalStatus || "N/A")}
                ${this.infoComponent("Place Of Birth", this.citizenDetails.placeOfBirth || "N/A")}
                ${this.infoComponent("Father's Names", this.citizenDetails.fatherNames || "N/A")}
                ${this.infoComponent("Mother's Names", this.citizenDetails.motherNames || "N/A")}
            </div> -->
            </div>
            <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
              <form-field label="Phone Number" type="tel" placeholder="Enter your phone number" name="phoneNumber" id="phoneNumber" 
              .value=${this.phoneNumber} .onChange=${(value: string) => this.phoneNumber = value} size="large" variant="primary"
              .errorMessage=${this.phoneNumberError} required></form-field>
              
            </div>
            `: ""}
            
             ${this.citizenDetails
        ? html`
              <div class="flex justify-end mt-[15px!important] md:px-6 ">
              <button @click="${() => {
            this.resetErrors();
            if (this.firstStepValidation()) {
              this.currentStep++;
            }
          }}" class="bg-black text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                Next
              </button>
              </div>
              `: ""}
      </div>
      </div>
    `;
  }

  userDetailsComponent = () => {
    return html`
        <form class="space-y-4 gap-6 mt-[30px!important] px-6 " @submit=${this.onSubmit}>
        
        <!-- <div class="grid grid-cols-2 gap-6 mt-[30px!important] px-6 "> -->
          <!-- <form-field label="Birth Date" type="date" placeholder="Enter your birth date" name="birthDate" id="birthDate"
          .value=${this.birthDate} .onChange=${(value: string) => this.birthDate = value} size="large" variant="primary"
          .errorMessage=${this.birthDateError}></form-field>
            <search-field
              label="Gender"
              name="gender"
              placeholder="Choose your gender"
              searchPlaceholder=""
              options='[
                {"value": "male", "label": "Male"},
                {"value": "female", "label": "Female"}
              ]'
              .onChange=${(value: string) => this.gender = value}
              .value=${this.gender}
              .errorMessage=${this.genderError}
            ></search-field>
        </div>
        <div class="grid grid-cols-2 gap-6 mt-[30px!important] px-6 ">
          <form-field label="Phone Number" type="tel" placeholder="Enter your phone number" name="phoneNumber" id="phoneNumber" 
          .value=${this.phoneNumber} .onChange=${(value: string) => this.phoneNumber = value} size="large" variant="primary"
          .errorMessage=${this.phoneNumberError}></form-field>
          <form-field label="Email" type="email" placeholder="Enter your email" name="email" id="email" size="large" 
          .value=${this.email} .onChange=${(value: string) => this.email = value}  variant="primary"
          .errorMessage=${this.emailError}></form-field>
        </div> -->
          <div>
            <h3 class="text-xl font-semiBold ">Service information</h3>
            <p class="text-[12px] mt-1">Provide your service information to continue</p>
          </div>
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            <search-field
              label="Workplace (Urwego ukorera)"
              name="serviceCategory"
              placeholder="Select workplace"
              .onChange=${(value: string) => {
        this.serviceCategory = value;
        this.selectedDistrict = ''; // Reset district selection when service category changes
        this.selectedSector = ''; // Reset sector selection when service category changes
        this.selectedCell = ''; // Reset cell selection when service category changes
        this.position = ''; // Reset position when country changes

      }}
              .value=${this.serviceCategory}
              .errorMessage=${this.serviceCategoryError}
              searchPlaceholder="Search workplace..."
              required
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
        </div>
        ${this.serviceCategory === "district" ? html`
            <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
              ${this.districtField()}
            </div>
          `: ""
      }
        ${this.serviceCategory === "sector" ? html`
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
              ${this.districtField()}
              ${this.sectorField()}
            </div>
          `: ""
      }
        ${this.serviceCategory === "cell" ? html`
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            ${this.districtField()}
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
            ${this.sectorField()}
            ${this.cellField()}
          </div>
        `: ""}
         ${this.serviceCategory === "village" ? html`
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
            ${this.districtField()}
            ${this.sectorField()}

          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
            ${this.cellField()}
            ${this.villageField()}
          </div>
        `: ""}
        ${this.serviceCategory === "health" ? html`
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-[30px!important] md:px-6 ">
            ${this.districtField()}
            ${this.sectorField()}
            
          </div>
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            ${this.facilitiesField()}
          </div>
          `: ""}
        ${this.serviceCategory === "embassy" ? html`
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            ${this.countriesField()}
          </div>
          `: ""}
        ${this.serviceCategory === "other" ? html`
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            <form-field label="Other service category" type="text" 
            placeholder="Enter your service category" 
            name="otherServiceCategory" id="otherServiceCategory"
            .value=${this.otherSpecified}
            .onChange=${(value: string) => this.otherSpecified = value}
            required .errorMessage=${this.otherSpecifiedError}
              size="large" variant="primary"></form-field>
          </div>
        `: ""}
        ${this.serviceCategory !== "other"
        ? html`
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            ${this.positionField()}
          </div>
          `: html`
          <div class="grid grid-cols-1 gap-6 mt-[30px!important] md:px-6 ">
            <form-field label="Position" type="text" 
            placeholder="Enter your position" 
            name="otherValueForPosition" id="otherValueForPosition"
            .value=${this.position}
            .onChange=${(value: string) => this.position = value}
            required .errorMessage=${this.otherSpecifiedError}
              size="large" variant="primary"></form-field>
          </div>
          `
      }
        <div class="flex  mb-[30px!important]   gap-4 justify-between mt-[40px!important] md:mx-6">
         
          ${!this.loading
        ? html`
             <button @click="${() => this.currentStep--}" class="bg-black text-white px-6  py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                Previous
            </button>
              <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                Done
              </button>
            `
        : html`
              <div></div>
              <div>
                <ui-button loading class="w-full md:w-fit" loadingText="Saving..."></ui-button>
              </div>
            `
      }
        </div>
        </form>
    `;
  }

  firstStepValidation = (): boolean => {
    let valid = true;
    if (!this.nationalId.trim()) {

      this.nationalIdError = "Names are required.";
      valid = false;

    }
    if (this.nationalId.trim().length && this.nationalId.trim().length < 16) {
      this.nationalIdError = "National ID must be 16 characters long.";
      valid = false;
    }


    if (!this.phoneNumber.trim()) {
      this.phoneNumberError = "Phone number is required.";
      valid = false;
    }

    return valid;
  }

  errorChecking(): boolean {
    let valid = true;
    valid = this.firstStepValidation();
    valid = this.secondStepValidation();
    return valid;
  }

  secondStepValidation = (): boolean => {
    let valid = true;
    // Service info checks
    if (!this.serviceCategory.trim()) {
      this.serviceCategoryError = "Service category is required.";
      valid = false;
    } else {
      switch (this.serviceCategory) {
        case "district":
          if (!this.selectedDistrict.trim()) {
            this.districtError = "District is required.";
            valid = false;
          }
          break;
        case "sector":
          if (!this.selectedDistrict.trim()) {
            this.districtError = "District is required.";
            valid = false;
          }
          if (!this.selectedSector.trim()) {
            this.sectorError = "Sector is required.";
            valid = false;
          }
          break;
        case "cell":
          if (!this.selectedDistrict.trim()) {
            this.districtError = "District is required.";
            valid = false;
          }
          if (!this.selectedSector.trim()) {
            this.sectorError = "Sector is required.";
            valid = false;
          }
          if (!this.selectedCell.trim()) {
            this.cellError = "Cell is required.";
            valid = false;
          }
          break;
        case "health":
          if (!this.selectedDistrict.trim()) {
            this.districtError = "District is required.";
            valid = false;
          }
          if (!this.selectedSector.trim()) {
            this.sectorError = "Sector is required.";
            valid = false;
          }
          if (!this.selectedHealth.trim()) {
            this.healthError = "Health facility is required.";
            valid = false;
          }
          break;
        case "embassy":
          if (!this.selectedCountry.trim()) {
            this.countryError = "Country is required.";
            valid = false;
          }
          break;
        case "village":
          if (!this.selectedVillage.trim()) {
            this.villageError = "Village is required.";
            valid = false;
          }
          break;
        case "other":
          if (!this.otherSpecified.trim()) {
            this.otherSpecifiedError = "Other service category is required.";
            valid = false;
          }
          break;


      }
      if (!this.position.trim()) {
        this.positionError = "Position is required.";
        valid = false;
      }
    }
    return valid;
  }

  resetErrors() {
    this.nationalIdError = '';
    this.namesError = '';
    this.birthDateError = '';
    this.ageError = '';
    this.genderError = '';
    this.phoneNumberError = '';
    this.emailError = '';
    this.serviceCategoryError = '';
    this.districtError = '';
    this.sectorError = '';
    this.cellError = '';
    this.healthError = '';
    this.countryError = '';
    this.otherSpecifiedError = '';
    this.positionError = '';
    this.villageError = '';
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
