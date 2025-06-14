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

$mydir = dirname(__FILE__);
require_once $mydir.'/classLang.php'; // todo; removed translation ; restore level higher
require_once $mydir.'/classDBConnector.php';

class SiteKinds {

    private $kindsArr,
            $lang,
            $lngObj;

    /**
     * @param Lang $lngObj
     */
    public function __construct(Lang $lngObj) {
        $this->lngObj = $lngObj;
        $this->lang = $lngObj->getLang();
    
        $this->db = new DBConnector();
        $this->db->set_charset("utf8");
        
        $result = $this->db->query("SELECT pkind_id, pkind_low, pkind_high, pkind_zindex, pkind_name, pkind_group, pkind_sort, pkind_line FROM pkinds ORDER BY pkind_group, pkind_sort");
        
        while ($obj = $result->fetch_object()) {
            $this->kindsArr[$obj->pkind_id] = $obj;
        }
        $result->close();        
    }

    public function optionList($current = 0) 
    {
        $ret = '';
        $group = 0;
        foreach($this->kindsArr as &$obj) {
            if ($obj->pkind_group <> $group) {
                $group = $obj->pkind_group;
                $ret .= "<option disabled>".$this->lngObj->str("category ".$group).":</option>";
            }
            $ret .= '<option value="'.$obj->pkind_id.'" ';  
            if ($obj->pkind_id == $current) {  $ret .= 'selected="selected"'; };    
            $ret .= '>';
            $ret .= $this->lngObj->str($obj->pkind_name);
            $ret .= '</option>';
        }
        return $ret;    
    }

    public function getIds() 
    {
        foreach($this->kindsArr as &$obj) {
            $ret[] = $obj->pkind_id;
        }
        return $ret;
    }  

    public function getName($id) 
    {
        return $this->kindsArr[$id]->pkind_name;
    }  
    
    public function getSmallZoom($id) 
    {
        return $this->kindsArr[$id]->pkind_low;
    }  
    
    public function getBigZoom($id) 
    {
        return $this->kindsArr[$id]->pkind_high;
    }

}