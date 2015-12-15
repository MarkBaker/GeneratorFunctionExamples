<?php

if (version_compare(PHP_VERSION, '5.6.0') <= 0) {
    die('This example requires at least PHP version 5.6.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}


include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/filter.php';

$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';


$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Set the filter parameters
$startTime = new DateTime('2012-03-02 13:20:00Z');
$endTime = new DateTime('2012-03-02 13:30:00Z');
// Create the filter callback
$timeFilter = function($timestamp) use ($startTime, $endTime) {
    return $timestamp >= $startTime && $timestamp <= $endTime;
};

foreach (filter($gpxReader->getElements('trkpt'), $timeFilter, ARRAY_FILTER_USE_KEY) as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
