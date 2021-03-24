<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$MARKETBEAT_START_URL = "https://www.americanconsumernews.net/scripts/click.aspx?SponsorshipID=";

$dateFuture = date('MY', strtotime(' + 7 days'));
$client = new Client();

$offerArray = array();

for ($i = 28000; $i <= 30300; $i++) {
    try {

        $url = $MARKETBEAT_START_URL . $i;
        $subCrawler = $client->request('GET', $url);
        $redirectUrl = $client->getHistory()->current()->getUri();

        echo $i . " - URL: " . $redirectUrl . "\n";

        array_push($offerArray, [$i,$redirectUrl]);

    } catch (\Exception $ex) {
        error_log($ex);
    }
}

// ***************************
// *********Reporting*********
// ***************************

// json
$json_data = json_encode($offerArray);
file_put_contents('data/marketbeat.json', $json_data);

// to csv
array2csv($offerArray);

/*
$fp = fopen('data/marketbeat.csv', 'w');
foreach ($offerArray as $fields) {
    fputcsv($fp, $fields, ";");
}
fclose($fp);
*/


function array2csv($data, $delimiter = ';', $enclosure = '"', $escape_char = "\\")
{
    $f = fopen('data/marketbeat.csv', 'w');
    foreach ($data as $item) {
        fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
    }
    rewind($f);
    return stream_get_contents($f);
}