<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();
$cssSelector = 'tr';
$coin = 'td.no-wrap.currency-name > a';
$url = 'td.no-wrap.currency-name > a';
$symbol = 'td.text-left.col-symbol';
$price = 'td:nth-child(5) > a';
$img = 'body > div.container > div > div.col-lg-10 > div:nth-child(5) > div.col-xs-6.col-sm-4.col-md-4 > h1 > img';

//arrays
$coinArr = array();
$urlArr = array();
$symbolArr = array();
$priceArr = array();
$imgArr = array();

$crawler = $client->request('GET', 'https://coinmarketcap.com/all/views/all/');

$crawler->filter($coin)->each(function ($node) use (&$coinArr) {
//    print $node->text()."\n";
    array_push($coinArr, $node->text());
});

$crawler->filter($url)->each(function ($node) use (&$urlArr) {
    $link = $node->link();
    $uri = $link->getUri();
//    print $uri."\n";
    array_push($urlArr, $uri);
});

$crawler->filter($symbol)->each(function ($node) use (&$symbolArr) {
//    print $node->text()."\n";
    array_push($symbolArr, $node->text());
});

$crawler->filter($price)->each(function ($node) use (&$priceArr) {
//    print $node->text()."\n";
    array_push($priceArr, $node->text());
});

// get Links from Subpages
print "Start SubPages";

foreach ($urlArr as $key => $v) {
// for ($key=0; $key < 2; $key++) { 
    
    $subCrawler = $client->request('GET', $urlArr[$key]);

    $image = $subCrawler->filter($img)->extract(array('src')); //->each(function ($node) use (&$imgArr) {
    print_r($image[0] . "\n");  
    //$link = $image[0]->link();
    $uri = $image[0];
    array_push($imgArr, $uri);
    //});

}

//Multi Dimensional Array
$multi = array();
foreach ($coinArr as $key => $v) {
    $multi[] = [$coinArr[$key], $imgArr[$key], $urlArr[$key], $symbolArr[$key], $priceArr[$key]];
}
$json_data = json_encode($multi);
file_put_contents('data/myfile1.json', $json_data);

