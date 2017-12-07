<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();
$cssSelector = 'tr';
$coin = 'td.no-wrap.currency-name > a';
$url = 'td.no-wrap.currency-name > a';
$symbol = 'td.text-left.col-symbol';
$price = 'td:nth-child(5) > a';

$result = array();

$crawler = $client->request('GET', 'https://coinmarketcap.com/all/views/all/');

$crawler->filter($coin)->each(function ($node) use (&$result) {
    print $node->text()."\n";
    array_push($result, $node->text());
});

$crawler->filter($url)->each(function ($node) use (&$result) {
    $link = $node->link();
    $uri = $link->getUri();
    print $uri."\n";
    array_push($result, $uri);
});

$crawler->filter($symbol)->each(function ($node) use (&$result) {
    print $node->text()."\n";
    array_push($result, $node->text());
});

$crawler->filter($price)->each(function ($node) use (&$result) {
    print $node->text()."\n";
    array_push($result, $node->text());
});

print_r($result);

$json_data = json_encode($result);
file_put_contents('data/myfile.json', $json_data);    
    
