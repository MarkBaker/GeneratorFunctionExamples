<?php

namespace GpxReader\Helpers;

class BoundingBox {
    /**
     *  @var   float  $top  The most northerly point
     **/
    public $top = -90.0;

    /**
     *  @var   float  $bottom  The most southerly point
     **/
    public $bottom = 90.0;

    /**
     *  @var   float  $left  The most westerly point
     **/
    public $left = 180.0;

    /**
     *  @var   float  $right  The most easterly point
     **/
    public $right = -180.0;

    /**
     * Set latitude bounds when using a bounding box filter
     *
     *  @param  float  $top     Northern boundary
     *  @param  float  $bottom  Southern boundary
     **/
    public function setLatitudes($top = 90.0, $bottom = -90.0) {
        $this->top = $top;
        $this->bottom = $bottom;
    }

    /**
     * Set longitude bounds when using a bounding box filter
     *
     *  @param  float  $left   Western boundary
     *  @param  float  $right  Eastern boundary
     **/
    public function setLongitudes($left = -180, $right = 180.0) {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     *  Identify whether a trackpoint falls inside the defined bounding box
     *
     *  @param  \GpxReader\GpxElement  The trackpoint with a new distance property added
     *  @return boolean  If the trackpoint falls outside (false) or inside (true) the bounding box
     **/
    public function inside(\GpxReader\GpxElement $point) {
        return (($point->position->longitude >= $this->left) && ($point->position->longitude <= $this->right) &&
            ($point->position->latitude >= $this->bottom) && ($point->position->latitude <= $this->top));
    }

    /**
     *  Identify whether a trackpoint falls outside the defined bounding box
     *
     *  @param  \GpxReader\GpxElement  The trackpoint with a new distance property added
     *  @return boolean  If the trackpoint falls outside (true) or inside (false) the bounding box
     **/
    public function outside(\GpxReader\GpxElement $point) {
        return (($point->position->longitude < $this->left) || ($point->position->longitude > $this->right) ||
            ($point->position->latitude < $this->bottom) || ($point->position->latitude > $this->top)
        );
    }

    /**
     * Calculate the top-,bottom-,left- and right-most most bounds of all points
     *
     *  @param   BoundingBox            $discard  $this gets passed back in to itself by the reduce logic, but as all the
     *                                                 bounding data is maintained here in properties anyway, we don't need
     *                                                 the reference, so we simply ignore it
     *  @param   \GpxReader\GpxElement  $point    The trackpoint to calculate the distance from the previous trackpoint
     *  @return  \GpxReader\GpxElement  The trackpoint with a new distance property added
     **/
    function calculate($discard, \GpxReader\GpxElement $point) {
        $this->top = max($point->position->latitude, $this->top);
        $this->bottom = min($point->position->latitude, $this->bottom);
        $this->left = min($point->position->longitude, $this->left);
        $this->right = max($point->position->longitude, $this->right);

        return $this;
    }
}
