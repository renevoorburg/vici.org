<?php

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