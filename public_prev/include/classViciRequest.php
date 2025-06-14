<?php

/**
Copyright 2014, René Voorburg, rene@digitopia.nl

This file is part of the Vici.org source.

Vici.org source is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Vici.org  source is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class ViciRequest : abstracts the users request for an item object.
 */


class ViciRequest
{
    private
        $allowedModes = array('view', 'new', 'update', 'insert'),
        $allowedModifiers = array('rdf', 'kml');

    private
        $mode,              // ('view', 'new', 'update', 'insert')
        $paramsArr,         // relevant POST and GET vars
        $item,              // item mode refers to
        $modifier;          // modifier for the view mode, eq 'rdf'

    /**
     * @param $postArr
     * @param $getArr
     */
    public function __construct($postArr, $getArr) {

        if (!empty($postArr['request']) && ($postArr['request'] == 'update' || $postArr['request'] == 'insert')) {
            // a form was submitted with a known 'request' token
            $this->mode = $postArr["request"];
        } else {
            // no form, this a "view" of "new" request
            if (empty($_GET['new'])) {
                $this->mode = 'view';

                $idArr = explode("/", $_GET['id']);
                $this->item = $idArr[0];

                if (in_array($idArr[1], $this->allowedModifiers)) {
                    $this->modifier = $idArr[1];
                }

            } else {
                $this->mode = 'new';
            }
        }

        if (! in_array($this->mode, $this->allowedModes)) {
            throw new Exception('Unknown request');
        }


    }

    /**
     * @return string ('view', 'new', 'update', 'insert')
     */
    public function getMode() {
        return $this->mode;
    }

    public function getItem() {
        return $this->item;
    }

    public function getParamsArr() {
        return $this->paramsArr;
    }


    public function getModifier() {
        return $this->modifier;
    }

}