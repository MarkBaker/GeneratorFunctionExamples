<?php

if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
    die('This example requires at least PHP version 5.5.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/classes/Bootstrap.php';
include __DIR__ . '/functions/withGenerator.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/data/Roman_2015-11-23.gpx';
$gpxReader = new \GpxReader\GpxHandler($gpxFilename);
$trackPoints = $gpxReader->getElements('trkpt');


// Create a bounding box defining the coordinates we want to test each point against
// This bounding box is for inside the house/garden
$boundaries = new \GpxReader\Helpers\BoundingBox();
$boundaries->setLatitudes(53.54382, 53.54340);
$boundaries->setLongitudes(-2.74059, -2.74005);
// We want to set the filter to include only points inside the bounding box
$boundingBoxFilter = [$boundaries, 'inside'];


// Define the date/time filter parameters
$startTime = new \DateTime('2015-11-23 12:00:00Z');
$endTime = new \DateTime('2015-11-23 12:20:00Z');
// Create the filter callback with the date/time parameters we've just defined
$timeFilter = function($timestamp) use ($startTime, $endTime) {
    return $timestamp >= $startTime && $timestamp <= $endTime;
};


// Set the mapper to calculate the distance between a trackpoint and the previous trackpoint
$distanceCalculator = new \GpxReader\Helpers\DistanceCalculator();


// We'll use a callback for the display as well
$display = function($time, $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL .
            '    distance from previous point:  %5.2f m' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation,
        $element->distance
    );
};


//  Create an instance of the fluent Generator Helper and set our filters, mapper offset and limits
//    and the display callback to "do" (or execute)
//  In this case, the "do" is a display callback, but it could also be a "reduce" callback
withGenerator($trackPoints)
    ->filteredBy($boundingBoxFilter)
    ->filteredBy($timeFilter, ARRAY_FILTER_USE_KEY)
    ->map([$distanceCalculator, 'setDistance'])
	->offset(1)
	->limit(1)
    ->do($display);
