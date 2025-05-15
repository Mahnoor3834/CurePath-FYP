using MySql.Data.MySqlClient;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using OpenQA.Selenium.Support.UI;
using System;
using System.Threading;
using System.Globalization;
using System.Xml.Linq;
using System.Collections.Generic;
using curepath_availscrap;

class ZiauddinScraper : IHospitalScraper
{
    private static readonly Dictionary<string, int> hospitalMapping = new Dictionary<string, int>
    {
        { "CLIFTON", 9 },
        { "NORTH NAZIMABAD", 8 },
        { "BOAT BASIN", 14 },
        { "KEAMARI", 10 },
        { "SUKKUR", 11 },
        { "SLT CLIFTON", 12 },
        { "PSYCHOLOGY CLIFTON", 13 }
    };
    public void Scrape()
    {
        string connectionString = "server=localhost;port=3307;database=curepath_db;uid=root;pwd=;";

        IWebDriver driver = new ChromeDriver();
        driver.Navigate().GoToUrl("https://www.ziauddinhospital.com/for-patients/find-a-doctor/");
        Thread.Sleep(5000);


        try
        {
            WebDriverWait wait = new WebDriverWait(driver, TimeSpan.FromSeconds(10));

            var searchButton = wait.Until(SeleniumExtras.WaitHelpers.ExpectedConditions.ElementToBeClickable(By.Id("submit")));
            searchButton.Click();
            Console.WriteLine("Search button clicked.");
            Thread.Sleep(5000);

            IJavaScriptExecutor js = (IJavaScriptExecutor)driver;
            long scrollHeight = 0;
            long currentHeight = -1;
            do
            {
                currentHeight = scrollHeight;
                scrollHeight = (long)js.ExecuteScript("window.scrollBy(0, 500); return document.documentElement.scrollTop;");
                Thread.Sleep(1000);
            } while (currentHeight < scrollHeight);

            Console.WriteLine("Finished scrolling.");

            var doctorCards = driver.FindElements(By.ClassName("profile-widget"));
            Console.WriteLine($"Total doctors found: {doctorCards.Count}");

            using (MySqlConnection connection = new MySqlConnection(connectionString))
            {
                connection.Open();

                foreach (var card in doctorCards)
                {
                    try
                    {
                        var name = ToTitleCase(card.FindElement(By.CssSelector(".pro-content h3.title")).Text);
                        var qualifications = card.FindElement(By.CssSelector(".pro-content p:nth-of-type(1)")).Text;
                        var specialty = card.FindElement(By.CssSelector(".pro-content p:nth-of-type(2)")).Text;
                        var urduSpecialty = card.FindElement(By.CssSelector(".pro-content p:nth-of-type(3)")).Text;

                        int specialtyId = GetOrInsertSpeciality(connection, specialty, urduSpecialty);

                        int doctorId = InsertDoctor(connection, name, specialtyId);

                        var availabilityButtons = card.FindElements(By.ClassName("show_schedule"));

                        foreach (var button in availabilityButtons)
                        {
                            try
                            {
                                var hospitalName = button.Text.Trim().ToUpper();
                                if (hospitalMapping.TryGetValue(hospitalName, out int hospitalId))
                                {
                                    InsertDoctorHospital(connection, doctorId, hospitalId);
                                    ((IJavaScriptExecutor)driver).ExecuteScript("arguments[0].scrollIntoView(true);", button);
                                    Thread.Sleep(500);
                                    button.Click();
                                    Thread.Sleep(2000);

                                    var modal = wait.Until(SeleniumExtras.WaitHelpers.ExpectedConditions.ElementIsVisible(By.ClassName("modal-content")));
                                    var availabilityRows = modal.FindElements(By.CssSelector(".table tbody tr"));

                                    foreach (var row in availabilityRows)
                                    {
                                        var day = row.FindElement(By.CssSelector("td:nth-of-type(1)")).Text;
                                        var timing = row.FindElement(By.CssSelector("td:nth-of-type(2)")).Text;
                                        InsertAvailability(connection, doctorId, day, timing, hospitalId);
                                    }

                                    modal.FindElement(By.CssSelector(".close")).Click();
                                    Thread.Sleep(1000);
                                }
                                else
                                {
                                    Console.WriteLine($"Hospital '{hospitalName}' not found in the dictionary.");
                                }
                            }
                            catch (Exception ex)
                            {
                                Console.WriteLine($"Error processing availability: {ex.Message}");
                            }
                        }
                    }
                    catch (Exception ex)
                    {
                        Console.WriteLine($"Error processing doctor card: {ex.Message}");
                    }
                }
            }

            LogScrapingOperation(connectionString, "https://www.ziauddinhospital.com/for-patients/find-a-doctor/", "Scraping complete.");
            Console.WriteLine("Scraping and insertion complete.");
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
    static int GetOrInsertSpeciality(MySqlConnection connection, string specialty, string alternateNames)
    {
        string formattedSpecialty = ToTitleCase(specialty);
        string databaseSpecialty = formattedSpecialty;
        if (formattedSpecialty == "Pulmonology (Chest Specialist)")
        {
            databaseSpecialty = "Pulmonology";
        }
        else if (formattedSpecialty == "Ent Surgery")
        {
            databaseSpecialty = "ENT (Otolaryngology)";
        }
        else if (formattedSpecialty == "Neuro & Spinal Surgeon")
        {
            databaseSpecialty = "Neuro Surgery";
        }
        else if (formattedSpecialty == "Rheumatology / Medicine (Joints Specialist)")
        {
            databaseSpecialty = "Rheumatology";
        }
        else if (formattedSpecialty == "General Surgery & Laparoscopy")
        {
            databaseSpecialty = "General Surgery";
        }
        else if (formattedSpecialty == "General Physician")
        {
            databaseSpecialty = "Internal Medicine";
        }
        else if (formattedSpecialty == "Dietitian & Nutritionist")
        {
            databaseSpecialty = "Nutrition";
        }
        else if (formattedSpecialty == "Urology (Kidney Surgeon)")
        {
            databaseSpecialty = "Urology";
        }
        else if (formattedSpecialty == "Pediatric Surgery (Child Surgery)")
        {
            databaseSpecialty = "Paediatric Surgery";
        }
        else if (formattedSpecialty == "Cardiac Surgery (Heart Surgery)")
        {
            databaseSpecialty = "Cardiothoracic Surgery";
        }
        else if (formattedSpecialty == "Pediatrics (Child Specialist)")
        {
            databaseSpecialty = "General Paediatrics";
        }
        else if (formattedSpecialty == "Orthopedic Surgery (Bones Specialist")
        {
            databaseSpecialty = "Orthopaedic Surgery";
        }
        else if (formattedSpecialty == "Gynaecology & Obstetrics")
        {
            databaseSpecialty = "Obstetrics and Gynaecology";
        }
        else if (formattedSpecialty == "Plastic Surgery (Cosmetic Surgery)")
        {
            databaseSpecialty = "Plastic Surgery";
        }
        else if (formattedSpecialty == "Anaesthesiology")
        {
            databaseSpecialty = "General Anaesthesia";
        }
        else if (formattedSpecialty == "Orthopedic & Spinal Surgery")
        {
            databaseSpecialty = "Orthopaedic Surgery";
        }
        else if (formattedSpecialty == "Psychiatry")
        {
            databaseSpecialty = "Psychiatry";
        }
        else if (formattedSpecialty == "Cardiology (Heart Specialist)")
        {
            databaseSpecialty = "Cardiology";
        }
        else if (formattedSpecialty == "Gastroenterology")
        {
            databaseSpecialty = "Gastroenterology";
        }
        else if (formattedSpecialty == "Dental Surgeon")
        {
            databaseSpecialty = "Dentistry";
        }
        else if (formattedSpecialty == "Dermatology (Skin Specialist)")
        {
            databaseSpecialty = "Dermatology";
        }
        else if (formattedSpecialty == "Nephrology (Kidney Physician)")
        {
            databaseSpecialty = "Nephrology";
        }
        else if (formattedSpecialty == "Consultant Interventional Radiologist")
        {
            databaseSpecialty = "Radiology";
        }
        else if (formattedSpecialty == "Neurology Physician")
        {
            databaseSpecialty = "Neurology";
        }
        else if (formattedSpecialty == "Infection Diseases")
        {
            databaseSpecialty = "Infectious Diseases";
        }
        else if (formattedSpecialty == "Thoracic Surgery / Chest Surgery")
        {
            databaseSpecialty = "Cardiothoracic Surgery";
        }
        else if (formattedSpecialty == "Dental, Oral & Maxillofacial Surgeon")
        {
            databaseSpecialty = "Dentistry";
        }
        else
        {
            Console.WriteLine($"Specialty '{formattedSpecialty}' not recognized. Inserting as new specialty.");
        }

        string query = @"
        SELECT speciality_id 
        FROM Speciality 
        WHERE name LIKE CONCAT('%', @Specialty, '%') 
        OR FIND_IN_SET(@Specialty, alternate_names)";
        using (MySqlCommand command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@Specialty", databaseSpecialty);
            object result = command.ExecuteScalar();

            if (result != null)
            {
                Console.WriteLine($"Specialty found: {databaseSpecialty}");
                return Convert.ToInt32(result);
            }
        }

        query = "INSERT INTO Speciality (name, alternate_names) VALUES (@Specialty, @AlternateNames)";
        using (MySqlCommand command = new MySqlCommand(query, connection))
        {
            Console.WriteLine($"Adding new specialty: {databaseSpecialty}");
            command.Parameters.AddWithValue("@Specialty", databaseSpecialty);
            command.Parameters.AddWithValue("@AlternateNames", alternateNames ?? string.Empty);
            command.ExecuteNonQuery();
            return (int)command.LastInsertedId;
        }
    }

    static string ToTitleCase(string input)
    {
        TextInfo textInfo = CultureInfo.CurrentCulture.TextInfo;
        return textInfo.ToTitleCase(input.ToLower());
    }


    static int InsertDoctor(MySqlConnection connection, string name, int specialtyId)
    {
        string checkQuery = "SELECT doctor_id FROM Doctors WHERE name = @Name AND speciality_id = @SpecialtyId";
        using (MySqlCommand checkCommand = new MySqlCommand(checkQuery, connection))
        {
            checkCommand.Parameters.AddWithValue("@Name", name);
            checkCommand.Parameters.AddWithValue("@SpecialtyId", specialtyId);
            var existingId = checkCommand.ExecuteScalar();

            if (existingId != null)
            {
                Console.WriteLine($"Doctor already exists: {name}");
                return Convert.ToInt32(existingId);
            }
        }

        string insertQuery = "INSERT INTO Doctors (name, speciality_id) VALUES (@Name, @SpecialtyId)";
        using (MySqlCommand insertCommand = new MySqlCommand(insertQuery, connection))
        {
            Console.WriteLine($"Inserting new doctor: {name}");
            insertCommand.Parameters.AddWithValue("@Name", name);
            insertCommand.Parameters.AddWithValue("@SpecialtyId", specialtyId);
            insertCommand.ExecuteNonQuery();
            return (int)insertCommand.LastInsertedId;
        }
    }

    static void InsertDoctorHospital(MySqlConnection connection, int doctorId, int hospitalId)
    {
        string checkQuery = "SELECT COUNT(*) FROM Doctor_Hospital WHERE doctor_id = @DoctorId AND hospital_id = @HospitalId";
        using (var checkCommand = new MySqlCommand(checkQuery, connection))
        {
            checkCommand.Parameters.AddWithValue("@DoctorId", doctorId);
            checkCommand.Parameters.AddWithValue("@HospitalId", hospitalId);

            int count = Convert.ToInt32(checkCommand.ExecuteScalar());
            if (count == 0)
            {
                string insertQuery = "INSERT INTO Doctor_Hospital (doctor_id, hospital_id) VALUES (@DoctorId, @HospitalId)";
                using (var insertCommand = new MySqlCommand(insertQuery, connection))
                {
                    insertCommand.Parameters.AddWithValue("@DoctorId", doctorId);
                    insertCommand.Parameters.AddWithValue("@HospitalId", hospitalId);
                    insertCommand.ExecuteNonQuery();
                }
            }
            else
            {
                Console.WriteLine($"Doctor {doctorId} already linked to hospital {hospitalId}.");
            }
        }
    }

    static void InsertAvailability(MySqlConnection connection, int doctorId, string day, string time, int hospitalId)
    {
        string query = "INSERT IGNORE INTO availability (doctor_id, day_of_week, time, hospital_id) VALUES (@DoctorId, @Day, @Time, @HospitalId)";
        using (MySqlCommand command = new MySqlCommand(query, connection))
        {
            command.Parameters.AddWithValue("@DoctorId", doctorId);
            command.Parameters.AddWithValue("@Day", day);
            command.Parameters.AddWithValue("@Time", time);
            command.Parameters.AddWithValue("@HospitalId", hospitalId);
            command.ExecuteNonQuery();
        }
    }

    static void LogScrapingOperation(string connectionString, string sourceUrl, string message)
    {
        using (MySqlConnection connection = new MySqlConnection(connectionString))
        {
            connection.Open();
            string query = "INSERT INTO scraped_data_log (source_url, data_fetched) VALUES (@SourceUrl, @Message)";
            using (MySqlCommand command = new MySqlCommand(query, connection))
            {
                command.Parameters.AddWithValue("@SourceUrl", sourceUrl);
                command.Parameters.AddWithValue("@Message", message);
                command.ExecuteNonQuery();
            }
        }
    }
}
