<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/../../GpxTracker/classes/Bootstrap.php';
include __DIR__ . '/../../functions/map.php';

// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/../../data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);


// Set the mapper to calculate the distance between a trackpoint and the previous trackpoint
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

// Iterate over the trackpoint set from the gpx file, mapping the distances as we go, displaying each point detail in turn
foreach (map( [$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')) as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL .
            '    distance from previous point:  %5.2f m' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation,
        $element->distance
    );
}
