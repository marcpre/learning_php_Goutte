<?php
/**
DOES NOT WORK FOR JAVASCRIPT
 */


require_once '../vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

try {

    $resArr = array();
    $tempArr = array();

    $url = "https://www.psacard.com/auctionprices/soccer-cards/1989-panini-calciatori/diego-armando-maradona/values/2448415";

    // get page
    $client = new Client();
    $content = $client->request('GET', $url)->html();
    $crawler = new Crawler($content, null, null);
    $table = $crawler->filter('#itemResults')->first()->closest('table');

    $table->filter('tr')
        ->each(function (Crawler $tr) use (&$firm, &$resArr, &$tempArr) {

            $lotUrl = $tr->filter("td > a")->attr('href');

            array_push($resArr, $lotUrl);

        });

    // crawl sub page
    foreach ($resArr as $el) {
        $subContent = $client->request('GET', $el)->html();
        $crawler = new Crawler($subContent, null, null);

        $img = $crawler->filter('#mainContent > div:nth-child(2) > div > div.col-xs-12.col-sm-4.col-sm-pull-8.padding-all.text-center > div > a > img')->attr('src');
        $item_name = $crawler->filter('#mainContent > div:nth-child(2) > div > div.col-xs-12.col-sm-8.col-sm-push-4.padding-all > h1')->text();

        array_push($resArr, [$item_name, $img]);

    }

    var_dump($resArr);
} catch (Exception $e) {
    report($e);
}


function checkNullAddArr($val, $key, $tempArr)
{
    if (!is_null($val)) {
        $tempArr[$key] = $val;
        $val = null;
    }
    return array($tempArr, $val);
}

function addScrappedLinkToArr(Crawler $tr, $scrapVal)
{
    if (strpos($tr->text(), $scrapVal) !== false) {
        $val = "https://edikte.justiz.gv.at" . trim($tr->filter('td > a')->attr("href"));
        return $val;
    }
}

function addScrappedTextToArr(Crawler $tr, $scrapVal)
{
    $label = "";
    $field = "";

    if ($tr->filter('td')->count() >= 2) {

        if ($tr->filter('td.tlabel')->count() > 0) {
            $label = $tr->filter('td.tlabel')->text();
            $field = $tr->filter('td.ttext')->text();
            echo $label . "\n";
        }

        if ($tr->filter('td.flabel')->count() > 0) {
            $label = $tr->filter('td.flabel')->text();
            $field = $tr->filter('td.ftext')->text();
            echo $label . "\n";
        }

        if (strpos($label, $scrapVal) !== false) {
            $val = trim(str_replace([$scrapVal], "", $field));
            return $val;
            // array_push($resArr, $val);
        }
    }
    // return $arr;
}
