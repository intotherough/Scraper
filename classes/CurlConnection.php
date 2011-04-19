<?php
class CurlConnection
{
	/**
	 * User Agent sent to remote server
	 * @var string
	 */
	private $_userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';
	/**
	 * Internal details of our connection - in this case a CURL resource
	 * @var unknown_type
	 */
	private $_connection;

	private $_cookieJar = './tmp/scraperconnection/cookies.txt';

	public function getConnection()
	{
		return $this->_connection;
	}

	public function __construct($proxy = null, $cookieJar = null)
	{
		if ($cookieJar) {
			$this->_cookieJar = $cookieJar;
		}

		$ch = curl_init();

		$header = array();
		$header[0] 	= "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] 	.= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] 	=  "Cache-Control: max-age=0";
		$header[] 	=  "Connection: keep-alive";
		$header[] 	= "Keep-Alive: 300";
		$header[] 	= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] 	= "Accept-Language: en-us,en;q=0.5";
		$header[] 	= "Pragma: "; // browsers keep this blank.

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookieJar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookieJar);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		if ($proxy) {
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}

		$this->_connection = $ch;
	}
	/**
	 * Point our CURL object at a URL
	 * @param $url
	 */
	private function setURL($url)
	{
		curl_setopt($this->getConnection(), CURLOPT_URL, $url);
	}

	/**
	 * Connect to URL and, if possible, return html
	 * @param string $url
	 * @return string $html
	 */
	public function connectAndGetHTML($url)
	{
		$this->setURL($url);

		$html = $this->executeRequest();

		return $html;
	}

	public function connectAndPostToURL($url, $fields, $referer = '')
	{
		$this->setURL($url);

		curl_setopt($this->getConnection(), CURLOPT_POST, 1);
		curl_setopt($this->getConnection(), CURLOPT_REFERER, $referer);
		curl_setopt($this->getConnection(), CURLOPT_POSTFIELDS, $fields);

		$html = $this->executeRequest();

		return $html;
	}
	/**
	 * Perform the request and catch any errors
	 * @throws Exception
	 * @return string $html
	 */
	private function executeRequest()
	{
		try {
			$html = curl_exec($this->getConnection());
			if (curl_error($this->getConnection())) {
				throw new Exception(curl_error($this->getConnection()));
			}
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}

		return $html;
	}


}