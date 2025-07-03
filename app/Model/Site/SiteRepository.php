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


    public function getById(int $id): ?Site
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.pnt_id,
                p.pnt_name,
                p.pnt_dflt_short,
                p.pnt_kind, 
                p.pnt_lat, 
                p.pnt_lng, 
                p.pnt_visible AS isVisible, 
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

        $site = new Site();
        $site->id = $id;
        $site->defaultTitle = $row['pnt_name'];
        $site->defaultSummary = $row['pnt_dflt_short'];
        $site->isVisible = (bool)$row['isVisible'];
        $site->isPublished = (bool)$row['isPublished'];

        $typeRepo = new SiteTypeRepository($this->db);
        $site->type = $typeRepo->getById((int)$row['pnt_kind']);


        $site->representativeLocation = new Point();
        $site->representativeLocation->latitude = (float)$row['pnt_lat'];
        $site->representativeLocation->longitude = (float)$row['pnt_lng'];
        $site->representativeLocation->qualifier = $row['locationAccuracy'];

        $localeRepo = new Locale\LocaleRepository($this->db);
        $locales = $localeRepo->getBySiteId($id);
        $site->locales = new Locale\LocaleCollection($locales, $site->defaultTitle, $site->defaultSummary); 

        $geocoder = new \Vici\Service\ReverseGeocoder($site->representativeLocation->latitude, $site->representativeLocation->longitude);
        $site->toponym = $geocoder->resolveToponym('nl');
       
        $site->period = new Period();
        $site->period->startYear = $row['startYear'];
        $site->period->endYear = $row['endYear'];
        $site->period->startQualifier = $row['startQualifier'];
        $site->period->endQualifier = $row['endQualifier'];
       
        $imageRepo = new Image\ImageRepository($this->db);
        $site->images = $imageRepo->findBySite($site->id);
        return $site;
    }

    // public function getNearbySites(float $lat, float $lng, int $radius = 25): array
    // {
    //     $stmt = $this->db->prepare("
    //         SELECT
    //             p.pnt_id,
    //             p.pnt_kind, 
    //             p.pnt_lat, 
    //             p.pnt_lng, 
    //             p.pnt_visible AS isVisible, 
    //             p.pnt_hide AS isPublished, 
    //             m.pmeta_loc_accuracy AS locationAccuracy,
    //             m.pmeta_startyr AS startYear, 
    //             m.pmeta_endyr AS endYear,
    //             m.pmeta_startyr_str AS startQualifier,
    //             m.pmeta_endyr_str AS endQualifier 
    //     FROM 
    //         points p
    //     LEFT JOIN 
    //         pmetadata m ON p.pnt_id = m.pmeta_pnt_id
    //     JOIN pnt_img_lnk ON pil_pnt=pnt_id	
    //     LEFT JOIN ( SELECT * FROM psummaries WHERE psum_lang='nl' ) AS x ON pnt_id = psum_pnt_id
    //     JOIN pkinds ON pkind_id=pnt_kind
    //     WHERE pnt_kind = 8 AND pil_img IN (
	// 	    SELECT pil_img  FROM points
	// 	    JOIN pmetadata ON pnt_id = pmeta_pnt_id
	// 	    JOIN pnt_img_lnk ON pil_pnt=pnt_id	
	// 	    WHERE pnt_hide=0 AND 
	// 	          pnt_kind != 8 AND
	// 	          6371*acos(cos(radians(@lat))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(@lng))+sin(radians(@lat))*sin(radians(pnt_lat))) < @radius
    //     ) ORDER BY acos(cos(radians(@lat))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(@lng))+sin(radians(@lat))*sin(radians(pnt_lat))) ");
    //     $stmt->execute(['lat' => $lat, 'lng' => $lng, 'radius' => $radius]);
    //     $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     return $result;
    // }


}
