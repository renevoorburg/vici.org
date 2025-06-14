<?php
/**
 * LinePart class provides a basic interface to single line of one site.
 *
 * @package Vici.org
 * @license http://www.gnu.org/licenses/gpl-3.0
 * @author  René Voorburg
 * @version 1.0
 */

class LinePart
{
    private $id = null;
    private $box = array(false, false, false, false); // swBoxLat, swBoxLng, neBoxLat, neBoxLng;
    private $data;

    public function __construct($lineId) {
        $this->id = $lineId;
    }


    /* setters: */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setBox($swLat, $swLng, $neLat, $neLng)
    {
        $this->box = array($swLat, $swLng, $neLat, $neLng);
    }


    /* getters: */
    public function getId()
    {
        return $this->id;
    }

    public function getBox() {
        return $this->box;
    }

    public function getData($swapXY = false)
    {
        if ($swapXY) {
            $linedataArr = json_decode($this->data);
            $swappedData = '[';
            $sep = '';
            foreach ($linedataArr as $point) {
                $swappedData .= $sep."[".$point[1].",".$point[0]."]";
                $sep = ",";
            }
            return $swappedData."]";
        } else {
            return $this->data;
        }
    }

}
