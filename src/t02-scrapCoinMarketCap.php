<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();
$cssSelector = 'tr';
$coin = 'td.no-wrap.currency-name > a';
$url = 'td.no-wrap.currency-name > a';
$symbol = 'td.text-left.col-symbol';
$price = 'td:nth-child(5) > a';



$crawler = $client->request('GET', 'https://coinmarketcap.com/all/views/all/');
/*
$crawler->filter($coin)->each(function ($node) {
    print $node->text()."\n";
});
*/
$crawler->filter($url)->each(function ($node) {
    print $node->text()."\n";
});

/*
$crawler->filter($symbol)->each(function ($node) {
    print $node->text()."\n";
});

$crawler->filter($price)->each(function ($node) {
    print $node->text()."\n";
});
*/
