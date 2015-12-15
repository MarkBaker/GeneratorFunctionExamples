<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}


include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/reduce.php';

$gpxFilename = __DIR__ . '/data/GpxTrackData1.gpx';


$gpxReader = new GpxReader\GpxHandler($gpxFilename);
$boundaries = new GpxReader\Helpers\BoundingBox();

$boundingBox = reduce(
    $gpxReader->getElements('trkpt'),
    [$boundaries, 'calculate']
);

echo 'Bounding box co-ordinates:', PHP_EOL;
printf(
    'Top: %7.4f Bottom: %7.4f' . PHP_EOL .
        'Left: %7.4f Right: %7.4f' . PHP_EOL,
    $boundingBox->top,
    $boundingBox->bottom,
    $boundingBox->left,
    $boundingBox->right
);
