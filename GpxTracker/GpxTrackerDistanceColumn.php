<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/map.php';
include __DIR__ . '/functions/column.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Set the mapper to calculate the distance between a trackpoint and the previous trackpoint
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

// Iterate over the trackpoint set from the gpx file, mapping the distance and extracting only that property to display
foreach (column(map( [$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')), 'distance') as $key => $value) {
    printf(
        '%d => %5.2f' . PHP_EOL,
        $key,
        $value
    );
}
