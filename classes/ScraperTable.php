<?php
require_once 'Scraper.php';
/**
 * Extension of Scraper class to accomodate HTML table functions - these are common in scraping
 * and the scope of this class is expected to increase
 * @author johnbyrne
 *
 */
class ScraperTable extends Scraper
{
	/**
	 * Take raw HTML and extract the <table>s from it
	 * Then traverse following the rows and columns
	 * The first row is a header, the ones that follow must correspond to it
	 * You can have multiple tables on a page in different formats
	 * TODO: string prep shouldn't really be handled here as it's somewhat project-dependent 
	 * Array is returned as follows:
	 * array(
	 * 	TABLE_NUMBER => 
	 * 		array(
	 * 			'header' => array(
	 * 							'col1' => val,
 	 * 								'col2' => val		
	 * 						),
	 * 			'content' => array(
	 * 								ROW_NUMBER => array(
	 * 													'col1' => val,
	 * 												)
	 * 								)	
	 * 							)
	 * 		}
	 * @param string $html
	 * @param string $query
	 * @return array $output
	 */
	public function convertTablesToArray($html, $query)
	{
		//fetch the tables - query dependency
		$nodes = self::parseDataForProcessing($html, $query);
		$output = array();
		$tableNum = 0;
		//traverse tales
		foreach ($nodes as $node) {
			$header = array();
			$body = array();
			//traverse rows
			$children = $node->getElementsByTagName('tr');
			$row = 0;
			foreach ($children as $child) {
				//traverse columns
				$tds = $child->getElementsByTagName('td');
				foreach ($tds as $td) {
					if ($row == 0) {
						$header[] = String::prepareFieldNameForDB($td->nodeValue);
					} else {
						$body[$row][] = String::prepareValueForDB($td->nodeValue);
					}
				}
				$row++;
			}
			$output[$tableNum] = array('header' => $header, 'content' => $body);
			$tableNum++;
		}
		
		return $output;
	}
}