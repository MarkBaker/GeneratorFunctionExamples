<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}


include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/map.php';
include __DIR__ . '/functions/reduce.php';

$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';


$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

$totalDistance = reduce(
    map( [$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')),
    function($runningTotal, $value) {
        $runningTotal += $value->distance;
        return $runningTotal;
    },
    0.0
);

printf(
    'Total distance travelled is %5.2f km' . PHP_EOL,
    $totalDistance / 1000
);
