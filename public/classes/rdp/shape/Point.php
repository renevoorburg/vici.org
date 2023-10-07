<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 17-01-17
 * Time: 18:39
 */

namespace rdp\shape;


class Point
{
    protected $lat;
    protected $lng;
    protected $seq;
    protected $lngC; // longitude corrected for latitude to use as 'x' for distance calculations;

    public function __construct($lat, $lng, $seq)
    {
        $this->seq = $seq;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->lngC = cos(deg2rad($this->lat)) * $this->lng;
    }

    public function getSeq() {
        return $this->seq;
    }

    public function getLat() {
        return $this->lat;
    }

    public function getLng() {
        return $this->lng;
    }

    public function getLngAsX() {
        return $this->lng;
    }
}