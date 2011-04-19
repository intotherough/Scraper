<?php
require_once('classes/ScrapeCoalmines.php');

$baseURL = 'http://freepages.genealogy.rootsweb.ancestry.com/~cmhrc/';
$startURL = 'http://freepages.genealogy.rootsweb.ancestry.com/~cmhrc/lom18.htm';
$proxy = '10.150.0.1:3128';
$noCache = true;

$scrapeCoalmines = new ScrapeCoalmines($baseURL, $startURL, $proxy, $noCache);
