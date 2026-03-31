export interface FilterState {
  sex: "Male" | "Female" | "All"
  minAge: string
  maxAge: string
  minGrades: string
  maxGrades: string
}

export interface StudentData {
  id: number
  name: string
  district: string
  sector: string
  cell: string
  village: string
  age: number
  gender: "Male" | "Female"
  healthFacility: string
  courseStatus: "completed" | "failed" | "not_started" | "in_progress"
  grades: number
  enrollmentDate: string
}

export interface ChartData {
  labels: string[]
  datasets: {
    label: string
    data: number[]
    backgroundColor?: string | string[]
    borderColor?: string | string[]
    borderWidth?: number
  }[]
}

export interface PerformanceMetrics {
  totalStudents: number
  enrolledStudents: number
  excelledStudents: number
  completedCourses: number
  failedCourses: number
  notStarted: number,
  males: number,
  allCourses: number,
  females: number
}

declare global {
  interface Window {
    Chart: any
  }
}

export interface PerformanceLearningMetrics {
totalCourses: number,
completedCourses: number,
failedCourses: number,
averageCompletionRate: number, // in percentage
averageScore: number, // in percentage
inProgressCourses: number,
notStartedCourses: number,
}


export interface LocationPerformanceTrend {
      location: string,
      enrolementCount: number,
      completionCount: number,
      faillerCount: number,
      successRate: number,
    }
export interface PositionTrendType {
      name: string,
      completions: number,
      enrollments: number,
      failures: number,
    }
export interface LearningJourneyTrendType {
      name: string,
      completions: number,
      enrollments: number,
      failures: number,
    }
export interface CourseTrendType {
      name: string,
      completions: number,
      enrollments: number,
      failures: number,
    }
export interface LearnerPerformanceTrend {
      month: string,
      numberOfEnrolments: number,
      numberOfcomplted: number,
      numberOfFailed: number,
      numberOfInprogress: number,
    }

export interface StudentDataEntity {
  names: string,
  id: string,
  userid: string,
  nationalid: string,
  dateofbirth: string,
  servicepointcategory: string,
  district: string,
  sector: string,
  cell: string,
  village: string,
  healthfacility: string,
  position: string,
  servicecountry: string,
  otherspecified: string,
  phoneNumber: string,
  email: string,
  sex: "M" | "F",
  enrollments: number,
  completed: number,
  failed: number,
  inprogress: number
}