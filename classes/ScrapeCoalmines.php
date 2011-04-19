<?php
require_once('String.php');
require_once('Scraper.php');
require_once('ScraperTable.php');
require_once('ScraperModel.php');
/**
 * First attempt at generic Scraper system - but a final, top-level class must always exist
 * This is because of the ad-hoc nature of scraping, every site is so different
 * For instance, this site is hand-written HTML, not a CMS, so the content was not uniform
 * This top-level class contains all the project-specific information and utlilises the scraping tools
 * to import the information as necessary
 * Final to emphasise this is a one-off solution that should be replicated/modified but not extended. 
 * @author johnbyrne
 *
 */
final class ScrapeCoalmines
{
	/**
	 * Stores the relative path that links gets prepended to scraped urls
	 * Most websites use relative, not absolute links for their internal links
	 * @var string
	 */
	private $_baseURL;
	/**
	 * URL we started the scraper at
	 * @var string
	 */
	private $_startURL;
	/**
	 * Any proxy details in 127.0.0.1:0 format
	 * @var string
	 */
	private $_proxy = null;
	/**
	 * If set to true, no cache reading or writing will take place
	 * @var boolean
	 */
	private $_noCache = false;

	public function __construct($baseURL, $startURL, $proxy = null, $noCache = false)
	{
		$this->_baseURL 	= $baseURL;
		$this->_startURL 	= $startURL;
		$this->_proxy 		= $proxy;
		$this->_noCache		= $noCache;

		$scraperModel = new ScraperModel('mines');

		$scraperModel->emptyForInsert();

		$html = Scraper::connectToURLAndReturnData($startURL, $this->_proxy, $this->_noCache);

		$links = Scraper::extractLinksFromHTML($html, "//table[@height='49%']//a[@href!='']", $this->getBaseURL());

		$total = 0;
		$tableCount = 0;
		
		foreach ($links as $link) {
			$tableCount++;

			$html = Scraper::connectToURLAndReturnData($link, $this->_proxy, $this->_noCache);

			//iterate through all available tables and store this data
			$query = "//table[@height='46']";
			$mines = ScraperTable::convertTablesToArray($html, $query);
				
			$mines = $this->convertDittoToLiteral($mines);
			

			foreach ($mines as $index => $row) {
				$total += $scraperModel->saveData($row);
			}
		}

		echo "$total entries scraped.";
	}

	private function getBaseURL()
	{
		return $this->_baseURL;
	}
	/**
	 * Extremely specific to this project - look for the word "ditto" and replace it with the corresponding
	 * entry in the previous row
	 * This means we have to input data in the order it was presented - which is the default anyway
	 * @param array $data
	 * @return array $data
	 */
	private function convertDittoToLiteral($data)
	{
		foreach ($data as $tableNum => $table) {
			foreach ($table['content'] as $rowNum => $row) {
				foreach ($row as $key => $val) {
					//find intstances of the word "ditto" and replace
					$pattern = '/ditto\b/';
					$replacement = $data[$tableNum]['content'][$rowNum - 1][$key];
					if (preg_match($pattern, $val)) {
						$data[$tableNum]['content'][$rowNum][$key] = preg_replace($pattern, $replacement, $data[$tableNum]['content'][$rowNum][$key]);
					}
				}
			}
		}
		return $data;
	}
}