<?php

require_once '../vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;


// fix crawler - https://stackoverflow.com/questions/70402267/goutte-get-list-with-date-on-top-and-title-below
try {

    $resArr = array();
    $tempArr = array();

    $url = "https://www.steelcitycollectibles.com/product-release-calendar";

    // get page
    $client = new Client();
    $content = $client->request('GET', $url)->html();
    $crawler = new Crawler($content, null, null);

    $table = $crawler->filter('#schedule');

    // use today's date as a default, in case first one is missing
    $releaseDate = (new DateTime())->format("m/d/y");
    $table->filter('div')
        ->each(function (Crawler $tr) use (&$index, &$resArr, &$releaseDate) {
            if ($tr->filter('.schedule-date')->count() > 0) {
                // update the date if it exists, otherwise continue with the old one
                $releaseDate = $tr->filter('.schedule-date')->text();
            }
            if ($tr->filter('div > div.eight.columns > a')->count() > 0) {
                $releaseStr = $tr->filter('div > div.eight.columns > a')->text();
                $resArr[] = [$releaseDate, $releaseStr];
            }
        });

    var_dump($resArr);
} catch (Exception $e) {}