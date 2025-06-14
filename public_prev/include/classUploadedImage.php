<?php

/**
 * Copyright 2013-2018, René Voorburg, rene@digitopia.nl
 *
 * 2018-03-23
 *  fix: flip width and height if exif asks us to
 *
*/

require_once(dirname(__FILE__).'/classDBConnector.php');

class UploadedImage 
{
    const IMAGEBASE = '/var/www/org.vici.static/public/',
          IMAGEDIR = 'uploads',
          DFLTLICENSE = 4; // CCBY-SA

    private $error = false,
            $errormsg = '',
            $db,
            $siteid,
            $path,
            $filename,
            $md5sum,
            $userid,
            $width,
            $height,
            $exif = null,
            $lat = null,
            $lng = null,
            $title,
            $description,
            $lang,
            $license = self::DFLTLICENSE,
            $ownwork = true,
            $owner = null,
            $attribute = null,
            $source = null;
            
                 
    private function Slug($string, $slug = '-', $extra = null)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, $this->Unaccent($string)), $slug));
    }

    private function Unaccent($string) // normalizes (romanization) accented chars
    {
        if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
            $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
        }
        return $string;
    }

    public function __construct($path, $filename, $userid, $siteid = null)
    {

        //echo "path: $path fn: $filename";

            $this->path = trim($path);
            $this->filename =  $this->Slug($filename, '_', '.'); // normalize characters
            $this->md5sum = md5_file($path);
            $this->userid = $userid;
            $this->siteid = $siteid;
            
            $this->db = new DBConnector();
            $this->db->set_charset("utf8");
            
            // ensure image is new (for this user at least):
            $result = $this->db->query("SELECT imgd_imgid FROM img_data WHERE imgd_md5sum='{$this->md5sum}' AND imgd_uploader=$userid");
            $this->error = ($result->num_rows > 0);
            
            if ($this->error) {
                $this->errormsg = 'Duplicate image, already uploaded using this account.<br/>';
            } else {
            
                // ensure unique filename:
                if (file_exists(self::IMAGEBASE.self::IMAGEDIR.'/'.$this->filename) === true) {
                    $this->filename = $this->md5sum.'_'.$this->filename;
                }

                // obtain image size:
                $size = getimagesize($this->path);
                $image = new Imagick($this->path);
                $orientation = $image->getImageOrientation();

                // test if w & h need to be flipped:
                if (($orientation == imagick::ORIENTATION_RIGHTTOP) || ($orientation == imagick::ORIENTATION_LEFTBOTTOM)) {
                    // flip
                    $this->width = $size[1];
                    $this->height = $size[0];
                } else {
                    $this->width = $size[0];
                    $this->height = $size[1];
                }

                // obtain exif string and coordinats:
                $this->exif = '';
                $exif_ifd0 = read_exif_data($this->path ,'IFD0' ,0);       
                $exif_exif = read_exif_data($this->path ,'EXIF' ,0);
                $exif = read_exif_data($this->path ,0, true);
                if (@array_key_exists('Make', $exif_ifd0)) {
                    $this->exif .= "Camera: ".$exif_ifd0['Make'];
                    if (@array_key_exists('Model', $exif_ifd0)) {
                        $this->exif .= ", ".$exif_ifd0['Model'];
                    }
                    $this->exif .= "<br/>";
                } 
                if (@array_key_exists('ExposureTime', $exif_ifd0)) {
                    $this->exif .= "Exposure: ".$exif_ifd0['ExposureTime']."<br/>";
                } 
                if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
                    $this->exif .=  "Aperture: ".$exif_ifd0['COMPUTED']['ApertureFNumber']."<br/>";
                }
                if (@array_key_exists('ISOSpeedRatings',$exif_exif)) {
                    $this->exif .= "ISO: ".$exif_exif['ISOSpeedRatings']."<br/>";
                } 
                if (@array_key_exists('GPS', $exif) && @array_key_exists('GPSLatitudeRef', exif['GPS']) && @array_key_exists('GPSLongitudeRef', exif['GPS'])) {
                    $lat_ref = $exif['GPS']['GPSLatitudeRef'];
                    $lat = $exif['GPS']['GPSLatitude'];
                    list($num, $dec) = explode('/', $lat[0]);
                    $lat_s = $num / $dec;
                    list($num, $dec) = explode('/', $lat[1]);
                    $lat_m = $num / $dec;
                    list($num, $dec) = explode('/', $lat[2]);
                    $lat_v = $num / $dec;

                    $lng_ref = $exif['GPS']['GPSLongitudeRef'];
                    $lng = $exif['GPS']['GPSLongitude'];
                    list($num, $dec) = explode('/', $lng[0]);
                    $lng_s = $num / $dec;
                    list($num, $dec) = explode('/', $lng[1]);
                    $lng_m = $num / $dec;
                    list($num, $dec) = explode('/', $lng[2]);
                    $lng_v = $num / $dec;

                    $lat_int = ($lat_s + $lat_m / 60.0 + $lat_v / 3600.0);
                    $lng_int = ($lng_s + $lng_m / 60.0 + $lng_v / 3600.0);
                
                    $this->lat = ($lat_ref == "S") ? '-' . $lat_int : $lat_int;
                    $this->lng = ($lng_ref == "W") ? '-' . $lng_int : $lng_int;
                    $this->exif .= "Location: ".$this->lat.", ".$this->lng."<br/>";
                } 
            } // $this->error
                          
        }
    
        public function setDescription($title, $description , $lang = 'en') 
        {
            $this->title = strip_tags(trim($title));
            $this->description = strip_tags(trim($description));
            $this->lang = $lang;
        }  
    
        public function setLicense($owner, $license, $attribute, $source)
        {
            $this->ownwork = false;
            $this->owner = strip_tags(trim($owner));
            $this->license = $license;
            $this->attribute = strip_tags(trim($attribute), '<a><b>');
            $this->source = strip_tags(trim($source));
        
        }

        public function store()
        {   
            // begin commit - rollback sequence:
            $result = $this->db->query('begin');
            
            $commitable = !$this->error;
            
            // save ref to image:
            if ($commitable) {
                $newfilepathname = '/'.self::IMAGEDIR.'/'.$this->filename;
                $result = $this->db->query("INSERT INTO images VALUES (NULL, '$newfilepathname', 0)");
                $imageid = $this->db->insert_id;
                $commitable = ($this->db->errno == 0);
            }

            // save image data:
            if ($commitable) {
                $esc_source = $this->db->real_escape_string($this->source);
                $esc_owner = $this->db->real_escape_string($this->owner);
                $esc_attribute = $this->db->real_escape_string($this->attribute);
                $esc_title = $this->db->real_escape_string($this->title);
                $esc_description = $this->db->real_escape_string($this->description);
                $esc_exif = $this->db->real_escape_string($this->exif);
                if (is_null($this->lat) || is_null($this->lng)) {
                    $result = $this->db->query("INSERT INTO img_data VALUES ($imageid, {$this->userid}, ".(int)$this->ownwork.", {$this->license}, '$esc_source', '$esc_owner', '$esc_attribute', NULL, {$this->width}, {$this->height}, NULL, NULL, '$esc_title', '$esc_description', NULL, '$esc_exif', '{$this->lang}', '{$this->md5sum}')");
                } else {
                    $result = $this->db->query("INSERT INTO img_data VALUES ($imageid, {$this->userid}, ".(int)$this->ownwork.", {$this->license}, '$esc_source', '$esc_owner', '$esc_attribute', NULL, {$this->width}, {$this->height}, '{$this->lat}', '{$this->lng}', '$esc_title', '$esc_description', NULL, '$esc_exif', '{$this->lang}', '{$this->md5sum}')");
                }
                $commitable = ($this->db->errno == 0);     
            }
            
            // save image - site link:
            if ($commitable) {
                // see if a default link to the image (for the given site) exists:
                $result = $this->db->query("SELECT pil_img FROM pnt_img_lnk WHERE pil_dflt=1 AND pil_pnt={$this->siteid}");
                $isdfltimage = ($result->num_rows > 0) ? 0 : 1;
                
                $result = $this->db->query("INSERT INTO pnt_img_lnk VALUES ({$this->siteid}, $imageid, $isdfltimage)");
                $commitable = ($this->db->errno == 0);
            }
            
            // write data and file:
            if ($commitable) {
                $this->db->query('commit');
                
                rename($this->path, self::IMAGEBASE.self::IMAGEDIR.'/'.$this->filename);
                chmod(self::IMAGEBASE.self::IMAGEDIR.'/'.$this->filename, 0644);
                
            } else {
                $this->db->query('rollback');
                $this->error = true;
                $this->errormsg .= 'Oops... could not save records.<br/>';
            }
            
        }
        
        public function error()
        {
            return $this->error;
        }
        
        public function errorMsg()
        {
            return $this->errormsg;
        }
}