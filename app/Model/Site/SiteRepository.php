<?php

namespace Vici\Model\Site;

use PDO;
use Vici\DB\DBConnector;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\Site\Image\ImageRepository;
use Vici\Model\Site\Identifier\IdentifierCollection;
use Vici\Model\Site\Identifier\IdentifierRepository;
use Vici\Model\Site\Line\LineCollection;
use Vici\Model\Site\Line\LineRepository;
use Vici\Model\Site\SiteCollection;
use Vici\Model\User\UserRepository;
use Vici\Model\License\LicenseRepository;
use Vici\Model\Site\SiteTypeRepository;
use Vici\Model\Site\Locale\LocaleCollection;
use Vici\Model\Site\Locale\LocaleRepository;

class SiteRepository
{
    private const BASE_SELECT = "   
        SELECT DISTINCT
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
            m.pmeta_endyr_str AS endQualifier,
            m.pmeta_creator AS creator,
            m.pmeta_editor AS updater,
            m.pmeta_create_date AS createDate,
            m.pmeta_edit_date AS updateDate
        FROM points p    
        LEFT JOIN pmetadata m ON p.pnt_id = m.pmeta_pnt_id 
        ";

    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Site
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . " WHERE p.pnt_id = :id"
        );
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
        $query = self::BASE_SELECT . "
            WHERE 6371*acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat))) < ?
            ORDER BY acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat)))
            LIMIT $number";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$lat, $lng, $lat, 25, $lat, $lng, $lat]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
            $sites[] = $this->mapRowToSite($row, $row['pnt_id']);
        }
        return new SiteCollection($sites);
    }

    public function getRelevantMuseums(float $lat, float $lng): SiteCollection
    {
        $stmt = $this->db->prepare(
            self::BASE_SELECT . "
            JOIN pnt_img_lnk l ON l.pil_pnt = p.pnt_id
            WHERE p.pnt_kind = 8 AND l.pil_img IN (
                SELECT pil_img  
                FROM points
                JOIN pmetadata ON pnt_id = pmeta_pnt_id
                JOIN pnt_img_lnk ON pil_pnt=pnt_id
                WHERE 
                    pnt_hide=0 AND 
                    pnt_kind != 8 AND 
                    6371*acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat))) < ?
            ) 
            ORDER BY acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat)))"
        );
        $stmt->execute([$lat, $lng, $lat, 25, $lat, $lng, $lat]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
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

        $site->period = new Period();
        $site->period->startYear = $row['startYear'];
        $site->period->endYear = $row['endYear'];
        $site->period->startQualifier = $row['startQualifier'];
        $site->period->endQualifier = $row['endQualifier'];

        $localeRepo = new LocaleRepository($this->db);
        $locales = $localeRepo->getBySiteId($id);
        $site->locales = new LocaleCollection($locales, $site->defaultTitle, $site->defaultSummary);

        $site->toponym = new Toponym($site->representativeLocation->latitude, $site->representativeLocation->longitude);

        $db = $this->db;

        $site->images = new ImageCollection();
        $site->images->setLazyLoader(function(ImageCollection $collection) use ($site, $db) {
            $imageRepo = new ImageRepository($db);
            $images = $imageRepo->findBySite($site->id);
            $collection->addLoadedItems(iterator_to_array($images));
        });

        $site->identifiers = new IdentifierCollection();
        $site->identifiers->setLazyLoader(function(IdentifierCollection $collection) use ($site, $db) {
            $identifierRepo = new IdentifierRepository($db);
            $identifiers = $identifierRepo->findBySite($site->id);
            $collection->addLoadedItems(iterator_to_array($identifiers));
        });

        $site->lines = new LineCollection();
        $site->lines->setLazyLoader(function(LineCollection $collection) use ($site, $db) {
            $lineRepo = new LineRepository($db);
            $lines = $lineRepo->getLinesForSite($site->id);
            $collection->addLoadedItems(iterator_to_array($lines));

            $userRepo = new UserRepository($db);
            $licenseRepo = new LicenseRepository($db);
            if (count($lines) > 0) {
                $uploader = null;
                $license = null;
                foreach ($lines as $line) {
                    if ($line->uploaderId !== null) {
                        $uploader = $userRepo->findById($line->uploaderId);
                        $license = $licenseRepo->findById($line->licenseId);
                        break;
                    }
                }
                $collection->uploader = $uploader;
                $collection->license = $license;
            }
        });

        $userRepo = new UserRepository($this->db);
        $site->creator = $userRepo->findById($row['creator']);
        $site->updater = $userRepo->findById($row['updater']);
        $site->createDate = $row['createDate'];
        $site->updateDate = $row['updateDate'];
        return $site;
    }

}

