<?php

namespace Vici\Model\Site;

use Vici\DB\DBConnector;
use PDO;

class SiteRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    /**
     * Haal een Site op met alle locales en type (nog zonder images/lines)
     */
    public function getById(int $id): ?Site
    {
        // Haal basisdata uit points
        $stmt = $this->db->prepare("
            SELECT 
                p.pnt_kind, 
                p.pnt_lat, 
                p.pnt_lng, 
                p.pnt_visible AS visibility, 
                p.pnt_hide AS isPublished, 
                m.pmeta_loc_accuracy AS locationAccuracy,
                m.pmeta_startyr AS startYear, 
                m.pmeta_endyr AS endYear,
                m.pmeta_startyr_str AS startQualifier,
                m.pmeta_endyr_str AS endQualifier 
            FROM 
                points p    
            LEFT JOIN 
                pmetadata m ON p.pnt_id = m.pmeta_pnt_id 
            WHERE 
                p.pnt_id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // Haal type op
        $typeRepo = new SiteTypeRepository($this->db);
        $type = $typeRepo->getById((int)$row['pnt_kind']);


        // Haal alle SiteLocales op via SiteLocaleRepository (associatieve array per taal)
        $localeRepo = new SiteLocaleRepository($this->db);
        $locales = $localeRepo->getBySiteId($id);

        // Maak Site object
        $site = new Site();
        $site->id = (int)$row['pnt_id'];
        $site->type = $type;
        $site->representativeLocation = new Point();
        $site->representativeLocation->latitude = (float)$row['pnt_lat'];
        $site->representativeLocation->longitude = (float)$row['pnt_lng'];
        $site->representativeLocation->qualifier = $row['locationAccuracy'];
        $site->locales = $locales; // ['nl' => SiteLocale, 'en' => SiteLocale, ...]
       
        $site->period = new Period();
        $site->period->startYear = $row['startYear'];
        $site->period->endYear = $row['endYear'];
        $site->period->startQualifier = $row['startQualifier'];
        $site->period->endQualifier = $row['endQualifier'];
       
       
        $site->images = []; // images later
        return $site;
    }


}
