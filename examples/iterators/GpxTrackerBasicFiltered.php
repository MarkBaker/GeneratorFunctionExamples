<?php

if (version_compare(PHP_VERSION, '5.6.0') <= 0) {
    die('This example requires at least PHP version 5.6.0' . PHP_EOL . '    but you are only running version ' . PHP_VERSION . PHP_EOL);
}

include __DIR__ . '/../../GpxTracker/classes/Bootstrap.php';


// Create our initial Generator to read the gpx file
$gpxFilename = __DIR__ . '/../../data/Roman_2015-11-23.gpx';
$gpxReader = new GpxReader\GpxHandler($gpxFilename);


// Define the date/time filter parameters
$startTime = new DateTime('2015-11-23 13:20:00Z');
$endTime = new DateTime('2015-11-23 13:30:00Z');

class DateRangeFilter extends FilterIterator
{
    private $startTime;
    private $endTime;
   
    public function __construct(Iterator $iterator, DateTime $startTime, DateTime $endTime)
    {
        parent::__construct($iterator);
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }
   
    public function accept()
    {
        $timestamp = $this->getInnerIterator()->key();

        return $timestamp >= $this->startTime && $timestamp <= $this->endTime;
    }
}


// Iterate over the trackpoint set from the gpx file, displaying each point detail in turn
foreach (new DateRangeFilter($gpxReader->getElements('trkpt'), $startTime, $endTime)
         as $time => $element) {
    printf(
        '%s' . PHP_EOL . '    latitude: %7.4f longitude: %7.4f elevation: %d' . PHP_EOL,
        $time->format('Y-m-d H:i:s'),
        $element->position->latitude,
        $element->position->longitude,
        $element->elevation
    );
}
