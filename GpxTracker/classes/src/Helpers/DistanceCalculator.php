<?php

namespace GpxReader\Helpers;

class DistanceCalculator {
    /**
     *  @var   \GpxReader\GpxElement|null  $previousPoint  The previous trackpoint element, from which we calculate the distance
     *                                                         or null if there is no previous trackpoint
     **/
    protected $previousPoint = null;

    /**
     *  Calculate the distance between two lat/long co-ordinates using the Haversine formula
     *
     *  @param   float  $fromPoint      Latitude/Longitude of the trackpoint to calculate the distance from
     *  @param   float  $toPoint        Latitude/Longitude of the trackpoint to calculate the distance to
     *  @param   float  $earthRadius    Earth mean radius in the appropriate unit of measure (default is meters)
     *  @return  float  The distance between the current and the previous trackpoints
     **/
    protected function haversine($fromPoint, $toPoint, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latitudeFrom = deg2rad($fromPoint->latitude);
        $longitudeFrom = deg2rad($fromPoint->longitude);
        $latitudeTo = deg2rad($toPoint->latitude);
        $longitudeTo = deg2rad($toPoint->longitude);

        $latitudeDelta = $latitudeTo - $latitudeFrom;
        $longitudeDelta = $longitudeTo - $longitudeFrom;

        $angle = 2 * asin(sqrt(pow(sin($latitudeDelta / 2), 2) +
            cos($latitudeFrom) * cos($latitudeTo) * pow(sin($longitudeDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    /**
     *  Calculate the distance between the current and previous trackpoints
     *
     *  @param   \GpxReader\GpxElement  $point  The trackpoint to calculate the distance from the previous trackpoint
     *  @return  float  The distance between the current and the previous trackpoints
     **/
    protected function calculateDistance(\GpxReader\GpxElement $point) {
        if ($this->previousPoint === null) {
            $distance = 0.0;
        } else {
            $distance = $this->haversine($this->previousPoint->position, $point->position);
        }
        // The current trackpoint now becomes our previous trackpoint
        $this->previousPoint = $point;

        return $distance;
    }

    /**
     * Calculate the distance between the current and previous trackpoints, and set it as a new property for the current trackpoint
     *
     *  @param   \GpxReader\GpxElement  $point  The trackpoint to calculate the distance from the previous trackpoint
     *  @return  \GpxReader\GpxElement  The trackpoint with a new distance property added
     **/
    function setDistance(\GpxReader\GpxElement $point) {
        $point->distance = $this->calculateDistance($point);
        return $point;
    }
}
