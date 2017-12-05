<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$client = new Client();

$crawler = $client->request('GET', 'http://coinmarketcap.com/');

$crawler->filter('td.no-wrap.currency-name > a')->each(function ($node) {
    print $node->text()."\n";
});