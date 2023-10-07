<?php

/**
 * Copyright 2014, René Voorburg, rene@digitopia.nl
 *
 * This file is part of the Vici.org source.
 *
 * Vici.org source is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Vici.org  source is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
 */

//require_once('geoPHP.inc');

require __DIR__ . '/../vendor/autoload.php';

require_once('classShape.php');
require_once('classShapeReducer.php');

class ReduceLines
{
    private
        $msg = '',
        $noErrors = true,
        $resultArr;

    private function shapeToJSON($shape, $digits)
    {
        $json = '[';
        $sep = "";
        foreach ($shape->points() as $k => $point) {
            $json .= $sep . "[" . substr((string)$point->lat, 0, $digits) . "," . substr((string)$point->lng, 0,
                    $digits) . "]";
            $sep = ",";
        }
        $json .= "]";
        return $json;
    }

    private function geoReduce($curGeom, $i)
    {
        $tolerancesArr = array(
            array("tolerance" => 0.000004, "fromZoom" => 16, "toZoom" => 99),
            array("tolerance" => 0.000016, "fromZoom" => 14, "toZoom" => 16),
            array("tolerance" => 0.000064, "fromZoom" => 12, "toZoom" => 14),
            array("tolerance" => 0.000256, "fromZoom" => 10, "toZoom" => 12),
            array("tolerance" => 0.001024, "fromZoom" => 8, "toZoom" => 10),
            array("tolerance" => 0.004096, "fromZoom" => 6, "toZoom" => 8),
            array("tolerance" => 0.016384, "fromZoom" => 4, "toZoom" => 6)
        );

        // store $curGeom in $origShape:
        $origShape = new Shape();
        for ($j = 1; $j <= $curGeom->numPoints(); $j++) {
            $point = $curGeom->pointN($j);
            $origShape->addPoint(new ShapePoint($point->coords[1], $point->coords[0], $j));
        }

        // calculate $smalShape and fill $resultArr:                 
        $smallShape = new Shape();
        $bBox = $curGeom->getBBox();
        $diagonal = sqrt(pow($bBox['maxx'] - $bBox['minx'], 2) + pow($bBox['maxy'] - $bBox['miny'], 2));
        foreach ($tolerancesArr as $factor) {
            if ($factor["tolerance"] < (2 * $diagonal)) {
                $reducer = new ShapeReducer();
                $smallShape = $reducer->reduceWithTolerance($origShape, $factor["tolerance"]);
                $this->resultArr[$i]['sw_lat'] = $bBox['miny'];
                $this->resultArr[$i]['sw_lng'] = $bBox['minx'];
                $this->resultArr[$i]['ne_lat'] = $bBox['maxy'];
                $this->resultArr[$i]['ne_lng'] = $bBox['maxx'];
                $this->resultArr[$i]['linedata'][] = array(
                    'fromZoom' => $factor['fromZoom'],
                    'toZoom' => $factor['toZoom'],
                    'data' => $this->shapeToJSON($smallShape, 2 + $factor["fromZoom"] / 2)
                );
            } // else line too small for tolerance
        }
    }

    public function __construct($filename, $type = 'kml')
    {

        $this->resultArr = array();

        $file = file_get_contents($filename, true);
        if ($file === false) {
            $this->noErrors = false;
            $this->msg = 'Error: could not read file.';
        }

        if ($this->noErrors) {
            // create geometry object:
            $geom = geoPHP::load($file, $type);

            // handle all line geometries:
            $lineCount = 0;
            if ($geom->geometryType() == "LineString") {
                $lineCount++;
                $this->geoReduce($geom, $lineCount);
            } else {
                // we might have received an array of (line) objects:
                for ($i = 1; $i <= $geom->numGeometries(); $i++) {
                    $curGeom = $geom->geometryN($i);
                    if ($curGeom->geometryType() == "LineString") {
                        $lineCount++;
                        $this->geoReduce($curGeom, $lineCount);
                    }
                } // for
            }

            if ($lineCount == 0) {
                $this->noErrors = false;
                $this->msg = 'Error: no lines in file.';
            }
        } // if($noErrors)

    }

    public function getLines()
    {
        if ($this->noErrors) {
            return $this->resultArr;
        } else {
            return $this->noErrors;
        }
    }

    public function getLastMessage()
    {
        return $this->msg;
    }

}