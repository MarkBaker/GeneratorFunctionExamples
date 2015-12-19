<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/filter55.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Create a bounding box defining the coordinates we want to test each point against
$boundaries = new GpxReader\Helpers\BoundingBox();
$boundaries->setLatitudes(54.496, 54.492);
$boundaries->setLongitudes(-3.010, -3.000);
// We want to set the filter to include only points inside the bounding box
$boundingBoxFilter = [$boundaries, 'inside'];


// Iterate over the trackpoint set from the gpx file, displaying each point detail in turn
foreach (filter($gpxReader->getElements('trkpt'), $boundingBoxFilter) as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}

