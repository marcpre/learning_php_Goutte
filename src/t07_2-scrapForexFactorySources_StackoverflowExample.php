<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

function updateCalendarDetailsData()
{
    try {
        $client = new Client();

        $x = 1;
        $LIMIT = 3;
        global $x;
        global $LIMIT;
        $x++;
        $res1Array = array();

        $ffUrlArr = ["https://www.forexfactory.com/calendar.php?month=Jan2020"];
        foreach ($ffUrlArr as $key => $v) {

            try {
                $crawler = $client->request('GET', $ffUrlArr[$key]);
            } catch (\Exception $ex) {
                error_log($ex);
            }

            $TEMP = array();

            $count = $crawler->filter('.calendar_row')->count();
            $i = 1; // count starts at 1
            $crawler->filter('.calendar_row')->each(function ($node) use ($count, $i, &$res1Array) {
                $EVENT = array();

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

                $subcrawler->filter('.calendarspecs__spec')->each(function ($LEFT_TD) use (&$res1Array, &$TEMP, &$EVENT) {

                    $LEFT_TD_INNER_TEXT = trim($LEFT_TD->text());

                    if ($LEFT_TD_INNER_TEXT == "Source") {

                        $TEMP = array();
                        $LEFT_TD->nextAll()->filter('a')->each(function ($LINK) use (&$TEMP) {
                            array_push($TEMP, $LINK->text(), $LINK->attr('href'));
                        });

                        $EVENT['sourceTEXT'] = $TEMP[0];
                        $EVENT['sourceURL'] = $TEMP[1];
                        $EVENT['latestURL'] = $TEMP[3];
                    }

                    if ($LEFT_TD_INNER_TEXT == "Measures") {
                        $EVENT['measures'] = $LEFT_TD->nextAll()->text();
                    }

                    if ($LEFT_TD_INNER_TEXT == "Usual Effect") {
                        $EVENT['usual_effect'] = $LEFT_TD->nextAll()->text();
                    }

                    if ($LEFT_TD_INNER_TEXT == "Frequency") {
                        $EVENT['frequency'] = $LEFT_TD->nextAll()->text();
                    }

                    if ($LEFT_TD_INNER_TEXT == "Why Traders") {
                        $EVENT['why_traders_care'] = $LEFT_TD->nextAll()->text();
                    }

                    if ($LEFT_TD_INNER_TEXT == "Derived Via") {
                        $EVENT['derived_via'] = $LEFT_TD->nextAll()->text();
                        // array_push($res1Array, $EVENT); // <---- HERE I GET THE ERROR!
                    }
                });
                $i++;
                if ($i > $count) {
                    echo "<pre>";
                    var_dump($res1Array);
                    print_r($res1Array);
                    echo "</pre>";
                    exit;
                }
            });
        }
    } catch (\Exception $ex) {
        error_log($ex);
    }
    return $res1Array;
}

updateCalendarDetailsData();