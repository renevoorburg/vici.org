<?php

require_once (dirname(__FILE__).'/classDBConnector.php');
require_once (dirname(__FILE__).'/classSite.php');

class SiteData extends Site 
{
    protected 
        $altLang = '', 
        $altText = '',
        $imgArr = array();
    
    public function __construct($lang = 'en', $id = null) 
    {     
        // get Site from DB:
        parent::__construct($lang, $id);
    
        // get aditional data:
        $sql = "SELECT ptxt_lang, ptxt_full FROM ptexts WHERE ptxt_pnt_id=".$this->id." AND ptxt_lang <> '".$lang."' ORDER BY LENGTH(ptxt_full) LIMIT 1";
        $result = $this->db->query($sql);
        if ($result->num_rows == 1 ) {
            $object = $result->fetch_object();
            $this->altLang = $object->ptxt_lang;
            $this->altText = $object->ptxt_full;
        }
        $result->free(); 
        
        // get pictures:
        $sql = "SELECT img_id, img_path, imgd_title, imgd_description, pil_dflt, acc_realname, imgd_ownwork, imgd_creator, imgd_attribution, imgd_date, license_short FROM pnt_img_lnk 
            LEFT JOIN images ON img_id=pil_img
            LEFT JOIN img_data ON imgd_imgid=pil_img
            LEFT JOIN licenses ON imgd_license=license_id
            LEFT JOIN accounts ON imgd_uploader=acc_id
            WHERE img_hide=0 AND pil_pnt=".$this->id." ORDER BY pil_dflt DESC";
            $result = $this->db->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $this->imgArr[] = array ("id" => $row["img_id"], 
                    "path" => $row["img_path"], 
                    "title" => $row["imgd_title"],
                    "description" => $row["imgd_description"],
                    "default" => $row["pil_dflt"],
                    "uploader" => $row["acc_realname"],
                    "ownwork" => $row["imgd_ownwork"],
                    "creator" => $row["imgd_creator"],
                    "attribution" => $row["imgd_attribution"],
                    "date" => $row["imgd_date"],
                    "license" => $row["license_short"]
                );
            }
        $result->free(); 
    }
    
    public function getAltLang() 
    {
        return $this->altLang;
    }
    
    public function getAltText() 
    {
        return $this->altText;
    }
    
    public function getImgArr()
    {
        return $this->imgArr;
    }
    
}