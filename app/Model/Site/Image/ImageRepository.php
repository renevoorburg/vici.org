<?php

namespace Vici\Model\Site\Image;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\Site\Image\Image;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\Site\SiteCollection;
use Vici\Model\Site\SiteRepository;
use Vici\Model\User\UserRepository;
use Vici\Model\License\LicenseRepository;

class ImageRepository
{
    private DBConnector $db;
    private UserRepository $userRepo;
    private LicenseRepository $licenseRepo;
    private ?SiteRepository $siteRepo = null;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
        $this->licenseRepo = new LicenseRepository($db);
        /* siteRepo is not yet set to prevent circular dependency with ImageRepository */
    }

    public function findById(int $id, bool $isPublished = true ): ?Image
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            WHERE i.img_id = :id AND i.img_hide = :isHidden LIMIT 1
        ");
        $isHidden = !$isPublished;
        $stmt->execute(['id' => $id, 'isHidden' => $isHidden]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToImage($row);
        }
        return null;
    }

    public function findRecentlyAdded(int $limit = 40): ImageCollection
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            WHERE i.img_hide = 0
            ORDER BY d.imgd_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $this->mapRowToImage($row);
        }
        return new ImageCollection($images);
    }

    public function findByUploader(int $userId, int $limit = 10): ImageCollection
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            WHERE i.img_hide = 0 AND d.imgd_uploader = :uid
            ORDER BY d.imgd_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $this->mapRowToImage($row);
        }
        return new ImageCollection($images);
    }

    /**
     * Map of site id => image path for each site's default image.
     * One query for a whole set of sites, used by the recent-tiles pages
     * to avoid a lazy-load per tile.
     */
    public function findDefaultThumbs(array $siteIds): array
    {
        $siteIds = array_values(array_unique(array_map('intval', $siteIds)));
        if (count($siteIds) === 0) {
            return [];
        }
        $in = implode(',', $siteIds);
        $stmt = $this->db->prepare("
            SELECT l.pil_pnt, i.img_path
            FROM pnt_img_lnk l
            JOIN images i ON i.img_id = l.pil_img AND i.img_hide = 0
            WHERE l.pil_dflt = 1 AND l.pil_pnt IN ($in)
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function findBySite(int $siteId, bool $isPublished = true): ImageCollection
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            JOIN pnt_img_lnk l ON i.img_id = l.pil_img 
            WHERE l.pil_pnt = :site_id AND i.img_hide = :isHidden
            ORDER BY i.img_id ASC
        ");
        $isHidden = !$isPublished;
        $stmt->execute(['site_id' => $siteId, 'isHidden' => $isHidden]);
        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $this->mapRowToImage($row);
        }
        return new ImageCollection($images);
    }

    /* minimal info (id, path, title) about hidden images linked to a
       site, for the admin "hidden images" box — no full hydration */
    public function findHiddenBySite(int $siteId): array
    {
        $stmt = $this->db->prepare("
            SELECT i.img_id, i.img_path, d.imgd_title
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            JOIN pnt_img_lnk l ON i.img_id = l.pil_img
            WHERE l.pil_pnt = :site_id AND i.img_hide = 1
            ORDER BY i.img_id ASC
        ");
        $stmt->execute(['site_id' => $siteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* pass the image's current published state; toggles img_hide */
    public function setHidden(int $id, bool $currentlyPublished): void
    {
        $stmt = $this->db->prepare("UPDATE images SET img_hide = :hide WHERE img_id = :id");
        $stmt->execute([
            'hide' => $currentlyPublished ? 1 : 0,
            'id'   => $id,
        ]);
    }

    public function updateMetadata(Image $image): void
    {
        $stmt = $this->db->prepare("
            UPDATE img_data SET
                imgd_title = :title,
                imgd_description = :description,
                imgd_license = :license
            WHERE imgd_imgid = :id
        ");
        $stmt->execute([
            'title'       => mb_substr($image->title, 0, 255),
            'description' => $image->description,
            'license'     => $image->license?->id,
            'id'          => $image->id,
        ]);
    }

    /* title + description only, for trusted users who may not
       touch attribution/creator/license */
    public function updateText(int $id, string $title, string $description): void
    {
        $stmt = $this->db->prepare("
            UPDATE img_data SET
                imgd_title = :title,
                imgd_description = :description
            WHERE imgd_imgid = :id
        ");
        $stmt->execute([
            'title'       => mb_substr($title, 0, 255),
            'description' => $description,
            'id'          => $id,
        ]);
    }

    private function mapRowToImage(array $row): Image
    {
        $image = new Image();
        $image->id = (int)$row['img_id'];
        $image->filepath = $row['img_path'];
        $image->isPublished = !(bool)$row['img_hide'];

        $image->isOwnWork = (bool)$row['imgd_ownwork'];
        $image->source = $row['imgd_source'] ?? '';
        $image->creator = $row['imgd_creator'] ?? '';
        $image->attribution = $row['imgd_attribution'] ?? '';

        $image->title = $row['imgd_title'] ?? '';
        $image->description = $row['imgd_description'] ?? '';
        $image->language = $row['imgd_lang'] ?? '';
        $image->md5sum = $row['imgd_md5sum'] ?? '';
        $image->dateAdded = $row['imgd_date'];
        $image->width = (int)$row['imgd_width'];
        $image->height = (int)$row['imgd_height'];

        $image->license = $this->licenseRepo->findById((int)$row['imgd_license']);
        $image->uploader = $this->userRepo->findById((int)$row['imgd_uploader']);

        $siteRepo = $this->getSiteRepo();
        $image->sites = new SiteCollection();
        $image->sites->setLazyLoader(function(SiteCollection $collection) use ($image, $siteRepo) {
            $sites = $siteRepo->findByImage($image->id);
            $collection->addLoadedItems(iterator_to_array($sites));
        });

        return $image;
    }

    private function getSiteRepo(): SiteRepository
    {
        if ($this->siteRepo === null) {
            $this->siteRepo = new SiteRepository($this->db);
        }
        return $this->siteRepo;
    }

    }
