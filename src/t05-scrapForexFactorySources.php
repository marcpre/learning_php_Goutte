<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

$x = 1;
$LIMIT = 20;
// $t = date(MY);
// $dateFuture = strtotime("+7 day", date(MY));

/**
 * @param Crawler $crawler
 */
function crawlForexFactoryDetails()
{
    $dateFuture = date('MY', strtotime(' + 7 days'));
    $client = new Client();
    $crawler = $client->request('GET', 'https://www.forexfactory.com/calendar.php?month=' . $dateFuture);

    $resArray = array();
    $TEMP = array();
    $crawler->filter('.calendar_row')->each(function ($node) {
        global $x;
        global $LIMIT;
        global $resArray;
        global $TEMP;
        $x++;

        $EVENTID = $node->attr('data-eventid');

        $API_RESPONSE = file_get_contents('https://www.forexfactory.com/flex.php?do=ajax&contentType=Content&flex=calendar_mainCal&details=' . $EVENTID);

        $API_RESPONSE = str_replace("<![CDATA[", "", $API_RESPONSE);
        $API_RESPONSE = str_replace("]]>", "", $API_RESPONSE);

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
            global $resArray;
            global $TEMP;
            $LEFT_TD_INNER_TEXT = trim($LEFT_TD->text());

            if ($LEFT_TD_INNER_TEXT == "Source") {

                $TEMP = array();
                $LEFT_TD->nextAll()->filter('a')->each(function ($LINK) {
                    global $TEMP;
                    array_push($TEMP, $LINK->text(), $LINK->attr('href'));
                });

                $EVENT['sourceTEXT'] = $TEMP[0];
                $EVENT['sourceURL'] = $TEMP[1];
                $EVENT['latestURL'] = $TEMP[3];

                array_push($resArray, $EVENT);
            }

        });

        if ($x > $LIMIT) {
            echo "<pre>";
            var_dump($resArray);
            echo "</pre>";
            exit;
        }

    });
    return $resArray;
}

crawlForexFactoryDetails();