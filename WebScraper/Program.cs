using curepath_availscrap;
using curepath_availscrap.Scrapers.LiaquatScrapers;

class Program
{
    static void Main(string[] args)
    {
        Console.WriteLine("Select Hospital: 1. Aga Khan   2. Ziauddin   3. Liaquat");
        string choice = Console.ReadLine();

        IHospitalScraper scraper = choice switch
        {
            "1" => new AgaKhanScraper(),
            "2" => new ZiauddinScraper(),
            "3" => new LiaquatCombinedScraper(),
            _ => throw new Exception("Invalid choice")
        };

        scraper.Scrape();
    }
}
