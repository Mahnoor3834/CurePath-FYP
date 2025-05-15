# CurePath C# Scrapers

This directory contains the C# console applications used to scrape doctor and availability data from hospital websites as part of the CurePath Final Year Project.

## Purpose

The scrapers extract doctor profiles, specialties, hospital affiliations, and appointment availability from the following hospital systems:

- Aga Khan Hospital
- Ziauddin Hospital
- Liaquat National Hospital (Online and Offline sections)

All data is stored in a MySQL database (`curepath_db`).

## Project Structure

- `Program.cs`: Entry point that prompts the user to choose a hospital and invokes the corresponding scraper.
- `IHospitalScraper.cs`: Interface implemented by all scraper classes.
- `AgaKhanScraper.cs`: Scrapes doctor data from Aga Khan Hospital.
- `ZiauddinScraper.cs`: Scrapes doctor data from all Ziauddin locations and inserts into the database.
- `LiaquatScraper.cs`: Scrapes in-person consultation availability from Liaquat.
- `LiaquatOnlineScraper.cs`: Scrapes telemedicine availability from Liaquat.
- `LiaquatCombinedScraper.cs`: Combines both Liaquat scrapers (online and in-person).
- `DatabaseHelper.cs`: Provides MySQL helper methods.
- `.csproj` and `.sln` files: Visual Studio project and solution files to compile the scrapers.

## How to Run

1. Open the solution (`curepath_availscrap.sln`) in **Visual Studio 2022** or later.
2. Build the solution to restore dependencies (Selenium, MySQL).
3. Ensure your MySQL server is running and the connection string in each scraper matches your local database.
4. Set `Program.cs` as the startup project.
5. Run the project (Ctrl+F5 or F5).
6. When prompted, enter a number to select the hospital to scrape:
   - `1` for Aga Khan
   - `2` for Ziauddin
   - `3` for both Liaquat Online and Offline

## Notes

- Scrapers require an active internet connection.
- Web elements are located using Selenium WebDriver with CSS selectors and wait conditions.
- All scraped data is stored in MySQL tables (`Doctors`, `Speciality`, `Availability`, `Doctor_Hospital`, etc.).
- The application avoids inserting duplicate records using `SELECT` checks and `INSERT IGNORE`.
- Build artifacts (e.g., `bin`, `obj`, `.vs`) are excluded via `.gitignore`.

## Requirements

- .NET 6 or later
- Visual Studio 2022 or compatible C# IDE
- Selenium WebDriver
- MySQL database
- Chrome browser and matching ChromeDriver

## Database Connection

Update the following connection string in each scraper file as per your environment:

```csharp
string connectionString = "server=localhost;port=3307;database=curepath_db;uid=root;pwd=;";
