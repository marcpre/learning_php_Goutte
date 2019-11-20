<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client();
$resArr = array();

$response = file_get_contents('https://www.forexfactory.com/flex.php?do=ajax&contentType=Content&flex=calendar_mainCal&details=107072');
$response = str_replace("<![CDATA[", "", $response);
$response = str_replace("]]>", "", $response);

$html = <<<HTML
<!DOCTYPE html>
<html>
    <body>
       $response
    </body>
</html>
HTML;

$subcrawler = new Crawler($html);
$subcrawler->filter('.calendarspecs__spec')->each(function ($node) use (&$resArr) {

    $innerText = trim($node->text());

    if ($innerText == "Source" || $innerText == "Usual Effect") {

        $sourceURL = $node->filter('a')->first()->link(); // error
        $relatedURL = $node->filter('a')->first()->link(); // error
        $source = $node->filter('a')->first()->text(); // error
        $related = $node->filter('a')->first()->text(); // error

        //echo $innerText . ": " . $node->nextAll()->html() . "\n";
        array_push($resArr, [$sourceURL, $source, $relatedURL, $related]);
    }
});

