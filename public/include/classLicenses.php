<?php

/**
Copyright 2013-2014, René Voorburg, rene@digitopia.nl

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

require_once(dirname(__FILE__).'/classDBConnector.php');

class Licenses {

    private $licensesArr;

    public function __construct($lang = 'en', $inclRightsReserved = false) 
    {   
        $this->lang = $lang;
        $this->db = new DBConnector();
        $this->db->set_charset("utf8");

        $extraSql = $inclRightsReserved ? '' : 'where license_uploadable=1 '; 

        $result = $this->db->query("select license_id, license_short, license_url from licenses $extraSql order by license_short");
        
        while ($obj = $result->fetch_object()) {
            $this->licensesArr[$obj->license_id] = $obj;
        }
        $result->close();        
    }

    public function optionList($current = 0) 
    {
        $ret = "";
        foreach($this->licensesArr as &$obj) {
            $ret .= '<option value="'.$obj->license_id.'" ';  
            if ($obj->license_id == $current) {  $ret .= 'selected="selected"'; };    
            $ret .= '>';
            $ret .= $obj->license_short;
            $ret .= '</option>';
        }
        return $ret;    
    }


    public function getUrl($id) 
    {
        return $this->licensesArr[$id]->license_url;
    }  


}