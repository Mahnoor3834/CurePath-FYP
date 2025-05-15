using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace curepath_availscrap.Scrapers.LiaquatScrapers
{
    public class LiaquatCombinedScraper : IHospitalScraper
    {
        public void Scrape()
        {
            new LiaquatOnlineScraper().Scrape();
            new LiaquatScraper().Scrape();
        }
    }
}
