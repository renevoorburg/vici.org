<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 20-01-17
 * Time: 11:18
 */

namespace rdp\shape\write;

use rdp\shape\Line;


class LineWriterGeoJSON implements LineWriterInterface
{

    /**
     * @param Line $polyline
     *
     * @return mixed
     */
    public function write(Line $polyline)
    {

        $points = [];

        foreach ($polyline->getPoints() as $point) {
            $points[] = [$point->getLng(), $point->getLat()];
        }

        return json_encode(
            [
                'type'        => 'LineString',
                'coordinates' => $points,
            ]
        );
    }
}