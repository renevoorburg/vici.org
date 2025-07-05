<?php

namespace Vici\Model\Site;

use PDO;
use Vici\DB\DBConnector;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\Site\SiteCollection;

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
        return $this->mapRowToSite($row, $id);

    }

    public function getNearbySites(float $lat, float $lng, int $number = 5): SiteCollection
    {
        $number = (int) $number;
        $query = "
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
                6371*acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat))) < ?
            ORDER BY 
                acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat)))
            LIMIT $number
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$lat, $lng, $lat, 25, $lat, $lng, $lat]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
            // let op: $row['pnt_id'] moet als id worden doorgegeven
            $sites[] = $this->mapRowToSite($row, $row['pnt_id']);
        }
        return new SiteCollection($sites);
    }


    private function mapRowToSite(array $row, int $id): Site
    {
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

        $site->images = new Image\ImageCollection();
        $db = $this->db;
        $site->images->setLazyLoader(function(ImageCollection $collection) use ($site, $db) {
            $imageRepo = new Image\ImageRepository($db);
            $images = $imageRepo->findBySite($site->id);
            $collection->addLoadedImages(iterator_to_array($images));
        });
        return $site;
    }

}

