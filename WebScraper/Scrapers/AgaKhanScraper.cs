using System;
using System.Collections.Generic;
using System.Data.SqlClient;
using System.Threading;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using MySql.Data.MySqlClient;
using OpenQA.Selenium.Support.UI;
using SeleniumExtras.WaitHelpers;
using curepath_availscrap;

class AgaKhanScraper : IHospitalScraper
{
    public void Scrape()
    {
        string connectionString = "server=localhost;port=3307;database=curepath_db;uid=root;pwd=;";
        string baseUrl = "https://hospitals.aku.edu";

        using (MySqlConnection conn = new MySqlConnection(connectionString))
        {
            conn.Open();
            string query = "SELECT name, speciality_id FROM speciality";
            var specialties = new List<(string Name, int SpecialityId)>();

            using (MySqlCommand cmd = new MySqlCommand(query, conn))
            using (MySqlDataReader reader = cmd.ExecuteReader())
            {
                while (reader.Read())
                {
                    specialties.Add((reader["name"].ToString(), Convert.ToInt32(reader["speciality_id"])));
                }
            }

            foreach (var specialty in specialties)
            {
                Console.WriteLine($"Processing specialty: {specialty.Name}");

                try
                {
                    using (IWebDriver driver = new ChromeDriver())
                    {
                        ScrapeDoctors(driver, conn, baseUrl, specialty.Name, specialty.SpecialityId);
                    }
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"Error scraping doctors for specialty {specialty.Name}: {ex.Message}");
                }
            }
        }
    }

    static void ScrapeDoctors(IWebDriver driver, MySqlConnection conn, string baseUrl, string specialty, int specialityId)
    {
        string url = $"{baseUrl}/pakistan/patientservices/pages/findadoctor.aspx?Spec={Uri.EscapeDataString(specialty)}";
        driver.Navigate().GoToUrl(url);
        Thread.Sleep(3000);

        ClosePopupIfPresent(driver);
        CloseBackdropIfPresent(driver);
        WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(10));
        wait.Until(ExpectedConditions.ElementExists(By.CssSelector("div.col-xs-12.col-sm-6.col-md-2")));

        string logData = $"Started scraping doctors for specialty: {specialty}. URL: {url}";

        var paginationElements = driver.FindElements(By.CssSelector("ul.pagination"));
        if (paginationElements.Count > 0)
        {
            IWebElement pagination = paginationElements[0];
            IWebElement secondLi = pagination.FindElements(By.CssSelector("li"))[1];
            IWebElement table = secondLi.FindElement(By.TagName("table"));
            var tdElements = table.FindElements(By.TagName("td"));
            IWebElement lastTd = tdElements[tdElements.Count - 1];
            string lastTdValue = lastTd.Text;
            int intValue = int.Parse(lastTdValue);
            int totalDoctorsScraped = 0;

            for (int j = 0; j < intValue; j++)
            {
                var doctorCards = driver.FindElements(By.CssSelector("div.col-xs-12.col-sm-6.col-md-2"));
                for (int i = 0; i < doctorCards.Count; i++)
                {
                    var card = doctorCards[i];

                    try
                    {
                        string doctorName = card.FindElement(By.CssSelector("h4.h5.g-color-black.g-mb-5")).Text.Trim();
                        string profileImg = card.FindElement(By.CssSelector("img")).GetAttribute("src");
                        string profileLink = card.FindElement(By.CssSelector("a.btn-xs-profil")).GetAttribute("href");

                        int doctorId = InsertDoctor(conn, doctorName, profileImg, specialityId);

                        var scheduleButtons = card.FindElements(By.CssSelector("a.btn-xs-Schedue"));
                        if (scheduleButtons.Count > 0)
                        {
                            ClickScheduleButton(scheduleButtons[0], driver);

                            ScrapeAvailability(driver, conn, doctorId);

                            CloseModal(driver);
                            CloseBackdropIfPresent(driver);
                            ReloadPage(driver);
                            ClosePopupIfPresent(driver);

                            doctorCards = driver.FindElements(By.CssSelector("div.col-xs-12.col-sm-6.col-md-2"));
                        }
                        totalDoctorsScraped++;
                    }
                    catch (Exception ex)
                    {
                        Console.WriteLine($"Error processing doctor card: {ex.Message}");
                    }
                }
                var nextButton = driver.FindElement(By.CssSelector("a.plastschild"));
                nextButton.Click();
                Thread.Sleep(3000);
                ClosePopupIfPresent(driver);
                CloseBackdropIfPresent(driver);
            }
            logData += $"\nTotal doctors scraped: {totalDoctorsScraped}";
        }
        else
        {
            var doctorCards = driver.FindElements(By.CssSelector("div.col-xs-12.col-sm-6.col-md-2"));
            int totalDoctorsScraped = doctorCards.Count;

            for (int i = 0; i < doctorCards.Count; i++)
            {
                var card = doctorCards[i];
                try
                {
                    string doctorName = card.FindElement(By.CssSelector("h4.h5.g-color-black.g-mb-5")).Text.Trim();
                    string profileImg = card.FindElement(By.CssSelector("img")).GetAttribute("src");
                    string profileLink = card.FindElement(By.CssSelector("a.btn-xs-profil")).GetAttribute("href");

                    int doctorId = InsertDoctor(conn, doctorName, profileImg, specialityId);

                    var scheduleButtons = card.FindElements(By.CssSelector("a.btn-xs-Schedue"));
                    if (scheduleButtons.Count > 0)
                    {
                        ClickScheduleButton(scheduleButtons[0], driver);

                        ScrapeAvailability(driver, conn, doctorId);

                        CloseModal(driver);
                        CloseBackdropIfPresent(driver);
                        ReloadPage(driver);
                        ClosePopupIfPresent(driver);

                        doctorCards = driver.FindElements(By.CssSelector("div.col-xs-12.col-sm-6.col-md-2"));
                    }
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"Error processing doctor card: {ex.Message}");
                }
            }
            logData += $"\nTotal doctors scraped: {totalDoctorsScraped}";
        }
        LogScrapedData(conn, url, logData);

    }


    static int GetHospitalId(MySqlConnection conn, string hospitalName)
    {
        string hospitalQuery = "SELECT hospital_id FROM Hospitals WHERE name = @hospitalName";
        using (MySqlCommand cmd = new MySqlCommand(hospitalQuery, conn))
        {
            cmd.Parameters.AddWithValue("@hospitalName", hospitalName);
            object result = cmd.ExecuteScalar();
            if (result != null)
            {
                return Convert.ToInt32(result);
            }
            else
            {
                throw new Exception($"Hospital not found: {hospitalName}");
            }
        }
    }



    static int InsertDoctor(MySqlConnection conn, string name, string profileImg, int specialityId)
    {
        string checkDoctorQuery = "SELECT doctor_id FROM Doctors WHERE name = @name AND speciality_id = @specialityId AND profile_img = @profileImg";

        using (MySqlCommand cmdCheck = new MySqlCommand(checkDoctorQuery, conn))
        {
            cmdCheck.Parameters.AddWithValue("@name", name);
            cmdCheck.Parameters.AddWithValue("@specialityId", specialityId);
            cmdCheck.Parameters.AddWithValue("@profileImg", profileImg);

            object result = cmdCheck.ExecuteScalar();
            if (result != null)
            {
                return Convert.ToInt32(result);
            }
        }
        string insertQuery = "INSERT INTO Doctors (name, profile_img, speciality_id) VALUES (@name, @profileImg, @specialityId); SELECT LAST_INSERT_ID();";

        using (MySqlCommand cmdInsert = new MySqlCommand(insertQuery, conn))
        {
            cmdInsert.Parameters.AddWithValue("@name", name);
            cmdInsert.Parameters.AddWithValue("@profileImg", profileImg);
            cmdInsert.Parameters.AddWithValue("@specialityId", specialityId);
            return Convert.ToInt32(cmdInsert.ExecuteScalar());
        }
    }

    static void AddDoctorToHospital(MySqlConnection conn, int doctorId, int hospitalId)
    {
        try
        {
            string checkQuery = @"
            SELECT COUNT(*) FROM doctor_hospital 
            WHERE doctor_id = @doctorId AND hospital_id = @hospitalId";

            using (MySqlCommand cmdCheck = new MySqlCommand(checkQuery, conn))
            {
                cmdCheck.Parameters.AddWithValue("@doctorId", doctorId);
                cmdCheck.Parameters.AddWithValue("@hospitalId", hospitalId);

                int count = Convert.ToInt32(cmdCheck.ExecuteScalar());

                if (count == 0)
                {
                    string insertQuery = @"
                    INSERT INTO doctor_hospital (doctor_id, hospital_id) 
                    VALUES (@doctorId, @hospitalId)";

                    using (MySqlCommand cmdInsert = new MySqlCommand(insertQuery, conn))
                    {
                        cmdInsert.Parameters.AddWithValue("@doctorId", doctorId);
                        cmdInsert.Parameters.AddWithValue("@hospitalId", hospitalId);

                        cmdInsert.ExecuteNonQuery();
                        Console.WriteLine($"Added doctor_id: {doctorId} with hospital_id: {hospitalId}");
                    }
                }
                else
                {
                    Console.WriteLine($"Doctor ID {doctorId} is already associated with Hospital ID {hospitalId}");
                }
            }
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error adding doctor to hospital: {ex.Message}");
        }
    }




    static void ScrapeAvailability(IWebDriver driver, MySqlConnection conn, int doctorId)
    {
        var locationMapping = new Dictionary<string, (string HospitalName, int HospitalId)>
    {
        { "CC Nazerali Walji Building 1st Floor", ("AKUH, Main Campus", 1) },
        { "CC- Integrated Medical Services Rashid Minhas Road", ("Rashid Minhas Road Medical Centre", 2) },
        { "CC-NAZIMABAD", ("Medical Center, Nazimabad (II)", 3) },
        { "CLIFTON MEDICAL SERVICES - FIRST FLOOR", ("Clifton Medical Services (CMS)", 4) },
        { "Consulting Clinic -2", ("AKUH, Main Campus", 1) },
        { "Princess Zahra Pavilion", ("AKUH, Main Campus", 1) },
        { "Smart Clinic at Outreach Center's (Appt. Booking)", ("Khayaban-e-Badar, Karachi", 5) },
        { "Tele-Internal Medicine Clinic", ("Tele-clinics", 6) },
        { "Tele-Internal Medicine Clinic CMS", ("Clifton Medical Services (CMS)", 4) },
        { "Tele-Rheumatology", ("Tele-clinics", 6) }
    };

        try
        {
            WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(20));
            wait.Until(ExpectedConditions.ElementExists(By.CssSelector(".modal-body table tbody")));
            Thread.Sleep(3000);

            var rows = driver.FindElements(By.CssSelector(".modal-body table tbody tr"));
            string availabilityData = $"Doctor ID: {doctorId}, Availability:\n";

            foreach (var row in rows)
            {
                var columns = row.FindElements(By.TagName("td"));
                if (columns.Count >= 4)
                {
                    string location = columns[0].Text.Trim();
                    string dayOfWeek = columns[1].Text.Trim();
                    string fromTime = columns[2].Text.Trim();
                    string toTime = columns[3].Text.Trim();
                    string time = $"{fromTime} - {toTime}";

                    if (!string.IsNullOrEmpty(location) && !string.IsNullOrEmpty(dayOfWeek))
                    {
                        var hospitalInfo = MapLocationToHospital(location, locationMapping);

                        string venue = location;
                        int hospitalId = hospitalInfo.HospitalId;

                        InsertAvailability(conn, doctorId, dayOfWeek, time, venue, hospitalId);
                        AddDoctorToHospital(conn, doctorId, hospitalId);
                        availabilityData += $"{dayOfWeek}: {time}, Venue: {venue}, Hospital: {hospitalInfo.HospitalName}\n";
                    }
                }
            }
            LogScrapedData(conn, driver.Url, availabilityData);
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error scraping availability: {ex.Message}");
        }
    }

    static (string HospitalName, int HospitalId) MapLocationToHospital(string location, Dictionary<string, (string, int)> locationMapping)
    {
        if (locationMapping.TryGetValue(location, out var hospitalInfo))
        {
            return hospitalInfo;
        }

        if (location.Contains("Clifton", StringComparison.OrdinalIgnoreCase))
        {
            return ("Clifton Medical Services (CMS)", 4);
        }

        if (location.Contains("Tele-", StringComparison.OrdinalIgnoreCase))
        {
            return location.Contains("CMS", StringComparison.OrdinalIgnoreCase)
                ? ("Clifton Medical Services (CMS)", 4)
                : ("Tele-clinics", 6);
        }

        if (location.Contains("Nazerali Walji", StringComparison.OrdinalIgnoreCase) ||
            location.Contains("Consulting Clinic", StringComparison.OrdinalIgnoreCase) ||
            location.Contains("Community Health Centre", StringComparison.OrdinalIgnoreCase) ||
            location.Contains("WCH Building", StringComparison.OrdinalIgnoreCase))
        {
            return ("AKUH, Main Campus", 1);
        }

        if (location.Contains("Malir", StringComparison.OrdinalIgnoreCase))
        {
            return ("Malir Clinic", 7);
        }

        return ("AKUH, Main Campus", 1);
    }



    static void InsertAvailability(MySqlConnection conn, int doctorId, string dayOfWeek, string time, string venue, int hospitalId)
    {
        string query = @"
    INSERT INTO availability (doctor_id, day_of_week, time, venue, hospital_id)
    VALUES (@doctorId, @dayOfWeek, @time, @venue, @hospitalId)";

        using (MySqlCommand cmd = new MySqlCommand(query, conn))
        {
            cmd.Parameters.AddWithValue("@doctorId", doctorId);
            cmd.Parameters.AddWithValue("@dayOfWeek", dayOfWeek);
            cmd.Parameters.AddWithValue("@time", time);
            cmd.Parameters.AddWithValue("@venue", venue);
            cmd.Parameters.AddWithValue("@hospitalId", hospitalId);
            cmd.ExecuteNonQuery();
        }
    }


    static void CloseModal(IWebDriver driver)
    {
        try
        {
            IJavaScriptExecutor js = (IJavaScriptExecutor)driver;
            js.ExecuteScript("document.querySelector('.modal-backdrop').remove();");
            Console.WriteLine("Modal backdrop removed using JavaScript.");
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error forcefully closing modal: {ex.Message}");
        }
    }

    static void ReloadPage(IWebDriver driver)
    {
        driver.Navigate().Refresh();
        Console.WriteLine("Page reloaded to handle modal issues.");
        Thread.Sleep(3000);
    }

    static void ClosePopupIfPresent(IWebDriver driver)
    {
        try
        {
            var popup = driver.FindElement(By.CssSelector(".popup-onload"));
            if (popup.Displayed)
            {
                var closeButton = popup.FindElement(By.CssSelector("a.close"));
                closeButton.Click();
                Thread.Sleep(2000);
                Console.WriteLine("Popup closed successfully.");
            }
        }
        catch (NoSuchElementException)
        {
            Console.WriteLine("No popup detected.");
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error while handling popup: {ex.Message}");
        }
    }

    static void CloseBackdropIfPresent(IWebDriver driver)
    {
        try
        {
            var backdrop = driver.FindElement(By.CssSelector(".modal-backdrop.fade.in"));
            if (backdrop.Displayed)
            {
                backdrop.Click();
                WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(10));
                wait.Until(ExpectedConditions.InvisibilityOfElementLocated(By.CssSelector(".modal-backdrop.fade.in")));
                Console.WriteLine("Backdrop clicked to close modal.");
                Thread.Sleep(2000);
            }
        }
        catch (NoSuchElementException)
        {
            Console.WriteLine("No backdrop found.");
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error closing backdrop: {ex.Message}");
        }
    }

    static void ClickScheduleButton(IWebElement button, IWebDriver driver)
    {
        try
        {
            WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(10));
            wait.Until(ExpectedConditions.ElementToBeClickable(button));
            button.Click();
            Console.WriteLine("Schedule button clicked.");
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Error clicking schedule button: {ex.Message}");
        }
    }

    static void LogScrapedData(MySqlConnection conn, string sourceUrl, string dataFetched)
    {
        string query = @"
        INSERT INTO scraped_data_log (source_url, data_fetched)
        VALUES (@sourceUrl, @dataFetched)";

        using (MySqlCommand cmd = new MySqlCommand(query, conn))
        {
            cmd.Parameters.AddWithValue("@sourceUrl", sourceUrl);
            cmd.Parameters.AddWithValue("@dataFetched", dataFetched);
            cmd.ExecuteNonQuery();
        }
    }
}