<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/../../GpxTracker/classes/Bootstrap.php';
include __DIR__ . '/../../functions/reduce.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/../../data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);

// Set our bounding box callback
$boundaries = new GpxReader\Helpers\BoundingBox();
// Reduce our trackpoint set from the gpx file against the bounding box callback
$boundingBox = reduce(
    $gpxReader->getElements('trkpt'),
    [$boundaries, 'calculate']
);

// Display the results of our reduce
echo 'Bounding box co-ordinates:', PHP_EOL;
printf(
    'Top: %7.4f Bottom: %7.4f' . PHP_EOL .
        'Left: %7.4f Right: %7.4f' . PHP_EOL,
    $boundingBox->top,
    $boundingBox->bottom,
    $boundingBox->left,
    $boundingBox->right
);
