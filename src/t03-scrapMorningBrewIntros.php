<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();

$url = 'body > div.container > div > div > ul.list-group.mb-5 > a';

//arrays
$resultArr = array();
$urlArr = array();

$fp = fopen('data/morningbrew.csv', 'w');

// 23
for ($i = 1; $i <= 23; $i++) {
    $crawler = $client->request('GET', 'https://www.morningbrew.com/archive?newsletter=daily&page=' . $i);
    $urlArr = array();
    $crawler->filter($url)->each(function ($node) use (&$urlArr) {
        $link = $node->link();
        $uri = $link->getUri();
        array_push($urlArr, $uri);
    });
    foreach ($urlArr as $key => $v) {
        try {

            $subCrawler = $client->request('GET', $urlArr[$key]);
            $intro = $subCrawler->filter('.pcard')
                ->filter('table:nth-child(4)')
                ->text();
            $date = $subCrawler->filter('.pcard')
                ->filter('table:first-child')
                ->filter('td:first-child')
                ->text();
            // if (!empty($intro) || !eympty($date)) {
            print("##########################################");
            print(trim($intro) . " - " . trim($date) . " - " . $urlArr[$key] . "\n");
            // fputcsv($fp, [ trim($intro), trim($date), $urlArr[$key]]);
            array_push($resultArr,  [ trim($intro), trim($date), $urlArr[$key]]);
            // }
        } catch (Exception $e) {
            // Node list is empty
            print($e);
        }
    }
}
$json_data = json_encode($resultArr);
file_put_contents('data/morningbrew.json', $json_data);
print("##########################################");
print("DONE!");

/**
 * Example
 *
 *
 * $subCrawler = $client->request('GET', 'https://www.morningbrew.com/daily/2019/11/07');
 * $intro = $subCrawler->filter('.pcard')
 * ->filter('table:nth-child(4)')
 * ->text();
 * $date = $subCrawler->filter('.pcard')
 * ->filter('table:first-child')
 * ->filter('td:first-child')
 * ->text();
 * array_push($introArr, trim($intro), trim($date));
 */