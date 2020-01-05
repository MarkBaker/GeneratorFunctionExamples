<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/../../GpxTracker/classes/Bootstrap.php';
include __DIR__ . '/../../functions/filter55.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/../../data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Create a bounding box defining the coordinates we want to test each point against
$boundaries = new GpxReader\Helpers\BoundingBox();
$boundaries->setLatitudes(53.54382, 53.54340);
$boundaries->setLongitudes(-2.74059, -2.74005);
// We want to set the filter to include only points inside the bounding box
$boundingBoxFilter = [$boundaries, 'inside'];


// Iterate over the trackpoint set from the gpx file, displaying each point detail in turn
foreach (new CallbackFilterIterator($gpxReader->getElements('trkpt'), $boundingBoxFilter)
         as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}

