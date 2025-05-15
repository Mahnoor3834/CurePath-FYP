using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using curepath_availscrap;
using MySql.Data.MySqlClient;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Support.UI;
using SeleniumExtras.WaitHelpers;

class LiaquatScraper : IHospitalScraper
{
    public void Scrape()
    {
        string connectionString = "server=localhost;port=3307;database=curepath_db;uid=root;pwd=;";

        var locations = new Dictionary<string, int>
        {
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=4a649fe7-776a-4436-b1a3-123fc0486fb0&IsTeleMedicine=false", 15 },
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=e990f6e0-8bfe-408d-b9e1-1b8aab44293c&IsTeleMedicine=false", 16 },
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=c88445ef-6051-4af6-9bb1-06a80622e70c&IsTeleMedicine=false", 17 },
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=cfa44c25-1e5f-440d-82a5-726730b6b582&IsTeleMedicine=false", 18 },
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=5496d837-3a57-4399-b08d-00837caf4717&IsTeleMedicine=false", 19 },
            { "https://www.lnh.edu.pk/Schedule/OpdSchedule?SelectedLocation=a84d5984-4524-4bd8-826b-09c6ba06abb4&IsTeleMedicine=false", 20 }
        };

        using (var connection = new MySqlConnection(connectionString))
        {
            connection.Open();
            Console.WriteLine("Database connection established.");

            var options = new ChromeOptions();
            var driverService = ChromeDriverService.CreateDefaultService();
            driverService.HideCommandPromptWindow = true;

            using (var driver = new ChromeDriver(driverService, options, TimeSpan.FromMinutes(2)))
            {
                driver.Manage().Timeouts().PageLoad = TimeSpan.FromMinutes(2);
                driver.Manage().Timeouts().ImplicitWait = TimeSpan.FromSeconds(10);

                foreach (var location in locations)
                {
                    string url = location.Key;
                    int hospitalId = location.Value;

                    Console.WriteLine($"\nScraping URL: {url}\nHospital ID: {hospitalId}");

                    try
                    {
                        driver.Navigate().GoToUrl(url);

                        WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(30));
                        wait.Until(ExpectedConditions.ElementExists(By.ClassName("departName")));

                        var specialties = driver.FindElements(By.CssSelector(".borderParent"));
                        Console.WriteLine($"Specialties: {specialties.Count}");

                        foreach (var specialty in specialties)
                        {
                            string specialtyName = specialty.FindElement(By.ClassName("departName")).Text;
                            string databaseSpecialty = specialtyName == "Chest Medicine" ? "Pulmonology" :
                                                       specialtyName == "Diabetes, Endocrinology And Metabolism" ? "Endocrinology" :
                                                       specialtyName;

                            string specialtyId = GetOrInsertSpecialty(databaseSpecialty, connection);
                            var doctorLinks = specialty.FindElements(By.CssSelector("a[data-toggle='collapse']"));
                            IJavaScriptExecutor js = (IJavaScriptExecutor)driver;

                            foreach (var doctorLink in doctorLinks)
                            {
                                try
                                {
                                    wait.Until(ExpectedConditions.ElementToBeClickable(doctorLink));
                                    js.ExecuteScript("arguments[0].scrollIntoView({block: 'center'});", doctorLink);
                                    Thread.Sleep(300);
                                    var doctorName = doctorLink.Text.Trim();

                                    if (doctorName.StartsWith("Dr.") || doctorName.StartsWith("Prof.") || doctorName.StartsWith("Ms."))
                                    {
                                        string doctorId = GetOrInsertDoctor(doctorName, specialtyId, connection);
                                        doctorLink.Click();

                                        var toggleId = doctorLink.GetAttribute("href").Split('#').Last();
                                        var expandedSection = wait.Until(ExpectedConditions.ElementIsVisible(By.Id(toggleId)));

                                        var nestedToggles = expandedSection.FindElements(By.CssSelector("a[data-toggle='collapse']"));
                                        var completedToggles = new HashSet<string>();
                                        string day = string.Empty;
                                        int rowSpanCount = 0;

                                        foreach (var nestedToggle in nestedToggles)
                                        {
                                            var toggleHref = nestedToggle.GetAttribute("href");
                                            if (completedToggles.Contains(toggleHref)) continue;

                                            js.ExecuteScript("arguments[0].scrollIntoView({block: 'center'});", nestedToggle);
                                            wait.Until(ExpectedConditions.ElementToBeClickable(nestedToggle)).Click();

                                            var nestedToggleId = toggleHref.Split('#').Last();
                                            var nestedSection = wait.Until(ExpectedConditions.ElementIsVisible(By.Id(nestedToggleId)));
                                            var tableRows = nestedSection.FindElements(By.CssSelector("tbody > tr"));

                                            foreach (var row in tableRows)
                                            {
                                                try
                                                {
                                                    var cells = row.FindElements(By.TagName("td")).ToList();

                                                    if (cells.Count > 0 && cells[0].GetAttribute("rowspan") != null)
                                                    {
                                                        day = cells[0].Text.Trim();
                                                        rowSpanCount = int.Parse(cells[0].GetAttribute("rowspan"));
                                                    }

                                                    if (rowSpanCount > 0)
                                                    {
                                                        var time = cells[cells.Count - 3].Text.Trim();
                                                        var venue = cells[cells.Count - 2].Text.Trim();
                                                        Console.WriteLine($"    Day: {day}, Timing: {time}, Venue: {venue}");

                                                        InsertAvailability(doctorId, day, time, hospitalId, venue, connection);
                                                        rowSpanCount--;
                                                    }
                                                }
                                                catch (Exception ex)
                                                {
                                                    Console.WriteLine($"Error processing row: {ex.Message}");
                                                }
                                            }

                                            completedToggles.Add(toggleHref);
                                            nestedToggle.Click();
                                            Thread.Sleep(300);
                                        }

                                        InsertDoctorHospitalRelation(doctorId, hospitalId, connection);
                                    }
                                }
                                catch (Exception ex)
                                {
                                    Console.WriteLine($"Error processing doctor: {ex.Message}");
                                }
                            }
                        }

                        LogScrapedData(url, specialties.Count.ToString(), connection);
                    }
                    catch (Exception ex)
                    {
                        Console.WriteLine($"Error scraping {url}: {ex.Message}");
                    }
                }

                driver.Quit();
            }
        }
    }

    static string GetOrInsertSpecialty(string specialtyName, MySqlConnection connection)
    {
        string query = "SELECT speciality_id FROM Speciality WHERE name = @name OR FIND_IN_SET(@name, alternate_names)";
        using (var command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@name", specialtyName);
            var result = command.ExecuteScalar();
            if (result != null)
                return result.ToString();

            // Insert new specialty
            query = "INSERT INTO Speciality (name, alternate_names) VALUES (@name, '')";
            command.CommandText = query;
            command.ExecuteNonQuery();
            return command.LastInsertedId.ToString();
        }
    }

    static string GetOrInsertDoctor(string doctorName, string specialtyId, MySqlConnection connection)
    {
        string query = "SELECT doctor_id FROM Doctors WHERE name = @name";
        using (var command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@name", doctorName);
            var result = command.ExecuteScalar();
            if (result != null)
                return result.ToString();

            // Insert new doctor
            query = "INSERT INTO Doctors (name, speciality_id) VALUES (@name, @specialtyId)";
            command.CommandText = query;
            command.Parameters.AddWithValue("@specialtyId", specialtyId);
            command.ExecuteNonQuery();
            return command.LastInsertedId.ToString();
        }
    }

    static void InsertDoctorHospitalRelation(string doctorId, int hospitalId, MySqlConnection connection)
    {
        string query = "INSERT IGNORE INTO Doctor_Hospital (doctor_id, hospital_id) VALUES (@doctorId, @hospitalId)";
        using (var command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@doctorId", doctorId);
            command.Parameters.AddWithValue("@hospitalId", hospitalId);
            command.ExecuteNonQuery();
        }
    }

    static void InsertAvailability(string doctorId, string day, string time, int hospitalId, string venue, MySqlConnection connection)
    {
        string query = @"
            INSERT IGNORE INTO availability (doctor_id, day_of_week, time, hospital_id, venue)
            VALUES (@doctorId, @day, @time, @hospitalId, @venue)";
        using (var command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@doctorId", doctorId);
            command.Parameters.AddWithValue("@day", day);
            command.Parameters.AddWithValue("@time", time);
            command.Parameters.AddWithValue("@hospitalId", hospitalId);
            command.Parameters.AddWithValue("@venue", venue);
            command.ExecuteNonQuery();
        }
    }

    static void LogScrapedData(string sourceUrl, string dataFetched, MySqlConnection connection)
    {
        string query = "INSERT INTO scraped_data_log (source_url, data_fetched) VALUES (@sourceUrl, @dataFetched)";
        using (var command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@sourceUrl", sourceUrl);
            command.Parameters.AddWithValue("@dataFetched", dataFetched);
            command.ExecuteNonQuery();
        }
    }
}