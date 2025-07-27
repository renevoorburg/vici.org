<?php

namespace Vici\Model\Site\Image;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\Site\Image\Image;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\User\UserRepository;
use Vici\Model\License\LicenseRepository;

class ImageRepository
{
    private DBConnector $db;
    private UserRepository $userRepo;
    private LicenseRepository $licenseRepo;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
        $this->licenseRepo = new LicenseRepository($db);
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

    private function mapRowToImage(array $row): Image
    {
        $image = new Image();
        $image->id = (int)$row['img_id'];
        $image->filepath = $row['img_path'];
        $image->isPublished = !(bool)$row['img_hide'];

        $image->isOwnWork = (bool)$row['imgd_ownwork'];
        $image->source = $row['imgd_source'] ?? '';
        $image->creator = $row['imgd_creator'] ?? '';

        $image->title = $row['imgd_title'] ?? '';
        $image->description = $row['imgd_description'] ?? '';
        $image->language = $row['imgd_lang'] ?? '';
        $image->md5sum = $row['imgd_md5sum'] ?? '';
        $image->dateAdded = $row['imgd_date'];
        $image->width = (int)$row['imgd_width'];
        $image->height = (int)$row['imgd_height'];

        $image->data = $row['imgd_data'];
        $image->license = $this->licenseRepo->findById((int)$row['imgd_license']);
        $image->uploader = $this->userRepo->findById((int)$row['imgd_uploader']);
        return $image;
    }
}
