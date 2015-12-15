<?php

namespace GpxReader;

class GpxElement {
    /**
     *  @var  \GpxReader\GpxLatLong  $position  Lat/Long position
     **/
    public $position;

    /**
     *  @var  float  $elevation  Elevation above the surfec of the WGS84 reference ellipsoid
     **/
    public $elevation = 0;

    /**
     *  @var  \DateTime  $timestamp  Timestamp
     **/
    public $timestamp;
}
