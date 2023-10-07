<?php
    class ShapePoint
    {
        public $lat;
        public $lng;
        public $seq;
 
        public function __construct($lat, $lng, $seq)
        {
            $this->seq = $seq;
            $this->lat = $lat;
            $this->lng = $lng;
        }
    }
?>