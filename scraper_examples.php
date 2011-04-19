<?php
require_once('classes/ScrapeCoalmines.php');
$proxy = '10.150.0.1:3128';
$noCache = true;


/**
 * Example one - scrape some content using custom content handlers
 */
$baseURL = 'http://freepages.genealogy.rootsweb.ancestry.com/~cmhrc/';
$startURL = 'http://freepages.genealogy.rootsweb.ancestry.com/~cmhrc/lom18.htm';

$scrapeCoalmines = new ScrapeCoalmines($baseURL, $startURL, $proxy, $noCache);
/**
 * Example two - post to a URL
 */
$data = 'email=test@test.com&newslettersignup=1';
$test = Scraper::postDataToURL('http://www.site.com/newslettersignup/', $data, $proxy, $noCache);
var_dump($test);