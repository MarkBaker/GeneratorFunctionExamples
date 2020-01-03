<?php

if (version_compare(PHP_VERSION, '5.6.0') <= 0) {
    die('This example requires at least PHP version 5.6.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/../../GpxTracker/classes/Bootstrap.php';
include __DIR__ . '/../../functions/filter.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/../../data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);


// Create a bounding box defining the coordinates we want to test each point against
// This bounding box is for inside the house/garden
$boundaries = new GpxReader\Helpers\BoundingBox();
$boundaries->setLatitudes(53.54382, 53.54340);
$boundaries->setLongitudes(-2.74059, -2.74005);
// We want to set the filter to include only points inside the bounding box
$boundingBoxFilter = [$boundaries, 'inside'];


// Define the date/time filter parameters
$startTime = new DateTime('2015-11-23 12:00:00Z');
$endTime = new DateTime('2015-11-23 12:20:00Z');
// Create the filter callback with the date/time parameters we've just defined
$timeFilter = function($timestamp) use ($startTime, $endTime) {
    return $timestamp >= $startTime && $timestamp <= $endTime;
};


// Iterate over the trackpoint set from the gpx file, displaying each point detail in turn
//     applying both a time filter (12:00:00 - 12:20:00 on 2015-11-23)
//     and the bounding box filter for inside the house/garden
foreach (filter(filter($gpxReader->getElements('trkpt'), $timeFilter, ARRAY_FILTER_USE_KEY), $boundingBoxFilter)
         as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
