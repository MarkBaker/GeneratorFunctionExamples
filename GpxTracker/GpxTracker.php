<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}


include __DIR__ . '/classes/Bootstrap.php';

$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';


$gpxReader = new GpxReader\GpxHandler($gpxFilename);

foreach ($gpxReader->getElements('trkpt') as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
