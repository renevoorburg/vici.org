<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 20-01-17
 * Time: 08:07
 */

namespace rdp\shape\write;

use rdp\shape\Line;


interface LineWriterInterface
{

    /**
     * @param PLine $polyline
     *
     * @return mixed
     */
    public function write(Line $polyline);
}