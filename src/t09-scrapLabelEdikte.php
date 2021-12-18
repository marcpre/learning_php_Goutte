<?php
require_once '../vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;

try {

    $resArr = array();
    $tempArr = array();

    $url = "https://edikte.justiz.gv.at/edikte/ex/exedi3.nsf/0/19dd135274ceb842c12586390028507e?OpenDocument&f=1&bm=2";

    // get page
    $client = new Client();
    $content = $client->request('GET', $url)->html();
    $crawler = new Crawler($content, null, null);
    $table = $crawler->filter('#diveddoc > div:nth-child(2) > table')->first()->closest('table');

    $table->filter('tr')
        ->each(function (Crawler $tr) use (&$firm, &$resArr, &$tempArr) {

            $val = addScrappedTextToArr($tr, 'PLZ/Ort:');
            list($tempArr, $val) = checkNullAddArr($val, "plz_ort", $tempArr);

            $val = addScrappedTextToArr($tr, 'Objektgröße:');
            list($tempArr, $val) = checkNullAddArr($val, "objektGroesse", $tempArr);

        });

    array_push($resArr, $tempArr);

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