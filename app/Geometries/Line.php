<?php

/**
 * kind of a mess, now used for creaton of GeoJSON only
 */

namespace Vici\Geometries;

class Line
{
    public $expire;
    public $kind;
    public $points = array();


    public static function kindStr($id) {
        switch ($id) {
            case 1:
                return '"road"';
            case 2:
                return '"aqueduct"';
            case 3:
                return '"canal"';
            case 4:
                return '"wall"';
            case 5:
                return '"other"';
        }
    }
    
    public static function flipLine($line) {
        return array_map(function($point) {
            return [$point[1], $point[0]];
        }, $line);
    }
    
    public static function flipMultiLine($multiLine) {
        return array_map(function($line) {
            $decoded = json_decode($line, true);
            return self::flipLine($decoded ?: []);
        }, $multiLine);
    }

}