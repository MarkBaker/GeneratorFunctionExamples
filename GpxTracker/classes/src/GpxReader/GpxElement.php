<?php

namespace GpxReader;

class GpxElement {
    /**
     *  @var    \GpxReader\GpxLatLong    $position    Latitude/Longitude position
     **/
    public $position;

    /**
     *  @var    float    $elevation    Elevation above the surface of the WGS84 reference ellipsoid
     **/
    public $elevation = 0;

    /**
     *  @var    \DateTime    $timestamp    Timestamp
     **/
    public $timestamp;
}
