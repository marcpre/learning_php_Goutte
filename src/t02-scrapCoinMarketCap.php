<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();
$cssSelector = 'tr';
$coin = 'td.no-wrap.currency-name > a';
$url = 'td.no-wrap.currency-name > a';
$symbol = 'td.text-left.col-symbol';
$price = 'td:nth-child(5) > a';

//arrays
$coinArr = array();
$urlArr = array();
$symbolArr = array();
$priceArr = array();

$crawler = $client->request('GET', 'https://coinmarketcap.com/all/views/all/');

$crawler->filter($coin)->each(function ($node) use (&$coinArr) {
    print $node->text()."\n";
    array_push($coinArr, $node->text());
});

$crawler->filter($url)->each(function ($node) use (&$urlArr) {
    $link = $node->link();
    $uri = $link->getUri();
    print $uri."\n";
    array_push($urlArr, $uri);
});

$crawler->filter($symbol)->each(function ($node) use (&$symbolArr) {
    print $node->text()."\n";
    array_push($symbolArr, $node->text());
});

$crawler->filter($price)->each(function ($node) use (&$priceArr) {
    print $node->text()."\n";
    array_push($priceArr, $node->text());
});

//Multi Dimensional Array
$multi = array();
foreach($coinArr as $key => $v) {
    $multi[] = [$coinArr[$key], $urlArr[$key], $symbolArr[$key], $priceArr[$key]];
}
$json_data = json_encode($multi);
file_put_contents('data/myfile.json', $json_data);    
    
