<?php
require_once 'vendor/autoload.php';

use Goutte\Client;

$dateFuture = date('MY', strtotime(' + 7 days'));
$client = new Client();
$subCrawler = $client->request('GET', 'https://www.forexfactory.com/calendar.php?month=' . $dateFuture);

$currArray = array();
$impactArray = array();
$reportArray = array();
$actualArray = array();
$forecastArray = array();
$previousArray = array();
$timeArray = array();
$dateArray = array();

/**
 * @param array $arr
 * @return array
 */
function fillUpArrayDate(array $arr)
{
    $resArr = array();
    $prev = "";
    foreach ($arr as $key => $v) {
        if ($arr[$key] !== '') {
            $prev = date("d.m.Y", strtotime(substr($arr[$key], 3)));
        }
        array_push($resArr, $prev);
    }
    return $resArr;
}

function fillUpArrayString(array $arr)
{
    $resArr = array();
    $prev = "";
    foreach ($arr as $key => $v) {
        if ($arr[$key] !== '') {
            $prev = $arr[$key];
        }
        array_push($resArr, $prev);
    }
    return $resArr;
}

/**
 * @param array $dateArr
 * @param array $timeArr
 * @return array
 */
function convertDateAndTimeToTimestamp(array $dateArr, array $timeArr)
{
    $resArr = array();
    foreach ($dateArr as $key => $v) {
        if (1 === preg_match('~[0-9]~', $timeArr[$key])) {
            $timestamp = strtotime($dateArr[$key] . " " . $timeArr[$key]);
            // echo $timestamp . "\n";
        } else {
            $timestamp = strtotime($dateArr[$key]);
            // echo $timestamp . "\n";
        }
        array_push($resArr, $timestamp);
    }
    return $resArr;
}

// $currency = $subCrawler->filter('calendar__cell calendar__currency currency ')->text();
$subCrawler->filter('td.calendar__cell.calendar__currency.currency')->each(function ($node) use (&$currArray) {
    $currency = $node->text();
    //if(!trim($currency))
    array_push($currArray, trim($currency));
});

// impact
$subCrawler->filter(' td.calendar__cell.calendar__impact.impact.calendar__impact.calendar__impact')->each(function ($node) use (&$impactArray) {
    // $impact = $node->text();
    if(!empty($node->filter('div.calendar__impact-icon.calendar__impact-icon > span')->extract(array('class')))) {
        $impact = $node->filter('div.calendar__impact-icon.calendar__impact-icon > span')->extract(array('class'));
    } else {
        $impact[0] = "";
    }
    array_push($impactArray, trim($impact[0]));
});

//report
$subCrawler->filter('td.calendar__cell.calendar__event.event > div > span')->each(function ($node) use (&$reportArray) {
    $report = $node->text();
    // if(!trim($report))
    array_push($reportArray, trim($report));
});

//actual
$subCrawler->filter('td.calendar__cell.calendar__actual.actual')->each(function ($node) use (&$actualArray) {
    $actual = $node->text();
    // if(!trim($actual))
    array_push($actualArray, trim($actual));
});

//forecast
$subCrawler->filter('td.calendar__cell.calendar__forecast.forecast')->each(function ($node) use (&$forecastArray) {
    $forecast = $node->text();
    // if(!trim($forecast))
    array_push($forecastArray, trim($forecast));
});

//previous
$subCrawler->filter('td.calendar__cell.calendar__previous.previous')->each(function ($node) use (&$previousArray) {
    $previous = $node->text();
    // if(!trim($previous))
    array_push($previousArray, trim($previous));
});

//time
$subCrawler->filter('td.calendar__cell.calendar__time.time')->each(function ($node) use (&$timeArray) {
    $time = $node->text();
    // if(!trim($previous))
    array_push($timeArray, trim($time));
});

//date
$subCrawler->filter('td.calendar__cell.calendar__date.date')->each(function ($node) use (&$dateArray) {
    $time = $node->text();
    // if(!trim($previous))
    array_push($dateArray, trim($time));
});

$dateArray = fillUpArrayDate($dateArray);
$timeArray = fillUpArrayString($timeArray);

$timestampArr = convertDateAndTimeToTimestamp($dateArray, $timeArray);

//Multi Dimensional Array
$multi = array();
foreach ($currArray as $key => $v) {
    /* if(empty($impactArray[$key])) {
        $impactArray[$key] = "lolonator";
    } */
    $multi[] = [$dateArray[$key], $timeArray[$key], $timestampArr[$key], $currArray[$key], $impactArray[$key], $reportArray[$key], $actualArray[$key], $forecastArray[$key], $previousArray[$key]];
}

// ***************************
// *********Reporting*********
// ***************************
$json_data = json_encode($multi);
file_put_contents('data/forexfactory.json', $json_data);

// to csv
$fp = fopen('data/forexfactory.csv', 'w');
foreach ($multi as $fields) {
    fputcsv($fp, $fields, ";");
}
fclose($fp);

