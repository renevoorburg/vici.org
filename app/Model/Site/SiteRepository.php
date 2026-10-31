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
        p.pnt_country,
        p.pnt_place,
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
        $latDelta = 25 / 111.0;
        $lngDelta = min(25 / (111.0 * max(cos(deg2rad($lat)), 0.1)), 180.0);
        $query = self::getBaseSelect() . "
            WHERE pnt_lat BETWEEN ? AND ? AND pnt_lng BETWEEN ? AND ?
            AND 6371*acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat))) < ?
            ORDER BY acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat)))
            LIMIT $number";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $lat - $latDelta, $lat + $latDelta, $lng - $lngDelta, $lng + $lngDelta,
            $lat, $lng, $lat, 25, $lat, $lng, $lat
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
            $sites[] = $this->mapRowToSite($row, $row['pnt_id']);
        }
        return new SiteCollection($sites);
    }

    public function findRelevantMuseums(float $lat, float $lng): SiteCollection
    {
        $latDelta = 25 / 111.0;
        $lngDelta = min(25 / (111.0 * max(cos(deg2rad($lat)), 0.1)), 180.0);
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
                    pnt_lat BETWEEN ? AND ? AND pnt_lng BETWEEN ? AND ? AND
                    6371*acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat))) < ?
            ) 
            ORDER BY acos(cos(radians(?))*cos(radians(pnt_lat))*cos(radians(pnt_lng)-radians(?))+sin(radians(?))*sin(radians(pnt_lat)))"
        );
        $stmt->execute([
            $lat - $latDelta, $lat + $latDelta, $lng - $lngDelta, $lng + $lngDelta,
            $lat, $lng, $lat, 25, $lat, $lng, $lat
        ]);
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
            LIMIT 500
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

    public function findRecentlyAdded(): SiteCollection
    {
        $stmt = $this->db->prepare(
            self::getBaseSelect() . "
            WHERE pnt_hide=0
            ORDER BY m.pmeta_create_date DESC
            LIMIT 50"
        );
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sites = [];
        foreach ($result as $row) {
            $sites[] = $this->mapRowToSite($row, $row['pnt_id']);
        }
        return new SiteCollection($sites);
    }

    public function findRecentlyChanged(): SiteCollection
    {
        $stmt = $this->db->prepare(
            self::getBaseSelect() . "
            WHERE pnt_hide=0 AND DATE(m.pmeta_create_date) != DATE(m.pmeta_edit_date)
            ORDER BY m.pmeta_edit_date DESC
            LIMIT 50"
        );
        $stmt->execute();
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

        $site->toponym = new Toponym($row['pnt_country'] ?? '', $row['pnt_place'] ?? '');

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

    /**
     * Persist a site edit in one transaction over points, pmetadata,
     * psummaries and ptexts (same write model as the legacy editor).
     * Rows are only updated when values actually changed; editor and
     * edit_date fields are only bumped on a real change.
     *
     * @return int the site id
     */
    public function save(\Vici\Model\Site\Edit\SiteFormData $data, int $userId, string $lang): int
    {
        $this->db->beginTransaction();
        try {
            $this->savePoint($data);
            $id = (int)$data->id;
            $changed = $this->saveMetadata($id, $data, $userId);
            $changed = $this->saveSummary($id, $data, $lang) || $changed;
            $changed = $this->saveText($id, $data, $userId, $lang) || $changed;
            if ($changed && $data->id !== null) {
                $this->touchEditStamp($id, $userId);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
        return $id;
    }

    /**
     * Bump pmeta_editor/edit_date so the site shows up in 'recently changed'.
     * Called when any part of the site data changed, and after uploads.
     */
    public function touchEditStamp(int $siteId, int $userId): void
    {
        $this->db->prepare("UPDATE pmetadata SET pmeta_editor = :editor, pmeta_edit_date = NOW() WHERE pmeta_pnt_id = :id")
            ->execute(['editor' => $userId, 'id' => $siteId]);
    }

    private function savePoint(\Vici\Model\Site\Edit\SiteFormData $data): bool
    {
        $geocoder = new \Vici\Service\ReverseGeocoder((float)$data->lat, (float)$data->lng);
        $values = [
            'pnt_name' => $data->title,
            'pnt_kind' => $data->kind,
            'pnt_visible' => $data->isVisible,
            'pnt_lat' => $data->lat,
            'pnt_lng' => $data->lng,
            'pnt_dflt_short' => $data->summary,
            'pnt_country' => $geocoder->getCountryCode() ?: null,
            'pnt_place' => $geocoder->getNearbyPlace() ?: null,
        ];

        if ($data->id === null) {
            $stmt = $this->db->prepare(
                "INSERT INTO points (pnt_name, pnt_kind, pnt_visible, pnt_lat, pnt_lng, pnt_dflt_short, pnt_country, pnt_place)
                 VALUES (:pnt_name, :pnt_kind, :pnt_visible, :pnt_lat, :pnt_lng, :pnt_dflt_short, :pnt_country, :pnt_place)"
            );
            $stmt->execute($values);
            $data->id = (int)$this->db->lastInsertId();
            return true;
        }

        return $this->updateIfChanged('points', $values, 'pnt_id = :id', ['id' => $data->id]);
    }

    private function saveMetadata(int $siteId, \Vici\Model\Site\Edit\SiteFormData $data, int $userId): bool
    {
        $values = [
            'pmeta_loc_accuracy' => $data->locationAccuracy,
            'pmeta_extids' => $data->extIds->getAllUrlsSpanned(),
            'pmeta_pleiades' => (int)$data->extIds->getId('pleiades') ?: null,
            'pmeta_livius' => $data->extIds->getId('livius') ?: null,
            'pmeta_romaq' => (int)$data->extIds->getId('romaq') ?: null,
            'pmeta_dare' => (int)$data->extIds->getId('dare') ?: null,
            'pmeta_mithraeum' => $data->extIds->getId('mithraeum') ?: null,
            'pmeta_startyr' => $data->startYear,
            'pmeta_endyr' => $data->endYear,
            'pmeta_startyr_str' => $data->startYearStr,
            'pmeta_endyr_str' => $data->endYearStr,
        ];

        $stmt = $this->db->prepare("SELECT pmeta_id, pmeta_creator FROM pmetadata WHERE pmeta_pnt_id = :id LIMIT 1");
        $stmt->execute(['id' => $siteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $values['pmeta_pnt_id'] = $siteId;
            $values['pmeta_creator'] = $userId;
            $values['pmeta_editor'] = $userId;
            $values['pmeta_create_date'] = date('Y-m-d H:i:s');
            $values['pmeta_edit_date'] = date('Y-m-d H:i:s');
            $cols = implode(', ', array_keys($values));
            $placeholders = ':' . implode(', :', array_keys($values));
            $this->db->prepare("INSERT INTO pmetadata ($cols) VALUES ($placeholders)")->execute($values);
            return true;
        }

        return $this->updateIfChanged('pmetadata', $values, 'pmeta_id = :id', ['id' => $row['pmeta_id']]);
    }

    private function saveSummary(int $siteId, \Vici\Model\Site\Edit\SiteFormData $data, string $lang): bool
    {
        $values = [
            'psum_pnt_name' => $data->title,
            'psum_short' => $data->summary,
        ];
        $where = 'psum_pnt_id = :pnt_id AND psum_lang = :lang';
        $params = ['pnt_id' => $siteId, 'lang' => $lang];

        if ($this->rowExists('psummaries', $where, $params)) {
            return $this->updateIfChanged('psummaries', $values, $where, $params);
        }
        $this->db->prepare(
            "INSERT INTO psummaries (psum_pnt_id, psum_lang, psum_pnt_name, psum_short)
             VALUES (:pnt_id, :lang, :psum_pnt_name, :psum_short)"
        )->execute($params + $values);
        return true;
    }

    private function saveText(int $siteId, \Vici\Model\Site\Edit\SiteFormData $data, int $userId, string $lang): bool
    {
        $values = ['ptxt_full' => $data->annotation];
        $where = 'ptxt_pnt_id = :pnt_id AND ptxt_lang = :lang';
        $params = ['pnt_id' => $siteId, 'lang' => $lang];

        if ($this->rowExists('ptexts', $where, $params)) {
            $changed = $this->updateIfChanged('ptexts', $values, $where, $params);
            if ($changed) {
                $this->db->prepare("UPDATE ptexts SET ptxt_editor = :editor, ptxt_edit_date = NOW() WHERE $where")
                    ->execute(['editor' => $userId] + $params);
            }
            return $changed;
        }
        $this->db->prepare(
            "INSERT INTO ptexts (ptxt_pnt_id, ptxt_lang, ptxt_full, ptxt_editor, ptxt_edit_date)
             VALUES (:pnt_id, :lang, :ptxt_full, :editor, NOW())"
        )->execute($params + $values + ['editor' => $userId]);
        return true;
    }

    private function rowExists(string $table, string $where, array $params): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM $table WHERE $where LIMIT 1");
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    /**
     * UPDATE only the columns whose values differ from the current row.
     * Returns true when an update was executed.
     */
    private function updateIfChanged(string $table, array $values, string $where, array $params): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE $where LIMIT 1");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $changed = [];
        foreach ($values as $col => $value) {
            $current = $row[$col];
            if ((string)$current !== (string)$value) {
                if (!($current === null && ($value === null || $value === ''))) {
                    $changed[$col] = $value;
                }
            }
        }

        if (count($changed) === 0) {
            return false;
        }

        $set = implode(', ', array_map(fn($col) => "$col = :set_$col", array_keys($changed)));
        $bind = [];
        foreach ($changed as $col => $value) {
            $bind['set_' . $col] = $value;
        }
        $this->db->prepare("UPDATE $table SET $set WHERE $where")->execute($bind + $params);
        return true;
    }

}
