import { writeFileSync, readFileSync } from "fs"
import { join } from "path"

async function processHealthFacilitiesData() {
  try {
    console.log("Reading Health Facilities data from local file...")

    // Read the CSV data from local file
    const csvText = readFileSync("health-facilities.csv", "utf8")

    console.log("Processing CSV data...")

    // Parse CSV manually
    const lines = csvText.trim().split("\n")
    const headers = lines[0].split(",").map((h) => h.trim().replace(/"/g, ""))

    console.log("Headers found:", headers)

    // Create the nested structure
    const healthFacilities = {}

    // Process each row (skip header)
    for (let i = 1; i < lines.length; i++) {
      const values = lines[i].split(",").map((v) => v.trim().replace(/"/g, ""))

      if (values.length < 4) continue // Skip incomplete rows

      const [district, sector, facilityName, code] = values

      // Skip rows with empty essential data
      if (!district || !sector || !facilityName || !code) continue

      // Create the key as district_sector in lowercase
      const key = `${district.toLowerCase()}_${sector.toLowerCase()}`

      // Initialize array if it doesn't exist
      if (!healthFacilities[key]) {
        healthFacilities[key] = []
      }

      // Add facility to the array
      healthFacilities[key].push({
        facilityName: facilityName,
        id: code,
      })
    }

    console.log(`Processed ${Object.keys(healthFacilities).length} district-sector combinations`)

    // Display sample of the structure
    const firstKey = Object.keys(healthFacilities)[0]
    const firstFacilities = healthFacilities[firstKey]

    console.log("\n=== SAMPLE HEALTH FACILITIES STRUCTURE ===")
    console.log(
      JSON.stringify(
        {
          [firstKey]: firstFacilities.slice(0, 3), // Show first 3 facilities
        },
        null,
        2,
      ),
    )

    console.log("\n=== COMPLETE JSON OUTPUT ===")

    // Statistics
    let totalFacilities = 0
    const districtSectorCount = Object.keys(healthFacilities).length

    Object.values(healthFacilities).forEach((facilities) => {
      totalFacilities += facilities.length
    })

    console.log("\n=== STATISTICS ===")
    console.log(`District-Sector combinations: ${districtSectorCount}`)
    console.log(`Total Health Facilities: ${totalFacilities}`)

    // Show breakdown by district-sector
    console.log("\n=== FACILITIES PER DISTRICT-SECTOR ===")
    Object.entries(healthFacilities)
      .sort(([a], [b]) => a.localeCompare(b))
      .forEach(([key, facilities]) => {
        console.log(`${key}: ${facilities.length} facilities`)
      })

    console.log("\n=== SAVING TO FILE ===")
    try {
      // Save the complete JSON data to a file
      const jsonFileName = "rwanda-health-facilities-data.json"
      writeFileSync(jsonFileName, JSON.stringify(healthFacilities, null, 2), "utf8")
      console.log(`✅ Data successfully saved to ${jsonFileName}`)
      console.log(`📁 File location: ${process.cwd()}/${jsonFileName}`)
    } catch (writeError) {
      console.error("❌ Error saving file:", writeError)
    }

    return healthFacilities
  } catch (error) {
    console.error("Error processing Health Facilities data:", error)
    throw error
  }
}

// Execute the function
processHealthFacilitiesData()
