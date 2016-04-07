<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/map.php';
include __DIR__ . '/functions/reduce.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);


// Set the mapper to calculate the distance between a trackpoint and the previous trackpoint
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

// Reduce our trackpoint set from the gpx file (mapping the distance as we go) and summing the results to calculate the total distance travelled
$totalDistance = reduce(
    map( [$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')),
    function($runningTotal, $value) {
        $runningTotal += $value->distance;
        return $runningTotal;
    },
    0.0
);

// Display the results of our reduce
printf(
    'Total distance travelled is %5.2f km' . PHP_EOL,
    $totalDistance / 1000
);
