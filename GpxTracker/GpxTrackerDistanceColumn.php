<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}


include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/map.php';
include __DIR__ . '/functions/column.php';

$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';


$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$distanceCalculator = new GpxReader\Helpers\DistanceCalculator();

foreach (column(map( [$distanceCalculator, 'setDistance'], $gpxReader->getElements('trkpt')), 'distance') as $key => $value) {
    printf(
        '%d => %5.2f' . PHP_EOL,
        $key,
        $value
    );
}
