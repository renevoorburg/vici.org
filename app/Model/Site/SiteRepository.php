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
    private const BASE_COLUMNS = "
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
    ";

    private static function getBaseSelect(): string {
        return "SELECT DISTINCT " . self::BASE_COLUMNS . 
            " FROM points p LEFT JOIN pmetadata m ON p.pnt_id = m.pmeta_pnt_id ";
    }

    private DBConnector $db;
    private SiteTypeRepository $typeRepo;
    private LocaleRepository $localeRepo;
    private UserRepository $userRepo;
    private LicenseRepository $licenseRepo;
    private IdentifierRepository $identifierRepo;
    private LineRepository $lineRepo;
    private ?ImageRepository $imageRepo = null;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
        $this->typeRepo = new SiteTypeRepository($this->db);
        $this->localeRepo = new LocaleRepository($this->db);
        $this->userRepo = new UserRepository($this->db);
        $this->licenseRepo = new LicenseRepository($this->db);
        $this->identifierRepo = new IdentifierRepository($this->db);
        $this->lineRepo = new LineRepository($this->db);
        /* imageRepo is not set at this point to prevent circular dependency with SiteRepository */
    }

    public function findById(int $id): ?Site
    {
        $stmt = $this->db->prepare(
            self::getBaseSelect() . " WHERE p.pnt_id = :id"
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

    public function findNearbySites(float $lat, float $lng, int $number = 5): SiteCollection
    {
        $number = (int) $number;
        $query = self::getBaseSelect() . "
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

    public function findRelevantMuseums(float $lat, float $lng): SiteCollection
    {
        $stmt = $this->db->prepare(
            self::getBaseSelect() . "
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

    public function findByImage(int $imageId, bool $isPublished = true): SiteCollection
    {
        $stmt = $this->db->prepare(
            self::getBaseSelect() . " 
            WHERE pnt_id IN (SELECT pil_pnt FROM pnt_img_lnk WHERE pil_img = :imageId)"
        );
        $stmt->execute(['imageId' => $imageId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
            $sites[] = $this->mapRowToSite($row, $row['pnt_id']);
        }
        return new SiteCollection($sites);
    }

    public function search(string $query): SiteCollection
    {
        $query = "nehalennia";

        $sql = "
            SELECT 
                " . self::BASE_COLUMNS . ",
                rs.relevance_score
            FROM points p
            LEFT JOIN pmetadata m ON p.pnt_id = m.pmeta_pnt_id
            INNER JOIN (
                SELECT pnt_id,
                    (
                        MATCH (pnt_name, pnt_dflt_short) AGAINST (? IN BOOLEAN MODE) * 4 +
                        MATCH (psum_short, psum_pnt_name) AGAINST (? IN BOOLEAN MODE) * 2 +
                        MATCH (ptxt_full) AGAINST (? IN BOOLEAN MODE) +
                        MATCH (imgd_title, imgd_description) AGAINST (? IN BOOLEAN MODE)
                    ) AS relevance_score
                FROM points p
                LEFT JOIN ptexts ON pnt_id = ptxt_pnt_id 
                LEFT JOIN psummaries ON pnt_id = psum_pnt_id 
                LEFT JOIN pmetadata m ON pnt_id = pmeta_pnt_id 
                LEFT JOIN pnt_img_lnk ON pnt_id = pil_pnt
                LEFT JOIN img_data ON pil_img = imgd_imgid
                WHERE pnt_hide=0  AND (
                    MATCH (pnt_name, pnt_dflt_short) AGAINST (? IN BOOLEAN MODE)
                    OR MATCH (psum_short, psum_pnt_name) AGAINST (? IN BOOLEAN MODE)
                    OR MATCH (ptxt_full) AGAINST (? IN BOOLEAN MODE)
                    OR MATCH (imgd_title, imgd_description) AGAINST (? IN BOOLEAN MODE)
                )
                GROUP BY pnt_id
            ) rs ON p.pnt_id = rs.pnt_id
            ORDER BY rs.relevance_score DESC
            LIMIT 100
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $query, $query, $query, $query, $query, $query]);
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

        $site->type = $this->typeRepo->getById((int)$row['pnt_kind']);

        $site->representativeLocation = new Point();
        $site->representativeLocation->latitude = (float)$row['pnt_lat'];
        $site->representativeLocation->longitude = (float)$row['pnt_lng'];
        $site->representativeLocation->qualifier = $row['locationAccuracy'] ?? '';  # 'fixes' some rare ? data errors

        $site->period = new Period();
        $site->period->startYear = $row['startYear'];
        $site->period->endYear = $row['endYear'];
        $site->period->startQualifier = $row['startQualifier'];
        $site->period->endQualifier = $row['endQualifier'];

        $locales = $this->localeRepo->getBySiteId($id);
        $site->locales = new LocaleCollection($locales, $site->defaultTitle, $site->defaultSummary);

        $site->toponym = new Toponym($site->representativeLocation->latitude, $site->representativeLocation->longitude);

        $identifierRepo = $this->identifierRepo;
        $lineRepo = $this->lineRepo;
        $userRepo = $this->userRepo;
        $licenseRepo = $this->licenseRepo;
        $imageRepo = $this->getImageRepo();

        $site->images = new ImageCollection();
        $site->images->setLazyLoader(function(ImageCollection $collection) use ($site, $imageRepo) {
            $images = $imageRepo->findBySite($site->id);
            $collection->addLoadedItems(iterator_to_array($images));
        });

        $site->identifiers = new IdentifierCollection();
        $site->identifiers->setLazyLoader(function(IdentifierCollection $collection) use ($site, $identifierRepo) {
            $identifiers = $identifierRepo->findBySite($site->id);
            $collection->addLoadedItems(iterator_to_array($identifiers));
        });

        $site->lines = new LineCollection();
        $site->lines->setLazyLoader(function(LineCollection $collection) use ($site, $lineRepo, $userRepo, $licenseRepo) {
            $lines = $lineRepo->getLinesForSite($site->id);
            $collection->addLoadedItems(iterator_to_array($lines));

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

        $site->creator = $userRepo->findById($row['creator']);
        $site->updater = $userRepo->findById($row['updater']);
        $site->createDate = $row['createDate'];
        $site->updateDate = $row['updateDate'];
        return $site;
    }

    private function getImageRepo(): ImageRepository
    {
        if ($this->imageRepo === null) {
            $this->imageRepo = new ImageRepository($this->db);
        }
        return $this->imageRepo;
    }   

}
