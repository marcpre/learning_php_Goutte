<?php

require_once '../vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

try {

    $resArr = array();
    $tempArr = array();

    $url = "https://www.steelcitycollectibles.com/product-release-calendar";

    // get page
    $client = new Client();
    $content = $client->request('GET', $url)->html();
    $crawler = new Crawler($content, null, null);

    $table = $crawler->filter('#schedule'); //->first()->closest('table');

    $index = 0;
    $resArr = array();
    $table->filter('div')
        ->each(function (Crawler $tr) use (&$index, &$resArr) {

            if ($tr->filter('.schedule-date')->count() > 0) {
                $releaseDate = $tr->filter('.schedule-date')->text();
            }

            if ($tr->filter('div > div.eight.columns > a')->count() > 0) {
                $releaseStr = $tr->filter('div > div.eight.columns > a')->text();
                array_push($resArr, [$releaseDate, $releaseStr]);
            }

        });

    var_dump($resArr);
} catch (Exception $e) {}