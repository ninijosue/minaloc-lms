import { writeFileSync, readFileSync } from "fs"
import { join } from "path"

async function processRwandaDelimitationData() {
  try {
    console.log("Reading Rwanda delimitation data from local file...")

    // Read the CSV data from local file
    // const csvFilePath = join(process.cwd(), "scripts", "rwanda_demographic.csv")
    // console.log(`Reading CSV file from: ${csvFilePath}`)
    const csvText = readFileSync("rwanda_demographic.csv", "utf8")

    console.log("Processing CSV data...")

    // Parse CSV manually (simple approach for this structure)
    const lines = csvText.trim().split("\n")
    const headers = lines[0].split(",").map((h) => h.trim().replace(/"/g, ""))

    console.log("Headers found:", headers)

    // Create the nested structure
    const districts = {}

    // Process each row (skip header)
    for (let i = 1; i < lines.length; i++) {
      const values = lines[i].split(",").map((v) => v.trim().replace(/"/g, ""))

      if (values.length < 10) continue // Skip incomplete rows

      const [
        provinceName,
        provinceId,
        districtName,
        districtId,
        sectorName,
        sectorId,
        cellName,
        cellId,
        villageName,
        villageId,
      ] = values

      // Initialize district if it doesn't exist
      if (!districts[districtName]) {
        districts[districtName] = {
          name: districtName,
          id: districtId,
          sectors: [],
        }
      }

      const district = districts[districtName]

      // Find or create sector
      let sector = district.sectors.find((s) => s.id === sectorId)
      if (!sector) {
        sector = {
          name: sectorName,
          id: sectorId,
          cells: [],
        }
        district.sectors.push(sector)
      }

      // Find or create cell
      let cell = sector.cells.find((c) => c.id === cellId)
      if (!cell) {
        cell = {
          name: cellName,
          id: cellId,
          villages: [],
        }
        sector.cells.push(cell)
      }

      // Add village if it doesn't exist
      const existingVillage = cell.villages.find((v) => v.id === villageId)
      if (!existingVillage) {
        cell.villages.push({
          name: villageName,
          id: villageId,
        })
      }
    }

    console.log(`Processed ${Object.keys(districts).length} districts`)

    // Display sample of the structure
    const firstDistrictKey = Object.keys(districts)[0]
    const firstDistrict = districts[firstDistrictKey]

    console.log("\n=== SAMPLE DISTRICT STRUCTURE ===")
    console.log(
      JSON.stringify(
        {
          [firstDistrictKey]: {
            name: firstDistrict.name,
            id: firstDistrict.id,
            sectors: firstDistrict.sectors.slice(0, 2).map((sector) => ({
              name: sector.name,
              id: sector.id,
              cells: sector.cells.slice(0, 1).map((cell) => ({
                name: cell.name,
                id: cell.id,
                villages: cell.villages.slice(0, 3),
              })),
            })),
          },
        },
        null,
        2,
      ),
    )

    console.log("\n=== COMPLETE JSON OUTPUT ===")

    // Statistics
    let totalSectors = 0
    let totalCells = 0
    let totalVillages = 0

    Object.values(districts).forEach((district) => {
      totalSectors += district.sectors.length
      district.sectors.forEach((sector) => {
        totalCells += sector.cells.length
        sector.cells.forEach((cell) => {
          totalVillages += cell.villages.length
        })
      })
    })

    console.log("\n=== STATISTICS ===")
    console.log(`Districts: ${Object.keys(districts).length}`)
    console.log(`Sectors: ${totalSectors}`)
    console.log(`Cells: ${totalCells}`)
    console.log(`Villages: ${totalVillages}`)

    console.log("\n=== SAVING TO FILE ===")
    try {
      // Save the complete JSON data to a file
      const jsonFileName = "rwanda-districts-data.json"
      writeFileSync(jsonFileName, JSON.stringify(districts, null, 2), "utf8")
      console.log(`✅ Data successfully saved to ${jsonFileName}`)
      console.log(`📁 File location: ${process.cwd()}/${jsonFileName}`)
    } catch (writeError) {
      console.error("❌ Error saving file:", writeError)
    }

    return districts
  } catch (error) {
    console.error("Error processing Rwanda delimitation data:", error)
    throw error
  }
}

// Execute the function
processRwandaDelimitationData()
