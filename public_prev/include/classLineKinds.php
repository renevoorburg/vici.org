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

//$mydir = dirname(__FILE__);
//require_once ($mydir.'/getlang.php');
//require_once ($mydir.'/classDBConnector.php');

class LineKinds 
{

    private $lang; // TODO removed translate part; optionlist should be part of ViciCommmon

    public function __construct($lang = 'en') 
    {   
        $this->lang = $lang;      
    }

    public function optionList($current = 0) 
    {
        // nicer to get these options from the db? :
        $ret = "<option value='2'>Aqueduct</option>";
        $ret.= "<option value='1'>Road</option>";
        $ret.= "<option value='3'>Canal</option>";
        $ret.= "<option value='4'>Wall</option>";
        $ret.= "<option value='5'>Other</option>";
        
        return $ret;    
    }

}
