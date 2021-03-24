<?php

require 'vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class CalendarParser
{

    const BASE_URL = 'https://www.forexfactory.com/calendar.php?month=%s';

    /**
     * @var
     */
    private $client;

    /**
     * @var DateTime
     */
    private $calendarMonth;

    /**
     * @var Crawler
     */
    private $page;

    /**
     * @var Crawler
     */
    private $table;

    /**
     * @var array
     */
    private $dateIndexes;

    /**
     * CalendarParser constructor.
     *
     * @param DateTime $calendarMonth
     * @throws Exception
     */
    public function __construct(DateTime $calendarMonth)
    {
        $this->client = new Client();
        $this->calendarMonth = $calendarMonth;

        // Fetch page and table data and store it so we can iterate over it.
        $this->page = $this->client->request('GET', sprintf(self::BASE_URL, $this->calendarMonth->format('MY')));
        $this->table = $this->page->filter('.calendar_row');

        // Get date indexes
        $this->generateDateIndexes();
    }

    /**
     * The table uses a class called `newday` at each new date which can be used to create an index of
     * where the date records begin which makes parsing easier.
     */
    private function generateDateIndexes()
    {
        $dateIndexes = [];

        $previousDate = null;
        $this->table
            /**
             * NOTE: This is a closure function which will be called until the foreach completes.
             *       You cannot break out of it like when you do `foreach() { break; }`.
             *       If you do `return` - it will simply skip executing the rest of the function but won't break the cycle.
             */
            ->each(function (Crawler $node, $index) use (&$dateIndexes, &$previousDate) {
                $isNewDateSeparator = strpos($node->getNode(0)->getAttribute('class'), 'newday') !== false;

                if ($isNewDateSeparator) {
                    // Convert the date to `Jan-1-STARTING_YEAR` to be easier to search in the array.
                    $dateColumnNode = $node->filter('.date > span > span');
                    $stringDate = str_replace(' ', '-', $dateColumnNode->text()) . '-' . $this->calendarMonth->format('Y');
                    $date = date_create_from_format('M-d-Y', $stringDate);
                    $formattedDate = $date->format('Y-m-d');

                    $dateIndexes[$formattedDate] = [
                        'start' => $index,
                        'end'   => null
                    ];

                    if ($previousDate) {
                        $dateIndexes[$previousDate]['end'] = ($index - 1);
                    }

                    $previousDate = $formattedDate;
                }
            });

        $this->dateIndexes = $dateIndexes;
    }

    /**
     * @param Crawler $row
     * @return array
     */
    private function processEvent(DateTime $date, Crawler $row)
    {
        $eventId = $row->attr('data-eventid');

        $event = [
            'eventId'          => $eventId,
            'date'             => $date->format('Y-m-d'),
            'sourceTEXT'       => null,
            'sourceURL'        => null,
            'latestURL'        => null,
            'measures'         => null,
            'usual_effect'     => null,
            'derived_via'      => null,
            'why_traders_care' => null,
            'frequency'        => null
        ];

        $content = $this->client->request('GET', 'https://www.forexfactory.com/flex.php?do=ajax&contentType=Content&flex=calendar_mainCal&details=' . $eventId)
            ->html();
        $crawler = new Crawler($content, null, null);

        $table = $crawler->filter('.calendarspecs__spec')->first()->closest('table');

        $table->filter('tr')
            ->each(function (Crawler $tr) use (&$event) {
                $label = $tr->filter('.calendarspecs__spec')->text();

                $description = $tr->filter('.calendarspecs__specdescription');

                if ($label === 'Source') {
                    $TEMP = [];
                    $description->filter(' a')
                        ->each(function ($link) use (&$TEMP) {
                            array_push($TEMP, $link->text(), $link->attr('href'));
                        });

                    $event['sourceTEXT'] = $TEMP[0];
                    $event['sourceURL'] = $TEMP[1];
                    $event['latestURL'] = $TEMP[3];
                }

                if ($label == "Measures") {
                    $event['measures'] = $description->text();
                }

                if ($label == "Usual effect") {
                    $event['usual_effect'] = $description->text();
                }

                if ($label == "Frequency") {
                    $event['frequency'] = $description->text();
                }

                // this is how it's returned.
                if ($label == "Why TradersCare") {
                    $event['why_traders_care'] = $description->text();
                }

                if ($label == "derived via") {
                    $event['derived_via'] = $description->text();
                }

            });

        return $event;
    }

    /**
     * Get the events between a start and end date.
     * If no endDate is defined - then it will get all events since $startDate.
     *
     * @param DateTime $startDate
     * @param DateTime|null $endDate
     *
     * @return array
     */
    public function getEventsBetweenDates(DateTime $startDate, DateTime $endDate = null)
    {
        $events = [];

        $totalCalendarRows = $this->table->count();
        foreach ($this->dateIndexes as $stringDate => $range) {
            $date = date_create_from_format('Y-m-d', $stringDate);

            // Process only the range from the start date
            if ($date >= $startDate) {
                // and break early when we reach the end.
                if ($endDate && $date > $endDate) {
                    break;
                }

                // collect and process events for the current date
                $start = $range['start'];
                $end = $range['end'] !== null ? $range['end'] : $totalCalendarRows;
                for ($i = $start; $i < $end; $i++) {
                    $events[] = $this->processEvent($date, new Crawler($this->table->getNode($i)));
                }
            }
        }

        return $events;
    }

}

$parser = new CalendarParser(date_create());

var_dump(
    $parser->getEventsBetweenDates(
        date_create_from_format('Y-m-d H:i:s', '2020-01-03 00:00:00'),
        date_create_from_format('Y-m-d H:i:s', '2020-01-08 23:59:59')
    )
);