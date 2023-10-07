<?php

/**
This class is for creating, reading and updating a 'site'. 
Although a site can have representations in different languages, this class can handle only one language at a time.
*/

require_once (dirname(__FILE__).'/classDBConnector.php');

use ExtIds\ExternalIdentifiers;

class Site 
{
    protected DBConnector $db;
    protected string $lastError = '';
    private bool $isNewSite;

    protected int $id ;
    private string $name;
    private int $kind;
    private bool $isVisible = true;
    private float $lat;
    private float $lng;
    private string $defaultSummary;

    private int $metaRecId;
    // id
    private int $locationAccuracy = 1;
    private int $creatorId;
    private int $editorId;
    private string $createDate;
    private string $editDate;
    // locked
    private string $extids;
    // pleiades, livius, romoq, dare, mithraeum
    private ExtIds\ExternalIdentifiers $extIdsObj ; // derived; see above
    private int $startYear;
    private int $endYear = 0;
    private string $startYearStr;
    private string $endYearStr;

    private int $sumRecId;
    // id
    private string $language;
    private string $title;
    private string $summary;

    private int $txtRecId;
    // id
    // lang
    private string $annotation;
    // editor
    // edit_date
    // locked

    private string $creatorUserName;
    private string $creatorName;
    private string $editorUserName;
    private string $editorName;

    private string $now;

    public function __construct($lang = 'en', $id = null) 
    {   
        $this->db = new DBConnector();

        $this->language = $lang;
        $this->now = date('Y-m-d H:i:s');

        if ($id) {
            $this->isNewSite = false;
            $this->id = (int)$id;

            $sql = "SELECT pnt_id, pnt_name, pnt_kind, pnt_visible, pnt_lat, pnt_lng, pnt_dflt_short, "
                .       "psum_id, psum_pnt_name, psum_short, ptxt_full, pmeta_id, pmeta_loc_accuracy, "
                .       "pmeta_pleiades, pmeta_romaq, pmeta_livius, pmeta_dare, "
                .       "pmeta_extids, pmeta_locked, editor_id, editor_name, editor_realname, ptxt_edit_date, "
                .       "creator_id, creator_name, creator_realname, pmeta_create_date, updater_id, updater_name, updater_realname, "
                .       "pmeta_edit_date, ptxt_locked, ptxt_id, pmeta_startyr, pmeta_endyr, pmeta_startyr_str, pmeta_endyr_str "
                . "FROM points " 
                . "LEFT JOIN (SELECT * FROM ptexts WHERE ptxt_lang='$lang') AS t ON pnt_id = ptxt_pnt_id "
                . "LEFT JOIN (SELECT * FROM psummaries WHERE psum_lang='$lang') AS x ON pnt_id = psum_pnt_id "
                . "LEFT JOIN pmetadata ON pnt_id = pmeta_pnt_id "
                . "LEFT JOIN (SELECT acc_id as editor_id, acc_name as editor_name, acc_realname as editor_realname FROM accounts) "
                .       "AS u ON ptxt_editor = editor_id "   
                . "LEFT JOIN (SELECT acc_id as creator_id, acc_name as creator_name, acc_realname as creator_realname FROM accounts) "
                .       "AS v ON pmeta_creator = creator_id "
                . "LEFT JOIN ( SELECT acc_id as updater_id, acc_name as updater_name, acc_realname as updater_realname FROM accounts) "
                .       "AS w ON pmeta_editor = updater_id "
                . "WHERE pnt_id=$id";
            
            $result = $this->db->query($sql);
            if ($result->num_rows < 1 ) {
                throw new Exception('Place not Found');
            }
            $object = $result->fetch_object();

            $this->name             = (string)$object->pnt_name;
            $this->kind             = (int)$object->pnt_kind;
            $this->isVisible        = (bool)$object->pnt_visible;
            $this->lat              = (float)$object->pnt_lat;
            $this->lng              = (float)$object->pnt_lng;
            $this->defaultSummary   = (string)$object->pnt_dflt_short;

            $this->metaRecId        = (int)$object->pmeta_id;
            $this->locationAccuracy = (int)$object->pmeta_loc_accuracy;
            $this->extids           = (string)$object->pmeta_extids;
            $this->startYear        = (int)$object->pmeta_startyr;
            $this->endYear          = (int)$object->pmeta_endyr;
            $this->startYearStr     = (string)$object->pmeta_startyr_str;
            $this->endYearStr       = (string)$object->pmeta_endyr_str;

            $this->sumRecId         = (int)$object->psum_id;
            $this->title            = empty($object->psum_pnt_name) ? $this->name : (string)$object->psum_pnt_name;
            $this->summary          = empty($object->psum_short) ? $this->defaultSummary : (string)$object->psum_short;
            $this->annotation       = (string)$object->ptxt_full;

            $this->txtRecId         = (int)$object->ptxt_id;

            $this->creatorId        = (int)$object->creator_id;
            $this->creatorUserName  = (string)$object->creator_name;
            $this->creatorName      = (string)$object->creator_realname;
            $this->createDate       = (string)$object->pmeta_create_date;

            if ( $object->ptxt_edit_date > $object->pmeta_edit_date) {
                $this->editorId       = (int)$object->editor_id;
                $this->editDate       = (string)$object->ptxt_edit_date;
                $this->editorUserName = (string)$object->editor_name;
                $this->editorName     = (string)$object->editor_realname;
            } else {
                $this->editorId       = (int)$object->updater_id;
                $this->editDate       = (string)$object->pmeta_edit_date;
                $this->editorUserName = (string)$object->updater_name;
                $this->editorName     = (string)$object->updater_realname;
            }
            $result->free();
            $this->extIdsObj        = ExtIds\ExternalIdentifiers::withDbParams($this->extids, (string)$object->pmeta_pleiades, (string)$object->pmeta_livius, (string)$object->pmeta_romaq, (string)$object->pmeta_dare);
        } else {
            // create a fresh site, do not load from db:
            $this->isNewSite = true;
            $this->creatorId = 0;
            $this->title = '';
            $this->summary = '';
            $this->annotation = '';
            $this->createDate = $this->now;
            $this->extIdsObj = ExtIds\ExternalIdentifiers::withDbParams('');
            $this->startYear = 0;
        }
    }

    public function getId() : ?int
    {
        return $this->id ?? null;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getKind() : int
    {
        return $this->kind;
    }
    
    public function getIsVisible() : bool
    {
        return $this->isVisible;
    }
    
    public function getLat() : float
    {
        return $this->lat;
    }
    
    public function getLng() : float
    {
        return $this->lng;
    }

    public function getLocationAccuracy() : int
    {
        return $this->locationAccuracy;
    }

    public function getExtIdsObj() : ExternalIdentifiers
    {
        return $this->extIdsObj;
    }

    public function setExtIdsObj(array $urlsAndTagsArr) : void
    {
        $this->extIdsObj = ExternalIdentifiers::withUrlsAndTagsArray($urlsAndTagsArr);
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getSummary() : string
    {
        return $this->summary;
    }
    
    public function getAnnotation() : string
    {
        return $this->annotation;
    }

    public function getStartYear() : string
    {
        if ($this->startYear === 0) {
            return '';
        }
        return (string)$this->startYear;
    }

    public function getStartYearStr() : string
    {
        if (empty($this->startYearStr)) {
            return $this->getStartYear();
        } else {
            return $this->startYearStr;
        }
    }

    public function getEndYearStr() : string
    {
        if ($this->endYear == 9999) {
            return 'open';
        } elseif ($this->endYear == 0) {
            return 'unknown';
        } else {
            if ($this->endYearStr) {
                return $this->endYearStr;
            } else {
                return (string)$this->endYear;
            }
        }
    }

    public function getCreatorId() : int
    {
        return $this->creatorId;
    }
    
    public function getCreatorUserName() : string
    {
        return $this->creatorUserName;
    }
    
    public function getCreatorName() : string
    {
        return $this->creatorName;
    }
    
    public function getCreateDate() : string
    {
        return substr($this->createDate, 0, 10);
    }
    
    public function getEditDate() : string
    {
        return substr($this->editDate, 0, 10);
    }
    
    public function getEditorId() : int
    {
        return $this->editorId;
    }
    
    public function getEditorUserName() : string
    {
        return $this->editorUserName;
    }
    
    public function getEditorName() : string
    {
        return $this->editorName;
    }
    
    public function getLastError() : string
    {
        return $this->lastError;
    }

    public function getPeriodStr() : string
    {
        $periodStr = '';
        if ($this->startYear) {

            if ($this->startYearStr) {
                $periodStr .= $this->startYearStr;

                if ($this->endYearStr != $this->startYearStr) {
                    if ($this->endYear > date("Y") ) {
                        $endYearStr = '';
                    } else {
                        $endYearStr = $this->endYearStr;
                    }
                    $periodStr .= " / " . $endYearStr;
                }

            } else {
                $periodStr .= "".$this->startYear;
                if ($this->endYear != $this->startYear) {
                    if ($this->endYear > date("Y") ) {
                        $endYearStr = '';
                    } elseif ($this->endYear == 0) {
                        $endYearStr = 'unknown';
                    } else {
                        $endYearStr = "".$this->endYear;
                    }
                    $periodStr .= " / " . $endYearStr;
                }
            }
        }
        return $periodStr;
    }

    private function setName(string $name) : bool
    {
        $this->name = $name;
        return true;
    }

    public function setIsVisible(bool $isVisible) : bool
    {
        $this->isVisible = $isVisible;
        return true;
    }
    
    public function setTitle(string $title) : bool
    {
        $title = preg_replace('/(^\s*)|(\s*$)/', '', $title);
        $title = preg_replace('/_/', ' ', $title);
        $title = preg_replace('/\//', '-', $title);
        
        if (strlen($title) < 3) {
            $this->lastError .= "Error: Title should be at least 3 characters.<br>";
            return false;
        }
        $this->title = $title;
        
        if ($this->isNewSite) {
            // we need to set the name too
            return ($this->setName($title));
        }
        return true; 
    }
    
    public function setSummary(string $summary) : bool
    {
        $summary = preg_replace('/(^\s*)|(\s*$)/', '', $summary);
        if (strlen($summary) < 5) {
            $this->lastError .= "Error: Summary should be at least 5 characters.<br>";
            return false;
        }
        $this->summary = $summary;
        if ($this->isNewSite) {
            $this->defaultSummary = $this->summary;
        }
        return true;
    }
    
    public function setAnnotation(string $annotation) : bool
    {
        $this->annotation = preg_replace('/(^\s*)|(\s*$)/', '', $annotation);
        return true;
    }
    
    public function setKind(int $kind) : bool
    {
        $this->kind = $kind;
        return true;
    }

    public function setLat(string $lat) : bool
    {
        $this->lat = $lat;
        return true;
    }
    
    public function setLng(string $lng) : bool
    {
        $this->lng = $lng;
        return true;
    }
    
    public function setCoords(string $coordsStr) : bool
    {
        $coordsArr = explode(",", $coordsStr); 
        $this->setLat((float)$coordsArr[0]);
        $this->setLng((float)$coordsArr[1]); 
        return true;
    }
      
    public function setLocationAccuracy(string $locationAccuracy) : bool
    {
        $this->locationAccuracy = (int)$locationAccuracy;
        return true;
    }

    public function setStartYear($year) : void
    {
        $this->startYearStr = trim($year);

        // earliest variant:
        if ((strlen($this->startYearStr) > 0) && ($this->startYearStr[0] == '-')) {
            $yeartmp = preg_replace('/x/', '9', $this->startYearStr);
        } else  {
            $yeartmp = preg_replace('/x/', '0', $this->startYearStr);
        }

        $this->startYear = (int)preg_replace('/[?~]*/', '', $yeartmp);

        if ($this->startYear === 0 && $this->kind != 8  && $this->kind != 18 && $this->kind != 19) {
            $this->lastError .= "Error: A valid (initial) year is required.<br>";
        }
    }

    public function setEndYear($year) : void
    {
        $year = trim($year);

        if ($this->kind === 8 || $this->kind  === 18 || $this->kind  === 19) {
            $this->endYearStr = '';
            $this->endYear = 9999;
        } else {
            if ($year === 'open') {
                $this->endYearStr = 'open';
                $this->endYear = 9999;
            } elseif ($year === 'unknown') {
                $this->endYearStr = 'unknown';
                $this->endYear = 0;
            } elseif ($year === '') {
                $this->lastError .= "Error: Please supply a valid end year.<br>";
            } else {
                $this->endYearStr = trim($year);

                // latest variant:
                if ($this->endYearStr[0] == '-') {
                    $yeartmp = preg_replace('/x/', '0', $this->endYearStr);
                } else {
                    $yeartmp = preg_replace('/x/', '9', $this->endYearStr);
                }

                $this->endYear = (int)preg_replace('/[?~]*/', '', $yeartmp);

                if ($this->endYear < $this->startYear) {
                    $this->lastError .= "Error: The end year cannot be before the initial year.<br>";
                }

                if ($this->endYear === 0) {
                    $this->lastError .= "Error: Please supply a valid end year or use 'open' or 'unknown'.<br>";
                }
            }
        }
    }

    public function save(int $userId = 1) : bool
    {
        $this->db->query("begin");

        if ($isCommitable = ($this->db->errno == 0)) {
            $this->db->insertUpdate(
                'points',
                    array (
                    'pnt_id' => $this->id ?? null,
                    'pnt_name' => $this->name,
                    'pnt_kind' => $this->kind,
                    'pnt_visible' => $this->isVisible,
                    'pnt_lat' => $this->lat,
                    'pnt_lng' => $this->lng,
                    'pnt_dflt_short' =>  $this->defaultSummary
                )
            );
            if (empty($this->id)) {
                $this->id = $this->db->insert_id;
            }
        }

        if ($isCommitable = $isCommitable && ($this->db->errno == 0)) {
            $this->db->insertUpdate(
                'pmetadata',
                array (
                    'pmeta_id' => $this->metaRecId ?? null,
                    'pmeta_pnt_id' => $this->id,
                    'pmeta_loc_accuracy' => $this->locationAccuracy,
                    'pmeta_creator' => empty($this->creatorId) ? $userId : $this->creatorId,
                    'pmeta_editor' => $userId,
                    'pmeta_create_date' => $this->createDate,
                    'pmeta_edit_date' => $this->now,
                    'pmeta_extids' => $this->extIdsObj->getAllUrlsSpanned(),
                    'pmeta_pleiades' => (int)$this->extIdsObj->getId('pleiades'),
                    'pmeta_livius' => $this->extIdsObj->getId('livius'),
                    'pmeta_romaq' =>  (int)$this->extIdsObj->getId('romaq'),
                    'pmeta_dare' => (int)$this->extIdsObj->getId('dare'),
                    'pmeta_mithraeum' => $this->extIdsObj->getId('mithraeum'),
                    'pmeta_startyr' => $this->startYear,
                    'pmeta_endyr' => $this->endYear,
                    'pmeta_startyr_str' => $this->startYearStr,
                    'pmeta_endyr_str' => $this->endYearStr
                )
            );
        }

        if ($isCommitable = $isCommitable && ($this->db->errno == 0)) {
            $this->db->insertUpdate(
                'psummaries',
                array (
                    'psum_id' => $this->sumRecId ?? null,
                    'psum_pnt_id' => $this->id,
                    'psum_lang' => $this->language,
                    'psum_pnt_name' => $this->title,
                    'psum_short' => $this->summary
                )
            );
        }

        if ($isCommitable = $isCommitable && ($this->db->errno == 0)) {
            $this->db->insertUpdate(
                'ptexts',
                array (
                    'ptxt_id' => $this->txtRecId ?? null,
                    'ptxt_pnt_id' => $this->id,
                    'ptxt_lang' => $this->language,
                    'ptxt_full' => $this->annotation,
                    'ptxt_editor' => $userId,
                    'ptxt_edit_date' => $this->now
                )
            );
        }

        if ($isCommitable && ($this->db->errno == 0)) {
             $this->db->query("commit");
            return true;
        } else {
            $this->db->query("rollback");
            return false;
        }
    }
      
}