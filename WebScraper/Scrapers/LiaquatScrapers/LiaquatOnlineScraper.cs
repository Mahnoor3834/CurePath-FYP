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

class LiaquatOnlineScraper : IHospitalScraper
{
    public void Scrape()
    {
        string connectionString = "server=localhost;port=3307;database=curepath_db;uid=root;pwd=;";

        using (var connection = new MySqlConnection(connectionString))
        {
            try
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

                    try
                    {
                        driver.Navigate().GoToUrl("https://www.lnh.edu.pk/Schedule/OpdSchedule?IsTeleMedicine=true");

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
                            Console.WriteLine($"Doctor Links: {doctorLinks.Count}");
                            IJavaScriptExecutor js = (IJavaScriptExecutor)driver;

                            foreach (var doctorLink in doctorLinks)
                            {
                                try
                                {
                                    wait.Until(ExpectedConditions.ElementToBeClickable(doctorLink));
                                    js.ExecuteScript("arguments[0].scrollIntoView({block: 'center'});", doctorLink);
                                    Thread.Sleep(300);
                                    var doctorName = doctorLink.Text.Trim();

                                    // Add a condition to check if the doctorName starts with the required prefixes
                                    if (doctorName.StartsWith("Dr.") || doctorName.StartsWith("Prof.") || doctorName.StartsWith("Ms.") || doctorName.StartsWith("Mr."))
                                    {
                                        string doctorId = GetOrInsertDoctor(doctorName, specialtyId, connection);
                                        Console.WriteLine($"doctorName: {doctorName}");
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

                                            Boolean i = true;
                                            foreach (var row in tableRows)
                                            {
                                                try
                                                {
                                                    var cells = row.FindElements(By.TagName("td")).ToList();
                                                    Thread.Sleep(300);

                                                    if (cells.Count > 0 && cells[0].GetAttribute("rowspan") != null)
                                                    {
                                                        rowSpanCount = int.Parse(cells[0].GetAttribute("rowspan"));
                                                        day = cells[0].Text.Trim();
                                                        Console.WriteLine($"rowSpanCount: {rowSpanCount} i= {i}");
                                                    }
                                                    if (rowSpanCount == 1)
                                                    {
                                                        var venue = nestedToggle.Text.Trim() + ", " + cells[2].Text.Trim();
                                                        Console.WriteLine($"Venue: {venue}    Day: {cells[0].Text.Trim()}, Timing: {cells[1].Text.Trim()}");
                                                        InsertAvailability(doctorId, cells[0].Text.Trim(), cells[1].Text.Trim(), 21, venue, connection);
                                                    }
                                                    else
                                                    {
                                                        if (rowSpanCount > 0)
                                                        {
                                                            var time = cells[cells.Count - 3].Text.Trim();
                                                            var venue = cells[cells.Count - 2].Text.Trim();
                                                            Console.WriteLine($"    Day: {day}, Timing: {time}");
                                                            Console.WriteLine($"    Venue: {nestedToggle.Text.Trim()}, {venue}");
                                                            InsertAvailability(doctorId, day, time, 21, nestedToggle.Text.Trim() + ", " + venue, connection);
                                                            rowSpanCount--;
                                                        }

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

                                        InsertDoctorHospitalRelation(doctorId, 21, connection);
                                    }
                                    else
                                    {
                                        Console.WriteLine($"Skipped doctor: {doctorName}");
                                        Thread.Sleep(300);
                                    }
                                }
                                catch (Exception ex)
                                {
                                    Console.WriteLine($"Error processing doctor: {ex.Message}");
                                }
                            }

                        }

                        LogScrapedData(driver.Url, specialties.Count.ToString(), connection);
                    }
                    catch (Exception ex)
                    {
                        Console.WriteLine($"Error: {ex.Message}");
                    }
                    finally
                    {
                        driver.Quit();
                    }
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Database connection error: {ex.Message}");
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
        string checkQuery = "SELECT COUNT(*) FROM Doctor_Hospital WHERE doctor_id = @doctorId AND hospital_id = @hospitalId";
        using (var checkCommand = new MySqlCommand(checkQuery, connection))
        {
            checkCommand.Parameters.AddWithValue("@doctorId", doctorId);
            checkCommand.Parameters.AddWithValue("@hospitalId", hospitalId);

            int count = Convert.ToInt32(checkCommand.ExecuteScalar());

            if (count == 0)
            {
                string insertQuery = "INSERT INTO Doctor_Hospital (doctor_id, hospital_id) VALUES (@doctorId, @hospitalId)";
                using (var insertCommand = new MySqlCommand(insertQuery, connection))
                {
                    insertCommand.Parameters.AddWithValue("@doctorId", doctorId);
                    insertCommand.Parameters.AddWithValue("@hospitalId", hospitalId);
                    insertCommand.ExecuteNonQuery();
                }
            }
            else
            {
                Console.WriteLine("Doctor-hospital relation already exists.");
            }
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