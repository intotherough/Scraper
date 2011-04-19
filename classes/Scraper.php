<pre><?php
require_once 'CurlConnection.php';
require_once('Zend/Cache.php');
/**
 * Scraper to retrieve data and do some basic processing (though this will probably be reactored)
 * Should not be tightly coupled to a datasource, thought typically this will be CURL
 * @author johnbyrne
 *
 */
class Scraper
{
	/**
	 * Args expected by Zend_Cache
	 * @var array
	 */
	public static $cacheFrontendOptions = array(
       'lifetime' => 604800// cache lifetime of one week
	);
	/**
	 * Args expected by Zend_Cache
	 * @var array
	 */
	public static $cacheBackendOptions = array('cache_dir' => './tmp/scrapercache/');
	/**
	 * Get data from a given URL in raw HTML format
	 * Check our cache to see if we've scraped this file recently
	 * @param string $url
	 * @param string $proxy
	 * @param bool $noCache
	 */
	public static function connectToURLAndReturnData($url, $proxy = null, $noCache = false)
	{
		return self::perform('connectToURLAndReturnData', $url, null, $proxy, $noCache);
	}
	/**
	 * Post data to a URL
	 * @param string $url
	 * @param string $data
	 * @param string $proxy
	 * @param bool $noCache
	 */
	public static function postDataToURL($url, $data, $proxy = null, $noCache = false)
	{
		return self::perform('postDataToURL', $url, $data, $proxy, $noCache);
	}

	private static function perform($method, $url, $data = null, $proxy = null, $noCache = false)
	{
		$html = false;
		//if caching is on, check there first
		if ($noCache === false) {
			$cache = Zend_Cache::factory('Output',
                                 	 	 'File',
			self::$cacheFrontendOptions,
			self::$cacheBackendOptions);

			$uniqueID =  str_replace('.', '', basename($url));

			$html = $cache->load($uniqueID);
		}
		//either cache is off, or we couldn't find what we wanted
		if ($noCache === true || $html === false) {
			$conn = new CurlConnection($proxy);
			//now perform the method itself
			switch ($method) {
				case 'postDataToURL':
					$html = $conn->connectAndPostToURL($url, $data);
					break;
				default:
				case 'connectToURLAndReturnData':
					$html = $conn->connectAndGetHTML($url);
					break;
			}

		}
		//if we have caching on, save this entry so it will not be fetched again
		if ($noCache === false) {
			$cache->save($html, $uniqueID);
		}

		return $html;
	}

	/**
	 * Get all links from a document, based on a given expression
	 * @param string $html
	 * @param string $query - the xpath query expression
	 * @return array $links
	 */
	public static function extractLinksFromHTML($html, $query, $baseURL = null)
	{
		$xpath = self::loadHTMLAndCreateXPath($html);
		$nodes = $xpath->query($query);
		$links = array();
		if (count($nodes)) {
			foreach ($nodes as $node) {
				if ($baseURL) {
					$url = $baseURL . $node->getAttribute('href');;
				} else {
					$url = $node->getAttribute('href');
				}
				$links[] = $url;
			}
		}

		return $links;
	}
	/**
	 * Run a given xpath query on some HTML
	 * @param unknown_type $html
	 * @param unknown_type $query
	 */
	public static function parseDataForProcessing($html, $query)
	{
		$xpath = self::loadHTMLAndCreateXPath($html);
		$nodes = $xpath->query($query);

		return $nodes;
	}

	/**
	 * Convert our HTML to an xpath-ready domdocument
	 * @param string $html
	 */
	private function loadHTMLAndCreateXPath($html)
	{
		$dom = new domdocument;
		//$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new domxpath($dom);

		return $xpath;
	}
}
