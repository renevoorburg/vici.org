<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 17-01-17
 * Time: 10:00
 */

namespace rdp;

use \geoPHP;

class Geometry
{
    private $shape;
    private $newShape;
    private $tolerance;


    /**
     * Geometry constructor.
     * @param string $geo
     * @param string $format
     */
    public function __construct($geo, $format = 'wkt')
    {
        $this->shape = geoPHP::load($geo, $format);
    }


    /**
     * @param $zoom
     * @param string $format
     * @return bool|\GeometryCollection|mixed
     */
    public function reduce($zoom, $format = 'wkt')
    {

        if ($this->shape->geometryType() !== "Point") {
            $this->tolerance = pow(0.5, $zoom) / 2;
            $this->newShape = [];

            $this->walk($this->shape);
            return geoPHP::geometryReduce(new \GeometryCollection($this->newShape));
        }
        return $this->shape;
    }


    /**
     * @param \Geometry $shape
     */
    private function walk(\Geometry $shape)
    {
        foreach ($shape->getComponents() as $component ) {

            switch ($component->geometryType()) {
                case "GeometryCollection":
                    $this->walk($component);
                    break;

                case "MultiPoint":
                    $this->walk($component);
                    break;

                case "MultiLineString":
                    $geoPHPLines = [];
                    foreach($component->getComponents() as $geoPHPLine) {
                        $reducedGeoPHPLine = $this->reduceGeoPHPLine($geoPHPLine);
                        if (! is_null($reducedGeoPHPLine)) {
                            $geoPHPLines[] = $reducedGeoPHPLine;
                        }
                    }
                    if (count($geoPHPLines) > 0) {
                        $this->newShape[] = new \MultiLineString($geoPHPLines);
                    }
                    break;

                case "MultiPolygon":
                    $this->walk($component);
                    break;

                case "Point":
                    $this->newShape[] = $component;
                    break;

                case "LineString":
                    $reducedGeoPHPLine = $this->reduceGeoPHPLine($component);
                    if (! is_null($reducedGeoPHPLine)) {
                        $this->newShape[] = $reducedGeoPHPLine;
                    }
                    break;

                case "Polygon":
                    // TODO
                    break;
            }
        }
    }


    /**
     * @param \LineString $geoPHPLine
     * @return \LineString|null
     */
    private function reduceGeoPHPLine(\LineString $geoPHPLine)
    {
        $line = $this->geoPHPLineToLine($geoPHPLine);
        $reducedLine = Reducer::RDP($line, $this->tolerance, true);
        if (! is_null( $reducedLine)) {
            return $this->lineToGeoPHPLine($reducedLine);
        }
       return null;
    }


    /**
     * @param \LineString $geoPHPLine
     * @return Shape\Line
     */
    private function geoPHPLineToLine(\LineString $geoPHPLine)
    {
        $line = new shape\Line();
        $i = 0;
        foreach ($geoPHPLine->getComponents() as $geoPHPPoint) {
            $line->addPoint(new shape\Point($geoPHPPoint->y(), $geoPHPPoint->x(), $i), false);
            $i++;
        }
        return $line;
    }


    /**
     * @param Shape\Line $line
     * @return \LineString
     */
    private function lineToGeoPHPLine(shape\Line $line)
    {
        $geoPHPPoints = [];
        foreach ($line->getPoints() as $point) {
            $geoPHPPoints[] = new \Point($point->getLng(), $point->getLat());
        }
        return new \LineString($geoPHPPoints);

    }

}