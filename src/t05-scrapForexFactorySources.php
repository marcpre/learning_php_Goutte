<?php
require 'vendor/autoload.php';
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;


$x = 1;
$LIMIT = 10;

$client = new Client();
$crawler = $client->request('GET', 'https://www.forexfactory.com/calendar.php?month=nov.2019');




$crawler->filter('.calendar_row')->each(function ($node) {
    global $x;
    global $LIMIT;
    $x++;


    $EVENTID   = $node->attr('data-eventid');
    $EVENTNAME = $node->filter('.event')->first()->filter('div')->first()->text();
    echo "<h4>".$EVENTNAME."</h4><br>";

    $API_RESPONSE = file_get_contents('https://www.forexfactory.com/flex.php?do=ajax&contentType=Content&flex=calendar_mainCal&details='.$EVENTID);

    $API_RESPONSE = str_replace("<![CDATA[","",$API_RESPONSE);
    $API_RESPONSE = str_replace("]]>","",$API_RESPONSE);

    $html = <<<HTML
<!DOCTYPE html>
<html>
    <body>
       $API_RESPONSE
    </body>
</html>
HTML;

    $subcrawler = new Crawler($html);



    $subcrawler->filter('.calendarspecs__spec')->each(function ($LEFT_TD) {

        $LEFT_TD_INNER_TEXT = trim($LEFT_TD->text());

        if($LEFT_TD_INNER_TEXT == "Source" || $LEFT_TD_INNER_TEXT == "Usual Effect"){
            echo $LEFT_TD_INNER_TEXT.": ".$LEFT_TD->nextAll()->html()."<br>";
        }

    });

    if($x>$LIMIT)
        exit;
    echo "<hr>";

});