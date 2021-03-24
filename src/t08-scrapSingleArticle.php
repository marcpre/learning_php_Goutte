<?php
require_once 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

$url = "https://seekingalpha.com/news/3539376-pais-c-band-plan-gets-majority-support-fcc?utm_source=feed_news_all&utm_medium=referral";

$client = new Client();
$content = $client->request('GET', $url)->html();
$crawler = new Crawler($content, null, null);
$arti = $crawler->filter("#bullets_ul")->first();

// how to get the content from the $crawler


